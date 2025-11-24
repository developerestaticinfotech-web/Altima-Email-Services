<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outbox;
use App\Models\EmailProvider;
use App\Models\Tenant;
use Illuminate\Support\Str;

class BouncedEmailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a sample tenant
        $tenant = Tenant::firstOrCreate(
            ['tenant_id' => 'T123'],
            [
                'tenant_name' => 'Sample Tenant',
                'status' => 'active'
            ]
        );

        // Get or create a sample email provider
        $provider = EmailProvider::firstOrCreate(
            ['provider_id' => 'P45'],
            [
                'tenant_id' => $tenant->tenant_id,
                'provider_name' => 'Sample Provider',
                'config_json' => json_encode([
                    'host' => 'smtp.example.com',
                    'port' => 587,
                    'username' => 'test@example.com',
                    'password' => 'password'
                ]),
                'is_active' => true
            ]
        );

        // Create sample bounced emails
        $bouncedEmails = [
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenant->tenant_id,
                'provider_id' => $provider->provider_id,
                'user_id' => 'U789',
                'message_id' => 'msg_' . Str::random(10),
                'subject' => 'Welcome Email - Bounced',
                'from' => 'noreply@example.com',
                'to' => ['invalid@nonexistentdomain.com'],
                'cc' => [],
                'bcc' => [],
                'body_format' => 'HTML',
                'body_content' => '<p>Welcome to our service!</p>',
                'status' => 'bounced',
                'bounce_reason' => 'Domain not found: nonexistentdomain.com',
                'error_message' => 'SMTP Error: Domain not found',
                'sent_at' => now()->subDays(2),
                'bounced_at' => now()->subDays(1),
                'retry_count' => 0,
                'source' => 'queue'
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenant->tenant_id,
                'provider_id' => $provider->provider_id,
                'user_id' => 'U790',
                'message_id' => 'msg_' . Str::random(10),
                'subject' => 'Newsletter - Bounced',
                'from' => 'newsletter@example.com',
                'to' => ['user@invalid-email-format'],
                'cc' => [],
                'bcc' => [],
                'body_format' => 'HTML',
                'body_content' => '<p>Check out our latest newsletter!</p>',
                'status' => 'bounced',
                'bounce_reason' => 'Invalid email format',
                'error_message' => 'SMTP Error: Invalid email address',
                'sent_at' => now()->subDays(3),
                'bounced_at' => now()->subDays(2),
                'retry_count' => 0,
                'source' => 'queue'
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenant->tenant_id,
                'provider_id' => $provider->provider_id,
                'user_id' => 'U791',
                'message_id' => 'msg_' . Str::random(10),
                'subject' => 'Password Reset - Failed',
                'from' => 'security@example.com',
                'to' => ['user@mailboxfull.com'],
                'cc' => [],
                'bcc' => [],
                'body_format' => 'HTML',
                'body_content' => '<p>Your password reset link</p>',
                'status' => 'bounced',
                'bounce_reason' => 'Mailbox full',
                'error_message' => 'SMTP Error: Mailbox full',
                'sent_at' => now()->subDays(1),
                'retry_count' => 1,
                'source' => 'queue'
            ]
        ];

        foreach ($bouncedEmails as $emailData) {
            Outbox::updateOrCreate(
                ['id' => $emailData['id']],
                $emailData
            );
        }

        $this->command->info('Sample bounced emails created successfully!');
    }
}
