<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Tenant, Customer, Lead, Ticket};
use Faker\Factory as Faker;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $tenants = Tenant::all();
        $leadStatuses = ['new', 'contacted', 'qualified', 'lost', 'won'];
        $ticketStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($tenants as $tenant) {
            $customers = Customer::where('tenant_id', $tenant->id)->get();

            for ($i = 0; $i < 50; $i++) {
                Lead::create([
                    'tenant_id' => $tenant->id,
                    'name' => $faker->name,
                    'company' => $faker->company,
                    'email' => $faker->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'source' => $faker->randomElement(['Website', 'Referral', 'Cold Call', 'Social Media']),
                    'status' => $faker->randomElement($leadStatuses),
                    'estimated_value' => $faker->randomFloat(2, 10000, 100000),
                ]);
            }

            for ($i = 0; $i < 50; $i++) {
                Ticket::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customers->isNotEmpty() ? $customers->random()->id : null,
                    'subject' => $faker->sentence(6),
                    'description' => $faker->paragraph,
                    'priority' => $faker->randomElement($priorities),
                    'status' => $faker->randomElement($ticketStatuses),
                    'assigned_to' => $faker->name,
                ]);
            }
        }
        $this->command->info('✅ CRM (Leads & Tickets) seeded.');
    }
}