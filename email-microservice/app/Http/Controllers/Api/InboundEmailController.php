<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InboundEmail;
use App\Models\Tenant;
use App\Models\EmailProvider;
use App\Services\RabbitMQService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InboundEmailController extends Controller
{
    protected $rabbitMQService;

    public function __construct(RabbitMQService $rabbitMQService)
    {
        $this->rabbitMQService = $rabbitMQService;
    }

    /**
     * Get inbound emails for a specific tenant
     */
    public function getInboundEmails(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required|string|exists:tenants,tenant_id',
                'status' => 'nullable|in:new,processed,queued,delivered,failed',
                'is_reply' => 'nullable|boolean',
                'thread_id' => 'nullable|string',
                'from_email' => 'nullable|email',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = InboundEmail::forTenant($request->tenant_id)
                ->with(['tenant', 'provider'])
                ->orderBy('received_at', 'desc');

            // Apply filters
            if ($request->has('status')) {
                $query->withStatus($request->status);
            }

            if ($request->has('is_reply')) {
                $query->where('is_reply', $request->is_reply);
            }

            if ($request->has('thread_id')) {
                $query->inThread($request->thread_id);
            }

            if ($request->has('from_email')) {
                $query->where('from_email', $request->from_email);
            }

            if ($request->has('date_from') && $request->has('date_to')) {
                $query->inDateRange($request->date_from, $request->date_to);
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $inboundEmails = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $inboundEmails->items(),
                'pagination' => [
                    'current_page' => $inboundEmails->currentPage(),
                    'last_page' => $inboundEmails->lastPage(),
                    'per_page' => $inboundEmails->perPage(),
                    'total' => $inboundEmails->total(),
                ],
                'filters' => $request->only(['status', 'is_reply', 'thread_id', 'from_email', 'date_from', 'date_to'])
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching inbound emails: ' . $e->getMessage(), [
                'tenant_id' => $request->tenant_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inbound emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new inbound email (for webhooks or manual entry)
     */
    public function createInboundEmail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required|string|exists:tenants,tenant_id',
                'provider_id' => 'required|string|exists:email_providers,provider_id',
                'message_id' => 'required|string|unique:inbound_emails,message_id',
                'subject' => 'required|string|max:255',
                'from_email' => 'required|email',
                'from_name' => 'nullable|string|max:255',
                'to_emails' => 'required|array|min:1',
                'to_emails.*' => 'email',
                'body_format' => 'required|in:EML,Text,HTML,JSON',
                'body_content' => 'required|string',
                'is_reply' => 'nullable|boolean',
                'in_reply_to' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['received_at'] = now();
            $data['is_reply'] = $request->boolean('is_reply', !empty($request->in_reply_to));
            $data['source'] = 'api';

            $inboundEmail = InboundEmail::create($data);

            // Publish to RabbitMQ queue for CRM consumption
            $this->publishToInboundQueue($inboundEmail);

            Log::info('Inbound email created and queued', [
                'email_id' => $inboundEmail->id,
                'tenant_id' => $inboundEmail->tenant_id,
                'message_id' => $inboundEmail->message_id,
                'is_reply' => $inboundEmail->is_reply
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inbound email created and queued successfully',
                'data' => $inboundEmail->load(['tenant', 'provider'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating inbound email: ' . $e->getMessage(), [
                'tenant_id' => $request->tenant_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create inbound email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inbound email statistics for a tenant
     */
    public function getInboundStats(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required|string|exists:tenants,tenant_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = InboundEmail::forTenant($request->tenant_id);

            $stats = [
                'total_emails' => $query->count(),
                'new_emails' => $query->clone()->withStatus('new')->count(),
                'processed_emails' => $query->clone()->withStatus('processed')->count(),
                'queued_emails' => $query->clone()->withStatus('queued')->count(),
                'delivered_emails' => $query->clone()->withStatus('delivered')->count(),
                'failed_emails' => $query->clone()->withStatus('failed')->count(),
                'reply_emails' => $query->clone()->replies()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching inbound stats: ' . $e->getMessage(), [
                'tenant_id' => $request->tenant_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inbound statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish inbound email to RabbitMQ queue
     */
    private function publishToInboundQueue(InboundEmail $inboundEmail): bool
    {
        try {
            $payload = [
                'id' => $inboundEmail->id,
                'tenant_id' => $inboundEmail->tenant_id,
                'provider_id' => $inboundEmail->provider_id,
                'message_id' => $inboundEmail->message_id,
                'subject' => $inboundEmail->subject,
                'from_email' => $inboundEmail->from_email,
                'from_name' => $inboundEmail->from_name,
                'to_emails' => $inboundEmail->to_emails,
                'body_format' => $inboundEmail->body_format,
                'body_content' => $inboundEmail->body_content,
                'is_reply' => $inboundEmail->is_reply,
                'received_at' => $inboundEmail->received_at->toISOString(),
                'timestamp' => now()->toISOString(),
                'type' => 'inbound_email'
            ];

            $result = $this->rabbitMQService->publishMessage('email.inbound', $payload);

            if ($result) {
                $inboundEmail->markAsQueued('email.inbound');
                Log::info('Inbound email published to queue', [
                    'email_id' => $inboundEmail->id,
                    'queue' => 'email.inbound'
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Error publishing inbound email to queue: ' . $e->getMessage(), [
                'email_id' => $inboundEmail->id,
                'error' => $e->getMessage()
            ]);

            $inboundEmail->markAsFailed('Failed to publish to queue: ' . $e->getMessage());
            return false;
        }
    }
}