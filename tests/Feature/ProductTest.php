<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    $this->unauthorizedUser = User::factory()->create();
    
    $this->category = Category::factory()->create();
    $this->supplier = Supplier::factory()->create();
});

test('unauthorized users cannot view products', function () {
    $this->actingAs($this->unauthorizedUser)
        ->get('/products')
        ->assertStatus(403);
});

test('admin can view products list', function () {
    Product::factory()->count(3)->create([
        'category_id' => $this->category->id,
        'supplier_id' => $this->supplier->id,
    ]);

    $this->actingAs($this->admin)
        ->get('/products')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('products/index'));
});

test('admin can create a product', function () {
    $productData = [
        'name' => 'Brake Pads',
        'part_number' => 'BP-001',
        'category_id' => $this->category->id,
        'supplier_id' => $this->supplier->id,
        'unit' => 'set',
        'cost_price' => 50,
        'selling_price' => 100,
        'quantity' => 20,
        'reorder_level' => 5,
        'is_active' => true,
    ];

    $this->actingAs($this->admin)
        ->post('/products', $productData)
        ->assertRedirect('/products');

    $this->assertDatabaseHas('products', ['name' => 'Brake Pads', 'part_number' => 'BP-001']);
});

test('admin can update a product', function () {
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'supplier_id' => $this->supplier->id,
        'name' => 'Old Pad',
    ]);

    $this->actingAs($this->admin)
        ->put("/products/{$product->id}", [
            'name' => 'New Pad',
            'part_number' => $product->part_number,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'unit' => 'set',
            'cost_price' => 55,
            'selling_price' => 110,
            'quantity' => 25,
            'reorder_level' => 5,
            'is_active' => true,
        ])
        ->assertRedirect('/products');

    $this->assertDatabaseHas('products', ['name' => 'New Pad']);
});

test('admin can delete a product', function () {
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'supplier_id' => $this->supplier->id,
    ]);

    $this->actingAs($this->admin)
        ->delete("/products/{$product->id}")
        ->assertRedirect('/products');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
