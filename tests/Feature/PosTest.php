<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    $this->cashier = User::factory()->create();
    $this->cashier->assignRole('cashier');
    
    $this->unauthorizedUser = User::factory()->create();
});

test('unauthorized users cannot access pos', function () {
    $this->actingAs($this->unauthorizedUser)
        ->get('/pos')
        ->assertStatus(403);
});

test('cashier can access pos', function () {
    $this->actingAs($this->cashier)
        ->get('/pos')
        ->assertStatus(200);
});

test('admin can access pos', function () {
    $this->actingAs($this->admin)
        ->get('/pos')
        ->assertStatus(200);
});

test('checkout reduces product stock correctly', function () {
    $category = Category::factory()->create();
    
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'quantity' => 10,
        'selling_price' => 100,
        'is_active' => true,
    ]);

    $this->actingAs($this->cashier)
        ->post('/pos', [
            'payment_method' => 'cash',
            'amount_tendered' => 200,
            'change_given' => 0,
            'discount' => 0,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 100,
                ]
            ]
        ])
        ->assertRedirect(); // Should redirect to receipt

    // Assert sale created
    $this->assertDatabaseHas('sales', [
        'user_id' => $this->cashier->id,
        'payment_method' => 'cash',
        'subtotal' => 200,
        'total' => 200,
    ]);

    // Assert item created
    $this->assertDatabaseHas('sale_items', [
        'product_id' => $product->id,
        'quantity' => 2,
        'line_total' => 200,
    ]);

    // Assert stock decremented
    expect($product->fresh()->quantity)->toBe(8);
});
