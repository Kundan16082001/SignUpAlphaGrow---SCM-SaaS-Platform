<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Faker\Factory as Faker;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $types = ['warehouse', 'manufacturer', 'distributor'];

        foreach ($types as $type) {
            Tenant::create([
                'code' => 'TST-' . strtoupper($faker->bothify('???###')),
                'name' => $faker->company . ' ' . $faker->randomElement(['Pvt Ltd', 'Inc', 'LLC']),
                'gstin' => $faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}'),
                'pan' => $faker->regexify('[A-Z]{5}[0-9]{4}[A-Z]{1}'),
                'status' => 'active',
            ]);
        }
        $this->command->info('✅ Tenants seeded.');
    }
}