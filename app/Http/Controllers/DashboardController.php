<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Invoice, PosTransaction, Order, Product,
    Customer, Employee, Lead, Ticket, Expense, AuditLog, Leave
};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id; // Can be null for global Super Admin

        // ============ 1. KPI STATS ============
        $totalRevenue = $this->calculateTotalRevenue($tenantId);
        $totalOrders = $tenantId ? Order::where('tenant_id', $tenantId)->count() : 0;
        $pendingInvoices = $tenantId ? Invoice::where('tenant_id', $tenantId)->whereIn('status', ['sent', 'overdue'])->count() : 0;
        $lowStockCount = $tenantId ? Product::where('tenant_id', $tenantId)->whereColumn('stock_qty', '<=', 'reorder_level')->where('status', 'active')->count() : 0;
        $activeEmployees = $tenantId ? Employee::where('tenant_id', $tenantId)->where('status', 'active')->count() : 0;
        $openTickets = $tenantId ? Ticket::where('tenant_id', $tenantId)->whereIn('status', ['open', 'in_progress'])->count() : 0;

        $stats = [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'pending_invoices' => $pendingInvoices,
            'low_stock_count' => $lowStockCount,
            'active_employees' => $activeEmployees,
            'open_tickets' => $openTickets,
        ];

        // ============ 2. REVENUE TREND (Last 7 Days) ============
        $revenueTrend = $this->getLast7DaysRevenue($tenantId);

        // ============ 3. RECENT ORDERS ============
        $recentOrders = $tenantId 
            ? Order::with('customer')->where('tenant_id', $tenantId)->latest()->take(5)->get() 
            : collect();

        // ============ 4. LOW STOCK ALERTS ============
        $lowStockProducts = $this->getLowStockProducts($tenantId);

        // ============ 5. PENDING ITEMS (Needs Attention) ============
        $pendingItems = $this->getPendingItems($tenantId);

        // ============ 6. RECENT ACTIVITY (from Audit Log) ============
        $recentActivity = $tenantId 
            ? AuditLog::with('user')->where('tenant_id', $tenantId)->latest()->take(8)->get() 
            : collect();

        // ============ 7. TOP PERFORMING PRODUCTS (by POS sales) ============
        $topProducts = $this->getTopProducts($tenantId);

        return view('dashboard', compact(
            'user', 'stats', 'revenueTrend', 'recentOrders',
            'lowStockProducts', 'pendingItems', 'recentActivity', 'topProducts'
        ));
    }

    /**
     * Calculate total revenue from Invoices + POS
     */
    private function calculateTotalRevenue(?int $tenantId): float
    {
        if (!$tenantId) {
            return 0.0;
        }

        $invoiceRevenue = Invoice::where('tenant_id', $tenantId)->where('status', 'paid')->sum('total');
        $posRevenue = PosTransaction::where('tenant_id', $tenantId)->where('status', 'completed')->sum('total');

        return (float) ($invoiceRevenue + $posRevenue);
    }

    /**
     * Get revenue for last 7 days (for mini chart)
     */
    private function getLast7DaysRevenue(?int $tenantId): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D'); // Mon, Tue, etc.

            if ($tenantId) {
                $invoiceRev = Invoice::where('tenant_id', $tenantId)
                    ->where('status', 'paid')
                    ->whereDate('created_at', $date)
                    ->sum('total');

                $posRev = PosTransaction::where('tenant_id', $tenantId)
                    ->where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total');

                $data[] = (float) ($invoiceRev + $posRev);
            } else {
                $data[] = 0.0;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get pending items requiring attention
     */
    private function getPendingItems(?int $tenantId): array
    {
        $items = [];

        if (!$tenantId) {
            return $items;
        }

        // Pending Expenses
        $pendingExpenses = Expense::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        if ($pendingExpenses > 0) {
            $items[] = [
                'type' => 'expense',
                'icon' => 'bi-receipt',
                'color' => 'warning',
                'title' => "{$pendingExpenses} expenses awaiting approval",
                'link' => route('finance-hr.expenses.index', ['status' => 'pending']),
            ];
        }

        // Pending Leaves
        $pendingLeaves = Leave::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        if ($pendingLeaves > 0) {
            $items[] = [
                'type' => 'leave',
                'icon' => 'bi-calendar-event',
                'color' => 'info',
                'title' => "{$pendingLeaves} leave applications pending",
                'link' => route('finance-hr.hr.index', ['tab' => 'leaves']),
            ];
        }

        // Overdue Invoices
        $overdueInvoices = Invoice::where('tenant_id', $tenantId)->where('status', 'overdue')->count();
        if ($overdueInvoices > 0) {
            $items[] = [
                'type' => 'invoice',
                'icon' => 'bi-exclamation-triangle',
                'color' => 'danger',
                'title' => "{$overdueInvoices} overdue invoices",
                'link' => route('finance-hr.finance.index', ['status' => 'overdue']),
            ];
        }

        // Urgent Tickets
        $urgentTickets = Ticket::where('tenant_id', $tenantId)->where('priority', 'urgent')->where('status', '!=', 'closed')->count();
        if ($urgentTickets > 0) {
            $items[] = [
                'type' => 'ticket',
                'icon' => 'bi-headset',
                'color' => 'danger',
                'title' => "{$urgentTickets} urgent support tickets",
                'link' => route('finance-hr.crm.index', ['tab' => 'tickets']),
            ];
        }

        return $items;
    }

    /**
     * Get low stock products
     */
    private function getLowStockProducts(?int $tenantId)
    {
        if (!$tenantId) {
            return collect();
        }
        
        return Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereColumn('stock_qty', '<=', 'reorder_level')
            ->orderBy('stock_qty')
            ->take(5)
            ->get();
    }

    /**
     * Get top 5 products by POS revenue (Parses JSON items column)
     */
    private function getTopProducts(?int $tenantId)
    {
        if (!$tenantId) {
            return collect();
        }

        // Fetch completed POS transactions
        $transactions = PosTransaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereNotNull('items')
            ->get();

        $productStats = [];

        foreach ($transactions as $transaction) {
            $items = json_decode($transaction->items, true);
            
            if (is_array($items)) {
                foreach ($items as $item) {
                    $productId = $item['product_id'] ?? null;
                    $productName = $item['name'] ?? 'Unknown Product';
                    $qty = (int) ($item['qty'] ?? 0);
                    $total = (float) ($item['total'] ?? 0);

                    if ($productId) {
                        if (!isset($productStats[$productId])) {
                            $productStats[$productId] = [
                                'product_id' => $productId,
                                'name' => $productName,
                                'qty' => 0,
                                'revenue' => 0.0,
                            ];
                        }
                        $productStats[$productId]['qty'] += $qty;
                        $productStats[$productId]['revenue'] += $total;
                    }
                }
            }
        }

        // Sort by revenue descending and take top 5
        return collect($productStats)
            ->sortByDesc('revenue')
            ->take(5)
            ->values();
    }
}