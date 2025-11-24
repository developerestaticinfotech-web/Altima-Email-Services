<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailWebhook;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle AWS SES webhooks.
     */
    public function handleSESWebhook(Request $request): JsonResponse
    {
        try {
            Log::info('Received SES webhook', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ]);

            // Process the webhook
            $result = $this->webhookService->processSESWebhook($request->all());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                    'processed' => $result['processed'] ?? 1,
                ], 200);
            } else {
                Log::error('SES webhook processing failed', ['result' => $result]);
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook processing failed',
                    'error' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('SES webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle generic webhooks (Postmark, etc.).
     */
    public function handleGenericWebhook(Request $request, string $provider = 'generic'): JsonResponse
    {
        try {
            Log::info('Received generic webhook', [
                'provider' => $provider,
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ]);

            // Process the webhook
            $result = $this->webhookService->processGenericWebhook($request->all(), $provider);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                    'event_type' => $result['event_type'],
                    'message_id' => $result['message_id'],
                ], 200);
            } else {
                Log::error('Generic webhook processing failed', ['result' => $result]);
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook processing failed',
                    'error' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Generic webhook error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get webhook statistics.
     */
    public function getWebhookStats(): JsonResponse
    {
        try {
            $stats = $this->webhookService->getWebhookStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get webhook stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get webhook events with filters.
     */
    public function getWebhookEvents(Request $request): JsonResponse
    {
        try {
            $query = EmailWebhook::query();

            // Apply filters
            if ($request->has('event_type')) {
                $query->byEventType($request->event_type);
            }

            if ($request->has('provider')) {
                $query->byProvider($request->provider);
            }

            if ($request->has('processed')) {
                $query->where('processed', $request->boolean('processed'));
            }

            if ($request->has('date_from') && $request->has('date_to')) {
                $query->withinDateRange($request->date_from, $request->date_to);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $webhooks = $query->orderBy('event_timestamp', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $webhooks->items(),
                'pagination' => [
                    'current_page' => $webhooks->currentPage(),
                    'last_page' => $webhooks->lastPage(),
                    'per_page' => $webhooks->perPage(),
                    'total' => $webhooks->total(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get webhook events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark webhooks as processed.
     */
    public function markWebhooksAsProcessed(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'webhook_ids' => 'required|array',
                'webhook_ids.*' => 'integer|exists:email_webhooks,id',
            ]);

            $result = $this->webhookService->markWebhooksAsProcessed($request->webhook_ids);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhooks marked as processed',
                    'updated_count' => $result['updated_count'],
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark webhooks as processed',
                    'error' => $result['error'],
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to mark webhooks as processed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get webhook event by ID.
     */
    public function getWebhookEvent(int $id): JsonResponse
    {
        try {
            $webhook = EmailWebhook::with('emailLog')->find($id);

            if (!$webhook) {
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook event not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $webhook,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get webhook event', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
