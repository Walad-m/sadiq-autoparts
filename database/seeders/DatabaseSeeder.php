<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RoleSeeder::class);

        // Seed reference data
        $this->call(CategorySeeder::class);
        $this->call(SupplierSeeder::class);

        // Seed transactional data
        $this->call(ProductSeeder::class);
        $this->call(CustomerSeeder::class);

        // Create admin user and assign role
        $admin = User::firstOrCreate(
            ['email' => 'admin@sabr89.test'],
            ['name'  => 'Sabr Admin', 'password' => bcrypt('password')]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create cashier (POS attendant) user
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@sabr89.test'],
            ['name'  => 'POS Attendant', 'password' => bcrypt('password')]
        );
        if (! $cashier->hasRole('cashier')) {
            $cashier->assignRole('cashier');
        }
    }
}

