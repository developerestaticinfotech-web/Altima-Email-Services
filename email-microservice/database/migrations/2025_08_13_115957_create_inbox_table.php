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
        Schema::create('inbox', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('provider_id');
            $table->uuid('user_id');
            $table->string('message_id')->nullable(); // Provider's message ID
            $table->string('subject')->nullable();
            $table->string('from');
            $table->json('to'); // Recipient List
            $table->json('cc')->nullable(); // CC List
            $table->json('bcc')->nullable(); // BCC List
            $table->enum('body_format', ['EML', 'Text', 'HTML', 'JSON']);
            $table->longText('body_content'); // Raw Email Content
            $table->json('attachments')->nullable(); // List of attachments
            $table->datetime('received_at');
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('tenant_id')->on('tenants')->onDelete('cascade');
            $table->foreign('provider_id')->references('provider_id')->on('email_providers')->onDelete('cascade');
            $table->index(['tenant_id', 'user_id']);
            $table->index(['provider_id']);
            $table->index(['received_at']);
            $table->index(['message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbox');
    }
};
