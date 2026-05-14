<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;
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
            new Middleware('can:view-products', only: ['index', 'show']),
            new Middleware('can:create-products', only: ['create', 'store']),
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
        $user = auth()->user();
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
        $user = auth()->user();
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
        $user = auth()->user();
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
}
