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
            $table->json('corrections')->nullable()->comment('History of email address corrections');
            $table->text('bounce_reason')->nullable()->comment('Reason for email bounce or failure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outbox', function (Blueprint $table) {
            $table->dropColumn(['corrections', 'bounce_reason']);
        });
    }
};
