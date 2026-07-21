<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ── Permissions ───────────────────────────────────────────────
        $permissions = [
            // Dashboard
            'view-dashboard',

            // Products
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',

            // Categories
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Point of Sale
            'access-pos',
            'create-sale',
            'print-receipt',

            // Sales
            'view-sales',
            'view-sale-detail',
            'cancel-sale',

            // Customers
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            // Suppliers
            'view-suppliers',
            'create-suppliers',
            'edit-suppliers',
            'delete-suppliers',
            'view-payables',
            'manage-payables',

            // Expenses
            'view-expenses',
            'create-expenses',
            'edit-expenses',
            'delete-expenses',

            // Reports
            'view-reports',
            'export-reports',

            // Administration
            'manage-users',
            'manage-roles',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Roles ─────────────────────────────────────────────────────

        // Admin — full system access including user & role management
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // Manager — full operational access, no user/role management
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view-dashboard',
            'view-products', 'create-products', 'edit-products', 'delete-products',
            'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
            'access-pos', 'create-sale', 'print-receipt',
            'view-sales', 'view-sale-detail', 'cancel-sale',
            'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
            'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers',
            'view-payables', 'manage-payables',
            'view-expenses', 'create-expenses', 'edit-expenses', 'delete-expenses',
            'view-reports', 'export-reports',
        ]);

        // Cashier — POS focused, limited to daily sales tasks
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'view-dashboard',
            'view-products',
            'access-pos', 'create-sale', 'print-receipt',
            'view-sales', 'view-sale-detail',
            'view-customers', 'create-customers',
        ]);

        // Inventory Clerk — manages stock, no financial data
        $inventoryClerk = Role::firstOrCreate(['name' => 'inventory clerk', 'guard_name' => 'web']);
        $inventoryClerk->syncPermissions([
            'view-dashboard',
            'view-products', 'create-products', 'edit-products',
            'view-categories', 'create-categories', 'edit-categories',
            'view-suppliers', 'create-suppliers', 'edit-suppliers',
        ]);

        // Accountant — financial view, no inventory management
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'view-dashboard',
            'view-sales', 'view-sale-detail',
            'view-expenses', 'create-expenses', 'edit-expenses', 'delete-expenses',
            'view-reports', 'export-reports',
        ]);
    }
}
