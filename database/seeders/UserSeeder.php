<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{Tenant, User};
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();
        $roles = [['role' => 'admin', 'count' => 1], ['role' => 'manager', 'count' => 2], ['role' => 'employee', 'count' => 5]];

        foreach ($tenants as $tenant) {
            foreach ($roles as $roleData) {
                for ($i = 0; $i < $roleData['count']; $i++) {
                    User::create([
                        'tenant_id' => $tenant->id,
                        'name' => $faker->name,
                        'email' => $faker->unique()->safeEmail,
                        'password' => Hash::make('password123'),
                        'role' => $roleData['role'],
                        'wing' => 'system', // Safe value for CHECK constraint
                        'status' => 'active',
                    ]);
                }
            }
        }
        $this->command->info('✅ Users seeded.');
    }
}