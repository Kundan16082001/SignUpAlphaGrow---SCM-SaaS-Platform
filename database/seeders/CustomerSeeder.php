<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Tenant, Customer};
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            for ($i = 0; $i < 100; $i++) {
                Customer::create([
                    'tenant_id' => $tenant->id,
                    // ✅ FIXED: Use unique() to guarantee no collisions
                    'customer_code' => 'CUST-' . $faker->unique()->bothify('????-####'),
                    'name' => $faker->company,
                    'gstin' => $faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}'),
                    'phone' => $faker->phoneNumber,
                    'email' => $faker->unique()->companyEmail, // ✅ Also ensure emails are unique
                    'credit_limit' => $faker->randomFloat(2, 10000, 500000),
                    'outstanding_amount' => $faker->randomFloat(2, 0, 50000),
                    'status' => 'active',
                ]);
            }
        }
        $this->command->info('✅ Customers seeded.');
    }
}