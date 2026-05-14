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
    
    $this->category = Category::factory()->create();
    
    $this->product1 = Product::factory()->create([
        'category_id' => $this->category->id,
        'quantity' => 100,
        'selling_price' => 100,
        'is_active' => true,
    ]);
    
    $this->product2 = Product::factory()->create([
        'category_id' => $this->category->id,
        'quantity' => 100,
        'selling_price' => 80,
        'is_active' => true,
    ]);

    // Create a completed sale
    $this->sale = Sale::create([
        'sale_number' => 'SAL-20260513-TEST',
        'customer_id' => null,
        'user_id' => $this->admin->id,
        'payment_method' => 'cash',
        'subtotal' => 300,
        'discount' => 0,
        'total' => 300,
        'amount_tendered' => 300,
        'change_given' => 0,
        'status' => 'completed',
    ]);

    // Create sale items
    SaleItem::create([
        'sale_id' => $this->sale->id,
        'product_id' => $this->product1->id,
        'quantity' => 2,
        'unit_price' => 100,
        'line_total' => 200,
    ]);

    SaleItem::create([
        'sale_id' => $this->sale->id,
        'product_id' => $this->product2->id,
        'quantity' => 1,
        'unit_price' => 100,
        'line_total' => 100,
    ]);

    // Update product quantities to reflect sale
    $this->product1->decrement('quantity', 2);
    $this->product2->decrement('quantity', 1);
});

