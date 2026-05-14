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
        $admin = User::factory()->create([
            'name'  => 'Sadiq Admin',
            'email' => 'admin@sadiq.test',
        ]);
        $admin->assignRole('admin');

        // Create cashier (POS attendant) user
        $cashier = User::factory()->create([
            'name'  => 'POS Attendant',
            'email' => 'cashier@sadiq.test',
        ]);
        $cashier->assignRole('cashier');
    }
}
