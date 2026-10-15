<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\{
    Tenant, User, Warehouse, Customer, Product, Batch, 
    Order, Invoice, PosTransaction, Expense, ExpenseCategory, 
    Employee, Leave, Lead, Ticket
};

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $this->command->info('🚀 Starting Bulletproof Master Seeding Process...');

        // Safely handle foreign key checks based on database driver
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        $entityTypes = ['warehouse', 'manufacturer', 'distributor'];

        for ($t = 0; $t < 3; $t++) {
            $entityType = $entityTypes[$t];
            $tenantName = $faker->company . ' ' . ['Pvt Ltd', 'Inc', 'LLC'][$faker->numberBetween(0, 2)];
            
            $this->command->info("Seeding data for {$entityType}: {$tenantName} (" . ($t + 1) . "/3)");

            // Create Tenant
            $tenant = Tenant::create([
                'code' => 'TST-' . strtoupper($faker->bothify('???###')),
                'name' => $tenantName,
                'gstin' => $faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}'),
                'pan' => $faker->regexify('[A-Z]{5}[0-9]{4}[A-Z]{1}'),
                'status' => 'active',
            ]);

            $this->seedUsers($tenant->id, $faker);
            
            $warehouses = [];
            $customers = [];

            if ($entityType === 'warehouse') {
                $warehouses = $this->seedWarehouses($tenant->id, $faker);
            } else {
                $customers = $this->seedCustomers($tenant->id, $entityType, $faker);
            }

            $products = $this->seedProducts($tenant->id, $faker);
            $this->seedBatches($tenant->id, $products, $warehouses, $faker);
            
            $orders = $this->seedOrders($tenant->id, $customers, $faker);
            $this->seedInvoices($tenant->id, $customers, $orders, $faker);
            $this->seedPosTransactions($tenant->id, $products, $faker);
            
            $this->seedExpenses($tenant->id, $faker);
            $this->seedHr($tenant->id, $faker);
            $this->seedCrm($tenant->id, $customers, $faker);
        }

        // Re-enable foreign key checks
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $this->command->info('✅ Master Seeding Completed Successfully!');
    }

    private function seedUsers(int $tenantId, $faker): void
    {
        $roles = [
            ['role' => 'admin', 'count' => 1],
            ['role' => 'manager', 'count' => 2],
            ['role' => 'employee', 'count' => 5],
        ];

        foreach ($roles as $roleData) {
            for ($i = 0; $i < $roleData['count']; $i++) {
                User::create([
                    'tenant_id' => $tenantId,
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'password' => Hash::make('password123'),
                    'role' => $roleData['role'],
                    'wing' => 'system', // ✅ Safe value for CHECK constraint
                    'status' => 'active',
                ]);
            }
        }
    }

    private function seedWarehouses(int $tenantId, $faker): array
    {
        $warehouses = [];
        for ($i = 0; $i < 2; $i++) {
            $wh = Warehouse::create([
                'tenant_id' => $tenantId,
                'code' => 'WH-' . $faker->bothify('??###'),
                'name' => $faker->city . ' ' . $faker->randomElement(['Main', 'North', 'South', 'Cold Storage']) . ' Warehouse',
                'type' => $faker->randomElement(['main', 'branch', 'cold_storage', 'hazmat']),
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->state,
                'pincode' => $faker->postcode,
                'country' => $faker->country,
                'latitude' => $faker->latitude,
                'longitude' => $faker->longitude,
                'manager_name' => $faker->name,
                'manager_phone' => $faker->phoneNumber,
                'manager_email' => $faker->safeEmail,
                'total_area_sqft' => $faker->numberBetween(5000, 50000),
                'pallet_capacity' => $faker->numberBetween(100, 1000),
                'shelf_capacity' => $faker->numberBetween(500, 5000),
                'cold_capacity' => $faker->numberBetween(0, 500),
                'hazmat_capacity' => $faker->numberBetween(0, 100),
                'max_weight_tons' => $faker->randomFloat(2, 10, 500),
                'status' => 'active',
            ]);
            $warehouses[] = $wh;
        }
        return $warehouses;
    }

        private function seedCustomers(int $tenantId, string $type, $faker): array
    {
        $customers = [];
        $prefix = $type === 'manufacturer' ? 'MFG' : 'DIST';
        
        for ($i = 0; $i < 100; $i++) {
            $cust = Customer::create([
                'tenant_id' => $tenantId,
                'customer_code' => $prefix . '-' . $faker->bothify('####'),
                'name' => $faker->company,
                // 'type' => $customerType, // ✅ REMOVED: Column does not exist in customers table
                'gstin' => $faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}'),
                'pan' => $faker->regexify('[A-Z]{5}[0-9]{4}[A-Z]{1}'),
                'phone' => $faker->phoneNumber,
                'email' => $faker->companyEmail,
                'address' => $faker->address,
                'city' => $faker->city,
                'state' => $faker->state,
                'pincode' => $faker->postcode,
                'country' => $faker->country,
                'credit_limit' => $faker->randomFloat(2, 10000, 500000),
                'outstanding_amount' => $faker->randomFloat(2, 0, 50000),
                'status' => 'active',
            ]);
            $customers[] = $cust;
        }
        return $customers;
    }

    private function seedProducts(int $tenantId, $faker): array
    {
        $products = [];
        $categories = ['Electronics', 'Raw Material', 'Finished Goods', 'Packaging', 'Spare Parts'];
        
        for ($i = 0; $i < 100; $i++) {
            $prod = Product::create([
                'tenant_id' => $tenantId,
                'sku' => 'PRD-' . $faker->bothify('????-####'),
                'name' => $faker->word . ' ' . $faker->randomElement(['Pro', 'Ultra', 'Basic', 'Premium']) . ' ' . $faker->word,
                'category' => $categories[array_rand($categories)],
                'unit' => $faker->randomElement(['PCS', 'KG', 'LTR', 'BOX', 'SET']),
                'gst_rate' => $faker->randomElement([0, 5, 12, 18, 28]),
                'hsn_code' => $faker->numerify('####'),
                'price' => $faker->randomFloat(2, 100, 10000),
                'cost_price' => $faker->randomFloat(2, 50, 8000),
                'stock_qty' => $faker->numberBetween(0, 500),
                'reorder_level' => $faker->numberBetween(10, 50),
                'status' => $faker->randomElement(['active', 'inactive']), // ✅ FIXED: Removed 'discontinued' to satisfy CHECK constraint
            ]);
            $products[] = $prod;
        }
        return $products;
    }

        private function seedBatches(int $tenantId, array $products, array $warehouses, $faker): void
    {
        if (empty($warehouses)) {
            $warehouses = [$this->seedWarehouses($tenantId, $faker)[0]];
        }

        for ($i = 0; $i < 200; $i++) {
            $product = $faker->randomElement($products);
            $warehouse = $faker->randomElement($warehouses);
            $qty = $faker->numberBetween(10, 200);

            Batch::create([
                'tenant_id' => $tenantId,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'batch_no' => 'B-' . $faker->bothify('??-#####'),
                'initial_qty' => $qty,
                'current_qty' => $faker->numberBetween(0, $qty),
                'status' => 'active',
            ]);
        }
    }

    private function seedOrders(int $tenantId, array $customers, $faker): array
    {
        $orders = [];
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (empty($customers)) {
            $customers = $this->seedCustomers($tenantId, 'distributor', $faker);
        }

        for ($i = 0; $i < 100; $i++) {
            $ord = Order::create([
                'tenant_id' => $tenantId,
                'customer_id' => $faker->randomElement($customers)->id,
                'order_no' => 'ORD-' . $faker->bothify('######'),
                'status' => $faker->randomElement($statuses),
                'total_amount' => $faker->randomFloat(2, 1000, 50000),
                'paid_amount' => $faker->randomFloat(2, 0, 25000),
                'shipping_address' => $faker->address,
                'notes' => $faker->sentence,
            ]);
            $orders[] = $ord;
        }
        return $orders;
    }

    private function seedInvoices(int $tenantId, array $customers, array $orders, $faker): void
    {
        if (empty($customers)) $customers = $this->seedCustomers($tenantId, 'distributor', $faker);
        if (empty($orders)) $orders = $this->seedOrders($tenantId, $customers, $faker);

        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        
        for ($i = 0; $i < 100; $i++) {
            $total = $faker->randomFloat(2, 1000, 50000);
            $tax = $total * 0.18;
            
            Invoice::create([
                'tenant_id' => $tenantId,
                'customer_id' => $faker->randomElement($customers)->id,
                'order_id' => $faker->randomElement($orders)->id ?? null,
                'invoice_no' => 'INV-' . $faker->date('Ymd') . '-' . $faker->bothify('####'),
                'type' => $faker->randomElement(['b2b', 'b2c']),
                'subtotal' => $total,
                'tax' => $tax,
                'cgst' => $tax / 2,
                'sgst' => $tax / 2,
                'igst' => 0,
                'discount' => $faker->randomFloat(2, 0, 500),
                'total' => $total + $tax,
                'status' => $faker->randomElement($statuses),
                'due_date' => $faker->dateTimeBetween('now', '+30 days'),
                'notes' => $faker->sentence,
            ]);
        }
    }

    private function seedPosTransactions(int $tenantId, array $products, $faker): void
    {
        $methods = ['cash', 'card', 'upi', 'net_banking'];
        
        for ($i = 0; $i < 100; $i++) {
            $total = $faker->randomFloat(2, 100, 5000);
            $items = [];
            $numItems = $faker->numberBetween(1, 5);
            
            for ($j = 0; $j < $numItems; $j++) {
                $prod = $faker->randomElement($products);
                $qty = $faker->numberBetween(1, 10);
                $items[] = [
                    'product_id' => $prod->id,
                    'name' => $prod->name,
                    'qty' => $qty,
                    'price' => $prod->price,
                    'total' => $qty * $prod->price,
                ];
            }

            PosTransaction::create([
                'tenant_id' => $tenantId,
                'receipt_no' => 'POS-' . $faker->bothify('######'),
                'total' => $total,
                'payment_method' => $faker->randomElement($methods),
                'status' => 'completed',
                'items' => json_encode($items),
                'cashier_name' => $faker->name,
            ]);
        }
    }

    private function seedExpenses(int $tenantId, $faker): void
    {
        $categories = ['Office Rent', 'Utilities', 'Salaries', 'Travel', 'Maintenance', 'Software'];
        
        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $cat],
                [
                    'description' => $faker->sentence,
                    'icon' => $faker->randomElement(['bi-receipt', 'bi-lightning', 'bi-people', 'bi-car-front', 'bi-tools', 'bi-laptop']),
                    'color' => $faker->randomElement(['primary', 'success', 'warning', 'danger', 'info', 'secondary']),
                ]
            );
        }

        $cats = ExpenseCategory::where('tenant_id', $tenantId)->get();
        $statuses = ['pending', 'approved', 'paid', 'rejected'];

        for ($i = 0; $i < 100; $i++) {
            Expense::create([
                'tenant_id' => $tenantId,
                'category_id' => $faker->randomElement($cats)->id,
                'amount' => $faker->randomFloat(2, 500, 20000),
                'date' => $faker->dateTimeBetween('-6 months', 'now'),
                'description' => $faker->sentence,
                'status' => $faker->randomElement($statuses),
                'approved_by' => $faker->name,
            ]);
        }
    }

    private function seedHr(int $tenantId, $faker): void
    {
        $departments = ['HR', 'IT', 'Sales', 'Operations', 'Finance'];
        $employees = [];

        for ($i = 0; $i < 20; $i++) {
            $emp = Employee::create([
                'tenant_id' => $tenantId,
                'employee_id' => 'EMP-' . $faker->bothify('####'),
                'name' => $faker->name,
                'email' => $faker->safeEmail,
                'phone' => $faker->phoneNumber,
                'department' => $faker->randomElement($departments),
                'designation' => $faker->jobTitle,
                'join_date' => $faker->dateTimeBetween('-3 years', 'now'),
                'salary' => $faker->randomFloat(2, 30000, 150000),
                'status' => 'active',
            ]);
            $employees[] = $emp;
        }

        $leaveTypes = ['Sick Leave', 'Casual Leave', 'Earned Leave', 'Maternity Leave'];
        $leaveStatuses = ['pending', 'approved', 'rejected'];

        for ($i = 0; $i < 30; $i++) {
            Leave::create([
                'tenant_id' => $tenantId,
                'employee_id' => $faker->randomElement($employees)->id,
                'leave_type' => $faker->randomElement($leaveTypes),
                'start_date' => $faker->dateTimeBetween('now', '+2 months'),
                'end_date' => $faker->dateTimeBetween('+1 month', '+3 months'),
                'reason' => $faker->sentence,
                'status' => $faker->randomElement($leaveStatuses),
            ]);
        }
    }

    private function seedCrm(int $tenantId, array $customers, $faker): void
    {
        if (empty($customers)) $customers = $this->seedCustomers($tenantId, 'distributor', $faker);

        $leadStatuses = ['new', 'contacted', 'qualified', 'lost', 'won'];
        $ticketStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        for ($i = 0; $i < 50; $i++) {
            Lead::create([
                'tenant_id' => $tenantId,
                'name' => $faker->name,
                'company' => $faker->company,
                'email' => $faker->safeEmail,
                'phone' => $faker->phoneNumber,
                'source' => $faker->randomElement(['Website', 'Referral', 'Cold Call', 'Social Media']),
                'status' => $faker->randomElement($leadStatuses),
                'estimated_value' => $faker->randomFloat(2, 10000, 100000),
            ]);
        }

        for ($i = 0; $i < 50; $i++) {
            Ticket::create([
                'tenant_id' => $tenantId,
                'customer_id' => $faker->randomElement($customers)->id,
                'subject' => $faker->sentence(6),
                'description' => $faker->paragraph,
                'priority' => $faker->randomElement($priorities),
                'status' => $faker->randomElement($ticketStatuses),
                'assigned_to' => $faker->name,
            ]);
        }
    }
}