<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Tenant, Customer, Order, Invoice};
use Faker\Factory as Faker;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

        foreach ($tenants as $tenant) {
            $customers = Customer::where('tenant_id', $tenant->id)->get();
            $orders = Order::where('tenant_id', $tenant->id)->get();
            if ($customers->isEmpty()) continue;

            for ($i = 0; $i < 100; $i++) {
                $total = $faker->randomFloat(2, 1000, 50000);
                $tax = $total * 0.18;
                
                Invoice::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customers->random()->id,
                    'order_id' => $orders->isNotEmpty() ? $orders->random()->id : null,
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
        $this->command->info('✅ Invoices seeded.');
    }
}