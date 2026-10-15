<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        // Default to Sales Report
        $reportType = $request->get('type', 'sales');
        $startDate = $request->get('start_date', now()->subMonths(1)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $data = [];
        $columns = [];
        $title = '';

        switch ($reportType) {
            case 'sales':
                $title = 'Sales & Revenue Report';
                $columns = ['Date', 'Invoice/Receipt #', 'Customer/Type', 'Amount', 'Tax', 'Total', 'Status'];
                $data = Invoice::where('tenant_id', $tenantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->select('created_at', 'invoice_no', 'type', 'total', 'cgst', 'sgst', 'igst', 'status')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'date' => $item->created_at->format('d M Y'),
                            'ref' => $item->invoice_no,
                            'entity' => ucfirst($item->type),
                            'amount' => $item->total - ($item->cgst + $item->sgst + $item->igst),
                            'tax' => $item->cgst + $item->sgst + $item->igst,
                            'total' => $item->total,
                            'status' => ucfirst($item->status),
                        ];
                    });
                break;

            case 'expenses':
                $title = 'Expense Summary Report';
                $columns = ['Date', 'Expense #', 'Category', 'Vendor', 'Amount', 'Status'];
                $data = Expense::with('category')
                    ->where('tenant_id', $tenantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->select('created_at', 'expense_no', 'category_id', 'vendor', 'total', 'status')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'date' => $item->created_at->format('d M Y'),
                            'ref' => $item->expense_no,
                            'entity' => $item->category->name ?? 'Uncategorized',
                            'vendor' => $item->vendor ?? 'N/A',
                            'amount' => $item->total,
                            'status' => ucfirst($item->status),
                        ];
                    });
                break;

            case 'inventory':
                $title = 'Inventory Valuation Report';
                $columns = ['SKU', 'Product Name', 'Qty', 'Unit Cost', 'Total Value', 'Status'];
                $data = Product::where('tenant_id', $tenantId)
                    ->select('sku', 'name', 'stock_qty', 'cost_price', 'status')
                    ->orderBy('name')
                    ->get()
                    ->map(function($item) {
                        return [
                            'sku' => $item->sku,
                            'name' => $item->name,
                            'qty' => $item->stock_qty,
                            'cost' => $item->cost_price,
                            'value' => $item->stock_qty * $item->cost_price,
                            'status' => ucfirst($item->status),
                        ];
                    });
                break;

            case 'aging':
                $title = 'Accounts Receivable Aging Report';
                $columns = ['Customer', 'Invoice #', 'Due Date', 'Total', 'Current', '1-30 Days', '31-60 Days', '60+ Days'];
                $data = Invoice::with('customer')
                    ->where('tenant_id', $tenantId)
                    ->whereIn('status', ['sent', 'overdue'])
                    ->select('customer_id', 'invoice_no', 'due_date', 'total')
                    ->orderBy('due_date', 'asc')
                    ->get()
                    ->map(function($item) {
                        $daysOverdue = $item->due_date ? now()->diffInDays($item->due_date) : 0;
                        return [
                            'customer' => $item->customer->name ?? 'Unknown',
                            'ref' => $item->invoice_no,
                            'due_date' => $item->due_date?->format('d M Y') ?? 'N/A',
                            'total' => $item->total,
                            'current' => $daysOverdue <= 0 ? $item->total : 0,
                            'd30' => $daysOverdue > 0 && $daysOverdue <= 30 ? $item->total : 0,
                            'd60' => $daysOverdue > 30 && $daysOverdue <= 60 ? $item->total : 0,
                            'd60_plus' => $daysOverdue > 60 ? $item->total : 0,
                        ];
                    });
                break;
        }

        $reportTypes = [
            'sales' => ['icon' => 'bi-cash-coin', 'name' => 'Sales & Revenue', 'desc' => 'Track all invoice and POS income'],
            'expenses' => ['icon' => 'bi-receipt', 'name' => 'Expense Summary', 'desc' => 'Categorized business expenditures'],
            'inventory' => ['icon' => 'bi-box-seam', 'name' => 'Inventory Valuation', 'desc' => 'Current stock levels and asset value'],
            'aging' => ['icon' => 'bi-calendar-x', 'name' => 'A/R Aging', 'desc' => 'Overdue invoices and payment delays'],
        ];

        return view('intelligence.reports.index', compact(
            'reportType', 'reportTypes', 'title', 'columns', 'data', 'startDate', 'endDate'
        ));
    }

    public function exportCsv(Request $request)
    {
        $request->validate(['type' => 'required|string']);
        
        // Re-use the index logic to get data (simplified for brevity)
        $response = $this->index($request);
        $viewData = $response->getData(true); // Get view data from response
        
        $data = $viewData['data'] ?? [];
        $columns = $viewData['columns'] ?? [];
        $title = $viewData['title'] ?? 'Report';

        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($columns, $data) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($file, $columns);
            
            // Write rows
            foreach ($data as $row) {
                // Ensure values are strings/numbers for CSV
                fputcsv($file, array_map(function($value) {
                    return is_numeric($value) ? $value : (string)$value;
                }, array_values($row)));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}