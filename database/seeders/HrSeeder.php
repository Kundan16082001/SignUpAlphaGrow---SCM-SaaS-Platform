<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Tenant, Employee, Leave};
use Faker\Factory as Faker;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();
        $departments = ['HR', 'IT', 'Sales', 'Operations', 'Finance'];
        $leaveTypes = ['Sick Leave', 'Casual Leave', 'Earned Leave', 'Maternity Leave'];
        $leaveStatuses = ['pending', 'approved', 'rejected'];

        foreach ($tenants as $tenant) {
            $employees = [];
            for ($i = 0; $i < 20; $i++) {
                $employees[] = Employee::create([
                    'tenant_id' => $tenant->id,
                    'employee_id' => 'EMP-' . $faker->bothify('####'),
                    'name' => $faker->name,
                    'email' => $faker->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'department' => $faker->randomElement($departments),
                    'designation' => $faker->jobTitle,
                    'join_date' => $faker->dateTimeBetween('-3 years', 'now'),
                    'salary' => $faker->randomFloat(2, 30000, 150000),
                    'status' => 'active',
                ]);
            }

            for ($i = 0; $i < 30; $i++) {
                Leave::create([
                    'tenant_id' => $tenant->id,
                    'employee_id' => $faker->randomElement($employees)->id,
                    'leave_type' => $faker->randomElement($leaveTypes),
                    'start_date' => $faker->dateTimeBetween('now', '+2 months'),
                    'end_date' => $faker->dateTimeBetween('+1 month', '+3 months'),
                    'reason' => $faker->sentence,
                    'status' => $faker->randomElement($leaveStatuses),
                ]);
            }
        }
        $this->command->info('✅ HR (Employees & Leaves) seeded.');
    }
}