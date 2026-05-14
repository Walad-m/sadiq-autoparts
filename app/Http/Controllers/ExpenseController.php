<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * @method \Illuminate\Contracts\Auth\Authenticatable|null user()
 */
class ExpenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view-expenses', only: ['index']),
            new Middleware('can:create-expenses', only: ['create', 'store']),
            new Middleware('can:edit-expenses', only: ['edit', 'update']),
            new Middleware('can:delete-expenses', only: ['destroy']),
        ];
    }
    public function index()
    {
        $expenses = Expense::latest()->paginate(20);

        return Inertia::render('expenses/index', [
            'expenses' => $expenses,
        ]);
    }

    public function create()
    {
        return Inertia::render('expenses/create');
    }

    public function store(StoreExpenseRequest $request)
    {
        $user = auth()->user();
        $expense = Expense::create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        // Log expense creation
        activity()
            ->causedBy($user)
            ->performedOn($expense)
            ->withProperties($request->validated())
            ->log('expense_recorded');

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        return Inertia::render('expenses/edit', [
            'expense' => $expense,
        ]);
    }

    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        $user = auth()->user();
        $oldData = $expense->getAttributes();
        $expense->update($request->validated());

        // Log expense update
        activity()
            ->causedBy($user)
            ->performedOn($expense)
            ->withProperties([
                'old' => $oldData,
                'new' => $request->validated(),
            ])
            ->log('expense_updated');

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $user = auth()->user();
        $expenseData = $expense->getAttributes();
        $expense->delete();

        // Log expense deletion
        activity()
            ->causedBy($user)
            ->withProperties($expenseData)
            ->log('expense_deleted');

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
