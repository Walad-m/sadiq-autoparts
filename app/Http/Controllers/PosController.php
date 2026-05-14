<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * @method \Illuminate\Contracts\Auth\Authenticatable|null user()
 */
class PosController extends Controller
{
    /**
     * Show the POS interface.
     */
    public function index()
    {
        $products = Product::where('is_active', true)
            ->where('quantity', '>', 0)
            ->with('category')
            ->orderBy('name')
            ->get(['id', 'name', 'part_number', 'selling_price', 'quantity', 'unit', 'category_id']);

        $customers = Customer::orderBy('name')
            ->get(['id', 'name', 'phone']);

        // Daily stats
        $today = now()->startOfDay();
        $todayRevenue = Sale::where('created_at', '>=', $today)
            ->where('status', 'completed')
            ->sum('total');
        $todaySalesCount = Sale::where('created_at', '>=', $today)
            ->where('status', 'completed')
            ->count();

        return Inertia::render('pos/index', [
            'products'        => $products,
            'customers'       => $customers,
            'todayRevenue'    => $todayRevenue,
            'todaySalesCount' => $todaySalesCount,
        ]);
    }

    /**
     * Search products for autocomplete.
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $products = Product::where('is_active', true)
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('part_number', 'like', "%{$query}%");
            })
            ->with('category')
            ->limit(20)
            ->get(['id', 'name', 'part_number', 'selling_price', 'quantity', 'unit', 'category_id']);

        return response()->json($products);
    }

    /**
     * Process the sale.
     */
    public function store(StoreSaleRequest $request)
    {
        $validated = $request->validated();

        $sale = DB::transaction(function () use ($validated, $request) {
            // Generate unique sale number
            $saleNumber = 'SAL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }

            $discount = $validated['discount'] ?? 0;
            $total = $subtotal - $discount;

            $sale = Sale::create([
                'sale_number'     => $saleNumber,
                'customer_id'     => $validated['customer_id'] ?? null,
                'user_id'         => $request->user()->id,
                'payment_method'  => $validated['payment_method'],
                'momo_reference'  => $validated['momo_reference'] ?? null,
                'subtotal'        => $subtotal,
                'discount'        => $discount,
                'total'           => $total,
                'amount_tendered' => $validated['amount_tendered'] ?? null,
                'change_given'    => $validated['change_given'] ?? null,
                'status'          => 'completed',
                'notes'           => $validated['notes'] ?? null,
            ]);

            // Create sale items and decrement stock
            foreach ($validated['items'] as $item) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['unit_price'] * $item['quantity'],
                ]);

                // Decrement product stock
                Product::where('id', $item['product_id'])
                    ->decrement('quantity', $item['quantity']);
            }

            return $sale;
        });

        // Load relationships for receipt
        $sale->load('items.product', 'customer', 'user');

        // Log the sale activity
        $user = auth()->user();
        activity()
            ->causedBy($user)
            ->performedOn($sale)
            ->withProperties([
                'sale_number' => $sale->sale_number,
                'items_count' => count($validated['items']),
                'subtotal' => $sale->subtotal,
                'discount' => $sale->discount,
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
            ])
            ->log('sale_completed');

        return redirect()->route('pos.receipt', $sale->id);
    }

    /**
     * Show the receipt page after a sale.
     */
    public function receipt(Sale $sale)
    {
        $sale->load('items.product', 'customer', 'user');

        return Inertia::render('pos/receipt', [
            'sale' => $sale,
        ]);
    }
}
