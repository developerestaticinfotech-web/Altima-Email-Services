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
        Schema::create('outbox', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('provider_id');
            $table->uuid('user_id');
            $table->string('message_id')->nullable(); // Provider's message ID
            $table->string('subject');
            $table->string('from');
            $table->json('to'); // Recipient List
            $table->json('cc')->nullable(); // CC List
            $table->json('bcc')->nullable(); // BCC List
            $table->enum('body_format', ['EML', 'Text', 'HTML', 'JSON']);
            $table->longText('body_content'); // Raw Email Content
            $table->json('attachments')->nullable(); // List of attachments
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('provider_response')->nullable(); // Provider's response data
            
            // Enhanced tracking fields
            $table->string('source')->default('direct'); // 'direct', 'queue', 'api', 'cron'
            $table->string('queue_name')->nullable(); // If sent via queue
            $table->string('processing_method')->nullable(); // 'rabbitmq', 'direct', 'batch'
            $table->boolean('queue_processed')->default(false); // Whether processed via queue
            $table->datetime('queued_at')->nullable(); // When added to queue
            $table->datetime('processing_started_at')->nullable(); // When queue processing started
            $table->datetime('sent_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->datetime('bounced_at')->nullable();
            
            // Performance metrics
            $table->integer('processing_time_ms')->nullable(); // Time taken to process
            $table->integer('delivery_time_ms')->nullable(); // Time from sent to delivered
            $table->integer('retry_count')->default(0); // Number of retry attempts
            
            // Additional metadata
            $table->json('metadata')->nullable(); // Custom metadata, tags, categories
            $table->string('campaign_id')->nullable(); // For email campaigns
            $table->string('template_id')->nullable(); // Email template used
            $table->json('headers')->nullable(); // Custom email headers
            
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onDelete('cascade');
            $table->foreign('provider_id')->references('provider_id')->on('email_providers')->onDelete('cascade');
            $table->index(['tenant_id', 'user_id']);
            $table->index(['provider_id']);
            $table->index(['status']);
            $table->index(['sent_at']);
            $table->index(['message_id']);
            $table->index(['source']);
            $table->index(['queue_name']);
            $table->index(['processing_method']);
            $table->index(['campaign_id']);
            $table->index(['template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox');
    }
};
