php artisan db:seed --class=SuperAdminSeeder

✅ SuperAdmin created: admin@alphagrow.com / password123



## use this script to test and sed data using ``php artisan tkinter``


<?php
// ========================================
// 🎯 SignUpAlphaGrow - Master Test Data
// ========================================

$tenantId = 1; // Your SuperAdmin tenant

echo "🚀 Starting test data generation...\n\n";

// ========================================
// 📦 SECTION 1: PRODUCTS & BATCHES
// ========================================
echo "📦 Creating Products...\n";

$products = [
    ['sku' => 'PRD-LAPTOP-001', 'name' => 'Business Laptop Pro', 'category' => 'Electronics', 'unit' => 'PCS', 'price' => 55000, 'cost_price' => 42000, 'stock_qty' => 45, 'reorder_level' => 10, 'gst_rate' => 18],
    ['sku' => 'PRD-MOUSE-002', 'name' => 'Wireless Mouse', 'category' => 'Electronics', 'unit' => 'PCS', 'price' => 850, 'cost_price' => 450, 'stock_qty' => 120, 'reorder_level' => 20, 'gst_rate' => 18],
    ['sku' => 'PRD-CHAIR-003', 'name' => 'Ergonomic Office Chair', 'category' => 'Furniture', 'unit' => 'PCS', 'price' => 12500, 'cost_price' => 8500, 'stock_qty' => 8, 'reorder_level' => 5, 'gst_rate' => 18],
    ['sku' => 'PRD-PAPER-004', 'name' => 'A4 Paper Bundle (500)', 'category' => 'Stationery', 'unit' => 'BOX', 'price' => 450, 'cost_price' => 280, 'stock_qty' => 200, 'reorder_level' => 50, 'gst_rate' => 12],
    ['sku' => 'PRD-TONER-005', 'name' => 'Printer Toner Cartridge', 'category' => 'Stationery', 'unit' => 'PCS', 'price' => 2800, 'cost_price' => 1800, 'stock_qty' => 3, 'reorder_level' => 10, 'gst_rate' => 18],
    ['sku' => 'RAW-STEEL-001', 'name' => 'Steel Rod 10mm', 'category' => 'Raw Material', 'unit' => 'KG', 'price' => 85, 'cost_price' => 65, 'stock_qty' => 500, 'reorder_level' => 100, 'gst_rate' => 18],
    ['sku' => 'RAW-PLASTIC-002', 'name' => 'Plastic Granules', 'category' => 'Raw Material', 'unit' => 'KG', 'price' => 120, 'cost_price' => 85, 'stock_qty' => 300, 'reorder_level' => 50, 'gst_rate' => 18],
    ['sku' => 'PRD-PHONE-006', 'name' => 'Smartphone X1', 'category' => 'Electronics', 'unit' => 'PCS', 'price' => 25000, 'cost_price' => 18000, 'stock_qty' => 25, 'reorder_level' => 10, 'gst_rate' => 18],
];

$productIds = [];
foreach ($products as $p) {
    $prod = App\Models\Product::firstOrCreate(
        ['sku' => $p['sku'], 'tenant_id' => $tenantId],
        array_merge($p, ['tenant_id' => $tenantId, 'status' => 'active'])
    );
    $productIds[$p['sku']] = $prod->id;
}
echo "✅ Created " . count($products) . " products\n\n";

// Create Batches
echo "📦 Creating Batches...\n";
$batches = [];
foreach (['PRD-LAPTOP-001', 'PRD-MOUSE-002', 'PRD-PHONE-006'] as $sku) {
    $batch = App\Models\Batch::firstOrCreate(
        ['batch_no' => 'BATCH-' . $sku . '-' . now()->format('Ymd'), 'tenant_id' => $tenantId],
        [
            'tenant_id' => $tenantId,
            'product_id' => $productIds[$sku],
            'mfg_date' => now()->subDays(30),
            'exp_date' => now()->addYears(2),
            'initial_qty' => 50,
            'current_qty' => 45,
            'status' => 'active'
        ]
    );
    $batches[$sku] = $batch->id;
}
echo "✅ Created 3 batches\n\n";

// ========================================
// 👥 SECTION 2: CUSTOMERS
// ========================================
echo "👥 Creating Customers...\n";

$customers = [
    ['customer_code' => 'CUST-ALP-101', 'name' => 'Alpha Retailers Pvt Ltd', 'gstin' => '27AABCU9603R1ZM', 'phone' => '9876543210', 'email' => 'info@alpharetail.com', 'credit_limit' => 500000],
    ['customer_code' => 'CUST-BET-205', 'name' => 'Beta Distributors', 'gstin' => '29AAFCD5862R1Z5', 'phone' => '9123456789', 'email' => 'sales@betadist.com', 'credit_limit' => 1000000],
    ['customer_code' => 'CUST-GAM-310', 'name' => 'Gamma Corporation', 'gstin' => '33AABCG1234R1Z2', 'phone' => '9988776655', 'email' => 'contact@gammacorp.in', 'credit_limit' => 750000],
    ['customer_code' => 'CUST-DEL-415', 'name' => 'Delta Enterprises', 'gstin' => '07AABCD4567R1Z8', 'phone' => '9876512340', 'email' => 'purchase@delta.in', 'credit_limit' => 300000],
    ['customer_code' => 'CUST-EPS-520', 'name' => 'Epsilon Traders', 'gstin' => '19AABCE7890R1Z3', 'phone' => '9123498765', 'email' => 'admin@epsilon.in', 'credit_limit' => 450000],
];

