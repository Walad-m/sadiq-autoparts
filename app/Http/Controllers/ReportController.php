<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Expense;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        // Simple initial dashboard for reports
        // We can build comprehensive reporting later, for now we want the page to work.
        
        $today = Carbon::today();
        
        $todaySales = Sale::whereDate('created_at', $today)->where('status', 'completed')->sum('total');
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');
        
        return Inertia::render('reports/index', [
            'stats' => [
                'today_sales' => $todaySales,
                'today_expenses' => $todayExpenses,
                'today_profit' => $todaySales - $todayExpenses,
            ]
        ]);
    }
}
