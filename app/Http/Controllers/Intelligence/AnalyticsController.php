<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\{Invoice, PosTransaction, Expense, Order, Product, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id; // Can be null for Super Admin
        
        // Date range filter (default: last 12 months)
        $months = $request->get('months', 12);
        $startDate = now()->subMonths($months)->startOfMonth();
        $endDate = now()->endOfMonth();

        // ✅ 1. DYNAMIC DATE FORMATTING (Fixes SQLite vs MySQL error)
        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite' 
            ? "strftime('%Y-%m', created_at)" 
            : "DATE_FORMAT(created_at, '%Y-%m')";

        // Helper function to safely apply tenant filter if tenantId exists
        $applyTenant = function ($query) use ($tenantId) {
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
        };

        // ============ REVENUE DATA ============
        
        // Monthly Invoice Revenue
        $invoiceQuery = Invoice::query();
        $applyTenant($invoiceQuery);
        $invoiceRevenue = $invoiceQuery
            ->where('status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw("{$dateFormat} as month"),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        // Monthly POS Revenue
        $posQuery = PosTransaction::query();
        $applyTenant($posQuery);
        $posRevenue = $posQuery
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw("{$dateFormat} as month"),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        // Generate last N months labels
        $labels = [];
        $invoiceData = [];
        $posData = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $invoiceData[] = (float)($invoiceRevenue[$month] ?? 0);
            $posData[] = (float)($posRevenue[$month] ?? 0);
        }

        // ============ EXPENSE DATA ============
        $expenseQuery = Expense::with('category');
        $applyTenant($expenseQuery);
        
        $expenseByCategory = $expenseQuery
            ->where('status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->select('category_id', DB::raw('SUM(total) as total'))
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Uncategorized',
                    'icon' => $item->category->icon ?? 'bi-receipt',
                    'color' => $item->category->color ?? 'secondary',
                    'total' => (float)$item->total,
                ];
            });

        // ============ TOP PRODUCTS ============
        $posTransQuery = PosTransaction::query();
        $applyTenant($posTransQuery);

        $topProducts = $posTransQuery
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->get()
            ->flatMap(function ($t) {
                return collect($t->items ?? []);
            })
            ->groupBy('name')
            ->map(function ($items, $name) {
                return [
                    'name' => $name,
                    'qty' => $items->sum('qty'),
                    'revenue' => $items->sum('total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        // ============ PAYMENT METHOD BREAKDOWN ============
        $paymentQuery = PosTransaction::query();
        $applyTenant($paymentQuery);

        $paymentMethods = $paymentQuery
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select('payment_method', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => ucfirst(str_replace('_', ' ', $item->payment_method)),
                    'total' => (float)$item->total,
                    'count' => (int)$item->count,
                ];
            });

        // ============ TOP CUSTOMERS ============
        $invoiceCustQuery = Invoice::with('customer');
        $applyTenant($invoiceCustQuery);

        $topCustomers = $invoiceCustQuery
            ->where('status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->take(5)
            ->get()
            ->map(function ($item) {
                // ✅ FIXED: Uses eager-loaded relationship instead of Customer::find() (Prevents N+1 queries)
                return [
                    'name' => $item->customer->name ?? 'Walk-in Customer',
                    'email' => $item->customer->email ?? '',
                    'revenue' => (float)$item->revenue,
                    'orders' => (int)$item->orders,
                ];
            });

        // ============ KPI CARDS ============
        $currentPeriodRevenue = array_sum($invoiceData) + array_sum($posData);
        
        $prevInvoiceRev = Invoice::query();
        $applyTenant($prevInvoiceRev);
        $previousPeriodRevenue = $prevInvoiceRev
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate->copy()->subMonths($months), $startDate])
            ->sum('total');

        $prevPosRev = PosTransaction::query();
        $applyTenant($prevPosRev);
        $previousPeriodRevenue += $prevPosRev
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate->copy()->subMonths($months), $startDate])
            ->sum('total');

        $revenueGrowth = $previousPeriodRevenue > 0 
            ? round((($currentPeriodRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100, 1) 
            : 0;

        $expQuery1 = Expense::query();
        $applyTenant($expQuery1);
        $currentExpenses = $expQuery1
            ->where('status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->sum('total');

        $expQuery2 = Expense::query();
        $applyTenant($expQuery2);
        $previousExpenses = $expQuery2
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate->copy()->subMonths($months), $startDate])
            ->sum('total');

        $expenseGrowth = $previousExpenses > 0 
            ? round((($currentExpenses - $previousExpenses) / $previousExpenses) * 100, 1) 
            : 0;

        $ordQuery = Order::query();
        $applyTenant($ordQuery);
        $totalOrders = $ordQuery->where('created_at', '>=', $startDate)->count();

        $custQuery = Customer::query();
        $applyTenant($custQuery);
        $totalCustomers = $custQuery->where('created_at', '>=', $startDate)->count();

        $stats = [
            'revenue' => $currentPeriodRevenue,
            'revenue_growth' => $revenueGrowth,
            'expenses' => $currentExpenses,
            'expense_growth' => $expenseGrowth,
            'profit' => $currentPeriodRevenue - $currentExpenses,
            'profit_margin' => $currentPeriodRevenue > 0 
                ? round((($currentPeriodRevenue - $currentExpenses) / $currentPeriodRevenue) * 100, 1) 
                : 0,
            'orders' => $totalOrders,
            'new_customers' => $totalCustomers,
        ];

        return view('intelligence.analytics.index', compact(
            'labels', 'invoiceData', 'posData',
            'expenseByCategory', 'topProducts', 'paymentMethods',
            'topCustomers', 'stats', 'months'
        ));
    }
}