$customerIds = [];
foreach ($customers as $c) {
    $cust = App\Models\Customer::firstOrCreate(
        ['customer_code' => $c['customer_code'], 'tenant_id' => $tenantId],
        array_merge($c, ['tenant_id' => $tenantId, 'outstanding_amount' => 0, 'status' => 'active'])
    );
    $customerIds[$c['customer_code']] = $cust->id;
}
echo "✅ Created " . count($customers) . " customers\n\n";

// ========================================
// 🛒 SECTION 3: ORDERS & ORDER ITEMS
// ========================================
echo "🛒 Creating Orders...\n";

$orders = [
    ['order_no' => 'ORD-O-20261001-1001', 'type' => 'outbound', 'customer_id' => $customerIds['CUST-ALP-101'], 'subtotal' => 110000, 'tax' => 19800, 'total' => 129800, 'status' => 'delivered', 'payment_status' => 'paid', 'order_date' => now()->subDays(15)],
    ['order_no' => 'ORD-O-20261003-1002', 'type' => 'outbound', 'customer_id' => $customerIds['CUST-BET-205'], 'subtotal' => 250000, 'tax' => 45000, 'total' => 295000, 'status' => 'shipped', 'payment_status' => 'partial', 'order_date' => now()->subDays(10)],
    ['order_no' => 'ORD-O-20261005-1003', 'type' => 'outbound', 'customer_id' => $customerIds['CUST-GAM-310'], 'subtotal' => 85000, 'tax' => 15300, 'total' => 100300, 'status' => 'pending', 'payment_status' => 'unpaid', 'order_date' => now()->subDays(3)],
    ['order_no' => 'ORD-I-20261006-2001', 'type' => 'inbound', 'customer_id' => $customerIds['CUST-DEL-415'], 'subtotal' => 50000, 'tax' => 9000, 'total' => 59000, 'status' => 'delivered', 'payment_status' => 'paid', 'order_date' => now()->subDays(7)],
];

$orderIds = [];
foreach ($orders as $o) {
    $order = App\Models\Order::firstOrCreate(
        ['order_no' => $o['order_no'], 'tenant_id' => $tenantId],
        array_merge($o, ['tenant_id' => $tenantId])
    );
    $orderIds[$o['order_no']] = $order->id;
    
    // Add order items
    if ($order->items()->count() == 0) {
        App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productIds['PRD-LAPTOP-001'],
            'sku' => 'PRD-LAPTOP-001',
            'qty' => 2,
            'unit_price' => 55000,
            'tax_rate' => 18,
            'tax_amount' => 19800,
            'total' => 129800,
        ]);
    }
}
echo "✅ Created " . count($orders) . " orders\n\n";

// ========================================
// 🧾 SECTION 4: INVOICES (For Finance Page)
// ========================================
echo "🧾 Creating Invoices...\n";

$invoices = [
    ['invoice_no' => 'INV-20261001-5001', 'order_id' => $orderIds['ORD-O-20261001-1001'], 'customer_id' => $customerIds['CUST-ALP-101'], 'type' => 'b2b', 'cgst' => 9900, 'sgst' => 9900, 'igst' => 0, 'total' => 129800, 'status' => 'paid', 'due_date' => now()->subDays(5)],
    ['invoice_no' => 'INV-20261003-5002', 'order_id' => $orderIds['ORD-O-20261003-1002'], 'customer_id' => $customerIds['CUST-BET-205'], 'type' => 'b2b', 'cgst' => 22500, 'sgst' => 22500, 'igst' => 0, 'total' => 295000, 'status' => 'sent', 'due_date' => now()->addDays(20)],
    ['invoice_no' => 'INV-20261005-5003', 'order_id' => $orderIds['ORD-O-20261005-1003'], 'customer_id' => $customerIds['CUST-GAM-310'], 'type' => 'b2b', 'cgst' => 7650, 'sgst' => 7650, 'igst' => 0, 'total' => 100300, 'status' => 'sent', 'due_date' => now()->subDays(45)],
    ['invoice_no' => 'INV-20261006-5004', 'customer_id' => $customerIds['CUST-DEL-415'], 'type' => 'b2c', 'cgst' => 4500, 'sgst' => 4500, 'igst' => 0, 'total' => 59000, 'status' => 'paid', 'due_date' => now()->subDays(2)],
    ['invoice_no' => 'INV-20261007-5005', 'customer_id' => $customerIds['CUST-EPS-520'], 'type' => 'export', 'cgst' => 0, 'sgst' => 0, 'igst' => 18000, 'total' => 118000, 'status' => 'sent', 'due_date' => now()->subDays(75)],
    ['invoice_no' => 'INV-20261008-5006', 'customer_id' => $customerIds['CUST-ALP-101'], 'type' => 'b2b', 'cgst' => 13500, 'sgst' => 13500, 'igst' => 0, 'total' => 177000, 'status' => 'overdue', 'due_date' => now()->subDays(95)],
];

