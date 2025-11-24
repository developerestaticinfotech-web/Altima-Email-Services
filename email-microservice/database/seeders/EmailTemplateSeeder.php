<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the direct-email template that the queue processor needs
        EmailTemplate::updateOrCreate(
            ['template_id' => 'direct-email'],
            [
                'name' => 'Direct Email Template',
                'subject' => '{{subject}}',
                'html_content' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{subject}}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="color: #007bff; margin-top: 0;">{{subject}}</h2>
    </div>
    
    <div style="background-color: white; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
        {{body_content}}
    </div>
    
    <div style="text-align: center; margin-top: 20px; padding: 20px; color: #6c757d; font-size: 14px;">
        <p>This email was sent via the RabbitMQ queue system.</p>
        <p>Sent at: {{sent_at}}</p>
    </div>
</body>
</html>',
                'text_content' => "{{subject}}\n\n{{body_content}}\n\n---\nThis email was sent via the RabbitMQ queue system.\nSent at: {{sent_at}}",
                'variables' => [
                    'subject' => 'Email subject line',
                    'body_content' => 'Main email content',
                    'sent_at' => 'Timestamp when email was sent'
                ],
                'category' => 'system',
                'language' => 'en',
                'is_active' => true,
                'metadata' => [
                    'description' => 'Template for direct emails sent via RabbitMQ queue',
                    'version' => '1.0',
                    'created_by' => 'system'
                ]
            ]
        );

        $this->command->info('Direct email template created successfully!');
    }
}
