<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * @method \Illuminate\Contracts\Auth\Authenticatable|null user()
 */
class SupplierController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view-suppliers', only: ['index']),
            new Middleware('can:create-suppliers', only: ['create', 'store']),
            new Middleware('can:edit-suppliers', only: ['edit', 'update']),
            new Middleware('can:delete-suppliers', only: ['destroy']),
        ];
    }
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(20);

        return Inertia::render('suppliers/index', [
            'suppliers' => $suppliers,
        ]);
    }

    public function create()
    {
        return Inertia::render('suppliers/create');
    }

    public function store(StoreSupplierRequest $request)
    {
        $user = auth()->user();
        $supplier = Supplier::create($request->validated());

        // Log supplier creation
        activity()
            ->causedBy($user)
            ->performedOn($supplier)
            ->withProperties($request->validated())
            ->log('supplier_created');

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('suppliers/edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier)
    {
        $user = auth()->user();
        $oldData = $supplier->getAttributes();
        $supplier->update($request->validated());

        // Log supplier update
        activity()
            ->causedBy($user)
            ->performedOn($supplier)
            ->withProperties([
                'old' => $oldData,
                'new' => $request->validated(),
            ])
            ->log('supplier_updated');

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $user = auth()->user();
        $supplierData = $supplier->getAttributes();
        $supplier->delete();

        // Log supplier deletion
        activity()
            ->causedBy($user)
            ->withProperties($supplierData)
            ->log('supplier_deleted');

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
