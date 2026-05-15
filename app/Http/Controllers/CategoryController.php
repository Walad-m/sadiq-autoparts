<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
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
class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view-categories', only: ['index', 'export']),
            new Middleware('can:create-categories', only: ['create', 'store', 'import']),
            new Middleware('can:edit-categories', only: ['edit', 'update']),
            new Middleware('can:delete-categories', only: ['destroy']),
        ];
    }
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('categories/index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('categories/create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $user = Auth::user();
        $category = Category::create($request->validated());

        // Log category creation
        activity()
            ->causedBy($user)
            ->performedOn($category)
            ->withProperties($request->validated())
            ->log('category_created');

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('categories/edit', [
            'category' => $category,
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $user = Auth::user();
        $oldData = $category->getAttributes();
        $category->update($request->validated());

        // Log category update
        activity()
            ->causedBy($user)
            ->performedOn($category)
            ->withProperties([
                'old' => $oldData,
                'new' => $request->validated(),
            ])
            ->log('category_updated');

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $user = Auth::user();
        $categoryData = $category->getAttributes();
        $category->delete();

        // Log category deletion
        activity()
            ->causedBy($user)
            ->withProperties($categoryData)
            ->log('category_deleted');

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function export()
    {
        $categories = Category::orderBy('name')
            ->get()
            ->map(static fn (Category $category): array => [
                $category->name,
                $category->description,
            ]);

        return CsvImportExport::download('categories.csv', [
            'name',
            'description',
        ], $categories);
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
                    throw new \InvalidArgumentException("Missing category name on CSV line {$line}.");
                }

                $category = Category::updateOrCreate(
                    ['name' => $name],
                    ['description' => $this->nullIfEmpty($row['description'] ?? null)],
                );

                activity()
                    ->causedBy($user)
                    ->performedOn($category)
                    ->withProperties([
                        'source' => 'csv_import',
                        'line' => $line,
                    ])
                    ->log('category_imported');
            });
        });

        return redirect()
            ->route('categories.index')
            ->with('success', "Imported {$imported} categories successfully.");
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
