<?php
/**
 * Simple RabbitMQ Connection Test Script
 * Run: php test-rabbitmq-connection.php
 */

require __DIR__ . '/email-microservice/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

echo "=== RabbitMQ Connection Test ===\n\n";

// Load environment variables
$envFile = __DIR__ . '/email-microservice/.env';
$config = [
    'host' => '127.0.0.1',
    'port' => 5672,
    'user' => 'guest',
    'password' => 'guest',
    'vhost' => '/'
];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'RABBITMQ_') === 0 && strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $value = trim($value);
            
            if ($key === 'RABBITMQ_HOST') $config['host'] = $value;
            if ($key === 'RABBITMQ_PORT') $config['port'] = (int)$value;
            if ($key === 'RABBITMQ_USER') $config['user'] = $value;
            if ($key === 'RABBITMQ_PASSWORD') $config['password'] = $value;
            if ($key === 'RABBITMQ_VHOST') $config['vhost'] = $value;
        }
    }
}

echo "Configuration:\n";
echo "  Host: {$config['host']}\n";
echo "  Port: {$config['port']}\n";
echo "  User: {$config['user']}\n";
echo "  VHost: {$config['vhost']}\n\n";

echo "Attempting to connect...\n";

try {
    $connection = new AMQPStreamConnection(
        $config['host'],
        $config['port'],
        $config['user'],
        $config['password'],
        $config['vhost']
    );
    
    echo "✅ SUCCESS! Connected to RabbitMQ!\n\n";
    
    // Create a channel
    $channel = $connection->channel();
    echo "✅ Channel created successfully!\n\n";
    
    // Declare a test queue
    $queueName = 'email.send';
    $channel->queue_declare($queueName, false, true, false, false);
    echo "✅ Queue '{$queueName}' declared successfully!\n\n";
    
    // Get queue info
    list($queue, $messageCount, $consumerCount) = $channel->queue_declare($queueName, false, true, false, false);
    echo "Queue Information:\n";
    echo "  Name: {$queueName}\n";
    echo "  Messages: {$messageCount}\n";
    echo "  Consumers: {$consumerCount}\n\n";
    
    // Close connections
    $channel->close();
    $connection->close();
    
    echo "✅ Connection closed gracefully.\n";
    echo "\n🎉 RabbitMQ is working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: Failed to connect to RabbitMQ\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "  1. Make sure RabbitMQ service is running\n";
    echo "  2. Check if port 5672 is accessible\n";
    echo "  3. Verify credentials in .env file\n";
    echo "  4. Check Windows Services for 'RabbitMQ'\n";
    exit(1);
}

