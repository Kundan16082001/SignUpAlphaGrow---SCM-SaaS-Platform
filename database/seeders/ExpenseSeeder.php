<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Tenant, ExpenseCategory, Expense};
use Faker\Factory as Faker;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();
        $categories = ['Office Rent', 'Utilities', 'Salaries', 'Travel', 'Maintenance', 'Software'];
        $statuses = ['pending', 'approved', 'paid', 'rejected'];

        foreach ($tenants as $tenant) {
            $catModels = [];
            foreach ($categories as $cat) {
                $catModels[] = ExpenseCategory::create([
                    'tenant_id' => $tenant->id,
                    'name' => $cat,
                    'description' => $faker->sentence,
                    'icon' => $faker->randomElement(['bi-receipt', 'bi-lightning', 'bi-people', 'bi-car-front', 'bi-tools', 'bi-laptop']),
                    'color' => $faker->randomElement(['primary', 'success', 'warning', 'danger', 'info', 'secondary']),
                ]);
            }

            for ($i = 0; $i < 100; $i++) {
                Expense::create([
                    'tenant_id' => $tenant->id,
                    'category_id' => $faker->randomElement($catModels)->id,
                    'amount' => $faker->randomFloat(2, 500, 20000),
                    'date' => $faker->dateTimeBetween('-6 months', 'now'),
                    'description' => $faker->sentence,
                    'status' => $faker->randomElement($statuses),
                    'approved_by' => $faker->name,
                ]);
            }
        }
        $this->command->info('✅ Expenses seeded.');
    }
}