<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add tenant_id column (nullable first)
            $table->uuid('tenant_id')->nullable()->after('id');
            
            // Add role column
            $table->enum('role', ['admin', 'user'])->default('user')->after('password');
            
            // Add is_active column
            $table->boolean('is_active')->default(true)->after('role');
            
            // Add last_login_at column
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        // Update existing users to have a default tenant
        $defaultTenant = \App\Models\Tenant::first();
        if ($defaultTenant) {
            \DB::table('users')->update(['tenant_id' => $defaultTenant->tenant_id]);
        }

        // Now make tenant_id not nullable and add foreign key
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable(false)->change();
            
            // Add foreign key constraint
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onDelete('cascade');
            
            // Add indexes
            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'role']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'email']);
            $table->dropIndex(['tenant_id', 'role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['tenant_id', 'role', 'is_active', 'last_login_at']);
        });
    }
};