<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\InboundEmailController;
use App\Http\Controllers\OutboxController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth API routes
Route::get('/auth/me', [AuthController::class, 'me']);

// Email API Routes
Route::prefix('email')->group(function () {
    // Send email
    Route::post('/send', [EmailController::class, 'sendEmail']);
    
    // Get emails (sent/received/both) with pagination and filters
    Route::get('/emails', [EmailController::class, 'getEmails']);
    
    // Get replied emails (NEW - unified replied emails endpoint)
    Route::get('/replies', [EmailController::class, 'getRepliedEmails']);
    
    // Get email status
    Route::get('/status/{messageId}', [EmailController::class, 'getEmailStatus']);
    
    // Get email templates
    Route::get('/templates', [EmailController::class, 'getTemplates']);
    Route::get('/templates/{templateId}', [EmailController::class, 'getTemplate']);
    
    // Get email logs
    Route::get('/logs', [EmailController::class, 'getEmailLogs']);
    
    // Get email statistics
    Route::get('/stats', [EmailController::class, 'getEmailStats']);
    
    // Bounced Emails Management
    Route::get('/bounced', [EmailController::class, 'getBouncedEmails']);
    Route::post('/bounced/{id}/update-email', [EmailController::class, 'updateBouncedEmail']);
    Route::post('/bounced/{id}/requeue', [EmailController::class, 'requeueBouncedEmail']);

    // Email Tracking and Analytics
    Route::get('/tracking/stats', [EmailController::class, 'getTrackingStats']);
    
    // Inbound Email Management
    Route::get('/inbound', [InboundEmailController::class, 'getInboundEmails']);
    Route::post('/inbound', [InboundEmailController::class, 'createInboundEmail']);
    Route::get('/inbound/stats', [InboundEmailController::class, 'getInboundStats']);
    
    // Tenants API Routes
    Route::get('/tenants', [EmailController::class, 'getTenants']);
    Route::get('/tracking/recent', [EmailController::class, 'getRecentEmails']);
    Route::get('/tracking/performance', [EmailController::class, 'getPerformanceMetrics']);
    Route::get('/tracking/analytics', [EmailController::class, 'getEmailAnalytics']);
    
    // Phase 1: New MIME and File Processing Endpoints
    Route::post('/test/mime-parsing', [EmailController::class, 'testMimeParsing']);
    Route::post('/test/file-storage', [EmailController::class, 'testFileStorage']);
    
    // Email Providers Management
    Route::get('/providers', [EmailController::class, 'getProviders']);
    Route::post('/providers', [EmailController::class, 'createProvider']);
    Route::get('/providers/{providerId}', [EmailController::class, 'getProvider']);
    Route::put('/providers/{providerId}', [EmailController::class, 'updateProvider']);
    Route::delete('/providers/{providerId}', [EmailController::class, 'deleteProvider']);
    
    // Email Sending
    Route::post('/send-test-email', [EmailController::class, 'sendTestEmail']);
});

// RabbitMQ Service Routes
Route::prefix('rabbitmq')->group(function () {
    // Send email via RabbitMQ queue
    Route::post('/send-email', [EmailController::class, 'sendEmailViaRabbitMQ']);
    
    // Check queue status (multiple aliases for convenience)
    Route::get('/status', [EmailController::class, 'getRabbitMQQueueStatus']);
    Route::get('/queue-status', [EmailController::class, 'getRabbitMQQueueStatus']);
    
    // Get queue statistics
    Route::get('/queue-stats', [EmailController::class, 'getRabbitMQQueueStats']);
    
    // Manual queue processing
    Route::post('/process-queue', [EmailController::class, 'processRabbitMQQueue']);
});

// Outbox Management Routes
Route::prefix('outbox')->group(function () {
    // Get outbox emails with filtering and pagination
    Route::get('/emails', [OutboxController::class, 'getEmails']);
    
    // Get specific email details
    Route::get('/emails/{id}', [OutboxController::class, 'show']);
    
    // Update email status
    Route::patch('/emails/{id}/status', [OutboxController::class, 'updateStatus']);
    
    // Resend failed email
    Route::post('/emails/{id}/resend', [OutboxController::class, 'resend']);
    
    // Delete email from outbox
    Route::delete('/emails/{id}', [OutboxController::class, 'destroy']);
    
    // Get outbox statistics
    Route::get('/stats', [OutboxController::class, 'getStats']);
});

// Webhook Routes
Route::prefix('webhook')->group(function () {
    // AWS SES webhook
    Route::post('/ses', [WebhookController::class, 'handleSESWebhook']);
    
    // Generic webhook for other providers
    Route::post('/{provider}', [WebhookController::class, 'handleGenericWebhook']);
    
    // Webhook management (protected routes)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/stats', [WebhookController::class, 'getWebhookStats']);
        Route::get('/events', [WebhookController::class, 'getWebhookEvents']);
        Route::get('/events/{id}', [WebhookController::class, 'getWebhookEvent']);
        Route::post('/mark-processed', [WebhookController::class, 'markWebhooksAsProcessed']);
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'AltimaCRM Email Microservice',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
    ]);
});

// API documentation endpoint
Route::get('/', function () {
    return response()->json([
        'service' => 'AltimaCRM Email Microservice',
        'version' => '1.0.0',
        'description' => 'A Laravel-based microservice for sending emails with AWS SES integration',
        'endpoints' => [
            'POST /api/email/send' => 'Send an email using a template',
            'GET /api/email/status/{messageId}' => 'Get email delivery status',
            'GET /api/email/templates' => 'Get available email templates',
            'GET /api/email/logs' => 'Get email logs with filters',
            'GET /api/email/stats' => 'Get email statistics',
            'POST /api/webhook/ses' => 'AWS SES webhook endpoint',
            'POST /api/webhook/{provider}' => 'Generic webhook endpoint',
            'GET /api/health' => 'Service health check',
        ],
        'documentation' => 'See the project documentation for detailed API usage',
    ]);
}); 

// Email tracking and analytics routes
Route::prefix('email/tracking')->group(function () {
    Route::get('/stats', [EmailController::class, 'getTrackingStats']);
    Route::get('/recent', [EmailController::class, 'getRecentEmails']);
    Route::get('/performance', [EmailController::class, 'getPerformanceMetrics']);
    Route::get('/analytics', [EmailController::class, 'getEmailAnalytics']);
}); 