foreach ($invoices as $inv) {
    App\Models\Invoice::firstOrCreate(
        ['invoice_no' => $inv['invoice_no'], 'tenant_id' => $tenantId],
        array_merge($inv, ['tenant_id' => $tenantId])
    );
}
echo "✅ Created " . count($invoices) . " invoices\n\n";

// ========================================
// 🏪 SECTION 5: VENDORS & PURCHASE ORDERS
// ========================================
echo "🏪 Creating Vendors...\n";

$vendors = [
    ['vendor_code' => 'VND-TAT-101', 'name' => 'Tata Steel Ltd', 'gstin' => '27AAACT1234R1Z5', 'contact_person' => 'Rajesh Kumar', 'phone' => '9876543211', 'bank_name' => 'SBI', 'account_number' => '1234567890', 'ifsc_code' => 'SBIN0001234', 'credit_limit' => 5000000, 'rating' => 4.8],
    ['vendor_code' => 'VND-REL-202', 'name' => 'Reliance Industries', 'gstin' => '27AAACR5678R1Z3', 'contact_person' => 'Priya Sharma', 'phone' => '9123456780', 'bank_name' => 'HDFC', 'account_number' => '9876543210', 'ifsc_code' => 'HDFC0005678', 'credit_limit' => 10000000, 'rating' => 4.9],
    ['vendor_code' => 'VND-INF-303', 'name' => 'InfoTech Supplies', 'gstin' => '29AAACI9012R1Z1', 'contact_person' => 'Amit Patel', 'phone' => '9988776650', 'bank_name' => 'ICICI', 'account_number' => '5555666677', 'ifsc_code' => 'ICIC0009012', 'credit_limit' => 2000000, 'rating' => 4.2],
    ['vendor_code' => 'VND-GOD-404', 'name' => 'Godrej Appliances', 'gstin' => '33AAACG3456R1Z9', 'contact_person' => 'Sneha Reddy', 'phone' => '9876512341', 'bank_name' => 'Axis', 'account_number' => '1111222233', 'ifsc_code' => 'UTIB0003456', 'credit_limit' => 3000000, 'rating' => 4.5],
];

$vendorIds = [];
foreach ($vendors as $v) {
    $vendor = App\Models\Vendor::firstOrCreate(
        ['vendor_code' => $v['vendor_code'], 'tenant_id' => $tenantId],
        array_merge($v, ['tenant_id' => $tenantId, 'status' => 'active'])
    );
    $vendorIds[$v['vendor_code']] = $vendor->id;
}

echo "📋 Creating Purchase Orders...\n";
$pos = [
    ['po_no' => 'PO-20261001-7001', 'vendor_id' => $vendorIds['VND-TAT-101'], 'po_date' => now()->subDays(10), 'subtotal' => 500000, 'tax' => 90000, 'total' => 590000, 'status' => 'received'],
    ['po_no' => 'PO-20261003-7002', 'vendor_id' => $vendorIds['VND-REL-202'], 'po_date' => now()->subDays(5), 'subtotal' => 250000, 'tax' => 45000, 'total' => 295000, 'status' => 'approved'],
    ['po_no' => 'PO-20261005-7003', 'vendor_id' => $vendorIds['VND-INF-303'], 'po_date' => now()->subDays(2), 'subtotal' => 85000, 'tax' => 15300, 'total' => 100300, 'status' => 'draft'],
];

foreach ($pos as $po) {
    App\Models\PurchaseOrder::firstOrCreate(
        ['po_no' => $po['po_no'], 'tenant_id' => $tenantId],
        array_merge($po, ['tenant_id' => $tenantId])
    );
}
echo "✅ Created " . count($vendors) . " vendors & " . count($pos) . " POs\n\n";

// ========================================
// 💸 SECTION 6: EXPENSES
// ========================================
echo "💸 Creating Expense Categories & Expenses...\n";

$categories = [
    ['name' => 'Travel', 'icon' => 'bi-airplane', 'color' => 'primary'],
    ['name' => 'Office Supplies', 'icon' => 'bi-receipt', 'color' => 'success'],
    ['name' => 'Utilities', 'icon' => 'bi-lightning', 'color' => 'warning'],
    ['name' => 'Marketing', 'icon' => 'bi-megaphone', 'color' => 'info'],
    ['name' => 'Maintenance', 'icon' => 'bi-tools', 'color' => 'danger'],
    ['name' => 'Salaries', 'icon' => 'bi-people', 'color' => 'dark'],
];

$categoryIds = [];
foreach ($categories as $cat) {
    $category = App\Models\ExpenseCategory::firstOrCreate(
        ['name' => $cat['name'], 'tenant_id' => $tenantId],
        array_merge($cat, ['tenant_id' => $tenantId, 'status' => 'active'])
    );
    $categoryIds[$cat['name']] = $category->id;
}

