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
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('email_id'); // Reference to inbox or outbox
            $table->string('email_type'); // 'inbox' or 'outbox'
            $table->string('filename');
            $table->string('mime_type');
            $table->string('storage_path'); // File path or S3 URL
            $table->bigInteger('file_size')->nullable(); // File size in bytes
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->timestamps();
            
            $table->index(['email_id', 'email_type']);
            $table->index(['mime_type']);
            $table->index(['file_size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
