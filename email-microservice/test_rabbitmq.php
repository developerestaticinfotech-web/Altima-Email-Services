<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== RabbitMQ Connection Test ===\n\n";

try {
    // Test 1: Check if RabbitMQ service is available
    echo "1. Testing RabbitMQ Service Availability...\n";
    
    // Check if we can connect to RabbitMQ
    $config = config('rabbitmq');
    echo "   Host: " . ($config['host'] ?? 'localhost') . "\n";
    echo "   Port: " . ($config['port'] ?? 5672) . "\n";
    echo "   User: " . ($config['user'] ?? 'guest') . "\n";
    echo "   VHost: " . ($config['vhost'] ?? '/') . "\n";
    
    // Test 2: Test RabbitMQ Service
    echo "\n2. Testing RabbitMQ Service...\n";
    $rabbitMQService = app(\App\Services\RabbitMQService::class);
    
    // Test queue status
    $status = $rabbitMQService->getQueueStatus();
    echo "   Queue Status: " . ($status['connection_status'] ?? 'unknown') . "\n";
    
    if (isset($status['email_send_queue'])) {
        echo "   Email Send Queue: " . $status['email_send_queue']['name'] . "\n";
        echo "   Message Count: " . $status['email_send_queue']['message_count'] . "\n";
    }
    
    // Test 3: Test Queue Statistics
    echo "\n3. Testing Queue Statistics...\n";
    $stats = $rabbitMQService->getQueueStats();
    
    if (isset($stats['queues'])) {
        foreach ($stats['queues'] as $queueName => $queueInfo) {
            echo "   Queue: $queueName\n";
            echo "     Messages: " . $queueInfo['messages'] . "\n";
            echo "     Status: " . $queueInfo['status'] . "\n";
        }
    }
    
    // Test 4: Test Publishing to Queue
    echo "\n4. Testing Message Publishing...\n";
    $testMessage = [
        'test' => true,
        'timestamp' => now()->toISOString(),
        'message' => 'Test message from RabbitMQ test script'
    ];
    
    $publishResult = $rabbitMQService->publishToQueue('email.send', $testMessage);
    echo "   Publish Result: " . ($publishResult ? 'SUCCESS' : 'FAILED') . "\n";
    
    // Test 5: Test Queue Processing
    echo "\n5. Testing Queue Processing...\n";
    $processedCount = $rabbitMQService->processQueue('email.send', 5);
    echo "   Processed Messages: $processedCount\n";
    
    echo "\n=== RabbitMQ Test Complete ===\n";
    echo "✅ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    echo "\n=== Troubleshooting Tips ===\n";
    echo "1. Make sure RabbitMQ service is running\n";
    echo "2. Check if port 5672 is accessible\n";
    echo "3. Verify credentials in .env file\n";
    echo "4. Try: net start RabbitMQ (as Administrator)\n";
    echo "5. Check RabbitMQ logs for errors\n";
}
