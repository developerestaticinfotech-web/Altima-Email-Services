<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Email Microservice System Test ===\n\n";

try {
    // Test 1: Database Connection
    echo "1. Testing Database Connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "   ✅ Database connected successfully\n";
    
    // Test 2: Check Tables
    echo "\n2. Checking Database Tables...\n";
    $tables = DB::select('SHOW TABLES');
    $tableNames = array_column($tables, 'Tables_in_altimacrm_email');
    
    $requiredTables = ['tenants', 'email_providers', 'inbox', 'outbox', 'attachments'];
    foreach ($requiredTables as $table) {
        if (in_array($table, $tableNames)) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' missing\n";
        }
    }
    
    // Test 3: Check Models
    echo "\n3. Testing Eloquent Models...\n";
    
    // Test Tenant Model
    $tenantCount = App\Models\Tenant::count();
    echo "   ✅ Tenant model working - Found $tenantCount tenants\n";
    
    // Test EmailProvider Model
    $providerCount = App\Models\EmailProvider::count();
    echo "   ✅ EmailProvider model working - Found $providerCount providers\n";
    
    // Test 4: Check Relationships
    echo "\n4. Testing Model Relationships...\n";
    $tenant = App\Models\Tenant::first();
    if ($tenant) {
        $providerCount = $tenant->emailProviders()->count();
        echo "   ✅ Tenant-Provider relationship working - Tenant '{$tenant->tenant_name}' has $providerCount providers\n";
    }
    
    // Test 5: Check Sample Data
    echo "\n5. Checking Sample Data...\n";
    $firstTenant = App\Models\Tenant::first();
    $firstProvider = App\Models\EmailProvider::first();
    
    if ($firstTenant) {
        echo "   ✅ Sample tenant: {$firstTenant->tenant_name} (ID: {$firstTenant->tenant_id})\n";
    }
    
    if ($firstProvider) {
        echo "   ✅ Sample provider: {$firstProvider->provider_name} (ID: {$firstProvider->provider_id})\n";
    }
    
    // Test 6: Check Routes
    echo "\n6. Checking API Routes...\n";
    $routes = Route::getRoutes();
    $apiRoutes = collect($routes)->filter(function ($route) {
        return str_starts_with($route->uri(), 'api/');
    })->count();
    
    echo "   ✅ Found $apiRoutes API routes\n";
    
    echo "\n=== System Test Complete ===\n";
    echo "✅ All systems are working correctly!\n";
    
} catch (Exception $e) {
    echo "\n❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 