$expenses = [
    ['expense_no' => 'EXP-20261001-9001', 'category_id' => $categoryIds['Travel'], 'vendor' => 'MakeMyTrip', 'amount' => 15000, 'gst' => 2700, 'tds' => 1500, 'total' => 16200, 'payment_mode' => 'bank', 'status' => 'paid', 'description' => 'Client visit to Delhi'],
    ['expense_no' => 'EXP-20261002-9002', 'category_id' => $categoryIds['Office Supplies'], 'vendor' => 'Stationery Hub', 'amount' => 3500, 'gst' => 630, 'tds' => 0, 'total' => 4130, 'payment_mode' => 'upi', 'status' => 'approved', 'description' => 'Monthly stationery'],
    ['expense_no' => 'EXP-20261003-9003', 'category_id' => $categoryIds['Utilities'], 'vendor' => 'BSES Electricity', 'amount' => 8500, 'gst' => 1530, 'tds' => 0, 'total' => 10030, 'payment_mode' => 'bank', 'status' => 'paid', 'description' => 'October electricity bill'],
    ['expense_no' => 'EXP-20261004-9004', 'category_id' => $categoryIds['Marketing'], 'vendor' => 'Digital Ads Co', 'amount' => 25000, 'gst' => 4500, 'tds' => 2500, 'total' => 27000, 'payment_mode' => 'card', 'status' => 'pending', 'description' => 'Google Ads campaign'],
    ['expense_no' => 'EXP-20261005-9005', 'category_id' => $categoryIds['Maintenance'], 'vendor' => 'TechFix Solutions', 'amount' => 12000, 'gst' => 2160, 'tds' => 1200, 'total' => 12960, 'payment_mode' => 'cheque', 'status' => 'rejected', 'description' => 'Machine repair'],
];

foreach ($expenses as $exp) {
    App\Models\Expense::firstOrCreate(
        ['expense_no' => $exp['expense_no'], 'tenant_id' => $tenantId],
        array_merge($exp, ['tenant_id' => $tenantId])
    );
}
echo "✅ Created " . count($categories) . " categories & " . count($expenses) . " expenses\n\n";

// ========================================
// 🚚 SECTION 7: LOGISTICS
// ========================================
echo "🚚 Creating Carriers, Drivers, Vehicles & Shipments...\n";

$carriers = [
    ['name' => 'BlueDart Express', 'type' => 'Road', 'contact' => '9876543210', 'gstin' => '27AABCB1234R1Z5', 'coverage' => 'Pan India', 'rating' => 4.5],
    ['name' => 'Delhivery', 'type' => 'Road', 'contact' => '9123456789', 'gstin' => '29AABCD5678R1Z3', 'coverage' => 'Metro Cities', 'rating' => 4.2],
    ['name' => 'DTDC Courier', 'type' => 'Road', 'contact' => '9988776655', 'gstin' => '33AABCD9012R1Z1', 'coverage' => 'South India', 'rating' => 4.0],
];

$carrierIds = [];
foreach ($carriers as $c) {
    $carrier = App\Models\Carrier::firstOrCreate(
        ['name' => $c['name'], 'tenant_id' => $tenantId],
        array_merge($c, ['tenant_id' => $tenantId, 'status' => 'active'])
    );
    $carrierIds[$c['name']] = $carrier->id;
}

$drivers = [
    ['name' => 'Rajesh Kumar', 'license' => 'DL-0420190012345', 'phone' => '9988776655', 'vehicle_type' => 'Truck', 'vehicle_no' => 'MH-12-AB-1234', 'capacity' => 5000, 'experience_years' => 8, 'rating' => 4.8],
    ['name' => 'Suresh Patel', 'license' => 'GJ-0620200054321', 'phone' => '9876512340', 'vehicle_type' => 'Van', 'vehicle_no' => 'GJ-06-CD-5678', 'capacity' => 2000, 'experience_years' => 5, 'rating' => 4.3],
    ['name' => 'Mohan Singh', 'license' => 'HR-0520180098765', 'phone' => '9123498765', 'vehicle_type' => 'Tempo', 'vehicle_no' => 'HR-05-EF-9012', 'capacity' => 1000, 'experience_years' => 12, 'rating' => 4.6],
];

$driverIds = [];
foreach ($drivers as $d) {
    $driver = App\Models\Driver::firstOrCreate(
        ['license' => $d['license'], 'tenant_id' => $tenantId],
        array_merge($d, ['tenant_id' => $tenantId, 'status' => 'active'])
    );
    $driverIds[$d['name']] = $driver->id;
}

$vehicles = [
    ['reg_no' => 'MH-12-AB-1234', 'type' => 'Truck', 'model' => 'Tata 407', 'capacity' => 5000, 'fuel_type' => 'Diesel', 'year' => 2022, 'insurance_expiry' => now()->addMonths(6), 'fitness_expiry' => now()->addYear(), 'status' => 'available'],
    ['reg_no' => 'GJ-06-CD-5678', 'type' => 'Van', 'model' => 'Eicher Pro', 'capacity' => 2000, 'fuel_type' => 'Diesel', 'year' => 2021, 'insurance_expiry' => now()->addDays(15), 'fitness_expiry' => now()->addMonths(3), 'status' => 'in_transit'],
    ['reg_no' => 'HR-05-EF-9012', 'type' => 'Tempo', 'model' => 'Mahindra Bolero', 'capacity' => 1000, 'fuel_type' => 'Diesel', 'year' => 2023, 'insurance_expiry' => now()->addYear(), 'fitness_expiry' => now()->addMonths(8), 'status' => 'available'],
];

