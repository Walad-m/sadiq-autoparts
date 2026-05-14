<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Expense;
use Spatie\ActivityLog\Models\Activity;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    Activity::truncate(); // Clear activity log before each test
});

// FIX #3 TEST SUITE: Activity Logging on All Transactions
describe('Fix #3: Activity Logging', function () {
    
    // Product logging tests
    test('product creation is logged', function () {
        $category = Category::factory()->create();
        
        $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => 'Brake Pads',
                'category_id' => $category->id,
                'unit' => 'set',
                'cost_price' => 50,
                'selling_price' => 100,
                'quantity' => 20,
                'reorder_level' => 5,
                'is_active' => true,
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'product_created')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['name'])->toBe('Brake Pads');
    });

    test('product update is logged with old and new values', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Original Name',
            'selling_price' => 100,
        ]);

        $this->actingAs($this->admin)
            ->patch("/products/{$product->id}", [
                'name' => 'Updated Name',
                'category_id' => $category->id,
                'unit' => 'piece',
                'cost_price' => 50,
                'selling_price' => 150,  // Changed
                'quantity' => 20,
                'reorder_level' => 5,
                'is_active' => true,
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'product_updated')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['old']['name'])->toBe('Original Name');
        expect($activities->first()->properties['new']['name'])->toBe('Updated Name');
        expect($activities->first()->properties['old']['selling_price'])->toBe('100.00');
        expect($activities->first()->properties['new']['selling_price'])->toBe('150.00');
    });

    test('product deletion is logged', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'To Delete',
        ]);

        $this->actingAs($this->admin)
            ->delete("/products/{$product->id}")
            ->assertRedirect();

        $activities = Activity::where('description', 'product_deleted')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['name'])->toBe('To Delete');
    });

    // Customer logging tests
    test('customer creation is logged', function () {
        $this->actingAs($this->admin)
            ->post(route('customers.store'), [
                'name' => 'Kwame',
                'phone' => '0537202641',
                'email' => 'kwame@example.com',
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'customer_created')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['name'])->toBe('Kwame');
        expect($activities->first()->properties['phone'])->toBe('0537202641');
    });

    test('customer update is logged', function () {
        $customer = Customer::factory()->create(['name' => 'Original']);

        $this->actingAs($this->admin)
            ->patch("/customers/{$customer->id}", [
                'name' => 'Updated',
                'phone' => '0559133733',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'customer_updated')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['old']['name'])->toBe('Original');
        expect($activities->first()->properties['new']['name'])->toBe('Updated');
    });

    test('customer deletion is logged', function () {
        $customer = Customer::factory()->create(['name' => 'Delete Me']);

        $this->actingAs($this->admin)
            ->delete("/customers/{$customer->id}")
            ->assertRedirect();

        $activities = Activity::where('description', 'customer_deleted')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['name'])->toBe('Delete Me');
    });

    // Supplier logging tests
    test('supplier creation is logged', function () {
        $this->actingAs($this->admin)
            ->post(route('suppliers.store'), [
                'name' => 'Auto Parts Ltd',
                'contact_person' => 'Abena',
                'phone' => '0537202641',
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'supplier_created')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['name'])->toBe('Auto Parts Ltd');
    });

    test('supplier update is logged', function () {
        $supplier = Supplier::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin)
            ->patch("/suppliers/{$supplier->id}", [
                'name' => 'New Name',
                'contact_person' => 'Contact',
                'phone' => '0537202641',
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'supplier_updated')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['old']['name'])->toBe('Old Name');
        expect($activities->first()->properties['new']['name'])->toBe('New Name');
    });

    test('supplier deletion is logged', function () {
        $supplier = Supplier::factory()->create(['name' => 'To Delete']);

        $this->actingAs($this->admin)
            ->delete("/suppliers/{$supplier->id}")
            ->assertRedirect();

        $activities = Activity::where('description', 'supplier_deleted')->get();
        expect($activities->count())->toBe(1);
    });

    // Category logging tests
    test('category creation is logged', function () {
        $this->actingAs($this->admin)
            ->post(route('categories.store'), [
                'name' => 'Oil Filters',
                'description' => 'All types of oil filters',
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'category_created')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['name'])->toBe('Oil Filters');
    });

    test('category update is logged', function () {
        $category = Category::factory()->create(['name' => 'Old Category']);

        $this->actingAs($this->admin)
            ->patch("/categories/{$category->id}", [
                'name' => 'New Category',
                'description' => 'Updated description',
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'category_updated')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['old']['name'])->toBe('Old Category');
        expect($activities->first()->properties['new']['name'])->toBe('New Category');
    });

    test('category deletion is logged', function () {
        $category = Category::factory()->create(['name' => 'To Delete']);

        $this->actingAs($this->admin)
            ->delete("/categories/{$category->id}")
            ->assertRedirect();

        $activities = Activity::where('description', 'category_deleted')->get();
        expect($activities->count())->toBe(1);
    });

    // Expense logging tests
    test('expense creation is logged', function () {
        $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'title' => 'Monthly Rent',
                'amount' => 500,
                'category' => 'rent',
                'payment_method' => 'cash',
                'expense_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'expense_recorded')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['title'])->toBe('Monthly Rent');
        expect($activities->first()->properties['amount'])->toBe('500.00');
    });

    test('expense update is logged', function () {
        $expense = Expense::factory()->create([
            'title' => 'Old Title',
            'amount' => 100,
        ]);

        $this->actingAs($this->admin)
            ->patch("/expenses/{$expense->id}", [
                'title' => 'New Title',
                'amount' => 200,
                'category' => $expense->category,
                'payment_method' => $expense->payment_method,
                'expense_date' => $expense->expense_date->toDateString(),
            ])
            ->assertRedirect();

        $activities = Activity::where('description', 'expense_updated')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['old']['title'])->toBe('Old Title');
        expect($activities->first()->properties['new']['title'])->toBe('New Title');
        expect($activities->first()->properties['old']['amount'])->toBe('100.00');
        expect($activities->first()->properties['new']['amount'])->toBe('200.00');
    });

    test('expense deletion is logged', function () {
        $expense = Expense::factory()->create(['title' => 'To Delete']);

        $this->actingAs($this->admin)
            ->delete("/expenses/{$expense->id}")
            ->assertRedirect();

        $activities = Activity::where('description', 'expense_deleted')->get();
        expect($activities->count())->toBe(1);
        expect($activities->first()->properties['title'])->toBe('To Delete');
    });

    // Activity log structure tests
    test('all activities include user information', function () {
        $category = Category::factory()->create();
        
        $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => 'Test Product',
                'category_id' => $category->id,
                'unit' => 'piece',
                'cost_price' => 50,
                'selling_price' => 100,
                'quantity' => 10,
                'reorder_level' => 5,
                'is_active' => true,
            ]);

        $activity = Activity::where('description', 'product_created')->first();
        expect($activity->causer_id)->toBe($this->admin->id);
        expect($activity->causer_type)->toBe('App\Models\User');
    });

    test('all activities include timestamp', function () {
        $category = Category::factory()->create();
        
        $this->actingAs($this->admin)
            ->post(route('products.store'), [
                'name' => 'Test Product',
                'category_id' => $category->id,
                'unit' => 'piece',
                'cost_price' => 50,
                'selling_price' => 100,
                'quantity' => 10,
                'reorder_level' => 5,
                'is_active' => true,
            ]);

        $activity = Activity::where('description', 'product_created')->first();
        expect($activity->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($activity->created_at->diffInSeconds(now()))->toBeLessThan(5);
    });

    test('activity log has subject information', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->patch("/products/{$product->id}", [
                'name' => 'Updated',
                'category_id' => $category->id,
                'unit' => 'piece',
                'cost_price' => 50,
                'selling_price' => 100,
                'quantity' => 10,
                'reorder_level' => 5,
                'is_active' => true,
            ]);

        $activity = Activity::where('description', 'product_updated')->first();
        expect($activity->subject_id)->toBe($product->id);
        expect($activity->subject_type)->toBe('App\Models\Product');
    });
});
