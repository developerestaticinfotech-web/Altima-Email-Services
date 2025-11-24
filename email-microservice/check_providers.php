<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

echo "=== Active Email Providers ===\n\n";

$providers = \App\Models\EmailProvider::with('tenant')->where('status', 'active')->get();

foreach($providers as $provider) {
    echo "Provider: " . $provider->provider_name . "\n";
    echo "Tenant: " . $provider->tenant->tenant_name . "\n";
    echo "Config:\n";
    echo json_encode($provider->config_json, JSON_PRETTY_PRINT) . "\n";
    echo "---\n\n";
}

echo "Total active providers: " . $providers->count() . "\n";
?>
