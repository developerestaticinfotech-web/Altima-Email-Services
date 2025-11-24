<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'tenant_name' => 'AltimaCRM',
                'status' => 'active',
            ],
            [
                'tenant_name' => 'Demo Company',
                'status' => 'active',
            ],
            [
                'tenant_name' => 'Test Organization',
                'status' => 'active',
            ],
        ];

        foreach ($tenants as $tenantData) {
            Tenant::create($tenantData);
        }

        $this->command->info('Tenants seeded successfully!');
    }
}
