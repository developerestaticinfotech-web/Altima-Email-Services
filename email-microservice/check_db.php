<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$tables = $pdo->query('SELECT name FROM sqlite_master WHERE type="table"')->fetchAll(PDO::FETCH_COLUMN);

echo "=== Database Tables ===\n";
foreach($tables as $table) {
    echo "- $table\n";
}

echo "\n=== Email Providers Table ===\n";
try {
    $providers = $pdo->query('SELECT * FROM email_providers')->fetchAll(PDO::FETCH_ASSOC);
    foreach($providers as $provider) {
        echo "Provider: " . $provider['provider_name'] . "\n";
        echo "Active: " . ($provider['is_active'] ? 'Yes' : 'No') . "\n";
        echo "Config: " . $provider['config_json'] . "\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
