<?php

use App\Models\User;
use App\Models\Category;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    
    $this->unauthorizedUser = User::factory()->create();
});

test('unauthorized users cannot view categories', function () {
    $this->actingAs($this->unauthorizedUser)
        ->get('/categories')
        ->assertStatus(403);
});

test('admin can view categories list', function () {
    Category::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get('/categories')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('categories/index'));
});

test('admin can create a category', function () {
    $categoryData = [
        'name' => 'Brakes',
        'description' => 'All kinds of brakes',
    ];

    $this->actingAs($this->admin)
        ->post('/categories', $categoryData)
        ->assertRedirect('/categories');

    $this->assertDatabaseHas('categories', ['name' => 'Brakes']);
});

test('admin can update a category', function () {
    $category = Category::factory()->create(['name' => 'Old Name']);

    $this->actingAs($this->admin)
        ->put("/categories/{$category->id}", [
            'name' => 'New Name',
            'description' => 'Updated desc'
        ])
        ->assertRedirect('/categories');

    $this->assertDatabaseHas('categories', ['name' => 'New Name']);
});

test('admin can delete a category', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/categories/{$category->id}")
        ->assertRedirect('/categories');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
