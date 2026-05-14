<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Today's metrics
        $todaySales = Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total');

        $todayTransactions = Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        // Totals
        $totalProducts = Product::where('is_active', true)->count();
        $totalCustomers = Customer::count();

        // Monthly metrics
        $monthlyRevenue = Sale::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('status', 'completed')
            ->sum('total');

        $monthlyExpenses = Expense::whereMonth('expense_date', $currentMonth)
            ->whereYear('expense_date', $currentYear)
            ->sum('amount');

        $grossProfit = $monthlyRevenue - $monthlyExpenses;

        // Low-stock products
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('quantity')
            ->limit(10)
            ->get(['id', 'name', 'quantity', 'reorder_level']);

        // Recent sales (last 10)
        $recentSales = Sale::with('customer')
            ->where('status', 'completed')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('dashboard', [
            'todaySales' => $todaySales,
            'todayTransactions' => $todayTransactions,
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyExpenses' => $monthlyExpenses,
            'grossProfit' => $grossProfit,
            'lowStockProducts' => $lowStockProducts,
            'recentSales' => $recentSales,
        ]);
    }
}
