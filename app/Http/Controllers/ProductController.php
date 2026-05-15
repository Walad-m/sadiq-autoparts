<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;
use App\Support\CsvImportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * @method \Illuminate\Contracts\Auth\Authenticatable|null user()
 */
class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view-products', only: ['index', 'show', 'export']),
            new Middleware('can:create-products', only: ['create', 'store', 'import']),
            new Middleware('can:edit-products', only: ['edit', 'update']),
            new Middleware('can:delete-products', only: ['destroy']),
        ];
    }
    public function index()
    {
        $products = Product::with('category', 'supplier')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('products/index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return Inertia::render('products/create', [
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $user = Auth::user();
        $product = Product::create($request->validated());

        // Log product creation
        activity()
            ->causedBy($user)
            ->performedOn($product)
            ->withProperties($request->validated())
            ->log('product_created');

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'supplier');

        return Inertia::render('products/show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return Inertia::render('products/edit', [
            'product' => $product,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $user = Auth::user();
        $oldData = $product->getAttributes();
        $product->update($request->validated());

        // Log product update
        activity()
            ->causedBy($user)
            ->performedOn($product)
            ->withProperties([
                'old' => $oldData,
                'new' => $request->validated(),
            ])
            ->log('product_updated');

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $user = Auth::user();
        $productData = $product->getAttributes();
        $product->delete();

        // Log product deletion
        activity()
            ->causedBy($user)
            ->withProperties($productData)
            ->log('product_deleted');

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function export()
    {
        $products = Product::with('category', 'supplier')
            ->orderBy('name')
            ->get()
            ->map(static function (Product $product): array {
                return [
                    $product->name,
                    $product->description,
                    $product->part_number,
                    $product->category?->name,
                    $product->supplier?->name,
                    $product->unit,
                    $product->cost_price,
                    $product->selling_price,
                    $product->quantity,
                    $product->reorder_level,
                    $product->is_active ? '1' : '0',
                ];
            });

        return CsvImportExport::download('products.csv', [
            'name',
            'description',
            'part_number',
            'category',
            'supplier',
            'unit',
            'cost_price',
            'selling_price',
            'quantity',
            'reorder_level',
            'is_active',
        ], $products);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $user = Auth::user();
        $imported = 0;

        DB::transaction(function () use (&$imported, $request, $user): void {
            $imported = CsvImportExport::import($request->file('file'), function (array $row, int $line) use ($user): void {
                $name = trim((string) ($row['name'] ?? ''));

                if ($name === '') {
                    throw new \InvalidArgumentException("Missing product name on CSV line {$line}.");
                }

                $categoryName = trim((string) ($row['category'] ?? ''));

                if ($categoryName === '') {
                    throw new \InvalidArgumentException("Missing product category on CSV line {$line}.");
                }

                $category = Category::firstOrCreate(['name' => $categoryName]);

                $supplierName = trim((string) ($row['supplier'] ?? ''));
                $supplier = $supplierName !== '' ? Supplier::firstOrCreate(['name' => $supplierName]) : null;

                $payload = [
                    'name' => $name,
                    'description' => $this->nullIfEmpty($row['description'] ?? null),
                    'part_number' => $this->nullIfEmpty($row['part_number'] ?? null),
                    'category_id' => $category->id,
                    'supplier_id' => $supplier?->id,
                    'unit' => $this->normalizeUnit($row['unit'] ?? null),
                    'cost_price' => $this->normalizeNumber($row['cost_price'] ?? null),
                    'selling_price' => $this->normalizeNumber($row['selling_price'] ?? null),
                    'quantity' => $this->normalizeInteger($row['quantity'] ?? null),
                    'reorder_level' => $this->normalizeInteger($row['reorder_level'] ?? null),
                    'is_active' => $this->normalizeBoolean($row['is_active'] ?? null),
                ];

                $product = $payload['part_number'] !== null
                    ? Product::updateOrCreate(['part_number' => $payload['part_number']], $payload)
                    : Product::updateOrCreate(['name' => $payload['name']], $payload);

                activity()
                    ->causedBy($user)
                    ->performedOn($product)
                    ->withProperties([
                        'source' => 'csv_import',
                        'line' => $line,
                    ])
                    ->log('product_imported');
            });
        });

        return redirect()
            ->route('products.index')
            ->with('success', "Imported {$imported} products successfully.");
    }

    private function normalizeNumber(mixed $value): float
    {
        return (float) str_replace(',', '', trim((string) $value));
    }

    private function normalizeInteger(mixed $value): int
    {
        return (int) round((float) str_replace(',', '', trim((string) $value)));
    }

    private function normalizeBoolean(mixed $value): bool
    {
        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'active', 'on'], true);
    }

    private function normalizeUnit(mixed $value): string
    {
        $unit = strtolower(trim((string) $value));

        return in_array($unit, ['piece', 'litre', 'set', 'pair', 'box'], true) ? $unit : 'piece';
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
