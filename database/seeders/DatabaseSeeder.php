<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class, // Creates the global super admin first
            TenantSeeder::class,
            UserSeeder::class,
            WarehouseSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            BatchSeeder::class,
            OrderSeeder::class,
            InvoiceSeeder::class,
            PosTransactionSeeder::class,
            ExpenseSeeder::class,
            HrSeeder::class,
            CrmSeeder::class,
        ]);
    }
}