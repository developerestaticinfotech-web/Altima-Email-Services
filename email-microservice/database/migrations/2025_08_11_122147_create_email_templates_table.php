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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_id')->unique()->comment('Unique template identifier');
            $table->string('name')->comment('Template display name');
            $table->string('subject')->comment('Default email subject');
            $table->text('html_content')->nullable()->comment('HTML version of the template');
            $table->text('text_content')->nullable()->comment('Plain text version of the template');
            $table->json('variables')->nullable()->comment('Template variables and their descriptions');
            $table->string('category')->default('general')->comment('Template category (welcome, invoice, etc.)');
            $table->string('language', 5)->default('en')->comment('Template language code');
            $table->boolean('is_active')->default(true)->comment('Whether template is active');
            $table->json('metadata')->nullable()->comment('Additional template metadata');
            $table->timestamps();
            
            $table->index(['template_id', 'is_active']);
            $table->index(['category', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
