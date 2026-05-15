<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
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
class CustomerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view-customers', only: ['index', 'export']),
            new Middleware('can:create-customers', only: ['create', 'store', 'import']),
            new Middleware('can:edit-customers', only: ['edit', 'update']),
            new Middleware('can:delete-customers', only: ['destroy']),
        ];
    }
    public function index()
    {
        $customers = Customer::latest()->paginate(20);

        return Inertia::render('customers/index', [
            'customers' => $customers,
        ]);
    }

    public function create()
    {
        return Inertia::render('customers/create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $user = Auth::user();
        $customer = Customer::create($request->validated());

        // Log customer creation
        activity()
            ->causedBy($user)
            ->performedOn($customer)
            ->withProperties($request->validated())
            ->log('customer_created');

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('customers/edit', [
            'customer' => $customer,
        ]);
    }

    public function update(StoreCustomerRequest $request, Customer $customer)
    {
        $user = Auth::user();
        $oldData = $customer->getAttributes();
        $customer->update($request->validated());

        // Log customer update
        activity()
            ->causedBy($user)
            ->performedOn($customer)
            ->withProperties([
                'old' => $oldData,
                'new' => $request->validated(),
            ])
            ->log('customer_updated');

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $user = Auth::user();
        $customerData = $customer->getAttributes();
        $customer->delete();

        // Log customer deletion
        activity()
            ->causedBy($user)
            ->withProperties($customerData)
            ->log('customer_deleted');

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function export()
    {
        $customers = Customer::orderBy('name')
            ->get()
            ->map(static fn (Customer $customer): array => [
                $customer->name,
                $customer->phone,
                $customer->email,
                $customer->address,
                $customer->notes,
            ]);

        return CsvImportExport::download('customers.csv', [
            'name',
            'phone',
            'email',
            'address',
            'notes',
        ], $customers);
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
                    throw new \InvalidArgumentException("Missing customer name on CSV line {$line}.");
                }

                $customer = Customer::updateOrCreate(
                    ['name' => $name],
                    [
                        'phone' => $this->nullIfEmpty($row['phone'] ?? null),
                        'email' => $this->nullIfEmpty($row['email'] ?? null),
                        'address' => $this->nullIfEmpty($row['address'] ?? null),
                        'notes' => $this->nullIfEmpty($row['notes'] ?? null),
                    ],
                );

                activity()
                    ->causedBy($user)
                    ->performedOn($customer)
                    ->withProperties([
                        'source' => 'csv_import',
                        'line' => $line,
                    ])
                    ->log('customer_imported');
            });
        });

        return redirect()
            ->route('customers.index')
            ->with('success', "Imported {$imported} customers successfully.");
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
