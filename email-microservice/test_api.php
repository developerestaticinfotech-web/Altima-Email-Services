<?php
/**
 * Simple API Test Script for AltimaCRM Email Microservice
 * 
 * This script tests the basic functionality of our email microservice API.
 * Run this after starting the Laravel server with: php artisan serve
 */

$baseUrl = 'http://localhost:8000/api';

echo "🧪 Testing AltimaCRM Email Microservice API\n";
echo "============================================\n\n";

// Test 1: Health Check
echo "1. Testing Health Check...\n";
$response = file_get_contents($baseUrl . '/health');
if ($response) {
    $data = json_decode($response, true);
    echo "   ✅ Health check passed: " . ($data['status'] ?? 'unknown') . "\n";
} else {
    echo "   ❌ Health check failed\n";
}

// Test 2: API Documentation
echo "\n2. Testing API Documentation...\n";
$response = file_get_contents($baseUrl . '/');
if ($response) {
    $data = json_decode($response, true);
    echo "   ✅ API documentation loaded: " . ($data['service'] ?? 'unknown') . "\n";
    echo "   📚 Available endpoints: " . count($data['endpoints'] ?? []) . "\n";
} else {
    echo "   ❌ API documentation failed\n";
}

// Test 3: Get Email Templates
echo "\n3. Testing Get Email Templates...\n";
$response = file_get_contents($baseUrl . '/email/templates');
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Templates retrieved successfully\n";
        echo "   📧 Total templates: " . count($data['data']) . "\n";
        foreach ($data['data'] as $template) {
            echo "      - " . $template['template_id'] . " (" . $template['category'] . ")\n";
        }
    } else {
        echo "   ❌ Failed to get templates: " . ($data['message'] ?? 'unknown error') . "\n";
    }
} else {
    echo "   ❌ Get templates request failed\n";
}

// Test 4: Get Email Statistics
echo "\n4. Testing Get Email Statistics...\n";
$response = file_get_contents($baseUrl . '/email/stats');
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Statistics retrieved successfully\n";
        echo "   📊 Total emails: " . ($data['data']['total_emails'] ?? 0) . "\n";
        echo "   📊 Sent emails: " . ($data['data']['sent_emails'] ?? 0) . "\n";
        echo "   📊 Failed emails: " . ($data['data']['failed_emails'] ?? 0) . "\n";
    } else {
        echo "   ❌ Failed to get statistics: " . ($data['message'] ?? 'unknown error') . "\n";
    }
} else {
    echo "   ❌ Get statistics request failed\n";
}

// Test 5: Test Email Sending (Template Validation)
echo "\n5. Testing Email Sending (Template Validation)...\n";
$testData = [
    'template_id' => 'welcome_user',
    'to' => [
        [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]
    ],
    'data' => [
        'user' => [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'signup_date' => '2025-08-11',
            'referral_code' => 'TEST123'
        ],
        'account' => [
            'type' => 'Standard',
            'leverage' => '1:100',
            'currency' => 'USD',
            'balance' => 0
        ],
        'broker' => [
            'name' => 'TestBroker',
            'support_email' => 'support@testbroker.com',
            'website' => 'https://testbroker.com'
        ]
    ],
    'source' => 'test'
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($testData)
    ]
]);

$response = file_get_contents($baseUrl . '/email/send', false, $context);
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Email sent successfully!\n";
        echo "   🆔 Message ID: " . ($data['data']['message_id'] ?? 'N/A') . "\n";
        echo "   📤 Status: " . ($data['data']['status'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ Email sending failed: " . ($data['message'] ?? 'unknown error') . "\n";
        if (isset($data['error'])) {
            echo "      Error: " . $data['error'] . "\n";
        }
    }
} else {
    echo "   ❌ Email sending request failed\n";
}

// Test 6: Get Webhook Statistics
echo "\n6. Testing Get Webhook Statistics...\n";
$response = file_get_contents($baseUrl . '/webhook/stats');
if ($response) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "   ✅ Webhook statistics retrieved successfully\n";
        echo "   📊 Total webhooks: " . ($data['data']['total_webhooks'] ?? 0) . "\n";
        echo "   📊 Unprocessed webhooks: " . ($data['data']['unprocessed_webhooks'] ?? 0) . "\n";
    } else {
        echo "   ❌ Failed to get webhook statistics: " . ($data['message'] ?? 'unknown error') . "\n";
    }
} else {
    echo "   ❌ Get webhook statistics request failed\n";
}

echo "\n🎉 API Testing Complete!\n";
echo "========================\n\n";

echo "📋 Next Steps:\n";
echo "1. Configure your AWS SES credentials in .env\n";
echo "2. Set up your DNS records for the sending subdomain\n";
echo "3. Test with real email addresses\n";
echo "4. Configure webhook endpoints in AWS SES\n";
echo "5. Monitor email delivery and webhook events\n\n";

echo "🔗 Useful URLs:\n";
echo "- API Base: http://localhost:8000/api\n";
echo "- Health Check: http://localhost:8000/api/health\n";
echo "- API Docs: http://localhost:8000/api\n";
echo "- Email Templates: http://localhost:8000/api/email/templates\n";
echo "- Email Stats: http://localhost:8000/api/email/stats\n\n";

echo "📚 For more information, check the README.md file.\n"; 