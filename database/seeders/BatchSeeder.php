<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Tenant, Product, Warehouse, Batch};
use Faker\Factory as Faker;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $products = Product::where('tenant_id', $tenant->id)->get();
            $warehouses = Warehouse::where('tenant_id', $tenant->id)->get();

            if ($products->isEmpty() || $warehouses->isEmpty()) continue;

            for ($i = 0; $i < 200; $i++) {
                Batch::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $products->random()->id,
                    'warehouse_id' => $warehouses->random()->id,
                    'batch_no' => 'B-' . $faker->bothify('??-#####'),
                    'initial_qty' => $faker->numberBetween(10, 200),
                    'current_qty' => $faker->numberBetween(0, 200),
                    'status' => 'active',
                ]);
            }
        }
        $this->command->info('✅ Batches seeded.');
    }
}