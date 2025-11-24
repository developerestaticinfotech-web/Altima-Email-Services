<?php
$host = '127.0.0.1';
$dbname = 'altimacrm_email';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Database Tables ===\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\n=== Email Providers ===\n";
    $providers = $pdo->query('SELECT * FROM email_providers')->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($providers) . "\n";
    foreach($providers as $provider) {
        echo "Provider: " . $provider['provider_name'] . " (Active: " . ($provider['is_active'] ? 'Yes' : 'No') . ")\n";
    }
    
    echo "\n=== Tenants ===\n";
    $tenants = $pdo->query('SELECT * FROM tenants')->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($tenants) . "\n";
    foreach($tenants as $tenant) {
        echo "Tenant: " . $tenant['tenant_name'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
