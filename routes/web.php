<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationController;

// Operations Controllers
use App\Http\Controllers\Operations\{
    InventoryController, OrderController, ProductionController, CustomerController,
    LogisticsController, MachineController, BomController, WarehouseController,
    WarehouseTransferController, WarehouseBinController, DataController, BarcodeController
};

// SCM Controllers
use App\Http\Controllers\Scm\{
    TraceabilityController, GrqrController, ReturnsController, WarehouseCapacityController
};

// Finance & HR Controllers
use App\Http\Controllers\FinanceHr\{
    ProcurementController, ExpenseController, HrPayrollController,
    CrmController, PosController, InvoiceController
};

// Intelligence Controllers
use App\Http\Controllers\Intelligence\{
    AnalyticsController, BiDashboardController, ReportController, DmsController
};

// Admin Controllers
use App\Http\Controllers\Admin\{
    CompanyController, UserController, AuditController, RoleController
};

// Email Controller
use App\Http\Controllers\Operations\EmailController;

// Backup Controller
use App\Http\Controllers\Admin\BackupController;

//signup Controller
use App\Http\Controllers\Auth\SignupController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


// ==========================================
// SIGNUP ROUTES (Public / Pre-Auth)

// Public Signup Routes
Route::get('/signup', [SignupController::class, 'showSignupForm'])->name('signup.form');
Route::post('/signup', [SignupController::class, 'register'])->name('signup.register');

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', fn() => redirect('/login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// PROTECTED ROUTES
// ==========================================
Route::middleware('auth')->group(function () {

    // Dashboard & Auth
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read'); // ✅ Changed to GET
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::delete('/notifications/{id}/delete', [NotificationController::class, 'delete'])->name('notifications.delete'); // ✅ Changed to DELETE
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
   
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');

    // ==========================================
    // 1. OPERATIONS
    // ==========================================
    Route::prefix('operations')->name('operations.')->group(function () {
        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::get('/inventory/export', [InventoryController::class, 'exportCsv'])->name('inventory.export');
        Route::get('/inventory/{id}/details', [InventoryController::class, 'getDetails'])->name('inventory.details'); // ✅ NEW AJAX ROUTE
        Route::post('/inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in'); 
        // Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'printInvoice'])->name('orders.invoice');

        // Production
        Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
        Route::post('/production', [ProductionController::class, 'store'])->name('production.store');
        Route::patch('/production/{workOrder}/progress', [ProductionController::class, 'updateProgress'])->name('production.update-progress');
        Route::get('/production/bom/{bom}/details', [ProductionController::class, 'showBomDetails'])->name('production.bom-details');

        // Customers
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Logistics
        Route::get('/logistics', [LogisticsController::class, 'index'])->name('logistics.index');
        Route::post('/logistics/shipment', [LogisticsController::class, 'storeShipment'])->name('logistics.shipment.store');
        Route::patch('/logistics/shipment/{shipment}/status', [LogisticsController::class, 'updateShipmentStatus'])->name('logistics.shipment.update-status');
        Route::post('/logistics/carrier', [LogisticsController::class, 'storeCarrier'])->name('logistics.carrier.store');
        Route::post('/logistics/driver', [LogisticsController::class, 'storeDriver'])->name('logistics.driver.store');
        Route::post('/logistics/vehicle', [LogisticsController::class, 'storeVehicle'])->name('logistics.vehicle.store');

        // Machines
        Route::get('/machines', [MachineController::class, 'index'])->name('machines.index');
        Route::post('/machines', [MachineController::class, 'store'])->name('machines.store');
        Route::put('/machines/{machine}', [MachineController::class, 'update'])->name('machines.update');
        Route::delete('/machines/{machine}', [MachineController::class, 'destroy'])->name('machines.destroy');

        // BOM
        Route::get('/bom', [BomController::class, 'index'])->name('bom.index');
        Route::get('/bom/create', [BomController::class, 'create'])->name('bom.create');
        Route::post('/bom', [BomController::class, 'store'])->name('bom.store');
        Route::get('/bom/{bom}', [BomController::class, 'show'])->name('bom.show');
        Route::get('/bom/{bom}/edit', [BomController::class, 'edit'])->name('bom.edit');
        Route::put('/bom/{bom}', [BomController::class, 'update'])->name('bom.update');
        Route::delete('/bom/{bom}', [BomController::class, 'destroy'])->name('bom.destroy');
        Route::patch('/bom/{bom}/activate', [BomController::class, 'activate'])->name('bom.activate');

        // Warehouses
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

        // Warehouse Transfers
        Route::get('/warehouse-transfers', [WarehouseTransferController::class, 'index'])->name('warehouse-transfers.index');
        Route::post('/warehouse-transfers', [WarehouseTransferController::class, 'store'])->name('warehouse-transfers.store');
        Route::post('/warehouse-transfers/{transfer}/receive', [WarehouseTransferController::class, 'receive'])->name('warehouse-transfers.receive');
        Route::get('/warehouse-transfers/batches', [WarehouseTransferController::class, 'getBatches'])->name('warehouse-transfers.batches');

        // Warehouse Bins
        Route::get('/warehouses/{warehouse}/bins', [WarehouseBinController::class, 'index'])->name('warehouse-bins.index');
        Route::post('/warehouses/{warehouse}/bins', [WarehouseBinController::class, 'store'])->name('warehouse-bins.store');
        Route::post('/warehouses/{warehouse}/bins/bulk', [WarehouseBinController::class, 'bulkCreate'])->name('warehouse-bins.bulk');
        Route::put('/bins/{bin}', [WarehouseBinController::class, 'update'])->name('warehouse-bins.update');
        Route::delete('/bins/{bin}', [WarehouseBinController::class, 'destroy'])->name('warehouse-bins.destroy');

        // Data Import/Export
        Route::get('/data', fn() => view('operations.data.import-export'))->name('data.index');
        Route::get('/export/products', [DataController::class, 'exportProducts'])->name('export.products');
        Route::get('/export/customers', [DataController::class, 'exportCustomers'])->name('export.customers');
        Route::get('/export/orders', [DataController::class, 'exportOrders'])->name('export.orders');
        Route::post('/import/products', [DataController::class, 'importProducts'])->name('import.products');
        Route::post('/import/customers', [DataController::class, 'importCustomers'])->name('import.customers');
        Route::get('/import/template/product', [DataController::class, 'downloadProductTemplate'])->name('import.template.product');
        Route::get('/import/template/customer', [DataController::class, 'downloadCustomerTemplate'])->name('import.template.customer');

        // Barcodes
        Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcodes.index');
        Route::post('/barcodes/generate', [BarcodeController::class, 'generate'])->name('barcodes.generate');
    });

    // ==========================================
    // 2. SCM (Supply Chain Management)
    // ==========================================
    Route::prefix('scm')->name('scm.')->group(function () {
        Route::get('/traceability', [TraceabilityController::class, 'index'])->name('traceability.index');
        Route::get('/traceability/journey/{batch}', [TraceabilityController::class, 'showJourney'])->name('traceability.journey');

        Route::get('/gr-qr', [GrqrController::class, 'index'])->name('gr-qr.index');
        Route::post('/gr-qr/post', [GrqrController::class, 'storePosting'])->name('gr-qr.store');
        Route::get('/gr-qr/{id}/print', [GrqrController::class, 'printPosting'])->name('gr-qr.print');

        Route::get('/returns', [ReturnsController::class, 'index'])->name('returns.index');
        Route::post('/returns', [ReturnsController::class, 'store'])->name('returns.store');
        Route::patch('/returns/{return}/status', [ReturnsController::class, 'updateStatus'])->name('returns.update-status');

        Route::get('/warehouse-capacity', [WarehouseCapacityController::class, 'index'])->name('warehouse-capacity.index');
        Route::post('/warehouse-capacity/bin', [WarehouseCapacityController::class, 'storeBin'])->name('warehouse-capacity.store-bin');
        Route::put('/warehouse-capacity/config', [WarehouseCapacityController::class, 'updateConfig'])->name('warehouse-capacity.update-config');
    });

    // ==========================================
    // 3. FINANCE & HR
    // ==========================================
    Route::prefix('finance-hr')->name('finance-hr.')->group(function () {
        // Finance / Invoices (Main list matches sidebar link)
        Route::get('/finance', [InvoiceController::class, 'index'])->name('finance.index');
        
        // Invoice CRUD
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.updateStatus');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/invoices/product/{product}', [InvoiceController::class, 'getProduct'])->name('invoices.product');

        // Procurement
        Route::get('/procurement', [ProcurementController::class, 'index'])->name('procurement.index');
        Route::post('/procurement/vendor', [ProcurementController::class, 'storeVendor'])->name('procurement.vendor.store');
        Route::post('/procurement/po', [ProcurementController::class, 'storePurchaseOrder'])->name('procurement.po.store');
        Route::patch('/procurement/po/{po}/status', [ProcurementController::class, 'updatePOStatus'])->name('procurement.po.update-status');
        
        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::patch('/expenses/{expense}/status', [ExpenseController::class, 'updateStatus'])->name('expenses.update-status');
        Route::post('/expenses/category', [ExpenseController::class, 'storeCategory'])->name('expenses.category.store');

        // HR & Payroll
        Route::get('/hr', [HrPayrollController::class, 'index'])->name('hr.index');
        Route::post('/hr/employee', [HrPayrollController::class, 'storeEmployee'])->name('hr.employee.store');
        Route::post('/hr/department', [HrPayrollController::class, 'storeDepartment'])->name('hr.department.store');
        Route::post('/hr/payroll/process', [HrPayrollController::class, 'processPayroll'])->name('hr.payroll.process');
        Route::get('/hr/payroll/{payroll}/slip/{employee}', [HrPayrollController::class, 'showPayrollSlip'])->name('hr.payroll.slip');
        Route::post('/hr/leave', [HrPayrollController::class, 'applyLeave'])->name('hr.leave.store');
        Route::patch('/hr/leave/{leave}/status', [HrPayrollController::class, 'updateLeaveStatus'])->name('hr.leave.update-status');

        // CRM
        Route::get('/crm', [CrmController::class, 'index'])->name('crm.index');
        Route::post('/crm/lead', [CrmController::class, 'storeLead'])->name('crm.lead.store');
        Route::patch('/crm/lead/{lead}/status', [CrmController::class, 'updateLeadStatus'])->name('crm.lead.update-status');
        Route::post('/crm/dealer', [CrmController::class, 'storeDealer'])->name('crm.dealer.store');
        Route::put('/crm/dealer/{dealer}', [CrmController::class, 'updateDealer'])->name('crm.dealer.update');
        Route::post('/crm/ticket', [CrmController::class, 'storeTicket'])->name('crm.ticket.store');
        Route::patch('/crm/ticket/{ticket}/status', [CrmController::class, 'updateTicketStatus'])->name('crm.ticket.update-status');

         // PDF Downloads
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::get('/pos/receipt/{receipt_no}/pdf', [PosController::class, 'downloadReceiptPdf'])->name('pos.receipt.pdf');
        
            // ==========================================
    // POS Routes (Order Matters!)
    // ==========================================
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [PosController::class, 'searchProduct'])->name('pos.search');
    Route::post('/pos/transaction', [PosController::class, 'processTransaction'])->name('pos.transaction');
    
    // ✅ 1. PDF Route MUST come FIRST
    Route::get('/pos/receipt/{receipt_no}/pdf', [PosController::class, 'downloadReceiptPdf'])->name('pos.receipt.pdf');
    
    // ✅ 2. Generic Receipt Route comes SECOND
    Route::get('/pos/receipt/{receipt_no}', [PosController::class, 'printReceipt'])->name('pos.receipt');
    
    Route::get('/pos/today-sales', [PosController::class, 'todaySales'])->name('pos.today-sales');
    });

    // ==========================================
    // 4. INTELLIGENCE
    // ==========================================
    Route::prefix('intelligence')->name('intelligence.')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/bi', [BiDashboardController::class, 'index'])->name('bi.index');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');
        Route::get('/dms', [DmsController::class, 'index'])->name('dms.index');
        Route::post('/dms', [DmsController::class, 'store'])->name('dms.store');
        Route::delete('/dms/{document}', [DmsController::class, 'destroy'])->name('dms.destroy');
    });

    // ==========================================
    // 5. ADMIN
    // ==========================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
        Route::post('/company', [CompanyController::class, 'update'])->name('company.update');
        
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        // Backup Management
       Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');
    Route::get('/backups/download/{fileName}', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{fileName}', [BackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('/backups/restore/{fileName}', [BackupController::class, 'restore'])->name('backups.restore');
        });

    // ==========================================
    // 6. GLOBAL EMAIL ROUTES
    // ==========================================
    Route::post('/invoices/{invoice}/send-email', [EmailController::class, 'sendInvoice'])->name('invoices.send-email');
    Route::post('/pos/{transaction}/send-receipt', [EmailController::class, 'sendReceipt'])->name('pos.send-receipt');
    Route::post('/email/test', [EmailController::class, 'testEmail'])->name('email.test');
});