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
        Schema::create('inbound_emails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('provider_id');
            $table->string('message_id')->unique(); // Unique message ID from email provider
            $table->string('in_reply_to')->nullable(); // Message ID this email is replying to
            $table->string('references')->nullable(); // References header for thread tracking
            $table->string('subject');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->json('to_emails'); // Array of recipient emails
            $table->json('cc_emails')->nullable(); // Array of CC emails
            $table->json('bcc_emails')->nullable(); // Array of BCC emails
            $table->enum('body_format', ['EML', 'Text', 'HTML', 'JSON']);
            $table->longText('body_content'); // Raw email content
            $table->json('attachments')->nullable(); // Array of attachment info
            $table->json('headers')->nullable(); // All email headers
            $table->enum('status', ['new', 'processed', 'queued', 'delivered', 'failed'])->default('new');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->boolean('is_reply')->default(false); // Whether this is a reply to an outbound email
            $table->boolean('is_forward')->default(false); // Whether this is a forwarded email
            $table->boolean('is_auto_reply')->default(false); // Whether this is an auto-reply
            $table->string('thread_id')->nullable(); // Thread identifier for conversation grouping
            $table->datetime('received_at'); // When the email was received
            $table->datetime('processed_at')->nullable(); // When it was processed by queue
            $table->datetime('delivered_at')->nullable(); // When it was delivered to CRM
            $table->text('error_message')->nullable(); // Error message if processing failed
            $table->json('provider_response')->nullable(); // Provider-specific response data
            $table->json('metadata')->nullable(); // Additional metadata
            $table->string('source')->default('imap'); // Source: imap, pop3, api, webhook
            $table->string('queue_name')->nullable(); // RabbitMQ queue name if queued
            $table->boolean('queue_processed')->default(false); // Whether processed via queue
            $table->integer('retry_count')->default(0); // Number of retry attempts
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onDelete('cascade');
            $table->foreign('provider_id')->references('provider_id')->on('email_providers')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'received_at']);
            $table->index(['message_id']);
            $table->index(['in_reply_to']);
            $table->index(['thread_id']);
            $table->index(['from_email']);
            $table->index(['status']);
            $table->index(['is_reply']);
            $table->index(['received_at']);
            $table->index(['source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_emails');
    }
};
