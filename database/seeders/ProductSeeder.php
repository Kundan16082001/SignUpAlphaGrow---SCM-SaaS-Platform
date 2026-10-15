<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Tenant, Product};
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();
        $categories = ['Electronics', 'Raw Material', 'Finished Goods', 'Packaging', 'Spare Parts'];

        foreach ($tenants as $tenant) {
            for ($i = 0; $i < 100; $i++) {
                Product::create([
                    'tenant_id' => $tenant->id,
                    'sku' => 'PRD-' . $faker->bothify('????-####'),
                    'name' => $faker->word . ' ' . $faker->randomElement(['Pro', 'Ultra', 'Basic', 'Premium']) . ' ' . $faker->word,
                    'category' => $faker->randomElement($categories),
                    'unit' => $faker->randomElement(['PCS', 'KG', 'LTR', 'BOX', 'SET']),
                    'gst_rate' => $faker->randomElement([0, 5, 12, 18, 28]),
                    'hsn_code' => $faker->numerify('####'),
                    'price' => $faker->randomFloat(2, 100, 10000),
                    'cost_price' => $faker->randomFloat(2, 50, 8000),
                    'stock_qty' => $faker->numberBetween(0, 500),
                    'reorder_level' => $faker->numberBetween(10, 50),
                    'status' => $faker->randomElement(['active', 'inactive']), // Safe for CHECK constraint
                ]);
            }
        }
        $this->command->info('✅ Products seeded.');
    }
}