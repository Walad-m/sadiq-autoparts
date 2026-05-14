<?php

use App\Models\User;
use App\Models\Expense;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    $this->unauthorizedUser = User::factory()->create();
});

test('unauthorized users cannot view expenses', function () {
    $this->actingAs($this->unauthorizedUser)
        ->get('/expenses')
        ->assertStatus(403);
});

test('admin can view expenses list', function () {
    Expense::factory()->count(3)->create([
        'user_id' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->get('/expenses')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('expenses/index'));
});

test('admin can create an expense', function () {
    $expenseData = [
        'title' => 'Electricity bill',
        'category' => 'utilities',
        'amount' => 150.50,
        'payment_method' => 'cash',
        'expense_date' => now()->format('Y-m-d'),
        'notes' => 'Month of May',
    ];

    $this->actingAs($this->admin)
        ->post('/expenses', $expenseData)
        ->assertRedirect('/expenses');

    $this->assertDatabaseHas('expenses', ['category' => 'utilities', 'amount' => 150.50, 'title' => 'Electricity bill']);
});

test('admin can update an expense', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->admin->id,
        'category' => 'utilities'
    ]);

    $this->actingAs($this->admin)
        ->put("/expenses/{$expense->id}", [
            'title' => 'New Title',
            'category' => 'rent',
            'amount' => 200,
            'payment_method' => 'cash',
            'expense_date' => now()->format('Y-m-d'),
        ])
        ->assertRedirect('/expenses');

    $this->assertDatabaseHas('expenses', ['category' => 'rent', 'title' => 'New Title']);
});

test('admin can delete an expense', function () {
    $expense = Expense::factory()->create([
        'user_id' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->delete("/expenses/{$expense->id}")
        ->assertRedirect('/expenses');

    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});
