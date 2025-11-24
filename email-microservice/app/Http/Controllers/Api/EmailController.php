<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outbox;
use App\Models\EmailProvider;
use App\Models\Tenant;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\InboundEmail;
use App\Services\EmailService;
use App\Services\EmailProcessingService;
use App\Services\MimeParserService;
use App\Services\FileStorageService;
use App\Services\RabbitMQService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EmailController extends Controller
{
    protected ?EmailService $emailService;
    protected EmailProcessingService $emailProcessingService;
    protected MimeParserService $mimeParserService;
    protected FileStorageService $fileStorageService;

    public function __construct(
        ?EmailService $emailService = null,
        ?EmailProcessingService $emailProcessingService = null,
        ?MimeParserService $mimeParserService = null,
        ?FileStorageService $fileStorageService = null
    ) {
        $this->emailService = $emailService ?? app(EmailService::class);
        $this->emailProcessingService = $emailProcessingService ?? app(EmailProcessingService::class);
        $this->mimeParserService = $mimeParserService ?? app(MimeParserService::class);
        $this->fileStorageService = $fileStorageService ?? app(FileStorageService::class);
    }

    /**
     * Send an email.
     */
    public function sendEmail(Request $request): JsonResponse
    {
        try {
            // Check if email service is available
            if (!$this->emailService) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email service not configured. Please check AWS credentials.',
                ], 503);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'template_id' => 'required|string|exists:email_templates,template_id',
                'to' => 'required|array',
                'to.*.email' => 'required|email',
                'to.*.name' => 'nullable|string|max:255',
                'cc' => 'nullable|array',
                'cc.*.email' => 'required|email',
                'cc.*.name' => 'nullable|string|max:255',
                'bcc' => 'nullable|array',
                'bcc.*.email' => 'required|email',
                'bcc.*.name' => 'nullable|string|max:255',
                'subject' => 'nullable|string|max:255',
                'data' => 'nullable|array',
                'source' => 'nullable|string|max:100',
                'headers' => 'nullable|array',
                'attachments' => 'nullable|array',
                'tracking' => 'nullable|array',
                'priority' => 'nullable|string|in:low,normal,high',
                'retry_policy' => 'nullable|array',
                'retry_policy.max_attempts' => 'nullable|integer|min:1|max:5',
                'retry_policy.backoff_seconds' => 'nullable|integer|min:30|max:3600',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Send email
            $result = $this->emailService->sendEmail($request->all());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email sent successfully',
                    'data' => [
                        'message_id' => $result['message_id'],
                        'provider_message_id' => $result['provider_message_id'],
                        'status' => $result['status'],
                    ],
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send email',
                    'error' => $result['error'],
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test MIME parsing with sample email
     */
    public function testMimeParsing(Request $request): JsonResponse
    {
        try {
            $sampleEmail = $request->input('email_content', $this->getSampleEmail());
            
            $parsedEmail = $this->mimeParserService->parseEmail($sampleEmail);
            
            return response()->json([
                'success' => true,
                'message' => 'MIME parsing test completed',
                'data' => [
                    'subject' => $parsedEmail['subject'],
                    'from' => $parsedEmail['from'],
                    'to' => $parsedEmail['to'],
                    'body_format' => !empty($parsedEmail['html_content']) ? 'HTML' : 'Text',
                    'attachments_count' => count($parsedEmail['attachments']),
                    'inline_images_count' => count($parsedEmail['inline_images']),
                    'headers_count' => count($parsedEmail['headers']),
                    'parsed_structure' => $parsedEmail,
                ],
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'MIME parsing test failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test file storage service
     */
    public function testFileStorage(Request $request): JsonResponse
    {
        try {
            $testContent = $request->input('content', 'This is a test file content');
            $filename = $request->input('filename', 'test.txt');
            $mimeType = $request->input('mime_type', 'text/plain');
            $emailId = $request->input('email_id', 'test-' . uniqid());
            
            $result = $this->fileStorageService->storeAttachment(
                $testContent,
                $filename,
                $mimeType,
                $emailId
            );
            
            return response()->json([
                'success' => true,
                'message' => 'File storage test completed',
                'data' => $result,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File storage test failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email processing statistics
     */
    public function getProcessingStats(): JsonResponse
    {
        try {
            $stats = $this->emailProcessingService->getProcessingStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get processing stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(): JsonResponse
    {
        try {
            $stats = $this->fileStorageService->getStorageStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get storage stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get comprehensive email tracking statistics
     */
    public function getTrackingStats(): JsonResponse
    {
        try {
            // Get total emails sent
            $totalEmails = Outbox::where('status', 'sent')->count();
            
            // Get queue processed emails
            $queueEmails = Outbox::where('source', 'queue')->where('status', 'sent')->count();
            
            // Get direct sent emails
            $directEmails = Outbox::whereIn('source', ['api', 'direct'])->where('status', 'sent')->count();
            
            // Calculate success rate
            $totalAttempts = Outbox::count();
            $successRate = $totalAttempts > 0 ? round(($totalEmails / $totalAttempts) * 100, 2) : 0;
            
            // Calculate average processing time
            $avgProcessingTime = Outbox::whereNotNull('processing_time_ms')
                ->where('processing_time_ms', '>', 0)
                ->avg('processing_time_ms');
            $avgProcessingTime = round($avgProcessingTime ?? 0, 2);
            
            // Get active providers count
            $activeProviders = EmailProvider::where('is_active', true)->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_emails' => $totalEmails,
                    'queue_emails' => $queueEmails,
                    'direct_emails' => $directEmails,
                    'success_rate' => $successRate,
                    'avg_processing_time' => $avgProcessingTime,
                    'active_providers' => $activeProviders,
                    'total_attempts' => $totalAttempts,
                    'failed_emails' => $totalAttempts - $totalEmails
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting tracking stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get tracking statistics'
            ], 500);
        }
    }

    /**
     * Get recent email activity
     */
    public function getRecentEmails(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            
            $recentEmails = Outbox::with('provider')
                ->orderBy('sent_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($email) {
                    return [
                        'id' => $email->id,
                        'sent_at' => $email->sent_at,
                        'from' => $email->from,
                        'to' => $email->to,
                        'subject' => $email->subject,
                        'source' => $email->source,
                        'status' => $email->status,
                        'processing_time_ms' => $email->processing_time_ms,
                        'provider_name' => $email->provider->provider_name ?? 'Unknown',
                        'queue_name' => $email->queue_name,
                        'processing_method' => $email->processing_method,
                        'body_format' => $email->body_format,
                        'error_message' => $email->error_message
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $recentEmails
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting recent emails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recent emails'
            ], 500);
        }
    }

    /**
     * Get performance metrics by source
     */
    public function getPerformanceMetrics(): JsonResponse
    {
        try {
            // Queue processing metrics
            $queueStats = Outbox::where('source', 'queue')
                ->selectRaw('
                    COUNT(*) as total_emails,
                    SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as successful_emails,
                    AVG(processing_time_ms) as avg_processing_time
                ')
                ->first();
            
            $queueSuccessRate = $queueStats->total_emails > 0 
                ? round(($queueStats->successful_emails / $queueStats->total_emails) * 100, 2) 
                : 0;
            
            // Direct sending metrics
            $directStats = Outbox::whereIn('source', ['api', 'direct'])
                ->selectRaw('
                    COUNT(*) as total_emails,
                    SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as successful_emails,
                    AVG(processing_time_ms) as avg_processing_time
                ')
                ->first();
            
            $directSuccessRate = $directStats->total_emails > 0 
                ? round(($directStats->successful_emails / $directStats->total_emails) * 100, 2) 
                : 0;
            
            // Overall metrics
            $overallStats = Outbox::selectRaw('
                COUNT(*) as total_emails,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as successful_emails,
                AVG(processing_time_ms) as avg_processing_time
            ')->first();
            
            $overallSuccessRate = $overallStats->total_emails > 0 
                ? round(($overallStats->successful_emails / $overallStats->total_emails) * 100, 2) 
                : 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'queue' => [
                        'total_emails' => $queueStats->total_emails ?? 0,
                        'successful_emails' => $queueStats->successful_emails ?? 0,
                        'success_rate' => $queueSuccessRate,
                        'avg_processing_time' => round($queueStats->avg_processing_time ?? 0, 2)
                    ],
                    'direct' => [
                        'total_emails' => $directStats->total_emails ?? 0,
                        'successful_emails' => $directStats->successful_emails ?? 0,
                        'success_rate' => $directSuccessRate,
                        'avg_processing_time' => round($directStats->avg_processing_time ?? 0, 2)
                    ],
                    'overall' => [
                        'total_emails' => $overallStats->total_emails ?? 0,
                        'successful_emails' => $overallStats->successful_emails ?? 0,
                        'success_rate' => $overallSuccessRate,
                        'avg_processing_time' => round($overallStats->avg_processing_time ?? 0, 2)
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting performance metrics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get performance metrics'
            ], 500);
        }
    }

    /**
     * Get email analytics by date range
     */
    public function getEmailAnalytics(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));
            
            $analytics = Outbox::whereBetween('sent_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->selectRaw('
                    DATE(sent_at) as date,
                    source,
                    COUNT(*) as total_emails,
                    SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as successful_emails,
                    SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_emails,
                    AVG(processing_time_ms) as avg_processing_time
                ')
                ->groupBy('date', 'source')
                ->orderBy('date')
                ->get()
                ->groupBy('date');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'date_range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'analytics' => $analytics
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting email analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get email analytics'
            ], 500);
        }
    }

    /**
     * Get sample email for testing
     */
    protected function getSampleEmail(): string
    {
        return "From: test@example.com
To: recipient@example.com
Subject: Test Email with Attachments
Date: " . date('r') . "
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary=\"boundary123\"

--boundary123
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit

This is a test email with attachments.

--boundary123
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 7bit

<html><body><h1>Test Email</h1><p>This is a test email with attachments.</p></body></html>

--boundary123
Content-Type: application/pdf; name=\"test.pdf\"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename=\"test.pdf\"

JVBERi0xLjQKJcOkw7zDtsO8DQoxIDAgb2JqDQo8PA0KL1R5cGUgL0NhdGFsb2cNCi9QYWdlcyAy
IDAgUg0KPj4NCmVuZG9iag0KMiAwIG9iag0KPDwNCi9UeXBlIC9QYWdlcw0KL0NvdW50IDENCi9L
aWRzIFsgMyAwIFIgXQ0KPj4NCmVuZG9iag0KMyAwIG9iag0KPDwNCi9UeXBlIC9QYWdlDQovUGFy
ZW50IDIgMCBSDQovUmVzb3VyY2VzIDw8DQovRm9udCA8PA0KL0YxIDQgMCBSDQo+Pg0KPj4NCi9D
b250ZW50cyA1IDAgUg0KPj4NCmVuZG9iag0KNCAwIG9iag0KPDwNCi9UeXBlIC9Gb250DQovU3Vi
dHlwZSAvVHlwZTENCi9QYXNjYWQgL0hlbHZldGljYQ0KL0Jhc2VGb250IC9IZWx2ZXRpY2ENCi9F
bmNvZGluZyAvV2luQW5zaUVuY29kaW5nDQo+Pg0KZW5kb2JqDQo1IDAgb2JqDQo8PA0KL0xlbmd0
aCAyNA0KPj4NCnN0cmVhbQ0KQlQNCi9GMSAxMiBUZg0KVGhlIGVuZA0KZW5kc3RyZWFtDQplbmRv
YmoNCnhyZWYNCjAgNg0KMDAwMDAwMDAwMCA2NTUzNSBmDQowMDAwMDAwMDEwIDAwMDAwIG4NCjAw
MDAwMDAwNzkgMDAwMDAgbg0KMDAwMDAwMDE3MyAwMDAwMCBuDQowMDAwMDAwMzAxIDAwMDAwIG4N
CjAwMDAwMDAzODAgMDAwMDAgbg0KdHJhaWxlcg0KPDwNCi9TaXplIDYNCi9Sb290IDEgMCBSDQo+
Pg0Kc3RhcnR4cmVmDQo0OTINCiUlRU9GDQo=
--boundary123--
";
    }

    /**
     * Get all email providers
     */
    public function getProviders(Request $request): JsonResponse
    {
        try {
            $query = EmailProvider::with('tenant');

            // Apply filters
            if ($request->has('tenant_id')) {
                $query->where('tenant_id', $request->tenant_id);
            }

            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            $providers = $query->get();

            return response()->json([
                'success' => true,
                'data' => $providers,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get providers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new email provider
     */
    public function createProvider(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required|string|exists:tenants,tenant_id',
                'provider_name' => 'required|string|max:255',
                'config_json' => 'required|array',
                'config_json.host' => 'required|string',
                'config_json.port' => 'required|integer|min:1|max:65535',
                'config_json.username' => 'required|string',
                'config_json.password' => 'required|string',
                'config_json.from_address' => 'required|email',
                'config_json.from_name' => 'nullable|string|max:255',
                'bounce_email' => 'nullable|email',
                'header_overrides' => 'nullable|array',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $provider = EmailProvider::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Provider created successfully',
                'data' => $provider,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create provider',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific email provider
     */
    public function getProvider(string $providerId): JsonResponse
    {
        try {
            $provider = EmailProvider::with('tenant')->find($providerId);

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $provider,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get provider',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an email provider
     */
    public function updateProvider(Request $request, string $providerId): JsonResponse
    {
        try {
            $provider = EmailProvider::find($providerId);

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'provider_name' => 'sometimes|string|max:255',
                'config_json' => 'sometimes|array',
                'config_json.host' => 'sometimes|string',
                'config_json.port' => 'sometimes|integer|min:1|max:65535',
                'config_json.username' => 'sometimes|string',
                'config_json.password' => 'sometimes|string',
                'config_json.from_address' => 'sometimes|email',
                'config_json.from_name' => 'sometimes|string|max:255',
                'bounce_email' => 'sometimes|email',
                'header_overrides' => 'sometimes|array',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $provider->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Provider updated successfully',
                'data' => $provider,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update provider',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an email provider
     */
    public function deleteProvider(string $providerId): JsonResponse
    {
        try {
            $provider = EmailProvider::find($providerId);

            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Provider not found',
                ], 404);
            }

            $provider->delete();

            return response()->json([
                'success' => true,
                'message' => 'Provider deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete provider',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all tenants
     */
    public function getTenants(): JsonResponse
    {
        try {
            $tenants = Tenant::all();

            return response()->json([
                'success' => true,
                'data' => $tenants,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get tenants',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email status by message ID.
     */
    public function getEmailStatus(string $messageId): JsonResponse
    {
        try {
            $emailLog = EmailLog::where('message_id', $messageId)->first();

            if (!$emailLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'message_id' => $emailLog->message_id,
                    'template_id' => $emailLog->template_id,
                    'to_email' => $emailLog->to_email,
                    'to_name' => $emailLog->to_name,
                    'subject' => $emailLog->subject,
                    'status' => $emailLog->status,
                    'provider_message_id' => $emailLog->provider_message_id,
                    'sent_at' => $emailLog->sent_at,
                    'delivered_at' => $emailLog->delivered_at,
                    'opened_at' => $emailLog->opened_at,
                    'clicked_at' => $emailLog->clicked_at,
                    'error_message' => $emailLog->error_message,
                    'source' => $emailLog->source,
                    'created_at' => $emailLog->created_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email templates.
     */
    public function getTemplates(Request $request): JsonResponse
    {
        try {
            $query = EmailTemplate::query();

            // Apply filters
            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            if ($request->has('language')) {
                $query->byLanguage($request->language);
            }

            if ($request->has('active')) {
                $query->active();
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $templates = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $templates->items(),
                'pagination' => [
                    'current_page' => $templates->currentPage(),
                    'last_page' => $templates->lastPage(),
                    'per_page' => $templates->perPage(),
                    'total' => $templates->total(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email template by ID.
     */
    public function getTemplate(string $templateId): JsonResponse
    {
        try {
            $template = EmailTemplate::where('template_id', $templateId)->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $template,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email logs from outbox table.
     */
    public function getEmailLogs(Request $request): JsonResponse
    {
        try {
            $query = Outbox::with(['tenant', 'provider']);

            // Apply filters
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            if ($request->has('provider')) {
                $query->byProvider($request->provider);
            }

            if ($request->has('tenant_id')) {
                $query->byTenant($request->tenant_id);
            }

            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->has('to_email')) {
                $query->whereJsonContains('to', $request->to_email);
            }

            if ($request->has('date_from') && $request->has('date_to')) {
                $query->sentBetween($request->date_from, $request->date_to);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            
            // For table view, return all logs if no per_page specified
            if ($request->has('per_page') && $request->per_page > 0) {
                $emailLogs = $query->orderBy('sent_at', 'desc')->orderBy('created_at', 'desc')->paginate($perPage);
                
                return response()->json([
                    'success' => true,
                    'data' => $emailLogs->items(),
                    'pagination' => [
                        'current_page' => $emailLogs->currentPage(),
                        'last_page' => $emailLogs->lastPage(),
                        'per_page' => $emailLogs->perPage(),
                        'total' => $emailLogs->total(),
                    ],
                ], 200);
            } else {
                // Return all logs for table view
                $emailLogs = $query->orderBy('sent_at', 'desc')->orderBy('created_at', 'desc')->get();
                
                return response()->json([
                    'success' => true,
                    'data' => $emailLogs,
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email statistics from outbox table.
     */
    public function getEmailStats(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_emails' => Outbox::count(),
                'sent_emails' => Outbox::byStatus('sent')->count(),
                'delivered_emails' => Outbox::whereNotNull('delivered_at')->count(),
                'failed_emails' => Outbox::byStatus('failed')->count(),
                'pending_emails' => Outbox::byStatus('pending')->count(),
                'bounced_emails' => Outbox::byStatus('bounced')->count(),
                'opened_emails' => 0, // Not tracked in outbox table
                'clicked_emails' => 0, // Not tracked in outbox table
            ];

            // Add provider stats
            $providerStats = Outbox::selectRaw('provider_id, COUNT(*) as count')
                ->groupBy('provider_id')
                ->pluck('count', 'provider_id')
                ->toArray();

            $stats['provider_stats'] = $providerStats;

            // Add tenant stats
            $tenantStats = Outbox::selectRaw('tenant_id, COUNT(*) as count')
                ->groupBy('tenant_id')
                ->pluck('count', 'tenant_id')
                ->toArray();

            $stats['tenant_stats'] = $tenantStats;

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Send a test email using a specific provider
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'provider_id' => 'required|string|exists:email_providers,provider_id',
                'to_email' => 'required|email',
                'from_email' => 'nullable|email', // Add validation for optional from_email
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $provider = EmailProvider::find($request->provider_id);
            if (!$provider) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email provider not found'
                ], 404);
            }

            if (!$provider->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email provider is not active'
                ], 400);
            }

            $config = $provider->config_json ?? [];
            
            // Extract SMTP settings with fallback to non-prefixed keys
            $smtpHost = $config['smtp_host'] ?? $config['host'] ?? 'localhost';
            $smtpPort = $config['smtp_port'] ?? $config['port'] ?? 587;
            $smtpUsername = $config['smtp_username'] ?? $config['username'] ?? $config['email'] ?? '';
            $smtpPassword = $config['smtp_password'] ?? $config['password'] ?? '';
            $smtpEncryption = $config['smtp_encryption'] ?? $config['encryption'] ?? 'tls';
            $fromEmail = $config['from_address'] ?? $config['email'] ?? $smtpUsername;
            $fromName = $config['from_name'] ?? 'Email Service';
            
            // Use Laravel's built-in mail configuration more carefully
            $originalMailConfig = config('mail');
            
            // Log the original configuration for debugging
            Log::info('Original mail config:', [
                'default' => config('mail.default'),
                'smtp_scheme' => config('mail.mailers.smtp.scheme'),
                'smtp_transport' => config('mail.mailers.smtp.transport'),
            ]);
            
            // Log provider config for debugging
            Log::info('Provider config extracted:', [
                'smtp_host' => $smtpHost,
                'smtp_port' => $smtpPort,
                'smtp_username' => $smtpUsername,
                'smtp_encryption' => $smtpEncryption,
                'from_email' => $fromEmail,
            ]);
            
            // Temporarily override mail configuration with explicit SMTP settings
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtpHost,
                'mail.mailers.smtp.port' => $smtpPort,
                'mail.mailers.smtp.username' => $smtpUsername,
                'mail.mailers.smtp.password' => $smtpPassword,
                'mail.mailers.smtp.encryption' => $smtpEncryption,
                'mail.mailers.smtp.timeout' => 30,
                'mail.mailers.smtp.local_domain' => 'localhost',
                'mail.from.address' => $fromEmail,
                'mail.from.name' => $fromName,
                // Explicitly remove any problematic fields
                'mail.mailers.smtp.scheme' => null,
                'mail.mailers.smtp.url' => null,
            ]);
            
            // Log the new configuration for debugging
            Log::info('New mail config:', [
                'default' => config('mail.default'),
                'smtp_scheme' => config('mail.mailers.smtp.scheme'),
                'smtp_transport' => config('mail.mailers.smtp.transport'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
            ]);
            
            try {
                // For Gmail and most SMTP providers, "from" address must match authenticated username
                // Use the SMTP username as the from address to avoid authentication issues
                $actualFromEmail = $request->filled('from_email') ? $request->from_email : $smtpUsername;
                
                // Warn if from email doesn't match SMTP username (Gmail will reject it)
                if ($actualFromEmail !== $smtpUsername && $smtpHost === 'smtp.gmail.com') {
                    Log::warning('From email does not match Gmail account', [
                        'from_email' => $actualFromEmail,
                        'smtp_username' => $smtpUsername,
                        'note' => 'Gmail requires from address to match authenticated account'
                    ]);
                }
                
                // Send the email
                Mail::raw($request->message, function ($message) use ($request, $actualFromEmail, $fromName) {
                    $message->to($request->to_email)
                            ->subject($request->subject);
                    
                    // Always use the SMTP username as from address for Gmail compatibility
                    $message->from($actualFromEmail, $fromName);
                });
                
                Log::info('Test email sent successfully', [
                    'to' => $request->to_email,
                    'from' => $request->filled('from_email') ? $request->from_email : $fromEmail,
                    'subject' => $request->subject,
                    'provider' => $provider->provider_name
                ]);
            } catch (\Swift_TransportException $e) {
                // SMTP connection/authentication errors
                Log::error('SMTP Transport Error sending test email', [
                    'error' => $e->getMessage(),
                    'to' => $request->to_email,
                    'provider' => $provider->provider_name,
                    'smtp_host' => $smtpHost,
                    'smtp_port' => $smtpPort,
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Restore original mail configuration
                config(['mail' => $originalMailConfig]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test email: SMTP connection error',
                    'error' => $e->getMessage(),
                    'debug' => [
                        'provider_config' => $config,
                        'error_type' => get_class($e),
                        'error_line' => $e->getLine(),
                        'error_file' => $e->getFile(),
                        'smtp_host' => $smtpHost,
                        'smtp_port' => $smtpPort,
                        'smtp_username' => $smtpUsername,
                        'smtp_encryption' => $smtpEncryption,
                        'hint' => 'Check SMTP credentials, firewall settings, and Gmail app password configuration'
                    ]
                ], 500);
            } catch (\Exception $e) {
                // Other email sending errors
                Log::error('Error sending test email', [
                    'error' => $e->getMessage(),
                    'to' => $request->to_email,
                    'provider' => $provider->provider_name,
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Restore original mail configuration
                config(['mail' => $originalMailConfig]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test email: ' . $e->getMessage(),
                    'error' => $e->getMessage(),
                    'debug' => [
                        'provider_config' => $config,
                        'error_type' => get_class($e),
                        'error_line' => $e->getLine(),
                        'error_file' => $e->getFile()
                    ]
                ], 500);
            } finally {
                // Restore original mail configuration
                config(['mail' => $originalMailConfig]);
            }

            // Note: $actualFromEmail is already determined above (uses SMTP username for Gmail compatibility)
            
            // Log the email to EmailLog table
            EmailLog::create([
                'message_id' => \Illuminate\Support\Str::uuid()->toString(),
                'template_id' => 'test-email-' . uniqid(),
                'to_email' => $request->to_email,
                'to_name' => null, // No name provided in test
                'subject' => $request->subject,
                'text_content' => $request->message, // Use text_content for plain text
                'html_content' => null, // No HTML content
                'status' => 'sent',
                'sent_at' => now(),
                'provider' => 'smtp',
                'source' => 'test-email',
                'data' => [
                    'from_email' => $actualFromEmail,
                    'provider_from_email' => $fromEmail,
                    'custom_from_email' => $request->from_email,
                    'provider_name' => $provider->provider_name,
                ],
            ]);
            
            // Also log to outbox table for consistency with enhanced tracking
            Outbox::create([
                'tenant_id' => $provider->tenant_id,
                'provider_id' => $provider->provider_id,
                'user_id' => 'system', // System user for test emails
                'message_id' => \Illuminate\Support\Str::uuid()->toString(),
                'subject' => $request->subject,
                'from' => $actualFromEmail,
                'to' => [$request->to_email],
                'cc' => null,
                'bcc' => null,
                'body_format' => 'Text',
                'body_content' => $request->message,
                'attachments' => null,
                'status' => 'sent',
                'error_message' => null,
                'provider_response' => [
                    'provider_name' => $provider->provider_name,
                    'smtp_host' => $smtpHost,
                    'smtp_port' => $smtpPort,
                    'encryption' => $smtpEncryption,
                    'sent_at' => now()->toISOString(),
                    'test_email' => true
                ],
                'sent_at' => now(),
                'delivered_at' => now(),
                // Enhanced tracking fields
                'source' => 'api',
                'queue_name' => null,
                'processing_method' => 'direct',
                'queue_processed' => false,
                'queued_at' => null,
                'processing_started_at' => now(),
                'processing_time_ms' => 0, // Direct sending, no queue processing
                'retry_count' => 0,
                'metadata' => [
                    'test_email' => true,
                    'api_endpoint' => '/api/email/send-test-email',
                    'request_method' => 'POST',
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip()
                ],
                'template_id' => 'test-email-' . uniqid(),
                'campaign_id' => null,
                'headers' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully',
                'data' => [
                    'provider_name' => $provider->provider_name,
                    'from_email' => $actualFromEmail,
                    'to_email' => $request->to_email,
                    'subject' => $request->subject,
                    'sent_at' => now()->toISOString(),
                    'used_custom_from' => $request->filled('from_email'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending test email: ' . $e->getMessage(), [
                'provider_id' => $request->provider_id ?? 'unknown',
                'to_email' => $request->to_email ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Add more specific error information
            $errorMessage = 'Failed to send test email: ' . $e->getMessage();
            
            // Check for common SMTP errors
            if (str_contains($e->getMessage(), 'scheme')) {
                $errorMessage .= ' (SMTP configuration issue - check provider settings)';
            } elseif (str_contains($e->getMessage(), 'authentication')) {
                $errorMessage .= ' (Authentication failed - check username/password)';
            } elseif (str_contains($e->getMessage(), 'connection')) {
                $errorMessage .= ' (Connection failed - check host/port/encryption)';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => [
                    'provider_config' => $provider->config_json ?? 'No config',
                    'error_type' => get_class($e),
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }
    
    /**
     * Send email via RabbitMQ queue (for external services)
     * Now uses template_id and template_data instead of body_content
     */
    public function sendEmailViaRabbitMQ(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required|string|exists:tenants,tenant_id',
                'provider_id' => 'required|string|exists:email_providers,provider_id',
                'user_id' => 'nullable|string',
                'from' => 'required|email',
                'to' => 'required|array|min:1',
                'to.*' => 'email',
                'cc' => 'nullable|array',
                'cc.*' => 'email',
                'bcc' => 'nullable|array',
                'bcc.*' => 'email',
                'subject' => 'nullable|string|max:255', // Optional if template has subject
                'template_id' => 'required|string|exists:email_templates,template_id',
                'template_data' => 'required|array', // Data to populate template variables
                'attachments' => 'nullable|array',
                'attachments.*.url' => 'required_with:attachments|url', // File URLs instead of content
                'attachments.*.filename' => 'required_with:attachments|string',
                'attachments.*.mime_type' => 'required_with:attachments|string',
                'priority' => 'nullable|in:low,normal,high',
                'scheduled_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the RabbitMQ service
            $rabbitMQService = app(RabbitMQService::class);
            
            // Prepare the payload
            $payload = $request->all();
            $payload['message_id'] = \Illuminate\Support\Str::uuid()->toString();
            $payload['timestamp'] = now()->toISOString();
            
            // Send to RabbitMQ queue
            $result = $rabbitMQService->publishToQueue('email.send', $payload);
            
            if ($result) {
                // Get template to determine body format and subject if not provided
                $template = EmailTemplate::where('template_id', $payload['template_id'])->first();
                
                // Create outbox record immediately (body will be built from template during processing)
                $outbox = Outbox::create([
                    'tenant_id' => $payload['tenant_id'],
                    'provider_id' => $payload['provider_id'],
                    'user_id' => $payload['user_id'] ?? 'external-service',
                    'message_id' => $payload['message_id'],
                    'subject' => $payload['subject'] ?? $template->subject ?? 'No Subject',
                    'from' => $payload['from'],
                    'to' => $payload['to'],
                    'cc' => $payload['cc'] ?? [],
                    'bcc' => $payload['bcc'] ?? [],
                    'body_format' => $template && $template->hasHtmlContent() ? 'HTML' : 'TEXT',
                    'body_content' => null, // Will be built from template during processing
                    'template_id' => $payload['template_id'],
                    'attachments' => $payload['attachments'] ?? [],
                    'status' => 'pending',
                    'queued_at' => now(),
                    'source' => 'rabbitmq',
                    'queue_name' => 'email.send',
                    'metadata' => [
                        'template_data' => $payload['template_data'],
                        'queued_at' => now()->toISOString(),
                        'queue_name' => 'email.send',
                        'external_service' => true,
                    ],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Email queued successfully for processing',
                    'data' => [
                        'message_id' => $payload['message_id'],
                        'outbox_id' => $outbox->id,
                        'status' => 'queued',
                        'queued_at' => now()->toISOString(),
                        'estimated_processing_time' => '2-5 minutes',
                    ]
                ], 202); // 202 Accepted
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to queue email',
                    'error' => 'RabbitMQ connection error'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error queuing email via RabbitMQ: ' . $e->getMessage(), [
                'payload' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get RabbitMQ queue status
     */
    public function getRabbitMQQueueStatus(Request $request): JsonResponse
    {
        try {
            $rabbitMQService = app(RabbitMQService::class);
            $status = $rabbitMQService->getQueueStatus();
            
            return response()->json([
                'success' => true,
                'data' => $status
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get queue status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get RabbitMQ queue statistics
     */
    public function getRabbitMQQueueStats(Request $request): JsonResponse
    {
        try {
            $rabbitMQService = app(RabbitMQService::class);
            $stats = $rabbitMQService->getQueueStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get queue statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Manually process RabbitMQ queue
     */
    public function processRabbitMQQueue(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'queue_name' => 'nullable|string|in:email.send,email.sync.user',
                'max_messages' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $queueName = $request->get('queue_name', 'email.send');
            $maxMessages = $request->get('max_messages', 10);
            
            Log::info("Processing queue request", [
                'queue_name' => $queueName,
                'max_messages' => $maxMessages
            ]);
            
            $rabbitMQService = app(RabbitMQService::class);
            $result = $rabbitMQService->processQueue($queueName, $maxMessages);
            
            return response()->json([
                'success' => true,
                'message' => 'Queue processing completed',
                'data' => $result
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error in processRabbitMQQueue: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process queue',
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Get bounced emails
     */
    public function getBouncedEmails(Request $request): JsonResponse
    {
        try {
            $query = Outbox::where('status', 'bounced')
                ->orWhere('status', 'failed')
                ->with(['provider', 'tenant'])
                ->orderBy('sent_at', 'desc');

            // Add pagination
            $perPage = $request->get('per_page', 20);
            $bouncedEmails = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $bouncedEmails->items(),
                'pagination' => [
                    'current_page' => $bouncedEmails->currentPage(),
                    'last_page' => $bouncedEmails->lastPage(),
                    'per_page' => $bouncedEmails->perPage(),
                    'total' => $bouncedEmails->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching bounced emails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bounced emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update bounced email address
     */
    public function updateBouncedEmail(Request $request, $id): JsonResponse
    {
        try {
            Log::info('Starting email update process', [
                'outbox_id' => $id,
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'new_email' => 'required|email|max:255',
                'reason' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for email update', [
                    'outbox_id' => $id,
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $outbox = Outbox::findOrFail($id);
            Log::info('Found outbox record', [
                'outbox_id' => $id,
                'current_to' => $outbox->to,
                'current_status' => $outbox->status
            ]);
            
            // Store the old email address before updating
            $oldEmail = is_array($outbox->to) ? $outbox->to[0] : $outbox->to;
            Log::info('Stored old email', ['old_email' => $oldEmail]);
            
            // Update the email address
            if (is_array($outbox->to)) {
                $outbox->to = [$request->new_email];
            } else {
                $outbox->to = $request->new_email;
            }
            Log::info('Updated email address', ['new_to' => $outbox->to]);
            
            // Add correction history
            $corrections = $outbox->corrections ?? [];
            $corrections[] = [
                'timestamp' => now()->toISOString(),
                'old_email' => $oldEmail,
                'new_email' => $request->new_email,
                'reason' => $request->reason ?? 'Manual correction by admin',
                'corrected_by' => 'admin'
            ];
            $outbox->corrections = $corrections;
            Log::info('Added correction history', ['corrections_count' => count($corrections)]);
            
            // Reset status for re-sending
            $outbox->status = 'pending';
            $outbox->error_message = null;
            $outbox->bounce_reason = null;
            $outbox->retry_count = 0;
            
            Log::info('About to save outbox record', [
                'outbox_id' => $id,
                'final_data' => [
                    'to' => $outbox->to,
                    'status' => $outbox->status,
                    'corrections' => $outbox->corrections
                ]
            ]);
            
            $outbox->save();
            Log::info('Outbox record saved successfully');

            Log::info("Bounced email corrected", [
                'outbox_id' => $id,
                'old_email' => $oldEmail,
                'new_email' => $request->new_email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email address updated successfully',
                'data' => [
                    'id' => $outbox->id,
                    'new_email' => $request->new_email,
                    'status' => 'pending'
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating bounced email: ' . $e->getMessage(), [
                'outbox_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update email address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Re-queue bounced email for sending
     */
    public function requeueBouncedEmail(Request $request, $id): JsonResponse
    {
        try {
            $outbox = Outbox::findOrFail($id);
            
            // Check if email is in correctable state
            if (!in_array($outbox->status, ['bounced', 'failed', 'pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is not in a correctable state'
                ], 400);
            }

            // Prepare email payload for RabbitMQ
            $emailPayload = [
                'tenant_id' => $outbox->tenant_id,
                'provider_id' => $outbox->provider_id,
                'from' => $outbox->from,
                'to' => is_array($outbox->to) ? $outbox->to : [$outbox->to],
                'cc' => $outbox->cc ?? [],
                'bcc' => $outbox->bcc ?? [],
                'subject' => $outbox->subject,
                'body_format' => $outbox->body_format ?? 'HTML',
                'body_content' => $outbox->body_content,
                'attachments' => $outbox->attachments ?? [],
                'header_overrides' => $outbox->header_overrides ?? [],
                'source' => 'requeue',
                'original_outbox_id' => $outbox->id,
                'retry_count' => ($outbox->retry_count ?? 0) + 1
            ];

            // Send to RabbitMQ queue
            $rabbitMQService = app(RabbitMQService::class);
            $result = $rabbitMQService->publishMessage('email.send', $emailPayload);

            if ($result) {
                // Update outbox status
                $outbox->status = 'queued';
                $outbox->queued_at = now();
                $outbox->retry_count = ($outbox->retry_count ?? 0) + 1;
                $outbox->save();

                Log::info("Bounced email re-queued", [
                    'outbox_id' => $id,
                    'retry_count' => $outbox->retry_count
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Email re-queued successfully',
                    'data' => [
                        'id' => $outbox->id,
                        'status' => 'queued',
                        'retry_count' => $outbox->retry_count
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to re-queue email'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error re-queuing bounced email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to re-queue email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get replied emails for a tenant
     */
    public function getRepliedEmails(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required|string|exists:tenants,tenant_id',
                'status' => 'nullable|in:new,processed,queued,delivered,failed',
                'in_reply_to' => 'nullable|string', // Specific message ID to get replies for
                'thread_id' => 'nullable|string', // Get all emails in a thread
                'from_email' => 'nullable|email',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $perPage = $request->get('per_page', 20);
            $tenantId = $request->tenant_id;

            $query = InboundEmail::where('tenant_id', $tenantId)
                ->where('is_reply', true)
                ->with(['tenant', 'provider', 'repliedToOutbound']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by specific message ID
            if ($request->has('in_reply_to')) {
                $query->where('in_reply_to', $request->in_reply_to);
            }

            // Filter by thread ID
            if ($request->has('thread_id')) {
                $query->where('thread_id', $request->thread_id);
            }

            // Filter by sender
            if ($request->has('from_email')) {
                $query->where('from_email', $request->from_email);
            }

            // Filter by date range
            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('received_at', [$request->date_from, $request->date_to]);
            } elseif ($request->has('date_from')) {
                $query->where('received_at', '>=', $request->date_from);
            } elseif ($request->has('date_to')) {
                $query->where('received_at', '<=', $request->date_to);
            }

            $repliedEmails = $query->orderBy('received_at', 'desc')->paginate($perPage);

            // Get thread information for each reply
            $repliedEmails->getCollection()->transform(function ($email) {
                // Get all emails in the thread
                $threadEmails = collect();
                
                if ($email->thread_id) {
                    $threadEmails = InboundEmail::where('thread_id', $email->thread_id)
                        ->orderBy('received_at')
                        ->get(['id', 'subject', 'from_email', 'from_name', 'body_content', 'received_at', 'is_reply', 'status']);
                } elseif ($email->in_reply_to) {
                    $threadEmails = InboundEmail::where('in_reply_to', $email->in_reply_to)
                        ->orWhere('message_id', $email->in_reply_to)
                        ->orderBy('received_at')
                        ->get(['id', 'subject', 'from_email', 'from_name', 'body_content', 'received_at', 'is_reply', 'status']);
                }
                
                $email->thread_emails = $threadEmails;
                return $email;
            });

            return response()->json([
                'success' => true,
                'data' => $repliedEmails->items(),
                'pagination' => [
                    'current_page' => $repliedEmails->currentPage(),
                    'last_page' => $repliedEmails->lastPage(),
                    'per_page' => $repliedEmails->perPage(),
                    'total' => $repliedEmails->total(),
                    'from' => $repliedEmails->firstItem(),
                    'to' => $repliedEmails->lastItem(),
                ],
                'filters' => $request->only(['in_reply_to', 'thread_id', 'from_email', 'date_from', 'date_to'])
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching replied emails: ' . $e->getMessage(), [
                'tenant_id' => $request->tenant_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch replied emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get emails (sent/received/both) with pagination and filters
     * Required: tenant_id
     * Type: sent|received|both
     */
    public function getEmails(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required|string|exists:tenants,tenant_id',
                'type' => 'required|in:sent,received,both',
                // Sent email filters
                'status' => 'nullable|in:pending,sent,failed,bounced,delivered,new,processed,queued',
                'search' => 'nullable|string|max:255',
                'from_email' => 'nullable|email',
                'to_email' => 'nullable|email',
                'subject' => 'nullable|string|max:255',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'provider_id' => 'nullable|string|exists:email_providers,provider_id',
                'user_id' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tenantId = $request->tenant_id;
            $type = $request->type;
            $perPage = $request->get('per_page', 20);
            $response = [
                'success' => true,
                'data' => [],
                'filters' => $request->only(['status', 'search', 'from_email', 'to_email', 'subject', 'date_from', 'date_to', 'provider_id', 'user_id'])
            ];

            // Get sent emails (from Outbox)
            if ($type === 'sent' || $type === 'both') {
                $sentQuery = Outbox::where('tenant_id', $tenantId)
                    ->with(['tenant', 'provider', 'user']);

                // Apply filters
                if ($request->has('status') && in_array($request->status, ['pending', 'sent', 'failed', 'bounced', 'delivered'])) {
                    $sentQuery->where('status', $request->status);
                }

                if ($request->has('search')) {
                    $search = $request->search;
                    $sentQuery->where(function($q) use ($search) {
                        $q->where('subject', 'like', "%{$search}%")
                          ->orWhere('from', 'like', "%{$search}%")
                          ->orWhereJsonContains('to', $search);
                    });
                }

                if ($request->has('from_email')) {
                    $sentQuery->where('from', $request->from_email);
                }

                if ($request->has('to_email')) {
                    $sentQuery->whereJsonContains('to', $request->to_email);
                }

                if ($request->has('subject')) {
                    $sentQuery->where('subject', 'like', "%{$request->subject}%");
                }

                if ($request->has('date_from') && $request->has('date_to')) {
                    $sentQuery->whereBetween('sent_at', [$request->date_from, $request->date_to])
                               ->orWhereBetween('created_at', [$request->date_from, $request->date_to]);
                } elseif ($request->has('date_from')) {
                    $sentQuery->where('sent_at', '>=', $request->date_from)
                              ->orWhere('created_at', '>=', $request->date_from);
                } elseif ($request->has('date_to')) {
                    $sentQuery->where('sent_at', '<=', $request->date_to)
                              ->orWhere('created_at', '<=', $request->date_to);
                }

                if ($request->has('provider_id')) {
                    $sentQuery->where('provider_id', $request->provider_id);
                }

                if ($request->has('user_id')) {
                    $sentQuery->where('user_id', $request->user_id);
                }

                $sentEmails = $sentQuery->orderBy('sent_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);

                $response['data']['sent'] = [
                    'data' => $sentEmails->items(),
                    'pagination' => [
                        'current_page' => $sentEmails->currentPage(),
                        'last_page' => $sentEmails->lastPage(),
                        'per_page' => $sentEmails->perPage(),
                        'total' => $sentEmails->total(),
                        'from' => $sentEmails->firstItem(),
                        'to' => $sentEmails->lastItem(),
                    ]
                ];
            }

            // Get received emails (from InboundEmail)
            if ($type === 'received' || $type === 'both') {
                $receivedQuery = InboundEmail::where('tenant_id', $tenantId)
                    ->with(['tenant', 'provider', 'repliedToOutbound']);

                // Apply filters
                if ($request->has('status') && in_array($request->status, ['new', 'processed', 'queued', 'delivered', 'failed'])) {
                    $receivedQuery->where('status', $request->status);
                }

                if ($request->has('search')) {
                    $search = $request->search;
                    $receivedQuery->where(function($q) use ($search) {
                        $q->where('subject', 'like', "%{$search}%")
                          ->orWhere('from_email', 'like', "%{$search}%")
                          ->orWhereJsonContains('to_emails', $search);
                    });
                }

                if ($request->has('from_email')) {
                    $receivedQuery->where('from_email', $request->from_email);
                }

                if ($request->has('to_email')) {
                    $receivedQuery->whereJsonContains('to_emails', $request->to_email);
                }

                if ($request->has('subject')) {
                    $receivedQuery->where('subject', 'like', "%{$request->subject}%");
                }

                if ($request->has('date_from') && $request->has('date_to')) {
                    $receivedQuery->whereBetween('received_at', [$request->date_from, $request->date_to]);
                } elseif ($request->has('date_from')) {
                    $receivedQuery->where('received_at', '>=', $request->date_from);
                } elseif ($request->has('date_to')) {
                    $receivedQuery->where('received_at', '<=', $request->date_to);
                }

                if ($request->has('provider_id')) {
                    $receivedQuery->where('provider_id', $request->provider_id);
                }

                $receivedEmails = $receivedQuery->orderBy('received_at', 'desc')
                    ->paginate($perPage);

                $response['data']['received'] = [
                    'data' => $receivedEmails->items(),
                    'pagination' => [
                        'current_page' => $receivedEmails->currentPage(),
                        'last_page' => $receivedEmails->lastPage(),
                        'per_page' => $receivedEmails->perPage(),
                        'total' => $receivedEmails->total(),
                        'from' => $receivedEmails->firstItem(),
                        'to' => $receivedEmails->lastItem(),
                    ]
                ];
            }

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Error fetching emails: ' . $e->getMessage(), [
                'tenant_id' => $request->tenant_id ?? 'unknown',
                'type' => $request->type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
