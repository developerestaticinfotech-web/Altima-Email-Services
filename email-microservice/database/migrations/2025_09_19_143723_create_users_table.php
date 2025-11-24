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
        // Check if users table already exists (from default Laravel migration)
        if (Schema::hasTable('users')) {
            // Table exists, check if we need to modify it
            if (!Schema::hasColumn('users', 'user_id')) {
                // Modify existing table to match our structure
                Schema::table('users', function (Blueprint $table) {
                    // Add missing columns if they don't exist
                    if (!Schema::hasColumn('users', 'tenant_id')) {
                        $table->uuid('tenant_id')->nullable()->after('id');
                    }
                    if (!Schema::hasColumn('users', 'role')) {
                        $table->enum('role', ['admin', 'user'])->default('user')->after('password');
                    }
                    if (!Schema::hasColumn('users', 'is_active')) {
                        $table->boolean('is_active')->default(true)->after('role');
                    }
                    if (!Schema::hasColumn('users', 'last_login_at')) {
                        $table->timestamp('last_login_at')->nullable()->after('is_active');
                    }
                });
            }
            return;
        }
        
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onDelete('cascade');

            // Indexes for performance
            $table->index(['tenant_id', 'email']);
            $table->index(['email']);
            $table->index(['tenant_id', 'role']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};