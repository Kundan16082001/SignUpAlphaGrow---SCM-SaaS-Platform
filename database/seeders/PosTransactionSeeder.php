<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Tenant, Product, PosTransaction};
use Faker\Factory as Faker;

class PosTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();
        $methods = ['cash', 'card', 'upi', 'net_banking'];

        foreach ($tenants as $tenant) {
            $products = Product::where('tenant_id', $tenant->id)->get();
            if ($products->isEmpty()) continue;

            for ($i = 0; $i < 100; $i++) {
                $items = [];
                $numItems = $faker->numberBetween(1, 5);
                $total = 0;
                
                for ($j = 0; $j < $numItems; $j++) {
                    $prod = $products->random();
                    $qty = $faker->numberBetween(1, 10);
                    $items[] = [
                        'product_id' => $prod->id,
                        'name' => $prod->name,
                        'qty' => $qty,
                        'price' => $prod->price,
                        'total' => $qty * $prod->price,
                    ];
                    $total += ($qty * $prod->price);
                }

                PosTransaction::create([
                    'tenant_id' => $tenant->id,
                    'receipt_no' => 'POS-' . $faker->bothify('######'),
                    'total' => $total,
                    'payment_method' => $faker->randomElement($methods),
                    'status' => 'completed',
                    'items' => json_encode($items),
                    'cashier_name' => $faker->name,
                ]);
            }
        }
        $this->command->info('✅ POS Transactions seeded.');
    }
}