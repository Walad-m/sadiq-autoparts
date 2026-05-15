<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('expenses', ExpenseController::class)->except(['show']);
    Route::resource('sales', SaleController::class)->only(['index', 'show', 'destroy']);

    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
    Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import');
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
    Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
    Route::post('suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');
    Route::get('expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
    Route::post('expenses/import', [ExpenseController::class, 'import'])->name('expenses.import');

    // Sales refunds and exchanges
    Route::get('sales/{sale}/refund', [SaleController::class, 'initiateRefund'])
        ->name('sales.refund.initiate');
    Route::post('sales/{sale}/refund', [SaleController::class, 'refund'])
        ->name('sales.refund');

    Route::get('sales/{sale}/exchange', [SaleController::class, 'initiateExchange'])
        ->name('sales.exchange.initiate');
    Route::post('sales/{sale}/exchange', [SaleController::class, 'processExchange'])
        ->name('sales.exchange.process');

    // Point of Sale
    Route::middleware('can:access-pos')->group(function () {
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('pos/search', [PosController::class, 'search'])->name('pos.search');
        Route::post('pos', [PosController::class, 'store'])->name('pos.store')->middleware('can:create-sale');
        Route::get('pos/{sale}/receipt', [PosController::class, 'receipt'])->name('pos.receipt')->middleware('can:print-receipt');
    });

    // Reports
    Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/settings.php';
