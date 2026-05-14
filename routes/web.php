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
