<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailWebhook;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Process AWS SES webhook data.
     */
    public function processSESWebhook(array $webhookData): array
    {
        try {
            Log::info('Processing SES webhook', ['data' => $webhookData]);

            $results = [];
            
            if (isset($webhookData['Records']) && is_array($webhookData['Records'])) {
                foreach ($webhookData['Records'] as $record) {
                    $result = $this->processSESRecord($record);
                    $results[] = $result;
                }
            } else {
                // Single event
                $result = $this->processSESRecord($webhookData);
                $results[] = $result;
            }

            return [
                'success' => true,
                'processed' => count($results),
                'results' => $results,
            ];

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process a single SES record.
     */
    protected function processSESRecord(array $record): array
    {
        $eventType = $record['eventType'] ?? 'Unknown';
        $ses = $record['ses'] ?? [];
        $mail = $ses['mail'] ?? [];
        $receipt = $ses['receipt'] ?? [];

        $messageId = $mail['messageId'] ?? null;
        $recipient = $receipt['recipients'][0] ?? null;
        $timestamp = $record['eventTime'] ?? now();

        if (!$messageId || !$recipient) {
            Log::warning('Invalid SES record', ['record' => $record]);
            return ['success' => false, 'error' => 'Invalid record data'];
        }

        // Find the email log
        $emailLog = EmailLog::where('provider_message_id', $messageId)->first();
        
        if (!$emailLog) {
            Log::warning('Email log not found for message ID', ['message_id' => $messageId]);
            return ['success' => false, 'error' => 'Email log not found'];
        }

        // Create webhook record
        $webhook = $this->createWebhookRecord($record, $eventType, $messageId, $recipient, $timestamp);

        // Update email log based on event type
        $this->updateEmailLogStatus($emailLog, $eventType, $webhook);

        return [
            'success' => true,
            'event_type' => $eventType,
            'message_id' => $messageId,
            'recipient' => $recipient,
            'webhook_id' => $webhook->id,
        ];
    }

    /**
     * Create webhook record.
     */
    protected function createWebhookRecord(array $record, string $eventType, string $messageId, string $recipient, string $timestamp): EmailWebhook
    {
        $ses = $record['ses'] ?? [];
        $mail = $ses['mail'] ?? [];
        $receipt = $ses['receipt'] ?? [];

        $webhookData = [
            'event_type' => $eventType,
            'recipient' => $recipient,
            'message_id' => $messageId,
            'provider_message_id' => $messageId,
            'provider' => 'ses',
            'event_timestamp' => $timestamp,
            'raw_data' => $record,
        ];

        // Add event-specific data
        switch ($eventType) {
            case 'Bounce':
                $bounce = $ses['bounce'] ?? [];
                $webhookData['reason'] = $bounce['bounceType'] ?? null;
                $webhookData['bounce_type'] = $bounce['bounceType'] ?? null;
                $webhookData['bounce_sub_type'] = $bounce['bounceSubType'] ?? null;
                break;

            case 'Complaint':
                $complaint = $ses['complaint'] ?? [];
                $webhookData['reason'] = $complaint['complaintFeedbackType'] ?? null;
                $webhookData['complaint_feedback_type'] = $complaint['complaintFeedbackType'] ?? null;
                $webhookData['complaint_user_agent'] = $complaint['userAgent'] ?? null;
                break;

            case 'Delivery':
                $webhookData['reason'] = 'Delivered successfully';
                break;

            case 'Open':
                $webhookData['reason'] = 'Email opened';
                break;

            case 'Click':
                $webhookData['reason'] = 'Link clicked';
                break;
        }

        return EmailWebhook::create($webhookData);
    }

    /**
     * Update email log status based on webhook event.
     */
    protected function updateEmailLogStatus(EmailLog $emailLog, string $eventType, EmailWebhook $webhook): void
    {
        $updateData = [];

        switch ($eventType) {
            case 'Bounce':
                $updateData['status'] = 'bounced';
                $updateData['error_message'] = $webhook->reason;
                break;

            case 'Complaint':
                $updateData['status'] = 'complained';
                $updateData['error_message'] = $webhook->reason;
                break;

            case 'Delivery':
                $updateData['status'] = 'delivered';
                $updateData['delivered_at'] = $webhook->event_timestamp;
                break;

            case 'Open':
                $updateData['opened_at'] = $webhook->event_timestamp;
                break;

            case 'Click':
                $updateData['clicked_at'] = $webhook->event_timestamp;
                break;
        }

        if (!empty($updateData)) {
            $emailLog->update($updateData);
            Log::info('Email log updated', [
                'message_id' => $emailLog->message_id,
                'event_type' => $eventType,
                'updates' => $updateData,
            ]);
        }
    }

    /**
     * Process webhook from other providers (Postmark, etc.).
     */
    public function processGenericWebhook(array $webhookData, string $provider = 'generic'): array
    {
        try {
            Log::info('Processing generic webhook', [
                'provider' => $provider,
                'data' => $webhookData,
            ]);

            $eventType = $webhookData['event_type'] ?? $webhookData['Type'] ?? 'Unknown';
            $recipient = $webhookData['recipient'] ?? $webhookData['Email'] ?? null;
            $messageId = $webhookData['message_id'] ?? $webhookData['MessageID'] ?? null;
            $timestamp = $webhookData['timestamp'] ?? $webhookData['DeliveredAt'] ?? now();

            if (!$recipient || !$messageId) {
                throw new \Exception('Missing required webhook data: recipient or message_id');
            }

            // Find the email log
            $emailLog = EmailLog::where('message_id', $messageId)
                ->orWhere('provider_message_id', $messageId)
                ->first();

            if (!$emailLog) {
                Log::warning('Email log not found for message ID', ['message_id' => $messageId]);
                return ['success' => false, 'error' => 'Email log not found'];
            }

            // Create webhook record
            $webhook = EmailWebhook::create([
                'event_type' => $eventType,
                'recipient' => $recipient,
                'message_id' => $emailLog->message_id,
                'provider_message_id' => $messageId,
                'provider' => $provider,
                'event_timestamp' => $timestamp,
                'raw_data' => $webhookData,
                'reason' => $webhookData['reason'] ?? $webhookData['Description'] ?? null,
            ]);

            // Update email log status
            $this->updateEmailLogStatus($emailLog, $eventType, $webhook);

            return [
                'success' => true,
                'event_type' => $eventType,
                'message_id' => $messageId,
                'recipient' => $recipient,
                'webhook_id' => $webhook->id,
            ];

        } catch (\Exception $e) {
            Log::error('Generic webhook processing failed', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
                'provider' => $provider,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get webhook statistics.
     */
    public function getWebhookStats(): array
    {
        $totalWebhooks = EmailWebhook::count();
        $unprocessedWebhooks = EmailWebhook::unprocessed()->count();
        
        $eventTypeStats = EmailWebhook::selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();

        $providerStats = EmailWebhook::selectRaw('provider, COUNT(*) as count')
            ->groupBy('provider')
            ->pluck('count', 'provider')
            ->toArray();

        return [
            'total_webhooks' => $totalWebhooks,
            'unprocessed_webhooks' => $unprocessedWebhooks,
            'event_type_stats' => $eventTypeStats,
            'provider_stats' => $providerStats,
        ];
    }

    /**
     * Mark webhooks as processed.
     */
    public function markWebhooksAsProcessed(array $webhookIds): array
    {
        try {
            $updated = EmailWebhook::whereIn('id', $webhookIds)
                ->update([
                    'processed' => true,
                    'processed_at' => now(),
                ]);

            return [
                'success' => true,
                'updated_count' => $updated,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to mark webhooks as processed', [
                'error' => $e->getMessage(),
                'webhook_ids' => $webhookIds,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
} 