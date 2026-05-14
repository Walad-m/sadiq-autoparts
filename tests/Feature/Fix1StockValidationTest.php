<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    $this->cashier = User::factory()->create();
    $this->cashier->assignRole('cashier');
    
    $this->category = Category::factory()->create();
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'quantity' => 5,
        'selling_price' => 100,
        'is_active' => true,
    ]);
});

// FIX #1 TEST SUITE: Stock Validation Before Sale
describe('Fix #1: Stock Validation', function () {
    test('checkout is blocked if quantity exceeds available stock', function () {
        $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 1000,
                'change_given' => 0,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 10,  // Only 5 available
                        'unit_price' => 100,
                    ]
                ]
            ])
            ->assertSessionHasErrors('items');
    });

    test('checkout succeeds if quantity is available', function () {
        $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 600,
                'change_given' => 100,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,  // Exact match
                        'unit_price' => 100,
                    ]
                ]
            ])
            ->assertRedirect();

        // Verify stock was decremented
        expect($this->product->fresh()->quantity)->toBe(0);
    });

    test('checkout blocks with specific error message', function () {
        $response = $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 2000,
                'change_given' => 0,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 20,  // Way over
                        'unit_price' => 100,
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
        $errors = session('errors');
        expect($errors->first('items'))->toContain('Insufficient stock');
    });

    test('checkout validates multiple items in cart', function () {
        $product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'quantity' => 3,
            'selling_price' => 50,
            'is_active' => true,
        ]);

        // First product OK, second product exceeds stock
        $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 1000,
                'change_given' => 0,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 3,  // OK
                        'unit_price' => 100,
                    ],
                    [
                        'product_id' => $product2->id,
                        'quantity' => 10,  // Exceeds 3 available
                        'unit_price' => 50,
                    ]
                ]
            ])
            ->assertSessionHasErrors('items');
    });

    test('empty cart is rejected', function () {
        $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 100,
                'change_given' => 0,
                'discount' => 0,
                'items' => []  // Empty
            ])
            ->assertSessionHasErrors('items');
    });

    test('negative quantity is rejected', function () {
        $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 100,
                'change_given' => 0,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => -5,  // Invalid
                        'unit_price' => 100,
                    ]
                ]
            ])
            ->assertSessionHasErrors();
    });
});
