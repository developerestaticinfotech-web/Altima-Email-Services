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
            // Make body_content nullable to support template-based emails
            // where body is built during queue processing
            $table->longText('body_content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outbox', function (Blueprint $table) {
            // Revert to non-nullable (use empty string as default)
            $table->longText('body_content')->nullable(false)->default('')->change();
        });
    }
};