$vehicleIds = [];
foreach ($vehicles as $v) {
    $vehicle = App\Models\Vehicle::firstOrCreate(
        ['reg_no' => $v['reg_no'], 'tenant_id' => $tenantId],
        array_merge($v, ['tenant_id' => $tenantId])
    );
    $vehicleIds[$v['reg_no']] = $vehicle->id;
}

$shipments = [
    ['shipment_no' => 'SHP-O-20261007-1001', 'direction' => 'outbound', 'origin' => 'Mumbai Warehouse', 'destination' => 'Delhi Hub', 'carrier_id' => $carrierIds['BlueDart Express'], 'driver_id' => $driverIds['Rajesh Kumar'], 'vehicle_id' => $vehicleIds['MH-12-AB-1234'], 'freight_cost' => 15000, 'eta_date' => now()->addDays(3), 'status' => 'in_transit'],
    ['shipment_no' => 'SHP-I-20261007-1002', 'direction' => 'inbound', 'origin' => 'Chennai Factory', 'destination' => 'Mumbai Warehouse', 'carrier_id' => $carrierIds['Delhivery'], 'driver_id' => $driverIds['Suresh Patel'], 'vehicle_id' => $vehicleIds['GJ-06-CD-5678'], 'freight_cost' => 12000, 'eta_date' => now()->subDays(1), 'status' => 'in_transit'],
    ['shipment_no' => 'SHP-O-20261007-1003', 'direction' => 'outbound', 'origin' => 'Mumbai Warehouse', 'destination' => 'Bangalore DC', 'carrier_id' => $carrierIds['DTDC Courier'], 'freight_cost' => 8500, 'eta_date' => now()->addDays(5), 'status' => 'pending'],
];

foreach ($shipments as $s) {
    App\Models\Shipment::firstOrCreate(
        ['shipment_no' => $s['shipment_no'], 'tenant_id' => $tenantId],
        array_merge($s, ['tenant_id' => $tenantId])
    );
}
echo "✅ Created " . count($carriers) . " carriers, " . count($drivers) . " drivers, " . count($vehicles) . " vehicles, " . count($shipments) . " shipments\n\n";

// ========================================
// 🏭 SECTION 8: PRODUCTION (Machines, BOMs, Work Orders)
// ========================================
echo "🏭 Creating Machines, BOMs & Work Orders...\n";

$machines = [
    ['machine_code' => 'MCH-001', 'name' => 'CNC Lathe A', 'type' => 'CNC', 'status' => 'operational', 'efficiency' => 95],
    ['machine_code' => 'MCH-002', 'name' => 'Assembly Line B', 'type' => 'Assembly', 'status' => 'maintenance', 'efficiency' => 0, 'next_maintenance' => now()->addDays(2)],
    ['machine_code' => 'MCH-003', 'name' => 'Packaging Unit C', 'type' => 'Packaging', 'status' => 'operational', 'efficiency' => 88],
    ['machine_code' => 'MCH-004', 'name' => 'Welding Station D', 'type' => 'Welding', 'status' => 'breakdown', 'efficiency' => 0],
];

$machineIds = [];
foreach ($machines as $m) {
    $machine = App\Models\Machine::firstOrCreate(
        ['machine_code' => $m['machine_code'], 'tenant_id' => $tenantId],
        array_merge($m, ['tenant_id' => $tenantId])
    );
    $machineIds[$m['machine_code']] = $machine->id;
}

// BOM
$firstProduct = App\Models\Product::where('sku', 'PRD-LAPTOP-001')->first();
$bom = App\Models\Bom::firstOrCreate(
    ['bom_no' => 'BOM-LAP-001', 'tenant_id' => $tenantId],
    [
        'tenant_id' => $tenantId,
        'product_id' => $firstProduct->id,
        'product_name' => $firstProduct->name,
        'product_sku' => $firstProduct->sku,
        'unit_cost' => 42000,
        'components_count' => 3,
        'status' => 'active',
        'version' => 1
    ]
);

if ($bom->components()->count() == 0) {
    App\Models\BomComponent::create(['bom_id' => $bom->id, 'component_sku' => 'RAW-STEEL-001', 'component_name' => 'Steel Frame', 'qty_required' => 5, 'unit' => 'KG', 'unit_cost' => 100, 'total_cost' => 500, 'wastage_percentage' => 2]);
    App\Models\BomComponent::create(['bom_id' => $bom->id, 'component_sku' => 'RAW-PLASTIC-002', 'component_name' => 'Plastic Casing', 'qty_required' => 2, 'unit' => 'PCS', 'unit_cost' => 150, 'total_cost' => 300, 'wastage_percentage' => 5]);
    App\Models\BomComponent::create(['bom_id' => $bom->id, 'component_sku' => 'RAW-CHIP-003', 'component_name' => 'Motherboard', 'qty_required' => 1, 'unit' => 'PCS', 'unit_cost' => 8500, 'total_cost' => 8500, 'wastage_percentage' => 0]);
}

