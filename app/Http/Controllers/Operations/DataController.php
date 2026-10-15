<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\{Product, Customer, Order};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataController extends Controller
{
    // ==========================================
    // EXPORT FUNCTIONS
    // ==========================================
    
    public function exportProducts()
    {
        $products = Product::where('tenant_id', Auth::user()->tenant_id)->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['SKU', 'Name', 'Category', 'Unit', 'Cost Price', 'Selling Price', 'Stock Qty', 'Reorder Level', 'GST Rate (%)', 'Status'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        // Data
        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->sku);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->category);
            $sheet->setCellValue('D' . $row, $product->unit);
            $sheet->setCellValue('E' . $row, $product->cost_price);
            $sheet->setCellValue('F' . $row, $product->price);
            $sheet->setCellValue('G' . $row, $product->stock_qty);
            $sheet->setCellValue('H' . $row, $product->reorder_level);
            $sheet->setCellValue('I' . $row, $product->gst_rate);
            $sheet->setCellValue('J' . $row, $product->status);
            $row++;
        }

        $fileName = 'products_' . date('Y-m-d') . '.xlsx';
        $this->downloadExcel($spreadsheet, $fileName);
    }

    public function exportCustomers()
    {
        $customers = Customer::where('tenant_id', Auth::user()->tenant_id)->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Customer Code', 'Name', 'GSTIN', 'Phone', 'Email', 'Address', 'Credit Limit', 'Outstanding', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($customers as $customer) {
            $sheet->setCellValue('A' . $row, $customer->customer_code);
            $sheet->setCellValue('B' . $row, $customer->name);
            $sheet->setCellValue('C' . $row, $customer->gstin);
            $sheet->setCellValue('D' . $row, $customer->phone);
            $sheet->setCellValue('E' . $row, $customer->email);
            $sheet->setCellValue('F' . $row, $customer->address);
            $sheet->setCellValue('G' . $row, $customer->credit_limit);
            $sheet->setCellValue('H' . $row, $customer->outstanding_amount);
            $sheet->setCellValue('I' . $row, $customer->status);
            $row++;
        }

        $fileName = 'customers_' . date('Y-m-d') . '.xlsx';
        $this->downloadExcel($spreadsheet, $fileName);
    }

    public function exportOrders()
    {
        $orders = Order::with('customer')->where('tenant_id', Auth::user()->tenant_id)->latest()->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Order No', 'Date', 'Customer', 'Type', 'Subtotal', 'Tax', 'Total', 'Status', 'Payment Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $order->order_no);
            $sheet->setCellValue('B' . $row, $order->order_date->format('d M Y'));
            $sheet->setCellValue('C' . $row, $order->customer->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $order->type);
            $sheet->setCellValue('E' . $row, $order->subtotal);
            $sheet->setCellValue('F' . $row, $order->tax);
            $sheet->setCellValue('G' . $row, $order->total);
            $sheet->setCellValue('H' . $row, $order->status);
            $sheet->setCellValue('I' . $row, $order->payment_status);
            $row++;
        }

        $fileName = 'orders_' . date('Y-m-d') . '.xlsx';
        $this->downloadExcel($spreadsheet, $fileName);
    }

    // ==========================================
    // IMPORT FUNCTIONS
    // ==========================================

    public function importProducts(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:10240']);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();
        
        array_shift($rows); // Remove header row

        $errors = [];
        $successCount = 0;

        foreach ($rows as $index => $row) {
            if (empty($row[0])) continue; // Skip completely empty rows

            try {
                Product::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'sku' => trim($row[0]),
                    'name' => trim($row[1]),
                    'category' => trim($row[2]) ?: 'General',
                    'unit' => trim($row[3]) ?: 'PCS',
                    'cost_price' => (float) ($row[4] ?? 0),
                    'price' => (float) ($row[5] ?? 0),
                    'stock_qty' => (int) ($row[6] ?? 0),
                    'reorder_level' => (int) ($row[7] ?? 10),
                    'gst_rate' => (float) ($row[8] ?? 18),
                    'status' => 'active',
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . " ({$row[0]}): " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->back()->withErrors(['import' => $errors])->with('success', "Imported {$successCount} products, but some rows failed.");
        }

        return redirect()->back()->with('success', "Successfully imported {$successCount} products!");
    }

    public function importCustomers(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:10240']);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();
        
        array_shift($rows); // Remove header row

        $errors = [];
        $successCount = 0;

        foreach ($rows as $index => $row) {
            if (empty($row[0])) continue;

            try {
                Customer::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'customer_code' => trim($row[0]),
                    'name' => trim($row[1]),
                    'gstin' => trim($row[2]) ?: null,
                    'phone' => trim($row[3]) ?: null,
                    'email' => trim($row[4]) ?: null,
                    'address' => trim($row[5]) ?: null,
                    'credit_limit' => (float) ($row[6] ?? 0),
                    'outstanding_amount' => 0,
                    'status' => 'active',
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . " ({$row[0]}): " . $e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->back()->withErrors(['import' => $errors])->with('success', "Imported {$successCount} customers, but some rows failed.");
        }

        return redirect()->back()->with('success', "Successfully imported {$successCount} customers!");
    }

    // ==========================================
    // HELPER FUNCTIONS
    // ==========================================

    private function downloadExcel($spreadsheet, $fileName)
    {
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $fileName .'"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        $writer->save('php://output');
        exit;
    }

    public function downloadProductTemplate()
    {
        $templatePath = public_path('templates/product_import_template.xlsx');
        return response()->download($templatePath, 'product_import_template.xlsx');
    }

    public function downloadCustomerTemplate()
    {
        $templatePath = public_path('templates/customer_import_template.xlsx');
        return response()->download($templatePath, 'customer_import_template.xlsx');
    }
}