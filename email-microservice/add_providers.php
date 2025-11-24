<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\EmailProvider;
use Illuminate\Support\Str;

echo "🚀 Adding Email Providers...\n\n";

try {
    // Get the first tenant (or create one if none exists)
    $tenant = Tenant::first();
    if (!$tenant) {
        echo "❌ No tenant found. Please run the seeders first.\n";
        exit(1);
    }
    
    echo "✅ Using tenant: {$tenant->tenant_name}\n\n";
    
    // Define email providers with their SMTP configurations
    $providers = [
        [
            'name' => 'Gmail SMTP',
            'config' => [
                'driver' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'your-email@gmail.com',
                'password' => 'your-app-password',
                'from_address' => 'your-email@gmail.com',
                'from_name' => 'Your Name',
            ],
            'bounce_email' => 'bounce@gmail.com',
            'headers' => [
                'X-Mailer' => 'AltimaCRM Email Service',
                'X-Priority' => '3',
            ]
        ],
        [
            'name' => 'Outlook/Hotmail SMTP',
            'config' => [
                'driver' => 'smtp',
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'your-email@outlook.com',
                'password' => 'your-password',
                'from_address' => 'your-email@outlook.com',
                'from_name' => 'Your Name',
            ],
            'bounce_email' => 'bounce@outlook.com',
            'headers' => [
                'X-Mailer' => 'AltimaCRM Email Service',
                'X-Priority' => '3',
            ]
        ],
        [
            'name' => 'Postmark SMTP',
            'config' => [
                'driver' => 'smtp',
                'host' => 'smtp.postmarkapp.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'your-postmark-username',
                'password' => 'your-postmark-password',
                'from_address' => 'your-verified-sender@yourdomain.com',
                'from_name' => 'Your Company',
            ],
            'bounce_email' => 'bounce@yourdomain.com',
            'headers' => [
                'X-Mailer' => 'AltimaCRM Email Service',
                'X-Priority' => '3',
            ]
        ],
        [
            'name' => 'SendGrid SMTP',
            'config' => [
                'driver' => 'smtp',
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'apikey',
                'password' => 'your-sendgrid-api-key',
                'from_address' => 'your-verified-sender@yourdomain.com',
                'from_name' => 'Your Company',
            ],
            'bounce_email' => 'bounce@yourdomain.com',
            'headers' => [
                'X-Mailer' => 'AltimaCRM Email Service',
                'X-Priority' => '3',
            ]
        ],
        [
            'name' => 'Custom SMTP Server',
            'config' => [
                'driver' => 'smtp',
                'host' => 'mail.yourdomain.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'noreply@yourdomain.com',
                'password' => 'your-smtp-password',
                'from_address' => 'noreply@yourdomain.com',
                'from_name' => 'Your Company',
            ],
            'bounce_email' => 'bounce@yourdomain.com',
            'headers' => [
                'X-Mailer' => 'AltimaCRM Email Service',
                'X-Priority' => '3',
            ]
        ]
    ];
    
    $addedCount = 0;
    
    foreach ($providers as $providerData) {
        // Check if provider already exists
        $existingProvider = EmailProvider::where('provider_name', $providerData['name'])
            ->where('tenant_id', $tenant->tenant_id)
            ->first();
            
        if ($existingProvider) {
            echo "⚠️  Provider '{$providerData['name']}' already exists, skipping...\n";
            continue;
        }
        
        // Create new provider
        $provider = EmailProvider::create([
            'tenant_id' => $tenant->tenant_id,
            'provider_name' => $providerData['name'],
            'config_json' => $providerData['config'],
            'bounce_email' => $providerData['bounce_email'],
            'header_overrides' => $providerData['headers'],
            'is_active' => true,
        ]);
        
        echo "✅ Added provider: {$provider->provider_name}\n";
        echo "   - Host: {$providerData['config']['host']}:{$providerData['config']['port']}\n";
        echo "   - Username: {$providerData['config']['username']}\n";
        echo "   - From: {$providerData['config']['from_address']}\n\n";
        
        $addedCount++;
    }
    
    echo "🎉 Successfully added {$addedCount} email providers!\n\n";
    
    // Show all providers for this tenant
    echo "📋 Current providers for tenant '{$tenant->tenant_name}':\n";
    $allProviders = EmailProvider::where('tenant_id', $tenant->tenant_id)->get();
    
    foreach ($allProviders as $provider) {
        echo "   - {$provider->provider_name} (ID: {$provider->provider_id})\n";
        echo "     Config: {$provider->config_json['host']}:{$provider->config_json['port']}\n";
        echo "     Active: " . ($provider->is_active ? 'Yes' : 'No') . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 