$workOrders = [
    ['wo_no' => 'WO-20261007-1001', 'product_id' => $firstProduct->id, 'product_name' => $firstProduct->name, 'bom_id' => $bom->id, 'machine_id' => $machineIds['MCH-001'], 'target_qty' => 100, 'completed_qty' => 75, 'scrap_qty' => 3, 'status' => 'in_progress', 'start_date' => now()->subDays(3), 'deadline' => now()->addDays(2), 'priority' => 1],
    ['wo_no' => 'WO-20261007-1002', 'product_id' => $firstProduct->id, 'product_name' => $firstProduct->name, 'machine_id' => $machineIds['MCH-003'], 'target_qty' => 50, 'completed_qty' => 50, 'scrap_qty' => 1, 'status' => 'completed', 'start_date' => now()->subDays(5), 'deadline' => now()->subDays(1), 'priority' => 2],
    ['wo_no' => 'WO-20261007-1003', 'product_id' => $firstProduct->id, 'product_name' => $firstProduct->name, 'target_qty' => 200, 'completed_qty' => 0, 'scrap_qty' => 0, 'status' => 'planned', 'start_date' => now(), 'deadline' => now()->subDays(2), 'priority' => 1],
];

foreach ($workOrders as $wo) {
    App\Models\WorkOrder::firstOrCreate(
        ['wo_no' => $wo['wo_no'], 'tenant_id' => $tenantId],
        array_merge($wo, ['tenant_id' => $tenantId])
    );
}
echo "✅ Created " . count($machines) . " machines, 1 BOM, " . count($workOrders) . " work orders\n\n";

// ========================================
// 📦 SECTION 9: SCM (GR/QR, Returns, Warehouse Bins)
// ========================================
echo "📦 Creating SCM Data...\n";

// GR/QR Postings
$firstBatch = App\Models\Batch::first();
App\Models\GrqrPosting::firstOrCreate(
    ['posting_no' => 'GRQR-20261007-001', 'tenant_id' => $tenantId],
    ['tenant_id' => $tenantId, 'type' => 'gr', 'batch_id' => $firstBatch->id, 'expected_qty' => 100, 'received_qty' => 95, 'damaged_qty' => 5, 'qc_result' => 'pass', 'qc_score' => 95, 'status' => 'posted']
);
App\Models\GrqrPosting::firstOrCreate(
    ['posting_no' => 'GRQR-20261007-002', 'tenant_id' => $tenantId],
    ['tenant_id' => $tenantId, 'type' => 'gr', 'batch_id' => $firstBatch->id, 'expected_qty' => 50, 'received_qty' => 0, 'damaged_qty' => 0, 'qc_result' => 'pending', 'status' => 'pending']
);

// Returns
App\Models\ReturnOrder::firstOrCreate(
    ['return_no' => 'RET-20261007-1001', 'tenant_id' => $tenantId],
    ['tenant_id' => $tenantId, 'reason' => 'Defect', 'direction' => 'customer_to_distributor', 'origin' => 'Customer Site A', 'destination' => 'Distributor Hub', 'qty' => 10, 'value' => 15000, 'refund_amount' => 15000, 'status' => 'initiated']
);
App\Models\ReturnOrder::firstOrCreate(
    ['return_no' => 'RET-20261007-1002', 'tenant_id' => $tenantId],
    ['tenant_id' => $tenantId, 'reason' => 'Expired', 'direction' => 'distributor_to_warehouse', 'origin' => 'Distributor Hub', 'destination' => 'Main Warehouse', 'qty' => 25, 'value' => 37500, 'refund_amount' => 37500, 'status' => 'in_transit']
);

// Warehouse Bins
$bins = [
    ['bin_code' => 'A-01-01', 'type' => 'shelf', 'zone' => 'A', 'aisle' => '01', 'rack' => '01', 'max_capacity' => 100, 'current_qty' => 75, 'status' => 'occupied'],
    ['bin_code' => 'A-01-02', 'type' => 'shelf', 'zone' => 'A', 'aisle' => '01', 'rack' => '02', 'max_capacity' => 100, 'current_qty' => 0, 'status' => 'available'],
    ['bin_code' => 'B-02-01', 'type' => 'pallet', 'zone' => 'B', 'aisle' => '02', 'rack' => '01', 'max_capacity' => 500, 'current_qty' => 450, 'status' => 'occupied'],
    ['bin_code' => 'C-01-01', 'type' => 'cold', 'zone' => 'C', 'aisle' => '01', 'rack' => '01', 'max_capacity' => 200, 'current_qty' => 180, 'temperature_zone' => 'chilled', 'status' => 'occupied'],
    ['bin_code' => 'D-01-01', 'type' => 'hazmat', 'zone' => 'D', 'aisle' => '01', 'rack' => '01', 'max_capacity' => 50, 'current_qty' => 10, 'status' => 'reserved'],
];

foreach ($bins as $b) {
    App\Models\WarehouseBin::firstOrCreate(
        ['bin_code' => $b['bin_code'], 'tenant_id' => $tenantId],
        array_merge($b, ['tenant_id' => $tenantId])
    );
}
echo "✅ Created SCM data (GR/QR, Returns, Bins)\n\n";

