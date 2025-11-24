<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

echo "=== Updating Gmail Provider with IMAP Configuration ===\n\n";

// Find the active Gmail provider
$provider = \App\Models\EmailProvider::where('provider_name', 'Gmail')
    ->where('is_active', true)
    ->first();

if (!$provider) {
    echo "❌ No active Gmail provider found!\n";
    exit(1);
}

echo "Found Gmail provider: {$provider->provider_name}\n";
echo "Current config: " . json_encode($provider->config_json, JSON_PRETTY_PRINT) . "\n\n";

// Update with IMAP configuration
$newConfig = $provider->config_json;
$newConfig['protocol'] = 'imap';
$newConfig['imap_host'] = 'imap.gmail.com';
$newConfig['imap_port'] = 993;
$newConfig['imap_encryption'] = 'ssl';
$newConfig['imap_username'] = 'nishantjoshiestaticinfotech@gmail.com';
$newConfig['imap_password'] = 'lqvv wifn mfcl kxey'; // Same as SMTP password
$newConfig['folder'] = 'INBOX';

$provider->config_json = $newConfig;
$provider->save();

echo "✅ Updated Gmail provider with IMAP configuration:\n";
echo json_encode($provider->config_json, JSON_PRETTY_PRINT) . "\n\n";

echo "=== Testing IMAP Connection ===\n";

// Test the IMAP connection
try {
    $host = $newConfig['imap_host'];
    $port = $newConfig['imap_port'];
    $encryption = $newConfig['imap_encryption'];
    $username = $newConfig['imap_username'];
    $password = $newConfig['imap_password'];
    
    $connectionString = "{{$host}:$port/imap/$encryption}INBOX";
    echo "Testing connection: $connectionString\n";
    
    $connection = imap_open($connectionString, $username, $password);
    
    if ($connection) {
        echo "✅ IMAP connection successful!\n";
        $messageCount = imap_num_msg($connection);
        echo "Total messages in inbox: $messageCount\n";
        imap_close($connection);
    } else {
        echo "❌ IMAP connection failed: " . imap_last_error() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Update Complete ===\n";
?>
