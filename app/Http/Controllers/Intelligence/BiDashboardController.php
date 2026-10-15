<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Invoice;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BiDashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        // 1. NORTH STAR METRICS
        $totalRevenue = Invoice::where('tenant_id', $tenantId)->where('status', 'paid')->sum('total') + 
                        PosTransaction::where('tenant_id', $tenantId)->where('status', 'completed')->sum('total');
        
        $revenueTarget = 5000000; // Example target (can be made dynamic later)
        $targetAchievement = $revenueTarget > 0 ? round(($totalRevenue / $revenueTarget) * 100, 1) : 0;

        $totalLeads = Lead::where('tenant_id', $tenantId)->count();
        $wonLeads = Lead::where('tenant_id', $tenantId)->where('status', 'won')->count();
        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        // Inventory Turnover (Simplified: Cost of Goods Sold / Average Inventory)
        $totalStockValue = Product::where('tenant_id', $tenantId)->sum(DB::raw('stock_qty * cost_price'));
        $inventoryHealth = $totalStockValue > 0 ? 'Healthy' : 'Critical';

        // 2. SALES FUNNEL DATA
        $funnelData = [
            'New Leads' => Lead::where('tenant_id', $tenantId)->where('status', 'new')->count(),
            'Contacted' => Lead::where('tenant_id', $tenantId)->where('status', 'contacted')->count(),
            'Quoted' => Lead::where('tenant_id', $tenantId)->where('status', 'quoted')->count(),
            'Won' => $wonLeads,
        ];

        // 3. INVENTORY HEALTH (Fast vs Slow Moving)
        // Simplified logic: Products with stock > reorder_level are "Healthy", others are "Low Stock"
        $healthyStock = Product::where('tenant_id', $tenantId)->whereColumn('stock_qty', '>', 'reorder_level')->count();
        $lowStock = Product::where('tenant_id', $tenantId)->whereColumn('stock_qty', '<=', 'reorder_level')->count();
        $outOfStock = Product::where('tenant_id', $tenantId)->where('stock_qty', 0)->count();

        // 4. SMART INSIGHTS (Actionable Alerts)
        $insights = [];
        
        // Insight 1: Overdue Invoices
        $overdueInvoices = Invoice::where('tenant_id', $tenantId)->where('status', 'overdue')->count();
        if ($overdueInvoices > 0) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'bi-exclamation-triangle-fill',
                'title' => 'Payment Delays',
                'message' => "{$overdueInvoices} invoices are overdue. Total pending: ₹" . number_format(Invoice::where('tenant_id', $tenantId)->where('status', 'overdue')->sum('total'), 0),
                'action' => 'View Invoices',
                'link' => route('finance-hr.finance.index', ['tab' => 'invoices'])
            ];
        }

        // Insight 2: Low Stock
        if ($lowStock > 0 || $outOfStock > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-box-seam-fill',
                'title' => 'Inventory Alert',
                'message' => "{$outOfStock} items are out of stock, and {$lowStock} are below reorder level.",
                'action' => 'Check Inventory',
                'link' => route('operations.inventory.index', ['stock_status' => 'low'])
            ];
        }

        // Insight 3: Urgent Tickets
        $urgentTickets = Ticket::where('tenant_id', $tenantId)->where('priority', 'urgent')->where('status', '!=', 'closed')->count();
        if ($urgentTickets > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-headset',
                'title' => 'Customer Support',
                'message' => "{$urgentTickets} urgent support tickets require immediate attention.",
                'action' => 'View Tickets',
                'link' => route('finance-hr.crm.index', ['tab' => 'tickets'])
            ];
        }

        // Default insight if everything is perfect
        if (empty($insights)) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-check-circle-fill',
                'title' => 'All Systems Optimal',
                'message' => 'No critical alerts. Business operations are running smoothly.',
                'action' => 'View Reports',
                'link' => '#'
            ];
        }

                return view('intelligence.bi.index', compact(
            'totalRevenue', 'revenueTarget', 'targetAchievement',
            'conversionRate', 'inventoryHealth', 'totalStockValue',
            'funnelData', 'healthyStock', 'lowStock', 'outOfStock',
            'insights',
            'wonLeads',      // ✅ ADDED
            'totalLeads'     // ✅ ADDED
        ));
    }
}