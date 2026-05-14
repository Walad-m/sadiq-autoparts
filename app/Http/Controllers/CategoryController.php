<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
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
            new Middleware('can:view-categories', only: ['index']),
            new Middleware('can:create-categories', only: ['create', 'store']),
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
        $user = auth()->user();
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
        $user = auth()->user();
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
        $user = auth()->user();
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
}
