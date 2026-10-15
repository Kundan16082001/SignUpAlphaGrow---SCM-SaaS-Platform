<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Tenant, Customer, Order};
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        foreach ($tenants as $tenant) {
            $customers = Customer::where('tenant_id', $tenant->id)->get();
            if ($customers->isEmpty()) continue;

            for ($i = 0; $i < 100; $i++) {
                Order::create([
                    'tenant_id'        => $tenant->id,
                    'customer_id'      => $customers->random()->id,
                    'order_no'         => 'ORD-' . $faker->bothify('######'),
                    'status'           => $faker->randomElement($statuses),
                    'shipping_address' => $faker->address,
                    'notes'            => $faker->sentence,
                    // ✅ REMOVED: 'total_amount' and 'paid_amount' as they do not exist in the orders table
                ]);
            }
        }
        $this->command->info('✅ Orders seeded.');
    }
}