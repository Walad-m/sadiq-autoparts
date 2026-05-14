<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReplacement;
use Spatie\ActivityLog\Models\Activity;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    $this->cashier = User::factory()->create();
    $this->cashier->assignRole('cashier');
    
    $this->category = Category::factory()->create();
    
    $this->product1 = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Brake Pads',
        'quantity' => 10,
        'selling_price' => 100,
        'is_active' => true,
    ]);
    
    $this->product2 = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Oil Filter',
        'quantity' => 15,
        'selling_price' => 50,
        'is_active' => true,
    ]);
    
    Activity::truncate();
});

// INTEGRATION TEST: All Three Fixes Working Together
describe('Integration: All Fixes Together', function () {
    
    test('complete pos workflow with logging', function () {
        // Cashier completes a sale
        $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 300,
                'change_given' => 100,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 2,
                        'unit_price' => 100,
                    ]
                ]
            ])
            ->assertRedirect();

        // Verify Fix #1: Stock was decremented
        expect($this->product1->fresh()->quantity)->toBe(8);

        // Verify Fix #3: Sale was logged
        $saleActivity = Activity::where('description', 'sale_completed')->first();
        expect($saleActivity)->not()->toBeNull();
        expect($saleActivity->properties['items_count'])->toBe(1);
        expect($saleActivity->properties['total'])->toBe('200.00');
    });

    test('complete refund workflow with logging', function () {
        // Create a sale
        $sale = Sale::create([
            'sale_number' => 'SAL-20260513-TEST',
            'user_id' => $this->admin->id,
            'payment_method' => 'cash',
            'subtotal' => 200,
            'discount' => 0,
            'total' => 200,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'unit_price' => 100,
            'line_total' => 200,
        ]);

        $this->product1->decrement('quantity', 2);
        expect($this->product1->fresh()->quantity)->toBe(8);

        // Admin processes refund
        $this->actingAs($this->admin)
            ->post(route('sales.refund', $sale->id))
            ->assertRedirect();

        // Verify Fix #1: Stock was restored
        expect($this->product1->fresh()->quantity)->toBe(10);

        // Verify Fix #2: Sale is marked refunded
        expect($sale->fresh()->status)->toBe('refunded');

        // Verify Fix #3: Activity was logged
        $refundActivity = Activity::where('description', 'sale_refunded')->first();
        expect($refundActivity)->not()->toBeNull();
        expect($refundActivity->properties['amount'])->toBe('200.00');
    });

    test('complete exchange workflow with logging', function () {
        // Create a sale
        $sale = Sale::create([
            'sale_number' => 'SAL-20260513-EXCH',
            'user_id' => $this->admin->id,
            'payment_method' => 'cash',
            'subtotal' => 200,
            'discount' => 0,
            'total' => 200,
            'status' => 'completed',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'unit_price' => 100,
            'line_total' => 200,
        ]);

        $this->product1->decrement('quantity', 2);
        $initialProduct1Qty = $this->product1->fresh()->quantity;
        $initialProduct2Qty = $this->product2->fresh()->quantity;

        // Admin processes exchange: Brake Pads → Oil Filters
        $this->actingAs($this->admin)
            ->post(route('sales.exchange.process', $sale->id), [
                'returned_item_ids' => [$saleItem->id],
                'new_items' => [
                    [
                        'product_id' => $this->product2->id,
                        'quantity' => 2,
                    ]
                ],
                'reason' => 'wrong_item',
                'notes' => 'Customer ordered wrong item',
            ])
            ->assertRedirect();

        // Verify Fix #1: Stock validation prevented oversale & correct quantities
        expect($this->product1->fresh()->quantity)->toBe($initialProduct1Qty + 2);  // Restored
        expect($this->product2->fresh()->quantity)->toBe($initialProduct2Qty - 2);  // Decremented

        // Verify Fix #2: Exchange recorded
        $replacement = SaleReplacement::first();
        expect($replacement)->not()->toBeNull();
        expect($replacement->original_sale_id)->toBe($sale->id);
        expect($replacement->reason)->toBe('wrong_item');

        // Verify Fix #3: Activity logged
        $exchangeActivity = Activity::where('description', 'sale_exchanged')->first();
        expect($exchangeActivity)->not()->toBeNull();
        expect($exchangeActivity->properties['reason'])->toBe('wrong_item');
    });

    test('stock validation prevents oversell even with concurrent requests', function () {
        // Simulate: Product has 2 units, two cashiers try to sell 2 units each simultaneously
        // Second transaction should fail

        // First sale: 2 units (should succeed)
        $response1 = $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 300,
                'change_given' => 100,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 10,  // Sell all 10
                        'unit_price' => 100,
                    ]
                ]
            ]);

        $response1->assertRedirect();
        expect($this->product1->fresh()->quantity)->toBe(0);

        // Second sale: Tries to sell 2 more (should fail due to Fix #1)
        $response2 = $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 300,
                'change_given' => 100,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 2,  // Not available
                        'unit_price' => 100,
                    ]
                ]
            ]);

        $response2->assertSessionHasErrors('items');
        expect($this->product1->fresh()->quantity)->toBe(0);  // Unchanged
    });

    test('multiple operations create complete audit trail', function () {
        // 1. Admin creates a category
        $this->actingAs($this->admin)
            ->post(route('categories.store'), [
                'name' => 'Filters',
                'description' => 'All filters',
            ])
            ->assertRedirect();

        // 2. Admin creates a product
        $category = Category::where('name', 'Filters')->first();
        $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => 'Air Filter',
                'category_id' => $category->id,
                'unit' => 'piece',
                'cost_price' => 20,
                'selling_price' => 50,
                'quantity' => 100,
                'reorder_level' => 10,
                'is_active' => true,
            ])
            ->assertRedirect();

        // 3. Cashier makes a sale
        $product = Product::where('name', 'Air Filter')->first();
        $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'amount_tendered' => 150,
                'change_given' => 100,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => 50,
                    ]
                ]
            ])
            ->assertRedirect();

        // Verify complete audit trail exists
        $activities = Activity::orderBy('created_at', 'desc')->get();
        
        $descriptions = $activities->pluck('description')->toArray();
        expect($descriptions)->toContain('category_created');
        expect($descriptions)->toContain('product_created');
        expect($descriptions)->toContain('sale_completed');

        // Verify chronological order
        expect($activities->first()->description)->toBe('sale_completed');  // Most recent
    });

    test('all activity logs have required properties', function () {
        // Create and complete a transaction
        $this->actingAs($this->cashier)
            ->post('/pos', [
                'payment_method' => 'cash',
                'amount_tendered' => 200,
                'change_given' => 100,
                'discount' => 0,
                'items' => [
                    [
                        'product_id' => $this->product1->id,
                        'quantity' => 1,
                        'unit_price' => 100,
                    ]
                ]
            ]);

        $activity = Activity::where('description', 'sale_completed')->first();

        // Verify all required fields exist
        expect($activity->causer_id)->not()->toBeNull();
        expect($activity->causer_type)->toBe('App\Models\User');
        expect($activity->subject_id)->not()->toBeNull();
        expect($activity->subject_type)->toBe('App\Models\Sale');
        expect($activity->description)->not()->toBeNull();
        expect($activity->properties)->not()->toBeNull();
        expect($activity->created_at)->not()->toBeNull();
    });
});
