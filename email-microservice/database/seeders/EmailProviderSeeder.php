<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\EmailProvider;

class EmailProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first tenant (AltimaCRM)
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $this->command->error('No tenants found. Please run TenantSeeder first.');
            return;
        }

        $providers = [
            [
                'tenant_id' => $tenant->tenant_id,
                'provider_name' => 'Postmark',
                'config_json' => [
                    'api_token' => 'your-postmark-api-token',
                    'server_token' => 'your-postmark-server-token',
                    'region' => 'us-east-1',
                ],
                'bounce_email' => 'bounce@mailer.broker.com',
                'header_overrides' => [
                    'X-Mailer' => 'AltimaCRM Email Service',
                    'X-Entity-Ref-ID' => 'postmark',
                ],
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'provider_name' => 'AWS SES',
                'config_json' => [
                    'access_key_id' => 'your-aws-access-key',
                    'secret_access_key' => 'your-aws-secret-key',
                    'region' => 'us-east-1',
                    'configuration_set' => 'default',
                ],
                'bounce_email' => 'bounce@mailer.broker.com',
                'header_overrides' => [
                    'X-Mailer' => 'AltimaCRM Email Service',
                    'X-Entity-Ref-ID' => 'ses',
                ],
            ],
            [
                'tenant_id' => $tenant->tenant_id,
                'provider_name' => 'Gmail',
                'config_json' => [
                    'smtp_host' => 'smtp.gmail.com',
                    'smtp_port' => 587,
                    'smtp_username' => 'your-gmail@gmail.com',
                    'smtp_password' => 'your-app-password',
                    'encryption' => 'tls',
                ],
                'bounce_email' => 'bounce@mailer.broker.com',
                'header_overrides' => [
                    'X-Mailer' => 'AltimaCRM Email Service',
                    'X-Entity-Ref-ID' => 'gmail',
                ],
            ],
        ];

        foreach ($providers as $providerData) {
            EmailProvider::create($providerData);
        }

        $this->command->info('Email providers seeded successfully!');
    }
}
