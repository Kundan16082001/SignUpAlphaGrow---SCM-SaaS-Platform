<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant; // <-- Make sure this is here
use App\Models\User;   // <-- Make sure this is here
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create System Tenant
        $tenant = Tenant::firstOrCreate(
            ['code' => 'SYS-ADMIN'],
            [
                'name' => 'AlphaGrow System', 
                'wing' => 'system',
                'status' => 'active'
            ]
        );

        // 2. Create SuperAdmin User
        User::firstOrCreate(
            ['email' => 'admin@alphagrow.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'wing' => 'system',
                'role' => 'superadmin',
                'status' => 'active'
            ]
        );
        
        $this->command->info('✅ SuperAdmin created: admin@alphagrow.com / password123');
    }
}