// ========================================
// 👔 SECTION 10: HR (Departments, Employees, Leaves)
// ========================================
echo "👔 Creating HR Data...\n";

$departments = [
    ['name' => 'Engineering', 'code' => 'ENG'],
    ['name' => 'Sales & Marketing', 'code' => 'SAL'],
    ['name' => 'Finance', 'code' => 'FIN'],
    ['name' => 'Operations', 'code' => 'OPS'],
    ['name' => 'HR', 'code' => 'HR'],
];

$deptIds = [];
foreach ($departments as $d) {
    $dept = App\Models\Department::firstOrCreate(
        ['code' => $d['code'], 'tenant_id' => $tenantId],
        array_merge($d, ['tenant_id' => $tenantId, 'status' => 'active'])
    );
    $deptIds[$d['code']] = $dept->id;
}

$employees = [
    ['emp_code' => 'EMP-001', 'name' => 'Vikram Malhotra', 'doj' => now()->subYears(5), 'department_id' => $deptIds['ENG'], 'designation' => 'Senior Engineer', 'uan' => '100123456789', 'esi_no' => 'ESI001', 'basic' => 60000, 'hra' => 24000, 'gross_salary' => 95000, 'status' => 'active'],
    ['emp_code' => 'EMP-002', 'name' => 'Anjali Verma', 'doj' => now()->subYears(3), 'department_id' => $deptIds['SAL'], 'designation' => 'Sales Manager', 'uan' => '100987654321', 'esi_no' => 'ESI002', 'basic' => 55000, 'hra' => 22000, 'gross_salary' => 85000, 'status' => 'active'],
    ['emp_code' => 'EMP-003', 'name' => 'Rohit Sharma', 'doj' => now()->subYears(2), 'department_id' => $deptIds['FIN'], 'designation' => 'Accountant', 'uan' => '100555666777', 'esi_no' => 'ESI003', 'basic' => 40000, 'hra' => 16000, 'gross_salary' => 62000, 'status' => 'active'],
    ['emp_code' => 'EMP-004', 'name' => 'Neha Gupta', 'doj' => now()->subYears(4), 'department_id' => $deptIds['OPS'], 'designation' => 'Operations Lead', 'uan' => '100111222333', 'esi_no' => 'ESI004', 'basic' => 50000, 'hra' => 20000, 'gross_salary' => 78000, 'status' => 'active'],
];

$employeeIds = [];
foreach ($employees as $e) {
    $emp = App\Models\Employee::firstOrCreate(
        ['emp_code' => $e['emp_code'], 'tenant_id' => $tenantId],
        array_merge($e, ['tenant_id' => $tenantId])
    );
    $employeeIds[$e['emp_code']] = $emp->id;
    
    // Create leave balance
    App\Models\LeaveBalance::firstOrCreate(
        ['employee_id' => $emp->id, 'year' => now()->year],
        [
            'casual_earned' => 12, 'casual_used' => 3,
            'sick_earned' => 10, 'sick_used' => 2,
            'privilege_earned' => 15, 'privilege_used' => 5,
        ]
    );
}

// Leaves
App\Models\Leave::firstOrCreate(
    ['leave_no' => 'LV-20261007-001', 'tenant_id' => $tenantId],
    ['tenant_id' => $tenantId, 'employee_id' => $employeeIds['EMP-001'], 'type' => 'casual', 'from_date' => now()->addDays(5), 'to_date' => now()->addDays(6), 'days' => 2, 'reason' => 'Family function', 'status' => 'approved']
);
App\Models\Leave::firstOrCreate(
    ['leave_no' => 'LV-20261007-002', 'tenant_id' => $tenantId],
    ['tenant_id' => $tenantId, 'employee_id' => $employeeIds['EMP-002'], 'type' => 'sick', 'from_date' => now()->subDays(2), 'to_date' => now()->subDays(1), 'days' => 2, 'reason' => 'Medical', 'status' => 'approved']
);
App\Models\Leave::firstOrCreate(
    ['leave_no' => 'LV-20261007-003', 'tenant_id' => $tenantId],
    ['tenant_id' => $tenantId, 'employee_id' => $employeeIds['EMP-003'], 'type' => 'privilege', 'from_date' => now()->addDays(10), 'to_date' => now()->addDays(14), 'days' => 5, 'reason' => 'Vacation', 'status' => 'pending']
);
echo "✅ Created " . count($departments) . " departments, " . count($employees) . " employees, 3 leaves\n\n";

// ========================================
// 📊 SECTION 11: CRM (Leads, Dealers, Tickets)
// ========================================
echo "📊 Creating CRM Data...\n";

