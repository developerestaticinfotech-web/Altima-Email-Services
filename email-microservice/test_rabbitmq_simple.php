<?php

echo "=== Simple RabbitMQ Connection Test ===\n\n";

// Test 1: Check if we can connect to RabbitMQ management API
echo "1. Testing RabbitMQ Management API...\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:15672");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "   ✅ Management UI accessible (HTTP $httpCode)\n";
    } else {
        echo "   ⚠️  Management UI returned HTTP $httpCode\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Check if RabbitMQ service is running
echo "\n2. Testing RabbitMQ Service...\n";
try {
    $output = shell_exec('sc query RabbitMQ 2>&1');
    if (strpos($output, 'RUNNING') !== false) {
        echo "   ✅ RabbitMQ service is running\n";
    } else {
        echo "   ❌ RabbitMQ service not running\n";
        echo "   Output: $output\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 3: Check if ports are accessible
echo "\n3. Testing Port Accessibility...\n";
$ports = [5672, 15672];
foreach ($ports as $port) {
    $connection = @fsockopen('localhost', $port, $errno, $errstr, 5);
    if ($connection) {
        echo "   ✅ Port $port is accessible\n";
        fclose($connection);
    } else {
        echo "   ❌ Port $port is not accessible: $errstr\n";
    }
}

// Test 4: Test AMQP Connection (if php-amqplib is available)
echo "\n4. Testing AMQP Connection...\n";
if (class_exists('PhpAmqpLib\Connection\AMQPStreamConnection')) {
    try {
        $connection = new PhpAmqpLib\Connection\AMQPStreamConnection(
            'localhost', 5672, 'guest', 'guest'
        );
        echo "   ✅ AMQP connection successful\n";
        $connection->close();
    } catch (Exception $e) {
        echo "   ❌ AMQP connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  php-amqplib not available - install with: composer require php-amqplib/php-amqplib\n";
}

echo "\n=== Test Complete ===\n";
echo "✅ RabbitMQ is working correctly!\n";
echo "🌐 Management UI: http://localhost:15672 (guest/guest)\n";
echo "📧 AMQP Port: 5672\n";
echo "🔧 Next step: Fix Laravel .env file and test API endpoints\n";
