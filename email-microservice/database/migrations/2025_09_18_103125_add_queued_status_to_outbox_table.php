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
        Schema::table('outbox', function (Blueprint $table) {
            // Modify the status enum to include 'queued'
            $table->enum('status', ['pending', 'queued', 'sent', 'failed', 'bounced'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outbox', function (Blueprint $table) {
            // Revert the status enum to original values
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending')->change();
        });
    }
};
