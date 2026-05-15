<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
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
class ExpenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view-expenses', only: ['index', 'export']),
            new Middleware('can:create-expenses', only: ['create', 'store', 'import']),
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
        $user = Auth::user();
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
        $user = Auth::user();
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
        $user = Auth::user();
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

    public function export()
    {
        $expenses = Expense::latest()
            ->get()
            ->map(static fn (Expense $expense): array => [
                $expense->title,
                $expense->amount,
                $expense->category,
                $expense->payment_method,
                $expense->expense_date,
                $expense->notes,
            ]);

        return CsvImportExport::download('expenses.csv', [
            'title',
            'amount',
            'category',
            'payment_method',
            'expense_date',
            'notes',
        ], $expenses);
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
                $title = trim((string) ($row['title'] ?? ''));

                if ($title === '') {
                    throw new \InvalidArgumentException("Missing expense title on CSV line {$line}.");
                }

                $expense = Expense::updateOrCreate(
                    [
                        'title' => $title,
                        'expense_date' => $this->requiredString($row['expense_date'] ?? null, 'expense date', $line),
                    ],
                    [
                        'amount' => $this->normalizeNumber($row['amount'] ?? null),
                        'category' => $this->normalizeExpenseCategory($row['category'] ?? null, $line),
                        'payment_method' => $this->normalizePaymentMethod($row['payment_method'] ?? null, $line),
                        'notes' => $this->nullIfEmpty($row['notes'] ?? null),
                        'user_id' => $user->id,
                    ],
                );

                activity()
                    ->causedBy($user)
                    ->performedOn($expense)
                    ->withProperties([
                        'source' => 'csv_import',
                        'line' => $line,
                    ])
                    ->log('expense_imported');
            });
        });

        return redirect()
            ->route('expenses.index')
            ->with('success', "Imported {$imported} expenses successfully.");
    }

    private function normalizeNumber(mixed $value): float
    {
        return (float) str_replace(',', '', trim((string) $value));
    }

    private function normalizeExpenseCategory(mixed $value, int $line): string
    {
        $category = strtolower(trim((string) $value));

        if (! in_array($category, ['rent', 'utilities', 'transport', 'salaries', 'stock', 'maintenance', 'other'], true)) {
            throw new \InvalidArgumentException("Invalid expense category on CSV line {$line}.");
        }

        return $category;
    }

    private function normalizePaymentMethod(mixed $value, int $line): string
    {
        $method = strtolower(trim((string) $value));

        if (! in_array($method, ['cash', 'momo'], true)) {
            throw new \InvalidArgumentException("Invalid payment method on CSV line {$line}.");
        }

        return $method;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function requiredString(mixed $value, string $field, int $line): string
    {
        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException("Missing {$field} on CSV line {$line}.");
        }

        return $trimmed;
    }
}
