<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Operations\InventoryController;
use App\Http\Controllers\Operations\OrderController;
use App\Http\Controllers\Operations\ProductionController;
use App\Http\Controllers\Operations\CustomerController;
use App\Http\Controllers\Operations\LogisticsController;
use App\Http\Controllers\Operations\MachineController;
use App\Http\Controllers\Operations\BomController;
use App\Http\Controllers\Scm\TraceabilityController;
use App\Http\Controllers\Scm\GrqrController;
use App\Http\Controllers\Scm\ReturnsController;
use App\Http\Controllers\Scm\WarehouseCapacityController;
use App\Http\Controllers\FinanceHr\FinanceController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



// Public Routes
Route::get('/', fn() => redirect('/login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // --- SETTINGS ROUTES ---
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');

    Route::prefix('operations')->name('operations.')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::get('/inventory/export', [InventoryController::class, 'exportCsv'])->name('inventory.export');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'printInvoice'])->name('orders.invoice');

        Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
        Route::post('/production', [ProductionController::class, 'store'])->name('production.store');
        Route::patch('/production/{workOrder}/progress', [ProductionController::class, 'updateProgress'])->name('production.update-progress');
        Route::get('/production/bom/{bom}/details', [ProductionController::class, 'showBomDetails'])->name('production.bom-details');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        Route::get('/logistics', [LogisticsController::class, 'index'])->name('logistics.index');
        Route::post('/logistics/shipment', [LogisticsController::class, 'storeShipment'])->name('logistics.shipment.store');
        Route::patch('/logistics/shipment/{shipment}/status', [LogisticsController::class, 'updateShipmentStatus'])->name('logistics.shipment.update-status');
        Route::post('/logistics/carrier', [LogisticsController::class, 'storeCarrier'])->name('logistics.carrier.store');
        Route::post('/logistics/driver', [LogisticsController::class, 'storeDriver'])->name('logistics.driver.store');
        Route::post('/logistics/vehicle', [LogisticsController::class, 'storeVehicle'])->name('logistics.vehicle.store');

        Route::get('/machines', [MachineController::class, 'index'])->name('machines.index');
        Route::post('/machines', [MachineController::class, 'store'])->name('machines.store');
        Route::put('/machines/{machine}', [MachineController::class, 'update'])->name('machines.update');
        Route::delete('/machines/{machine}', [MachineController::class, 'destroy'])->name('machines.destroy');


        Route::get('/bom', [BomController::class, 'index'])->name('bom.index');
        Route::get('/bom/create', [BomController::class, 'create'])->name('bom.create');
        Route::post('/bom', [BomController::class, 'store'])->name('bom.store');
        Route::get('/bom/{bom}', [BomController::class, 'show'])->name('bom.show');
        Route::get('/bom/{bom}/edit', [BomController::class, 'edit'])->name('bom.edit');
        Route::put('/bom/{bom}', [BomController::class, 'update'])->name('bom.update');
        Route::delete('/bom/{bom}', [BomController::class, 'destroy'])->name('bom.destroy');
        Route::patch('/bom/{bom}/activate', [BomController::class, 'activate'])->name('bom.activate');

    });

    Route::middleware('auth')->prefix('scm')->name('scm.')->group(function () {
    // Traceability
    Route::get('/traceability', [TraceabilityController::class, 'index'])->name('traceability.index');
    Route::get('/traceability/batch/{batch}', [TraceabilityController::class, 'show'])->name('traceability.show');
    
    // GR/QR Posting
    Route::get('/gr-qr', [GrqrController::class, 'index'])->name('gr-qr.index');
    Route::post('/gr-qr/post', [GrqrController::class, 'storePosting'])->name('gr-qr.store');


     // Returns
    Route::get('/returns', [ReturnsController::class, 'index'])->name('returns.index');
    Route::post('/returns', [ReturnsController::class, 'store'])->name('returns.store');
    Route::patch('/returns/{return}/status', [ReturnsController::class, 'updateStatus'])->name('returns.update-status');

    // Warehouse Capacity
    Route::get('/warehouse-capacity', [WarehouseCapacityController::class, 'index'])->name('warehouse-capacity.index');
    Route::post('/warehouse-capacity/bin', [WarehouseCapacityController::class, 'storeBin'])->name('warehouse-capacity.store-bin');
    Route::put('/warehouse-capacity/config', [WarehouseCapacityController::class, 'updateConfig'])->name('warehouse-capacity.update-config');


});


Route::middleware('auth')->prefix('finance-hr')->name('finance-hr.')->group(function () {
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
});
});