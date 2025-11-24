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
        Schema::create('email_providers', function (Blueprint $table) {
            $table->uuid('provider_id')->primary();
            $table->uuid('tenant_id');
            $table->string('provider_name'); // Postmark, AWS SES, Gmail, etc.
            $table->json('config_json'); // Provider-specific configuration
            $table->string('bounce_email')->nullable(); // Optional bounce address
            $table->json('header_overrides')->nullable(); // Custom headers for sending
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'provider_name']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_providers');
    }
};
