<?php

use App\Models\User;
use App\Models\Supplier;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    $this->unauthorizedUser = User::factory()->create();
});

test('unauthorized users cannot view suppliers', function () {
    $this->actingAs($this->unauthorizedUser)
        ->get('/suppliers')
        ->assertStatus(403);
});

test('admin can view suppliers list', function () {
    Supplier::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get('/suppliers')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('suppliers/index'));
});

test('admin can create a supplier', function () {
    $supplierData = [
        'name' => 'Auto Parts Ltd',
        'contact_person' => 'Mike',
        'phone' => '0551234567',
        'email' => 'mike@example.com',
    ];

    $this->actingAs($this->admin)
        ->post('/suppliers', $supplierData)
        ->assertRedirect('/suppliers');

    $this->assertDatabaseHas('suppliers', ['name' => 'Auto Parts Ltd']);
});

test('admin can update a supplier', function () {
    $supplier = Supplier::factory()->create(['name' => 'Old Name']);

    $this->actingAs($this->admin)
        ->put("/suppliers/{$supplier->id}", [
            'name' => 'New Parts Ltd',
            'phone' => '0551234567',
        ])
        ->assertRedirect('/suppliers');

    $this->assertDatabaseHas('suppliers', ['name' => 'New Parts Ltd']);
});

test('admin can delete a supplier', function () {
    $supplier = Supplier::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/suppliers/{$supplier->id}")
        ->assertRedirect('/suppliers');

    $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
});
