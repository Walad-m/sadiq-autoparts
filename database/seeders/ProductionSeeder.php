<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's database for production.
     * This seeder is idempotent—it won't delete or duplicate data.
     */
    public function run(): void
    {
        // Seed roles and permissions (uses firstOrCreate, safe to repeat)
        $this->call(RoleSeeder::class);

        // Ensure admin user exists with admin role (production email)
        $admin = User::firstOrCreate(
            ['email' => 'sadiq@sadiqautoparts.com'],
            [
                'name' => 'Sadiq Admin',
                'password' => bcrypt('password123'), // Explicit password for the user
                'email_verified_at' => now(),
            ]
        );

        // Ensure admin has admin role (idempotent)
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
