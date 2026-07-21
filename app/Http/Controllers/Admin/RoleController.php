<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage-roles'),
        ];
    }

    /**
     * All permissions grouped by module for the UI checkboxes.
     */
    private function groupedPermissions(): array
    {
        $allPermissions = Permission::orderBy('name')->get(['id', 'name']);

        $groups = [
            'Dashboard' => [],
            'Products' => [],
            'Categories' => [],
            'Point of Sale' => [],
            'Sales' => [],
            'Customers' => [],
            'Suppliers' => [],
            'Expenses' => [],
            'Reports' => [],
            'Administration' => [],
        ];

        $mapping = [
            'view-dashboard' => 'Dashboard',
            'view-products' => 'Products', 'create-products' => 'Products',
            'edit-products' => 'Products', 'delete-products' => 'Products',
            'view-categories' => 'Categories', 'create-categories' => 'Categories',
            'edit-categories' => 'Categories', 'delete-categories' => 'Categories',
            'access-pos' => 'Point of Sale', 'create-sale' => 'Point of Sale',
            'print-receipt' => 'Point of Sale',
            'view-sales' => 'Sales', 'view-sale-detail' => 'Sales', 'cancel-sale' => 'Sales',
            'view-customers' => 'Customers', 'create-customers' => 'Customers',
            'edit-customers' => 'Customers', 'delete-customers' => 'Customers',
            'view-suppliers' => 'Suppliers', 'create-suppliers' => 'Suppliers',
            'edit-suppliers' => 'Suppliers', 'delete-suppliers' => 'Suppliers',
            'view-payables' => 'Suppliers', 'manage-payables' => 'Suppliers',
            'view-expenses' => 'Expenses', 'create-expenses' => 'Expenses',
            'edit-expenses' => 'Expenses', 'delete-expenses' => 'Expenses',
            'view-reports' => 'Reports', 'export-reports' => 'Reports',
            'manage-users' => 'Administration', 'manage-roles' => 'Administration',
        ];

        foreach ($allPermissions as $perm) {
            $group = $mapping[$perm->name] ?? 'Other';
            if (! isset($groups[$group])) {
                $groups[$group] = [];
            }
            $groups[$group][] = ['id' => $perm->id, 'name' => $perm->name];
        }

        return array_filter($groups, fn ($perms) => count($perms) > 0);
    }

    public function index()
    {
        $roles = Role::withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users_count,
            ]);

        return Inertia::render('admin/roles/index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/roles/create', [
            'groupedPermissions' => $this->groupedPermissions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->withProperties(['permissions' => $data['permissions'] ?? []])
            ->log('role_created');

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" created successfully.");
    }

    public function edit(Role $role)
    {
        return Inertia::render('admin/roles/edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
            'groupedPermissions' => $this->groupedPermissions(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->withProperties([
                'old' => ['name' => $role->getOriginal('name'), 'permissions' => $oldPermissions],
                'new' => ['name' => $data['name'], 'permissions' => $data['permissions'] ?? []],
            ])
            ->log('role_updated');

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" updated successfully.");
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', "Cannot delete \"{$role->name}\" — {$role->users()->count()} user(s) are assigned to it.");
        }

        if ($role->name === 'admin') {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'The admin role cannot be deleted.');
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties(['name' => $role->name])
            ->log('role_deleted');

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" deleted.");
    }
}
