<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReplacement;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SaleController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $sales = Sale::with('customer', 'user')
            ->latest()
            ->paginate(20);

        return Inertia::render('sales/index', [
            'sales' => $sales,
        ]);
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product', 'customer', 'user', 'replacements');

        return Inertia::render('sales/show', [
            'sale' => $sale,
        ]);
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale deleted successfully.');
    }

    /**
     * Show the refund initiation page
     */
    public function initiateRefund(Sale $sale)
    {
        if (!$sale->isReturnable()) {
            return redirect()->route('sales.show', $sale->id)
                ->with('error', 'This sale cannot be returned (older than 30 days or not completed)');
        }

        $sale->load('items.product', 'customer');

        return Inertia::render('sales/refund', [
            'sale' => $sale,
        ]);
    }

    /**
     * Process a full refund
     */
    public function refund(Sale $sale)
    {
        $this->authorize('update', $sale);

        if (!$sale->isReturnable()) {
            return redirect()->back()->with(
                'error',
                'Only completed sales within 30 days can be refunded'
            );
        }

        try {
            $sale->refund();

            return redirect()->route('sales.show', $sale->id)
                ->with('success', "Sale {$sale->sale_number} has been fully refunded");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error processing refund: ' . $e->getMessage());
        }
    }

    /**
     * Show exchange initiation page
     */
    public function initiateExchange(Sale $sale)
    {
        if (!$sale->isReturnable()) {
            return redirect()->route('sales.show', $sale->id)
                ->with('error', 'This sale cannot be exchanged (older than 30 days or not completed)');
        }

        $sale->load('items.product');
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'selling_price', 'quantity', 'unit']);

        return Inertia::render('sales/exchange', [
            'sale' => $sale,
            'availableProducts' => $products,
        ]);
    }

    /**
     * Process an exchange or partial refund
     */
    public function processExchange(Request $request, Sale $sale)
    {
        $this->authorize('update', $sale);

        if (!$sale->isReturnable()) {
            return redirect()->back()
                ->with('error', 'Sale is not returnable');
        }

        $validated = $request->validate([
            'returned_item_ids' => 'required|array|min:1',
            'returned_item_ids.*' => 'exists:sale_items,id',
            'new_items' => 'required|array|min:1',
            'new_items.*.product_id' => 'required|exists:products,id',
            'new_items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|in:defective,wrong_size,wrong_item,customer_request,damaged',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $replacement = DB::transaction(function () use ($sale, $validated, $request) {
                // Step 1: Calculate values
                $returnedValue = $sale->items()
                    ->whereIn('id', $validated['returned_item_ids'])
                    ->get()
                    ->sum(function ($item) {
                        return $item->quantity * $item->unit_price;
                    });

                $newValue = 0;
                $newItemsWithPrice = [];

                foreach ($validated['new_items'] as $newItem) {
                    $product = Product::findOrFail($newItem['product_id']);
                    $itemTotal = $product->selling_price * $newItem['quantity'];
                    $newValue += $itemTotal;
                    $newItemsWithPrice[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $newItem['quantity'],
                        'selling_price' => $product->selling_price,
                    ];
                }

                $difference = $returnedValue - $newValue;
                $refundAmount = $difference > 0 ? $difference : 0;
                $additionalCharge = $difference < 0 ? abs($difference) : 0;

                // Step 2: Restore returned stock
                $returnedItemsWithData = [];
                foreach ($validated['returned_item_ids'] as $saleItemId) {
                    $item = $sale->items()->findOrFail($saleItemId);
                    $item->product->increment('quantity', $item->quantity);
                    $returnedItemsWithData[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ];
                }

                // Step 3: Validate and decrement new stock
                foreach ($validated['new_items'] as $newItem) {
                    $product = Product::findOrFail($newItem['product_id']);
                    if ($product->quantity < $newItem['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                    $product->decrement('quantity', $newItem['quantity']);
                }

                // Step 4: Create replacement record
                $replacement = SaleReplacement::create([
                    'original_sale_id' => $sale->id,
                    'returned_items' => $returnedItemsWithData,
                    'new_items' => $newItemsWithPrice,
                    'refund_amount' => $refundAmount,
                    'additional_charge' => $additionalCharge,
                    'reason' => $validated['reason'],
                    'notes' => $validated['notes'],
                    'processed_by' => $request->user()->id,
                ]);

                // Step 5: Log activity
                activity()
                    ->causedBy($request->user())
                    ->performedOn($sale)
                    ->withProperties([
                        'returned_value' => $returnedValue,
                        'new_value' => $newValue,
                        'net_difference' => $difference,
                        'reason' => $validated['reason'],
                        'refund_amount' => $refundAmount,
                        'additional_charge' => $additionalCharge,
                    ])
                    ->log('sale_exchanged');

                return $replacement;
            });

            $message = match (true) {
                $replacement->isRefund() => "Exchange completed. Refund: GH₵" . number_format($replacement->refund_amount, 2),
                $replacement->isUpgrade() => "Exchange completed. Additional charge: GH₵" . number_format($replacement->additional_charge, 2),
                $replacement->isEvenExchange() => "Even exchange completed.",
                default => "Exchange processed."
            };

            return redirect()->route('sales.show', $sale->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error processing exchange: ' . $e->getMessage());
        }
    }
}
