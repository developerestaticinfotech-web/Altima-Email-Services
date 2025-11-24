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
        Schema::create('email_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->comment('Event type: Bounce, Complaint, Delivery, Open, Click, etc.');
            $table->string('recipient')->comment('Recipient email address');
            $table->string('message_id')->nullable()->comment('Message ID from email provider');
            $table->string('provider_message_id')->nullable()->comment('Provider-specific message ID');
            $table->string('provider')->default('ses')->comment('Email provider (SES, Postmark, etc.)');
            $table->text('reason')->nullable()->comment('Reason for bounce/complaint');
            $table->string('bounce_type')->nullable()->comment('Bounce type (Permanent, Transient)');
            $table->string('bounce_sub_type')->nullable()->comment('Bounce sub-type');
            $table->string('complaint_feedback_type')->nullable()->comment('Complaint feedback type');
            $table->string('complaint_user_agent')->nullable()->comment('User agent that reported complaint');
            $table->timestamp('event_timestamp')->comment('When the event occurred');
            $table->json('raw_data')->nullable()->comment('Raw webhook data from provider');
            $table->json('metadata')->nullable()->comment('Additional event metadata');
            $table->string('ip_address')->nullable()->comment('IP address related to event');
            $table->string('user_agent')->nullable()->comment('User agent related to event');
            $table->boolean('processed')->default(false)->comment('Whether webhook has been processed');
            $table->timestamp('processed_at')->nullable()->comment('When webhook was processed');
            $table->timestamps();
            
            $table->index(['event_type', 'recipient']);
            $table->index(['message_id', 'provider']);
            $table->index(['event_timestamp']);
            $table->index(['processed', 'event_timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_webhooks');
    }
};
