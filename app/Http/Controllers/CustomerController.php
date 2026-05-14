<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
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
            new Middleware('can:view-customers', only: ['index']),
            new Middleware('can:create-customers', only: ['create', 'store']),
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
        $user = auth()->user();
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
        $user = auth()->user();
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
        $user = auth()->user();
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
}
