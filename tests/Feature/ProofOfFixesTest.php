<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;

test('stock validation prevents oversale - sale not created when stock insufficient', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'quantity' => 5,
        'selling_price' => 100,
        'is_active' => true,
    ]);

    // Verify product exists and has correct stock
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'quantity' => 5,
    ]);

    $saleBefore = Sale::count();

    // Try to sell more than available
    $response = $this->actingAs($cashier)->post(route('pos.store'), [
        'payment_method' => 'cash',
        'amount_tendered' => 1000,
        'change_given' => 0,
        'discount' => 0,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 10,  // Only 5 available - should fail
                'unit_price' => 100,
            ]
        ]
    ]);

    $saleAfter = Sale::count();
    
    // No new sale should have been created
    expect($saleAfter)->toBe($saleBefore);
    
    // Stock should remain at 5
    expect($product->fresh()->quantity)->toBe(5);
});

test('valid sale goes through and stock is decremented', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'quantity' => 10,
        'selling_price' => 100,
        'is_active' => true,
    ]);

    $saleBefore = Sale::count();

    // Valid sale
    $response = $this->actingAs($cashier)->post(route('pos.store'), [
        'payment_method' => 'cash',
        'amount_tendered' => 300,
        'change_given' => 100,
        'discount' => 0,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 100,
            ]
        ]
    ]);

    $saleAfter = Sale::count();
    
    // New sale should have been created
    expect($saleAfter)->toBe($saleBefore + 1);
    
    // Stock should be decremented by 2
    expect($product->fresh()->quantity)->toBe(8);
});

test('activity log shows sale was created', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');
    
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'quantity' => 10,
        'selling_price' => 100,
        'is_active' => true,
    ]);

    // Make a sale
    $response = $this->actingAs($cashier)->post(route('pos.store'), [
        'payment_method' => 'cash',
        'amount_tendered' => 300,
        'change_given' => 100,
        'discount' => 0,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 100,
            ]
        ]
    ]);

    // Check activity log
    $activities = \Spatie\ActivityLog\Models\Activity::where('description', 'sale_completed')->get();
    
    expect($activities->count())->toBeGreaterThan(0);
    
    $activity = $activities->first();
    expect($activity->properties['items_count'])->toBe(1);
});
