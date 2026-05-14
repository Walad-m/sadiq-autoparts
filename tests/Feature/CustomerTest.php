<?php

use App\Models\User;
use App\Models\Customer;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    $this->unauthorizedUser = User::factory()->create();
});

test('unauthorized users cannot view customers', function () {
    $this->actingAs($this->unauthorizedUser)
        ->get('/customers')
        ->assertStatus(403);
});

test('admin can view customers list', function () {
    Customer::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get('/customers')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('customers/index'));
});

test('admin can create a customer', function () {
    $customerData = [
        'name' => 'John Doe',
        'phone' => '0551234567',
        'email' => 'john@example.com',
        'address' => 'Accra',
    ];

    $this->actingAs($this->admin)
        ->post('/customers', $customerData)
        ->assertRedirect('/customers');

    $this->assertDatabaseHas('customers', ['name' => 'John Doe']);
});

test('admin can update a customer', function () {
    $customer = Customer::factory()->create(['name' => 'Old Name']);

    $this->actingAs($this->admin)
        ->put("/customers/{$customer->id}", [
            'name' => 'Jane Doe',
            'phone' => '0551234567',
        ])
        ->assertRedirect('/customers');

    $this->assertDatabaseHas('customers', ['name' => 'Jane Doe']);
});

test('admin can delete a customer', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/customers/{$customer->id}")
        ->assertRedirect('/customers');

    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
});
