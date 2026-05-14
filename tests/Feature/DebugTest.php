<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;

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

describe('Debug Tests', function () {
    
    test('database seeding works', function () {
        expect($this->product->id)->toBeGreaterThan(0);
        expect($this->product->quantity)->toBe(5);
        expect($this->cashier->id)->toBeGreaterThan(0);
    });

    test('cashier has access-pos permission', function () {
        expect($this->cashier->hasPermissionTo('access-pos'))->toBeTrue();
        expect($this->cashier->hasPermissionTo('create-sale'))->toBeTrue();
    });

    test('admin has all permissions', function () {
        expect($this->admin->hasPermissionTo('create-sale'))->toBeTrue();
        expect($this->admin->hasPermissionTo('access-pos'))->toBeTrue();
    });

    test('route exists', function () {
        $route = route('pos.store');
        expect($route)->toContain('/pos');
    });

    test('simple post to pos route', function () {
        $response = $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 600,
                'change_given' => 100,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                        'unit_price' => 100,
                    ]
                ]
            ]);

        // Show response details
        if ($response->getStatusCode() !== 302) {
            echo "Response Status: " . $response->getStatusCode() . "\n";
            if ($response->getStatusCode() === 422) {
                echo "Validation Errors: " . json_encode($response->errors()) . "\n";
            }
        }

        $response->assertRedirect();
    });

    test('insufficient stock is rejected', function () {
        // Check initial stock
        expect($this->product->quantity)->toBe(5);

        $response = $this->actingAs($this->cashier)
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
            ]);

        // Check if sale was created (it shouldn't be)
        $saleCount = Sale::count();
        
        // The response should have validation errors or be a 422
        if ($response->getStatusCode() === 422) {
            expect($response->errors())->toHaveKey('items');
        } elseif ($response->getStatusCode() === 302) {
            // If redirect, check if there are session errors
            $errors = session('errors');
            if ($errors !== null) {
                expect($errors->first('items'))->not()->toBeNull();
            } else {
                // No errors in session means validation passed when it shouldn't
                expect($saleCount)->toBe(0);  // Sale should not have been created
            }
        }

        // Stock should remain at 5
        expect($this->product->fresh()->quantity)->toBe(5);
    });
});
