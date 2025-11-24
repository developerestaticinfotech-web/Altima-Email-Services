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
            // Enhanced tracking fields - check if columns exist before adding
            if (!Schema::hasColumn('outbox', 'source')) {
                $table->string('source')->nullable()->after('provider_response');
            }
            if (!Schema::hasColumn('outbox', 'queue_name')) {
                $table->string('queue_name')->nullable()->after('source');
            }
            if (!Schema::hasColumn('outbox', 'processing_method')) {
                $table->string('processing_method')->nullable()->after('queue_name');
            }
            if (!Schema::hasColumn('outbox', 'queue_processed')) {
                $table->boolean('queue_processed')->default(false)->after('processing_method');
            }
            if (!Schema::hasColumn('outbox', 'queued_at')) {
                $table->timestamp('queued_at')->nullable()->after('queue_processed');
            }
            if (!Schema::hasColumn('outbox', 'processing_started_at')) {
                $table->timestamp('processing_started_at')->nullable()->after('queued_at');
            }
            if (!Schema::hasColumn('outbox', 'bounced_at')) {
                $table->timestamp('bounced_at')->nullable()->after('processing_started_at');
            }
            if (!Schema::hasColumn('outbox', 'processing_time_ms')) {
                $table->integer('processing_time_ms')->nullable()->after('processing_started_at');
            }
            if (!Schema::hasColumn('outbox', 'delivery_time_ms')) {
                $table->integer('delivery_time_ms')->nullable()->after('processing_time_ms');
            }
            if (!Schema::hasColumn('outbox', 'retry_count')) {
                $table->integer('retry_count')->default(0)->after('delivery_time_ms');
            }
            if (!Schema::hasColumn('outbox', 'metadata')) {
                $table->json('metadata')->nullable()->after('retry_count');
            }
            if (!Schema::hasColumn('outbox', 'campaign_id')) {
                $table->string('campaign_id')->nullable()->after('metadata');
            }
            if (!Schema::hasColumn('outbox', 'template_id')) {
                $table->string('template_id')->nullable()->after('campaign_id');
            }
            if (!Schema::hasColumn('outbox', 'headers')) {
                $table->json('headers')->nullable()->after('template_id');
            }
        });
        
        // Add indexes separately to avoid conflicts (using try-catch for safety)
        try {
            Schema::table('outbox', function (Blueprint $table) {
                // Try to add indexes - will fail silently if they exist
                try {
                    $table->index(['source', 'queue_name'], 'outbox_source_queue_name_index');
                } catch (\Exception $e) {}
                try {
                    $table->index(['processing_method', 'queue_processed'], 'outbox_processing_method_queue_processed_index');
                } catch (\Exception $e) {}
                try {
                    $table->index(['queued_at', 'processing_started_at'], 'outbox_queued_at_processing_started_at_index');
                } catch (\Exception $e) {}
                try {
                    $table->index(['campaign_id', 'template_id'], 'outbox_campaign_id_template_id_index');
                } catch (\Exception $e) {}
            });
        } catch (\Exception $e) {
            // Indexes may already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outbox', function (Blueprint $table) {
            // Remove indexes first
            $table->dropIndex(['source', 'queue_name']);
            $table->dropIndex(['processing_method', 'queue_processed']);
            $table->dropIndex(['queued_at', 'processing_started_at']);
            $table->dropIndex(['campaign_id', 'template_id']);
            
            // Remove tracking columns
            $table->dropColumn([
                'source',
                'queue_name',
                'processing_method',
                'queue_processed',
                'queued_at',
                'processing_started_at',
                'bounced_at',
                'processing_time_ms',
                'delivery_time_ms',
                'retry_count',
                'metadata',
                'campaign_id',
                'template_id',
                'headers'
            ]);
        });
    }
};
