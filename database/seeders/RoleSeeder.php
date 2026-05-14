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
        // Create roles
        $adminRole   = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        // Define permissions for each module
        $permissions = [
            // Dashboard
            'view-dashboard',

            // Products
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
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
        ];

        // Create and sync all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin gets all permissions
        $adminRole->syncPermissions($permissions);

        // Cashier gets POS + limited access
        $cashierPermissions = [
            'view-dashboard',
            'view-products',
            'access-pos',
            'create-sale',
            'print-receipt',
            'view-sales',
            'view-sale-detail',
            'view-customers',
            'create-customers',
        ];
        $cashierRole->syncPermissions($cashierPermissions);
    }
}