$leads = [
    ['lead_code' => 'LEAD-001', 'company_name' => 'TechStart Solutions', 'contact_person' => 'Arjun Kapoor', 'source' => 'Website', 'value' => 500000, 'status' => 'new', 'follow_up_date' => now()->addDays(3)],
    ['lead_code' => 'LEAD-002', 'company_name' => 'Innovate Corp', 'contact_person' => 'Kavya Nair', 'source' => 'Referral', 'value' => 750000, 'status' => 'contacted', 'follow_up_date' => now()->addDays(1)],
    ['lead_code' => 'LEAD-003', 'company_name' => 'Global Traders', 'contact_person' => 'Rahul Mehta', 'source' => 'Trade Show', 'value' => 1200000, 'status' => 'quoted', 'follow_up_date' => now()->subDays(1)],
    ['lead_code' => 'LEAD-004', 'company_name' => 'Prime Industries', 'contact_person' => 'Divya Iyer', 'source' => 'Cold Call', 'value' => 350000, 'status' => 'won', 'follow_up_date' => null],
    ['lead_code' => 'LEAD-005', 'company_name' => 'Metro Supplies', 'contact_person' => 'Sanjay Joshi', 'source' => 'Email', 'value' => 200000, 'status' => 'lost', 'follow_up_date' => null],
];

foreach ($leads as $l) {
    App\Models\Lead::firstOrCreate(
        ['lead_code' => $l['lead_code'], 'tenant_id' => $tenantId],
        array_merge($l, ['tenant_id' => $tenantId])
    );
}

$dealers = [
    ['dealer_code' => 'DLR-001', 'name' => 'North Zone Dealers', 'region' => 'North India', 'total_orders' => 145, 'total_business' => 2500000, 'credit_limit' => 1000000, 'outstanding' => 350000, 'status' => 'active'],
    ['dealer_code' => 'DLR-002', 'name' => 'South Zone Dealers', 'region' => 'South India', 'total_orders' => 98, 'total_business' => 1800000, 'credit_limit' => 800000, 'outstanding' => 200000, 'status' => 'active'],
    ['dealer_code' => 'DLR-003', 'name' => 'West Zone Dealers', 'region' => 'West India', 'total_orders' => 67, 'total_business' => 950000, 'credit_limit' => 500000, 'outstanding' => 150000, 'status' => 'active'],
    ['dealer_code' => 'DLR-004', 'name' => 'East Zone Dealers', 'region' => 'East India', 'total_orders' => 34, 'total_business' => 450000, 'credit_limit' => 300000, 'outstanding' => 80000, 'status' => 'active'],
];

foreach ($dealers as $d) {
    App\Models\Dealer::firstOrCreate(
        ['dealer_code' => $d['dealer_code'], 'tenant_id' => $tenantId],
        array_merge($d, ['tenant_id' => $tenantId])
    );
}

$tickets = [
    ['ticket_no' => 'TKT-001', 'customer_name' => 'Alpha Retailers', 'subject' => 'Invoice not received', 'priority' => 'high', 'status' => 'open', 'description' => 'Customer claims invoice INV-5001 not received via email'],
    ['ticket_no' => 'TKT-002', 'customer_name' => 'Beta Distributors', 'subject' => 'Product quality issue', 'priority' => 'urgent', 'status' => 'in_progress', 'description' => 'Batch BATCH-001 showing defects'],
    ['ticket_no' => 'TKT-003', 'customer_name' => 'Gamma Corp', 'subject' => 'Payment query', 'priority' => 'medium', 'status' => 'resolved', 'description' => 'Query about partial payment adjustment', 'resolution' => 'Payment adjusted in next invoice'],
    ['ticket_no' => 'TKT-004', 'customer_name' => 'Delta Enterprises', 'subject' => 'Delivery delay', 'priority' => 'low', 'status' => 'closed', 'description' => 'Shipment delayed by 2 days', 'resolution' => 'Compensation provided'],
];

foreach ($tickets as $t) {
    App\Models\Ticket::firstOrCreate(
        ['ticket_no' => $t['ticket_no'], 'tenant_id' => $tenantId],
        array_merge($t, ['tenant_id' => $tenantId])
    );
}
echo "✅ Created " . count($leads) . " leads, " . count($dealers) . " dealers, " . count($tickets) . " tickets\n\n";

// ========================================
// 🎉 DONE!
// ========================================
echo "\n";
echo "╔══════════════════════════════════════════════════╗\n";
echo "║  🎉 TEST DATA GENERATION COMPLETE! 🎉            ║\n";
echo "╠══════════════════════════════════════════════════╣\n";
echo "║  📦 Products:      " . count($products) . "                         ║\n";
echo "║  👥 Customers:     " . count($customers) . "                         ║\n";
echo "║  🛒 Orders:        " . count($orders) . "                         ║\n";
echo "║  🧾 Invoices:      " . count($invoices) . "                         ║\n";
echo "║  🏪 Vendors:       " . count($vendors) . "                         ║\n";
echo "║  💸 Expenses:      " . count($expenses) . "                         ║\n";
echo "║  🚚 Shipments:     " . count($shipments) . "                         ║\n";
echo "║  🏭 Machines:      " . count($machines) . "                         ║\n";
echo "║  👔 Employees:     " . count($employees) . "                         ║\n";
echo "║  📊 Leads:         " . count($leads) . "                         ║\n";
echo "║  🏬 Dealers:       " . count($dealers) . "                         ║\n";
echo "║  🎫 Tickets:       " . count($tickets) . "                         ║\n";
echo "╚══════════════════════════════════════════════════╝\n";
echo "\n🌐 Now visit your app to see all the data in action!\n";

exit;

## ######################################################################## ##