<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

echo "=== Testing IMAP Connection ===\n\n";

// Test Gmail IMAP connection
$host = 'imap.gmail.com';
$port = 993;
$encryption = 'ssl';
$username = 'nishantjoshiestaticinfotech@gmail.com';
$password = 'your_app_password_here'; // You'll need to use an App Password

echo "Testing IMAP connection to Gmail...\n";
echo "Host: $host:$port\n";
echo "Encryption: $encryption\n";
echo "Username: $username\n\n";

try {
    $connectionString = "{{$host}:$port/imap/$encryption}INBOX";
    echo "Connection string: $connectionString\n\n";
    
    $connection = imap_open($connectionString, $username, $password);
    
    if ($connection) {
        echo "✅ IMAP connection successful!\n";
        
        // Get message count
        $messageCount = imap_num_msg($connection);
        echo "Total messages in inbox: $messageCount\n";
        
        // Get recent messages
        if ($messageCount > 0) {
            $recentCount = min(5, $messageCount);
            echo "Fetching last $recentCount messages...\n";
            
            for ($i = $messageCount - $recentCount + 1; $i <= $messageCount; $i++) {
                $header = imap_headerinfo($connection, $i);
                echo "Message $i: From: {$header->from[0]->mailbox}@{$header->from[0]->host}\n";
                echo "Subject: {$header->subject}\n";
                echo "Date: {$header->date}\n\n";
            }
        }
        
        imap_close($connection);
    } else {
        echo "❌ IMAP connection failed!\n";
        echo "Error: " . imap_last_error() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== End Test ===\n";
?>
