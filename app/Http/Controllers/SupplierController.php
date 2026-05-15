<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use App\Support\CsvImportExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            new Middleware('can:view-suppliers', only: ['index', 'export']),
            new Middleware('can:create-suppliers', only: ['create', 'store', 'import']),
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
        $user = Auth::user();
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
        $user = Auth::user();
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
        $user = Auth::user();
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

    public function export()
    {
        $suppliers = Supplier::orderBy('name')
            ->get()
            ->map(static fn (Supplier $supplier): array => [
                $supplier->name,
                $supplier->contact_person,
                $supplier->phone,
                $supplier->email,
                $supplier->address,
                $supplier->notes,
            ]);

        return CsvImportExport::download('suppliers.csv', [
            'name',
            'contact_person',
            'phone',
            'email',
            'address',
            'notes',
        ], $suppliers);
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
                    throw new \InvalidArgumentException("Missing supplier name on CSV line {$line}.");
                }

                $supplier = Supplier::updateOrCreate(
                    ['name' => $name],
                    [
                        'contact_person' => $this->nullIfEmpty($row['contact_person'] ?? null),
                        'phone' => $this->nullIfEmpty($row['phone'] ?? null),
                        'email' => $this->nullIfEmpty($row['email'] ?? null),
                        'address' => $this->nullIfEmpty($row['address'] ?? null),
                        'notes' => $this->nullIfEmpty($row['notes'] ?? null),
                    ],
                );

                activity()
                    ->causedBy($user)
                    ->performedOn($supplier)
                    ->withProperties([
                        'source' => 'csv_import',
                        'line' => $line,
                    ])
                    ->log('supplier_imported');
            });
        });

        return redirect()
            ->route('suppliers.index')
            ->with('success', "Imported {$imported} suppliers successfully.");
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