// FIX #2 TEST SUITE: Refund & Exchange Workflow
describe('Fix #2: Refund & Exchange Workflow', function () {
    test('full refund restores stock', function () {
        expect($this->product1->fresh()->quantity)->toBe(98); // 100 - 2

        $this->actingAs($this->admin)
            ->post(route('sales.refund', $this->sale->id))
            ->assertRedirect();

        // Stock should be restored
        expect($this->product1->fresh()->quantity)->toBe(100);
        expect($this->product2->fresh()->quantity)->toBe(100);

        // Sale should be marked refunded
        expect($this->sale->fresh()->status)->toBe('refunded');
    });

    test('cannot refund completed sale outside 30-day window', function () {
        // Create an old sale (31 days old)
        $oldSale = Sale::create([
            'sale_number' => 'SAL-20260412-OLD',
            'customer_id' => null,
            'user_id' => $this->admin->id,
            'payment_method' => 'cash',
            'subtotal' => 100,
            'discount' => 0,
            'total' => 100,
            'status' => 'completed',
            'created_at' => now()->subDays(31),
        ]);

        $this->actingAs($this->admin)
            ->post(route('sales.refund', $oldSale->id))
            ->assertSessionHasErrors();
    });

    test('refund logs activity', function () {
        $this->actingAs($this->admin)
            ->post(route('sales.refund', $this->sale->id));

        // Check activity log
        $activities = Activity::where('description', 'sale_refunded')->get();
        expect($activities->count())->toBeGreaterThan(0);
        expect($activities->first()->subject_id)->toBe($this->sale->id);
    });

    test('even exchange swaps stock correctly', function () {
        // Exchange product1 (qty 2 @ 100 each = 200) for product2 (qty 2 @ 100 each = 200)
        $saleItem = $this->sale->items->first();
        
        $this->actingAs($this->admin)
            ->post(route('sales.exchange.process', $this->sale->id), [
                'returned_item_ids' => [$saleItem->id],
                'new_items' => [
                    [
                        'product_id' => $this->product2->id,
                        'quantity' => 2,
                    ]
                ],
                'reason' => 'customer_request',
                'notes' => 'Even exchange',
            ])
            ->assertRedirect();

        // Product1 should be restored
        expect($this->product1->fresh()->quantity)->toBe(100);
        
        // Product2 should be decremented (was 100, borrowed 1 from sale, now -2 = 97)
        expect($this->product2->fresh()->quantity)->toBe(97);

        // Exchange record should exist
        expect(SaleReplacement::count())->toBe(1);
        expect(SaleReplacement::first()->isEvenExchange())->toBeTrue();
    });

    test('upgrade charges customer additional amount', function () {
        $premiumProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'quantity' => 100,
            'selling_price' => 150,  // More expensive
            'is_active' => true,
        ]);

        $saleItem = $this->sale->items->first();

        $this->actingAs($this->admin)
            ->post(route('sales.exchange.process', $this->sale->id), [
                'returned_item_ids' => [$saleItem->id],
                'new_items' => [
                    [
                        'product_id' => $premiumProduct->id,
                        'quantity' => 2,
                    ]
                ],
                'reason' => 'customer_request',
            ])
            ->assertRedirect();

        // Should record additional charge
        $replacement = SaleReplacement::first();
        expect($replacement->isUpgrade())->toBeTrue();
        expect($replacement->additional_charge)->toBe(100); // (150 - 100) * 2
    });

    test('downgrade refunds customer difference', function () {
        $cheaperProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'quantity' => 100,
            'selling_price' => 50,  // Less expensive
            'is_active' => true,
        ]);

        $saleItem = $this->sale->items->first();

        $this->actingAs($this->admin)
            ->post(route('sales.exchange.process', $this->sale->id), [
                'returned_item_ids' => [$saleItem->id],
                'new_items' => [
                    [
                        'product_id' => $cheaperProduct->id,
                        'quantity' => 2,
                    ]
                ],
                'reason' => 'customer_request',
            ])
            ->assertRedirect();

        // Should record refund
        $replacement = SaleReplacement::first();
        expect($replacement->isRefund())->toBeTrue();
        expect($replacement->refund_amount)->toBe(100); // (100 - 50) * 2
    });

    test('exchange validates sufficient stock', function () {
        $lowStockProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'quantity' => 1,  // Only 1 available
            'selling_price' => 100,
            'is_active' => true,
        ]);

        $saleItem = $this->sale->items->first();

        // Try to exchange for 5 units (only 1 available)
        $this->actingAs($this->admin)
            ->post(route('sales.exchange.process', $this->sale->id), [
                'returned_item_ids' => [$saleItem->id],
                'new_items' => [
                    [
                        'product_id' => $lowStockProduct->id,
                        'quantity' => 5,  // Not enough stock
                    ]
                ],
                'reason' => 'customer_request',
            ])
            ->assertSessionHasErrors();
    });

    test('exchange logs activity with detailed properties', function () {
        $saleItem = $this->sale->items->first();

        $this->actingAs($this->admin)
            ->post(route('sales.exchange.process', $this->sale->id), [
                'returned_item_ids' => [$saleItem->id],
                'new_items' => [
                    [
                        'product_id' => $this->product2->id,
                        'quantity' => 2,
                    ]
                ],
                'reason' => 'defective',
                'notes' => 'Item was damaged in shipping',
            ])
            ->assertRedirect();

        // Check activity log
        $activities = Activity::where('description', 'sale_exchanged')->get();
        expect($activities->count())->toBeGreaterThan(0);
        
        $activity = $activities->first();
        expect($activity->properties['reason'])->toBe('defective');
        expect($activity->properties['notes'])->toBe('Item was damaged in shipping');
    });

    test('refund shows correct success message', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('sales.refund', $this->sale->id));

        $response->assertSessionHas('success', fn($msg) => 
            str_contains($msg, 'refunded')
        );
    });

    test('exchange shows correct financial message', function () {
        // Upgrade scenario
        $premiumProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'quantity' => 100,
            'selling_price' => 150,
            'is_active' => true,
        ]);

        $saleItem = $this->sale->items->first();

        $response = $this->actingAs($this->admin)
            ->post(route('sales.exchange.process', $this->sale->id), [
                'returned_item_ids' => [$saleItem->id],
                'new_items' => [
                    [
                        'product_id' => $premiumProduct->id,
                        'quantity' => 2,
                    ]
                ],
                'reason' => 'customer_request',
            ]);

        $response->assertSessionHas('success', fn($msg) => 
            str_contains($msg, 'Additional charge')
        );
    });
});
