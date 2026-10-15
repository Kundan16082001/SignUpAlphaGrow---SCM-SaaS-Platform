<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Tenant, Warehouse};
use Faker\Factory as Faker;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            for ($i = 0; $i < 2; $i++) {
                Warehouse::create([
                    'tenant_id' => $tenant->id,
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
            }
        }
        $this->command->info('✅ Warehouses seeded.');
    }
}