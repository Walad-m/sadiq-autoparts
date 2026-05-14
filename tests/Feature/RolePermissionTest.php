<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

test('roles are seeded correctly', function () {
    expect(Role::count())->toBeGreaterThanOrEqual(2);
    $this->assertDatabaseHas('roles', ['name' => 'admin']);
    $this->assertDatabaseHas('roles', ['name' => 'cashier']);
});

test('admin has all permissions', function () {
    $adminRole = Role::findByName('admin');
    $totalPermissions = Permission::count();
    
    expect($adminRole->permissions()->count())->toBe($totalPermissions);
});

test('cashier has restricted permissions', function () {
    $cashierRole = Role::findByName('cashier');
    
    expect($cashierRole->hasPermissionTo('access-pos'))->toBeTrue();
    expect($cashierRole->hasPermissionTo('delete-products'))->toBeFalse();
});
