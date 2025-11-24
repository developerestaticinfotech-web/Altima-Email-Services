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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('message_id')->unique()->comment('Unique message identifier');
            $table->string('template_id')->comment('Template used for this email');
            $table->string('to_email')->comment('Recipient email address');
            $table->string('to_name')->nullable()->comment('Recipient name');
            $table->string('cc_emails')->nullable()->comment('CC recipients (comma-separated)');
            $table->string('bcc_emails')->nullable()->comment('BCC recipients (comma-separated)');
            $table->string('subject')->comment('Email subject line');
            $table->text('html_content')->nullable()->comment('HTML content sent');
            $table->text('text_content')->nullable()->comment('Text content sent');
            $table->json('data')->nullable()->comment('Template data used for rendering');
            $table->string('status')->default('pending')->comment('Email status: pending, sent, delivered, failed, bounced');
            $table->string('provider_message_id')->nullable()->comment('Provider message ID (SES, etc.)');
            $table->string('provider')->default('ses')->comment('Email provider used');
            $table->json('provider_response')->nullable()->comment('Provider response data');
            $table->text('error_message')->nullable()->comment('Error message if failed');
            $table->timestamp('sent_at')->nullable()->comment('When email was sent');
            $table->timestamp('delivered_at')->nullable()->comment('When email was delivered');
            $table->timestamp('opened_at')->nullable()->comment('When email was opened');
            $table->timestamp('clicked_at')->nullable()->comment('When email was clicked');
            $table->string('source')->nullable()->comment('Source system (crm, api, etc.)');
            $table->json('headers')->nullable()->comment('Custom headers sent');
            $table->json('attachments')->nullable()->comment('Attachment information');
            $table->json('tracking')->nullable()->comment('Tracking settings and data');
            $table->string('ip_address')->nullable()->comment('IP address of sender');
            $table->string('user_agent')->nullable()->comment('User agent of sender');
            $table->timestamps();
            
            $table->index(['message_id']);
            $table->index(['to_email', 'status']);
            $table->index(['template_id', 'status']);
            $table->index(['sent_at']);
            $table->index(['source', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
