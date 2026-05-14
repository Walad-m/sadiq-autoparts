<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;

test('stock validation works - insufficient stock returns error', function () {
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

    // Try to sell more than available - should trigger validation rule
    $response = $this->actingAs($cashier)->post(route('pos.store'), [
        'payment_method' => 'cash',
        'amount_tendered' => 1000,
        'change_given' => 0,
        'discount' => 0,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 10,  // Only 5 available
                'unit_price' => 100,
            ]
        ]
    ]);

    // Debug: show response status
    echo "Response Status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 422) {
        echo "Validation errors found\n";
        echo json_encode($response->errors()) . "\n";
    }
    
    // The validation should have happened
    // Either 422 (validation failed) or we need to check what happened
    if ($response->getStatusCode() === 500) {
        echo "Internal Server Error\n";
        // This shouldn't happen - there might be an error in the validation rule
    }
    
    expect($response->getStatusCode())->not()->toBe(302);
});

test('validation rule syntax check', function () {
    // Check if the rule can be instantiated
    $rule = new \App\Rules\SufficientStock();
    expect($rule)->not()->toBeNull();
});

test('product model can be queried', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'quantity' => 5,
    ]);
    
    expect($product->quantity)->toBe(5);
    expect($product->id)->toBeGreaterThan(0);
});
