<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Create admin user for each tenant
            User::updateOrCreate(
                [
                    'email' => 'admin@altimacrm.com',
                    'tenant_id' => $tenant->tenant_id,
                ],
                [
                    'user_id' => Str::uuid(),
                    'name' => 'Admin User',
                    'password' => bcrypt('admin'),
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );

            // Create additional demo users for each tenant
            $demoUsers = [
                [
                    'name' => 'John Doe',
                    'email' => 'john.doe@' . strtolower(str_replace(' ', '', $tenant->tenant_name)) . '.com',
                ],
                [
                    'name' => 'Jane Smith',
                    'email' => 'jane.smith@' . strtolower(str_replace(' ', '', $tenant->tenant_name)) . '.com',
                ],
            ];

            foreach ($demoUsers as $demoUser) {
                User::updateOrCreate(
                    [
                        'email' => $demoUser['email'],
                        'tenant_id' => $tenant->tenant_id,
                    ],
                    [
                        'user_id' => Str::uuid(),
                        'name' => $demoUser['name'],
                        'password' => bcrypt('password123'),
                        'role' => 'user',
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Default admin credentials: admin@altimacrm.com / admin');
    }
}