<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Outbox;
use App\Models\EmailProvider;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

class RabbitMQService
{
    private $config;
    private $connection = null;
    private $channel = null;
    private $isMockMode = false;

    public function __construct()
    {
        $this->config = config('rabbitmq') ?? [];
        $this->initializeConnection();
    }

    private function initializeConnection()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $this->config['host'] ?? 'localhost',
                $this->config['port'] ?? 5672,
                $this->config['user'] ?? 'guest',
                $this->config['password'] ?? 'guest',
                $this->config['vhost'] ?? '/'
            );
            
            $this->channel = $this->connection->channel();
            $this->isMockMode = false;
            
            // Declare queues
            $this->channel->queue_declare(
                $this->config['queues']['email_send'] ?? 'email.send',
                false,
                true,
                false,
                false
            );
            
            $this->channel->queue_declare(
                $this->config['queues']['email_sync_user'] ?? 'email.sync.user',
                false,
                true,
                false,
                false
            );
            
            Log::info('RabbitMQ connection established successfully');
            
        } catch (\Exception $e) {
            Log::warning('Failed to connect to RabbitMQ, falling back to mock mode: ' . $e->getMessage());
            $this->isMockMode = true;
        }
    }

    public function publishToQueue($queue, $data)
    {
        if ($this->isMockMode) {
            Log::info("Message published to mock queue: {$queue}");
            return true;
        }

        try {
            $message = new AMQPMessage(json_encode($data));
            $this->channel->basic_publish($message, '', $queue);
            Log::info("Message published to RabbitMQ queue: {$queue}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to publish to RabbitMQ queue {$queue}: " . $e->getMessage());
            return false;
        }
    }

    public function publishMessage($queue, $data)
    {
        return $this->publishToQueue($queue, $data);
    }

    public function getQueueStatus()
    {
        try {
            if ($this->isMockMode) {
                return $this->getMockQueueStatus();
            }

            return $this->getRealQueueStatus();
        } catch (\Exception $e) {
            Log::error('Error in getQueueStatus: ' . $e->getMessage());
            return $this->getMockQueueStatus();
        }
    }

    private function getRealQueueStatus()
    {
        try {
            $emailSendQueue = $this->config['queues']['email_send'] ?? 'email.send';
            $emailSyncQueue = $this->config['queues']['email_sync_user'] ?? 'email.sync.user';
            
            // Get pending count from outbox (this is the source of truth)
            // Only count emails that are actually pending/queued, not sent
            $pendingCount = 0;
            try {
                // Count only emails that are pending/queued AND not sent
                // Exclude emails with status='sent' or with sent_at timestamp
                $pendingCount = Outbox::whereIn('status', ['pending', 'queued'])
                    ->where('status', '!=', 'sent') // Explicitly exclude sent
                    ->whereNull('sent_at') // Only count emails that haven't been sent yet
                    ->count();
                    
                Log::info('Queue count calculated', [
                    'pending_count' => $pendingCount,
                    'query' => 'status IN (pending, queued) AND status != sent AND sent_at IS NULL'
                ]);
            } catch (\Exception $e) {
                Log::warning('Could not get pending email count: ' . $e->getMessage());
            }
            
            // Use ONLY outbox count - it's the source of truth
            // Don't use RabbitMQ count because it may include already-processed messages
            $actualCount = $pendingCount;
            
            return [
                'email_send_queue' => [
                    'name' => $emailSendQueue,
                    'message_count' => $actualCount,
                    'status' => 'connected'
                ],
                'email_sync_user_queue' => [
                    'name' => $emailSyncQueue,
                    'message_count' => 0,
                    'status' => 'connected'
                ],
                'connection_status' => 'connected',
                'note' => 'Connected to real RabbitMQ server. Count shows pending/queued emails from outbox.'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting real queue status: ' . $e->getMessage());
            return $this->getMockQueueStatus();
        }
    }

    private function getMockQueueStatus()
    {
        $pendingCount = 0;
        try {
            // Count both 'pending' and 'queued' status emails that haven't been sent
            // Only count emails that are actually waiting to be processed
            $pendingCount = Outbox::whereIn('status', ['pending', 'queued'])
                ->where('status', '!=', 'sent') // Explicitly exclude sent
                ->whereNull('sent_at') // Exclude emails that have already been sent
                ->count();
                
            Log::info('Mock queue count calculated', [
                'pending_count' => $pendingCount,
                'query' => 'status IN (pending, queued) AND status != sent AND sent_at IS NULL'
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not get pending email count: ' . $e->getMessage());
        }

        return [
            'email_send_queue' => [
                'name' => $this->config['queues']['email_send'] ?? 'email.send',
                'message_count' => $pendingCount,
                'status' => 'mock'
            ],
            'email_sync_user_queue' => [
                'name' => $this->config['queues']['email_sync_user'] ?? 'email.sync.user',
                'message_count' => 0,
                'status' => 'mock'
            ],
            'connection_status' => 'mock',
            'note' => 'RabbitMQ running in mock mode - emails processed immediately. Count shows pending/queued emails from outbox.'
        ];
    }

    public function getQueueStats()
    {
        try {
            if ($this->isMockMode) {
                return $this->getMockQueueStats();
            }

            return $this->getRealQueueStats();
        } catch (\Exception $e) {
            Log::error('Error in getQueueStats: ' . $e->getMessage());
            return $this->getMockQueueStats();
        }
    }

    private function getRealQueueStats()
    {
        try {
            $emailSendQueue = $this->config['queues']['email_send'] ?? 'email.send';
            $emailSyncQueue = $this->config['queues']['email_sync_user'] ?? 'email.sync.user';
            
            // Get queue info from RabbitMQ
            $emailSendInfo = $this->channel->queue_declare($emailSendQueue, false, true, false, false);
            $emailSyncInfo = $this->channel->queue_declare($emailSyncQueue, false, true, false, false);
            
            return [
                'queues' => [
                    'email.send' => [
                        'messages' => $emailSendInfo[1],
                        'consumers' => $emailSendInfo[2],
                        'status' => 'connected'
                    ],
                    'email.sync.user' => [
                        'messages' => $emailSyncInfo[1],
                        'consumers' => $emailSyncInfo[2],
                        'status' => 'connected'
                    ],
                    'connection' => [
                        'host' => $this->config['host'] ?? 'localhost',
                        'port' => $this->config['port'] ?? 5672,
                        'status' => 'connected'
                    ]
                ],
                'outbox_stats' => [
                    'total_pending' => Outbox::whereIn('status', ['pending', 'queued'])->count(),
                    'total_sent' => Outbox::where('status', 'sent')->count(),
                    'total_failed' => Outbox::where('status', 'failed')->count(),
                    'total' => Outbox::count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error getting real queue stats: ' . $e->getMessage());
            return $this->getMockQueueStats();
        }
    }

    private function getMockQueueStats()
    {
        $pendingEmails = 0;
        $sentEmails = 0;
        $failedEmails = 0;
        
        try {
            // Only count emails that are actually pending (not sent)
            $pendingEmails = Outbox::whereIn('status', ['pending', 'queued'])
                ->where('status', '!=', 'sent')
                ->whereNull('sent_at')
                ->count();
            $sentEmails = Outbox::where('status', 'sent')->count();
            $failedEmails = Outbox::where('status', 'failed')->count();
        } catch (\Exception $e) {
            Log::warning('Could not get email counts: ' . $e->getMessage());
        }
        
        return [
            'queues' => [
                'email.send' => [
                    'messages' => $pendingEmails,
                    'consumers' => 0,
                    'status' => 'mock'
                ],
                'email.sync.user' => [
                    'messages' => 0,
                    'consumers' => 0,
                    'status' => 'mock'
                ],
                'connection' => [
                    'host' => $this->config['host'] ?? 'localhost',
                    'port' => $this->config['port'] ?? 5672,
                    'status' => 'mock'
                ]
            ],
            'outbox_stats' => [
                'total_pending' => $pendingEmails,
                'total_sent' => $sentEmails,
                'total_failed' => $failedEmails,
                'total' => $pendingEmails + $sentEmails + $failedEmails
            ]
        ];
    }

    public function processQueue($queueName = 'email.send', $maxMessages = 10)
    {
        try {
            Log::info("Starting queue processing for: {$queueName} with max messages: {$maxMessages}");
            
            // Try real queue processing first, fallback to mock if it fails
            try {
                Log::info("Attempting real queue processing for: {$queueName}");
                return $this->processRealQueue($queueName, $maxMessages);
            } catch (\Exception $realQueueError) {
                Log::warning("Real queue processing failed, falling back to mock: " . $realQueueError->getMessage());
                Log::info("Using mock processing for queue: {$queueName}");
                return $this->processMockQueue($queueName, $maxMessages);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in processQueue: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
                
                'queue_name' => $queueName,
                'max_messages' => $maxMessages
            ]);
            
            return [
                'processed' => 0,
                'success' => 0,
                'failed' => 0,
                'queue' => $queueName,
                'error' => 'Queue processing failed: ' . $e->getMessage()
            ];
        }
    }

    private function processRealQueue($queueName, $maxMessages)
    {
        try {
            $processed = 0;
            $successCount = 0;
            $failedCount = 0;
            
            // First, check if there are actually messages in the queue
            $queueInfo = $this->channel->queue_declare($queueName, false, true, false, false);
            $messageCount = $queueInfo[1];
            
            Log::info("Queue '{$queueName}' contains {$messageCount} messages");
            
            if ($messageCount == 0) {
                Log::info("No messages in queue '{$queueName}' to process");
                return [
                    'processed' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'queue' => $queueName,
                    'note' => 'Queue is empty'
                ];
            }
            
            for ($i = 0; $i < min($maxMessages, $messageCount); $i++) {
                $message = $this->channel->basic_get($queueName);
                if ($message) {
                    try {
                        // Decode the message
                        $messageBody = json_decode($message->getBody(), true);
                        Log::info("Processing message from queue {$queueName}: " . json_encode($messageBody));
                        
                        // Extract email data - the message contains the full email payload
                        $emailData = $messageBody;
                        
                        // Check if this email is already sent in outbox - skip if already processed
                        $messageId = $emailData['message_id'] ?? null;
                        $outboxId = $emailData['outbox_id'] ?? null;
                        $shouldSkip = false;
                        $skipReason = '';
                        
                        if ($messageId || $outboxId) {
                            $existingOutbox = null;
                            
                            // Try to find by message_id first
                            if ($messageId) {
                                $existingOutbox = Outbox::where('message_id', $messageId)->first();
                            }
                            
                            // If not found, try by outbox_id
                            if (!$existingOutbox && $outboxId) {
                                $existingOutbox = Outbox::find($outboxId);
                            }
                            
                            if ($existingOutbox) {
                                // Skip if already sent
                                if ($existingOutbox->status === 'sent' || $existingOutbox->sent_at !== null) {
                                    $shouldSkip = true;
                                    $skipReason = 'already-sent';
                                    Log::info("Skipping already-sent email from queue", [
                                        'message_id' => $messageId,
                                        'outbox_id' => $existingOutbox->id,
                                        'status' => $existingOutbox->status,
                                        'sent_at' => $existingOutbox->sent_at
                                    ]);
                                }
                                // Only process if status is pending or queued
                                elseif (!in_array($existingOutbox->status, ['pending', 'queued'])) {
                                    $shouldSkip = true;
                                    $skipReason = 'non-pending-status';
                                    Log::info("Skipping email with non-pending status from queue", [
                                        'message_id' => $messageId,
                                        'outbox_id' => $existingOutbox->id,
                                        'status' => $existingOutbox->status
                                    ]);
                                } else {
                                    // Email is pending/queued - proceed with processing
                                    Log::info("Processing pending/queued email from queue", [
                                        'message_id' => $messageId,
                                        'outbox_id' => $existingOutbox->id,
                                        'status' => $existingOutbox->status
                                    ]);
                                }
                            } else {
                                // No outbox record found - this might be a new email, proceed with processing
                                Log::info("No outbox record found for message, proceeding with processing", [
                                    'message_id' => $messageId,
                                    'outbox_id' => $outboxId
                                ]);
                            }
                        } else {
                            // No message_id or outbox_id - proceed with processing (might be a new email)
                            Log::info("No message_id or outbox_id in queue message, proceeding with processing");
                        }
                        
                        // Skip if needed
                        if ($shouldSkip) {
                            // Acknowledge the message to remove it from queue
                            try {
                                $deliveryTag = $message->get_properties()['delivery_tag'] ?? null;
                                if ($deliveryTag !== null) {
                                    $this->channel->basic_ack($deliveryTag);
                                    Log::info("Acknowledged skipped message ({$skipReason}): " . ($messageId ?? 'unknown'));
                                }
                            } catch (\Exception $ackError) {
                                Log::error("Failed to acknowledge skipped message: " . $ackError->getMessage());
                            }
                            
                            $processed++; // Count as processed (skipped)
                            continue; // Skip processing this message
                        }
                        
                        // Log the email data being processed
                        Log::info("Processing email data from queue: " . json_encode([
                            'from' => $emailData['from'] ?? 'unknown',
                            'to' => $emailData['to'] ?? 'unknown',
                            'subject' => $emailData['subject'] ?? 'unknown',
                            'body_format' => $emailData['body_format'] ?? 'unknown',
                            'message_id' => $messageId
                        ]));
                        
                        // Resolve provider using tenant_id and provider_id from payload if available
                        $tenantIdFromMsg = $emailData['tenant_id'] ?? null;
                        $providerIdFromMsg = $emailData['provider_id'] ?? null;

                        $providerQuery = \App\Models\EmailProvider::query()->where('is_active', true);
                        if ($tenantIdFromMsg) {
                            $providerQuery->where('tenant_id', $tenantIdFromMsg);
                        }
                        if ($providerIdFromMsg) {
                            $providerQuery->where('provider_id', $providerIdFromMsg);
                        }

                        $activeProvider = $providerQuery->first();
                        if (!$activeProvider && $tenantIdFromMsg) {
                            // Fallback: first active provider for tenant
                            $activeProvider = \App\Models\EmailProvider::where('tenant_id', $tenantIdFromMsg)
                                ->where('is_active', true)
                                ->first();
                        }
                        if (!$activeProvider) {
                            // Final fallback: any active provider
                            $activeProvider = \App\Models\EmailProvider::where('is_active', true)->first();
                        }
                        
                        if ($activeProvider) {
                            Log::info("Using active email provider: " . $activeProvider->provider_name);
                            
                            // Send email using the active provider's configuration from database
                            try {
                                // Get provider configuration from config_json field
                                $providerConfig = $activeProvider->getConfig() ?? [];
                                Log::info("Provider config from database: " . json_encode($providerConfig));
                                
                                // Configure mail settings dynamically for this provider
                                $mailConfig = [
                                    'driver' => 'smtp',
                                    'host' => $providerConfig['smtp_host'] ?? $providerConfig['host'] ?? 'localhost',
                                    'port' => $providerConfig['smtp_port'] ?? $providerConfig['port'] ?? 587,
                                    'username' => $providerConfig['smtp_username'] ?? $providerConfig['username'] ?? '',
                                    'password' => $providerConfig['smtp_password'] ?? $providerConfig['password'] ?? '',
                                    'encryption' => $providerConfig['smtp_encryption'] ?? $providerConfig['encryption'] ?? 'tls',
                                    'from' => [
                                        'address' => $emailData['from'] ?? $providerConfig['from_address'] ?? $providerConfig['email'] ?? config('mail.from.address'),
                                        'name' => $providerConfig['from_name'] ?? config('mail.from.name')
                                    ]
                                ];
                                
                                Log::info("Mail config: " . json_encode($mailConfig));
                                
                                // Use the EXACT same working configuration as the test email method
                                $originalMailConfig = config('mail');
                                
                                // Temporarily override mail configuration with explicit SMTP settings
                                config([
                                    'mail.default' => 'smtp',
                                    'mail.mailers.smtp.transport' => 'smtp',
                                    'mail.mailers.smtp.host' => $providerConfig['smtp_host'] ?? $providerConfig['host'] ?? 'localhost',
                                    'mail.mailers.smtp.port' => $providerConfig['smtp_port'] ?? $providerConfig['port'] ?? 587,
                                    'mail.mailers.smtp.username' => $providerConfig['smtp_username'] ?? $providerConfig['username'] ?? '',
                                    'mail.mailers.smtp.password' => $providerConfig['smtp_password'] ?? $providerConfig['password'] ?? '',
                                    'mail.mailers.smtp.encryption' => $providerConfig['smtp_encryption'] ?? $providerConfig['encryption'] ?? 'tls',
                                    'mail.mailers.smtp.timeout' => 30,
                                    'mail.mailers.smtp.local_domain' => 'localhost',
                                    // Explicitly remove any problematic fields
                                    'mail.mailers.smtp.scheme' => null,
                                    'mail.mailers.smtp.url' => null,
                                ]);
                                
                                $processingStartTime = microtime(true);
                                
                                try {
                                    // Build email body from template if template_id is provided
                                    $bodyContent = '';
                                    $bodyFormat = 'TEXT';
                                    $subject = $emailData['subject'] ?? '';
                                    
                                    if (isset($emailData['template_id']) && isset($emailData['template_data'])) {
                                        // Fetch template from database
                                        $template = \App\Models\EmailTemplate::where('template_id', $emailData['template_id'])
                                            ->where('is_active', true)
                                            ->first();
                                        
                                        if ($template) {
                                            Log::info('Template found, rendering with data', [
                                                'template_id' => $emailData['template_id'],
                                                'template_data_keys' => array_keys($emailData['template_data']),
                                                'template_data' => $emailData['template_data']
                                            ]);
                                            
                                            // Use EmailService to render template
                                            $emailService = app(\App\Services\EmailService::class);
                                            $renderedContent = $emailService->renderTemplate($template, $emailData['template_data']);
                                            
                                            $bodyContent = $renderedContent['html'] ?? $renderedContent['text'] ?? '';
                                            $bodyFormat = $template->hasHtmlContent() ? 'HTML' : 'TEXT';
                                            
                                            Log::info('Template rendered', [
                                                'body_length' => strlen($bodyContent),
                                                'body_format' => $bodyFormat,
                                                'has_html' => !empty($renderedContent['html']),
                                                'has_text' => !empty($renderedContent['text'])
                                            ]);
                                            
                                            // Render subject with template data if it contains variables
                                            $templateSubject = $subject ?: $template->subject;
                                            if (!empty($emailData['template_data']) && (strpos($templateSubject, '{{') !== false || strpos($templateSubject, '{') !== false)) {
                                                $subject = $emailService->renderBladeContent($templateSubject, $emailData['template_data']);
                                                Log::info('Subject rendered with template data', [
                                                    'original' => $templateSubject,
                                                    'rendered' => $subject
                                                ]);
                                            } else {
                                                $subject = $templateSubject;
                                            }
                                        } else {
                                            throw new \Exception("Template '{$emailData['template_id']}' not found or inactive");
                                        }
                                    } else {
                                        // Fallback to body_content if provided (backward compatibility)
                                        $bodyContent = $emailData['body_content'] ?? '';
                                        $bodyFormat = $emailData['body_format'] ?? 'TEXT';
                                        Log::warning('No template_id or template_data provided, using body_content', [
                                            'has_body_content' => !empty($bodyContent)
                                        ]);
                                    }
                                    
                                    $recipients = $emailData['to'] ?? [];
                                    $fromEmail = $emailData['from'] ?? ($providerConfig['from_address'] ?? null);
                                    $fromName = $providerConfig['from_name'] ?? 'Email Service';
                                    
                                    // Process attachments from URLs
                                    $attachments = $this->processAttachmentsFromUrls($emailData['attachments'] ?? []);
                                    
                                    Log::info('Attachments processed', [
                                        'requested_count' => count($emailData['attachments'] ?? []),
                                        'processed_count' => count($attachments),
                                        'attachment_details' => array_map(function($att) {
                                            return [
                                                'filename' => $att['filename'] ?? 'unknown',
                                                'size' => $att['size'] ?? 0,
                                                'mime_type' => $att['mime_type'] ?? 'unknown'
                                            ];
                                        }, $attachments)
                                    ]);

                                    if (strtoupper($bodyFormat) === 'HTML') {
                                        Mail::html($bodyContent, function ($message) use ($recipients, $subject, $fromEmail, $fromName, $attachments) {
                                            $message->to($recipients)->subject($subject);
                                            if ($fromEmail) {
                                                $message->from($fromEmail, $fromName);
                                            }
                                            
                                            // Attach files
                                            foreach ($attachments as $attachment) {
                                                if (isset($attachment['content']) && isset($attachment['filename'])) {
                                                    $message->attachData(
                                                        $attachment['content'],
                                                        $attachment['filename'],
                                                        ['mime' => $attachment['mime_type'] ?? 'application/octet-stream']
                                                    );
                                                }
                                            }
                                        });
                                    } else {
                                        Mail::raw($bodyContent, function ($message) use ($recipients, $subject, $fromEmail, $fromName, $attachments) {
                                            $message->to($recipients)->subject($subject);
                                            if ($fromEmail) {
                                                $message->from($fromEmail, $fromName);
                                            }
                                            
                                            // Attach files
                                            foreach ($attachments as $attachment) {
                                                if (isset($attachment['content']) && isset($attachment['filename'])) {
                                                    $message->attachData(
                                                        $attachment['content'],
                                                        $attachment['filename'],
                                                        ['mime' => $attachment['mime_type'] ?? 'application/octet-stream']
                                                    );
                                                }
                                            }
                                        });
                                    }
                                    
                                    $mailResult = true; // Email sent successfully
                                    
                                    // Calculate processing time
                                    $processingTime = round((microtime(true) - $processingStartTime) * 1000, 2);
                                    
                                    // Update existing outbox record with rendered body content
                                    // (Outbox record was already created in EmailController when queuing)
                                    try {
                                        // Try to find by message_id first
                                        $outboxRecord = Outbox::where('message_id', $emailData['message_id'])->first();
                                        
                                        // If not found, try to find by outbox_id if provided
                                        if (!$outboxRecord && isset($emailData['outbox_id'])) {
                                            $outboxRecord = Outbox::find($emailData['outbox_id']);
                                        }
                                        
                                        // If still not found, try to find by subject and from/to (fallback)
                                        if (!$outboxRecord) {
                                            $outboxRecord = Outbox::where('subject', $subject)
                                                ->where('from', $fromEmail)
                                                ->whereIn('status', ['queued', 'pending'])
                                                ->whereNull('sent_at')
                                                ->orderBy('created_at', 'desc')
                                                ->first();
                                        }
                                        
                                        if ($outboxRecord) {
                                            Log::info("Found outbox record ID: {$outboxRecord->id}, current status: {$outboxRecord->status}, message_id: {$outboxRecord->message_id}");
                                            // Update existing record with rendered body and processing details
                                            $outboxRecord->update([
                                                'body_content' => $bodyContent, // Update with rendered content
                                                'body_format' => $bodyFormat,
                                                'status' => 'sent',
                                                'sent_at' => now(),
                                                'delivered_at' => now(),
                                                'processing_started_at' => now()->subMilliseconds($processingTime),
                                                'processing_time_ms' => $processingTime,
                                                'queue_processed' => true,
                                                'provider_response' => array_merge(
                                                    $outboxRecord->provider_response ?? [],
                                                    [
                                                        'provider_name' => $activeProvider->provider_name,
                                                        'smtp_host' => $providerConfig['host'],
                                                        'smtp_port' => $providerConfig['port'],
                                                        'encryption' => $providerConfig['encryption'] ?? 'tls',
                                                        'sent_at' => now()->toISOString(),
                                                        'provider_message_id' => 'outlook-' . time(),
                                                        'queue_processed' => true
                                                    ]
                                                ),
                                                'metadata' => array_merge(
                                                    $outboxRecord->metadata ?? [],
                                                    [
                                                        'queue_worker' => 'RabbitMQService',
                                                        'queue_message_id' => $emailData['message_id'] ?? null,
                                                        'original_timestamp' => $emailData['timestamp'] ?? null,
                                                        'body_format_original' => $emailData['body_format'] ?? 'Text',
                                                        'body_built_from_template' => true,
                                                        'template_rendered_at' => now()->toISOString()
                                                    ]
                                                )
                                            ]);
                                            
                                            Log::info("Outbox record updated with rendered body for message ID: " . $emailData['message_id']);
                                        } else {
                                            // Fallback: Create new record if not found (shouldn't happen normally)
                                            Log::warning("Outbox record not found for message_id: " . ($emailData['message_id'] ?? 'unknown') . ", creating new record");
                                            $outboxRecord = Outbox::create([
                                                'tenant_id' => $emailData['tenant_id'] ?? $activeProvider->tenant_id,
                                                'provider_id' => $activeProvider->provider_id,
                                                'user_id' => $emailData['user_id'] ?? 'system',
                                                'message_id' => $emailData['message_id'] ?? uniqid(),
                                                'subject' => $subject,
                                                'from' => $fromEmail,
                                                'to' => $recipients,
                                                'cc' => $emailData['cc'] ?? null,
                                                'bcc' => $emailData['bcc'] ?? null,
                                                'body_format' => $bodyFormat,
                                                'body_content' => $bodyContent,
                                                'template_id' => $emailData['template_id'] ?? null,
                                                'attachments' => $emailData['attachments'] ?? null,
                                                'status' => 'sent',
                                                'sent_at' => now(),
                                                'delivered_at' => now(),
                                                'source' => 'queue',
                                                'queue_name' => $queueName,
                                                'processing_method' => 'rabbitmq',
                                                'queue_processed' => true,
                                                'queued_at' => $emailData['timestamp'] ? \Carbon\Carbon::parse($emailData['timestamp']) : now(),
                                                'processing_started_at' => now()->subMilliseconds($processingTime),
                                                'processing_time_ms' => $processingTime,
                                            ]);
                                            
                                            Log::info("Fallback: New outbox record created for queue email: " . $outboxRecord->id);
                                        }
                                        
                                    } catch (\Exception $outboxError) {
                                        Log::error("Failed to create outbox record: " . $outboxError->getMessage());
                                    }
                                    
                                } finally {
                                    // Restore original mail configuration
                                    config(['mail' => $originalMailConfig]);
                                }
                                
                                $result = [
                                    'success' => true,
                                    'message_id' => $emailData['message_id'] ?? uniqid(),
                                    'provider_message_id' => 'outlook-' . time(),
                                    'status' => 'sent',
                                    'outbox_id' => $outboxRecord->id ?? null,
                                    'processing_time_ms' => $processingTime ?? null
                                ];
                                
                                Log::info("Email sent successfully via {$activeProvider->provider_name}: " . json_encode($result));
                            } catch (\Exception $mailError) {
                                Log::error("Failed to send email via {$activeProvider->provider_name}: " . $mailError->getMessage());
                                $result = [
                                    'success' => false,
                                    'message' => $mailError->getMessage(),
                                    'status' => 'failed'
                                ];
                            }
                        } else {
                            Log::error("No active email provider found");
                            $result = [
                                'success' => false,
                                'message' => 'No active email provider found',
                                'status' => 'failed'
                            ];
                        }
                        
                        Log::info("Email sending result: " . json_encode($result));
                            
                            if ($result['success']) {
                                $successCount++;
                                Log::info("Email sent successfully via queue processing: " . ($result['message_id'] ?? 'unknown'));
                                
                                // Update the outbox record to mark as sent (if not already updated above)
                                try {
                                    $outbox = Outbox::where('message_id', $emailData['message_id'])->first();
                                    
                                    // If not found by message_id, try by outbox_id
                                    if (!$outbox && isset($result['outbox_id'])) {
                                        $outbox = Outbox::find($result['outbox_id']);
                                    }
                                    
                                    if ($outbox && $outbox->status !== 'sent') {
                                        $outbox->update([
                                            'status' => 'sent',
                                            'sent_at' => now(),
                                            'provider_response' => array_merge(
                                                $outbox->provider_response ?? [],
                                                [
                                                    'sent_at' => now()->toISOString(),
                                                    'provider_message_id' => $result['provider_message_id'] ?? null,
                                                    'queue_processed' => true
                                                ]
                                            )
                                        ]);
                                        Log::info("Outbox record #{$outbox->id} updated to 'sent' for message ID: " . $emailData['message_id']);
                                    } elseif ($outbox && $outbox->status === 'sent') {
                                        Log::info("Outbox record #{$outbox->id} already marked as 'sent'");
                                    } else {
                                        Log::warning("Outbox record not found for message_id: " . ($emailData['message_id'] ?? 'unknown'));
                                    }
                                } catch (\Exception $e) {
                                    Log::error("Could not update outbox record: " . $e->getMessage(), [
                                        'message_id' => $emailData['message_id'] ?? 'unknown',
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            } else {
                                $failedCount++;
                                Log::error("Failed to send email via queue processing: " . ($result['message'] ?? 'unknown error'));
                                
                                // Update the outbox record to mark as failed
                                try {
                                    $outbox = Outbox::where('message_id', $emailData['message_id'])->first();
                                    
                                    // If not found by message_id, try by outbox_id
                                    if (!$outbox && isset($result['outbox_id'])) {
                                        $outbox = Outbox::find($result['outbox_id']);
                                    }
                                    
                                    if ($outbox) {
                                        $outbox->update([
                                            'status' => 'failed',
                                            'error_message' => $result['message'] ?? 'Unknown error',
                                            'provider_response' => array_merge(
                                                $outbox->provider_response ?? [],
                                                [
                                                    'failed_at' => now()->toISOString(),
                                                    'error' => $result['message'] ?? 'Unknown error',
                                                    'queue_processed' => true
                                                ]
                                            )
                                        ]);
                                        Log::info("Outbox record #{$outbox->id} marked as failed for message ID: " . $emailData['message_id']);
                                    } else {
                                        Log::warning("Outbox record not found for failed message_id: " . ($emailData['message_id'] ?? 'unknown'));
                                    }
                                } catch (\Exception $e) {
                                    Log::error("Could not update outbox record: " . $e->getMessage(), [
                                        'message_id' => $emailData['message_id'] ?? 'unknown',
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            }

                        
                        // Safely acknowledge the message
                        try {
                            $deliveryTag = $message->get_properties()['delivery_tag'] ?? null;
                            if ($deliveryTag !== null) {
                                $this->channel->basic_ack($deliveryTag);
                                Log::info("Message acknowledged with delivery tag: {$deliveryTag}");
                            } else {
                                Log::warning("No delivery tag found for message, skipping acknowledgment");
                            }
                        } catch (\Exception $e) {
                            Log::error("Failed to acknowledge message: " . $e->getMessage());
                        }
                        
                        $processed++;
                        
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errorMessage = $e->getMessage();
                        Log::error("Error processing message from queue {$queueName}: " . $errorMessage, [
                            'trace' => $e->getTraceAsString(),
                            'message_id' => $emailData['message_id'] ?? 'unknown'
                        ]);
                        
                        // Update outbox record to mark as failed
                        try {
                            if (isset($emailData['message_id'])) {
                                $outbox = Outbox::where('message_id', $emailData['message_id'])->first();
                                if ($outbox) {
                                    $outbox->update([
                                        'status' => 'failed',
                                        'error_message' => $errorMessage,
                                        'provider_response' => array_merge(
                                            $outbox->provider_response ?? [],
                                            [
                                                'failed_at' => now()->toISOString(),
                                                'error' => $errorMessage,
                                                'queue_processed' => true,
                                                'exception_type' => get_class($e)
                                            ]
                                        )
                                    ]);
                                    Log::info("Outbox record marked as failed for message ID: " . $emailData['message_id']);
                                } else {
                                    Log::warning("Outbox record not found for message_id: " . $emailData['message_id']);
                                }
                            }
                        } catch (\Exception $updateError) {
                            Log::error("Failed to update outbox record for failed email: " . $updateError->getMessage());
                        }
                        
                        // Safely acknowledge the message even if processing failed
                        try {
                            $deliveryTag = $message->get_properties()['delivery_tag'] ?? null;
                            if ($deliveryTag !== null) {
                                $this->channel->basic_ack($deliveryTag);
                                Log::info("Failed message acknowledged with delivery tag: {$deliveryTag}");
                            }
                        } catch (\Exception $ackError) {
                            Log::error("Failed to acknowledge failed message: " . $ackError->getMessage());
                        }
                        
                        $processed++;
                    }
                } else {
                    Log::info("No more messages available in queue '{$queueName}'");
                    break; // No more messages
                }
            }
            
            // After processing RabbitMQ messages, ALWAYS check for pending emails in outbox
            // This handles cases where emails are in outbox but not in RabbitMQ queue
            // OR when all RabbitMQ messages were skipped
            $remainingPending = Outbox::whereIn('status', ['pending', 'queued'])
                ->where('status', '!=', 'sent')
                ->whereNull('sent_at')
                ->count();
            
            Log::info("After RabbitMQ processing, checking for remaining pending emails", [
                'remaining_pending' => $remainingPending,
                'processed_from_rabbitmq' => $processed,
                'success_from_rabbitmq' => $successCount,
                'failed_from_rabbitmq' => $failedCount,
                'skipped_from_rabbitmq' => $processed - $successCount - $failedCount,
                'max_messages' => $maxMessages
            ]);
            
            // Process pending emails if:
            // 1. There are pending emails in outbox, AND
            // 2. We haven't successfully sent max_messages yet (successCount < maxMessages)
            // This ensures we process pending emails even if RabbitMQ messages were all skipped
            if ($remainingPending > 0 && $successCount < $maxMessages) {
                $remainingToProcess = $maxMessages - $successCount; // Process based on successful sends, not total processed
                Log::info("Found {$remainingPending} pending emails in outbox, processing {$remainingToProcess} of them");
                $mockResult = $this->processMockQueue($queueName, $remainingToProcess);
                
                // Merge results - only count actual processing, not skipped messages
                $processed += $mockResult['processed'] ?? 0;
                $successCount += $mockResult['success'] ?? 0;
                $failedCount += $mockResult['failed'] ?? 0;
                
                Log::info("Combined processing results", [
                    'total_processed' => $processed,
                    'total_success' => $successCount,
                    'total_failed' => $failedCount,
                    'from_rabbitmq' => [
                        'processed' => $processed - ($mockResult['processed'] ?? 0),
                        'success' => $successCount - ($mockResult['success'] ?? 0),
                        'failed' => $failedCount - ($mockResult['failed'] ?? 0)
                    ],
                    'from_outbox' => [
                        'processed' => $mockResult['processed'] ?? 0,
                        'success' => $mockResult['success'] ?? 0,
                        'failed' => $mockResult['failed'] ?? 0
                    ]
                ]);
            } else {
                Log::info("No additional pending emails to process", [
                    'remaining_pending' => $remainingPending,
                    'success_count' => $successCount,
                    'max_messages' => $maxMessages
                ]);
            }
            
            $skippedCount = $processed - $successCount - $failedCount;
            Log::info("Processed {$processed} messages from real queue: {$queueName} - Success: {$successCount}, Failed: {$failedCount}, Skipped: {$skippedCount}");
            
            return [
                'processed' => $processed,
                'success' => $successCount,
                'failed' => $failedCount,
                'skipped' => $skippedCount,
                'queue' => $queueName,
                'note' => $skippedCount > 0 ? "{$skippedCount} messages were skipped (already sent or non-pending status)" : null
            ];
        } catch (\Exception $e) {
            Log::error("Error processing real queue {$queueName}: " . $e->getMessage());
            return [
                'processed' => 0,
                'success' => 0,
                'failed' => 0,
                'queue' => $queueName,
                'error' => $e->getMessage()
            ];
        }
    }

    private function processMockQueue($queueName, $maxMessages)
    {
        try {
            Log::info("Starting mock queue processing for: {$queueName}");
            
            $pendingEmails = collect();
            try {
                $pendingEmails = Outbox::whereIn('status', ['pending', 'queued'])
                    ->where('status', '!=', 'sent') // Explicitly exclude sent
                    ->whereNull('sent_at') // Only get emails that haven't been sent
                    ->limit($maxMessages)
                    ->get();
                    
                Log::info("Found {$pendingEmails->count()} pending emails to process");
            } catch (\Exception $e) {
                Log::warning('Could not get pending emails: ' . $e->getMessage());
                return [
                    'processed' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'queue' => $queueName,
                    'error' => 'Could not retrieve pending emails: ' . $e->getMessage()
                ];
            }
            
            $processed = 0;
            $successCount = 0;
            $failedCount = 0;

            foreach ($pendingEmails as $email) {
                try {
                    $processingStartTime = microtime(true);
                    
                    // Convert outbox record to emailData format for processing
                    // Get attachments - check both direct field and ensure it's an array
                    $outboxAttachments = $email->attachments ?? [];
                    if (!is_array($outboxAttachments)) {
                        // If attachments is stored as JSON string, decode it
                        if (is_string($outboxAttachments)) {
                            $outboxAttachments = json_decode($outboxAttachments, true) ?? [];
                        } else {
                            $outboxAttachments = [];
                        }
                    }
                    
                    $emailData = [
                        'message_id' => $email->message_id,
                        'outbox_id' => $email->id,
                        'tenant_id' => $email->tenant_id,
                        'provider_id' => $email->provider_id,
                        'user_id' => $email->user_id,
                        'from' => $email->from,
                        'to' => $email->to ?? [],
                        'cc' => $email->cc ?? [],
                        'bcc' => $email->bcc ?? [],
                        'subject' => $email->subject,
                        'body_content' => $email->body_content,
                        'body_format' => $email->body_format,
                        'template_id' => $email->template_id,
                        'template_data' => $email->metadata['template_data'] ?? [],
                        'attachments' => $outboxAttachments
                    ];
                    
                    Log::info("Processing outbox email ID: {$email->id} from mock queue", [
                        'message_id' => $email->message_id,
                        'subject' => $email->subject,
                        'has_template' => !empty($email->template_id),
                        'has_body' => !empty($email->body_content),
                        'template_id' => $email->template_id,
                        'template_data_keys' => array_keys($emailData['template_data']),
                        'template_data' => $emailData['template_data'],
                        'attachments_count' => count($emailData['attachments']),
                        'attachments' => $emailData['attachments'],
                        'metadata' => $email->metadata
                    ]);
                    
                    // Get provider
                    $activeProvider = \App\Models\EmailProvider::where('provider_id', $email->provider_id)
                        ->where('is_active', true)
                        ->first();
                    
                    if (!$activeProvider) {
                        throw new \Exception("No active email provider found for provider_id: {$email->provider_id}");
                    }
                    
                    $providerConfig = $activeProvider->getConfig() ?? [];
                    
                    Log::info("Provider config retrieved", [
                        'provider_name' => $activeProvider->provider_name,
                        'smtp_host' => $providerConfig['smtp_host'] ?? $providerConfig['host'] ?? 'unknown',
                        'smtp_port' => $providerConfig['smtp_port'] ?? $providerConfig['port'] ?? 'unknown',
                        'has_username' => !empty($providerConfig['smtp_username'] ?? $providerConfig['username'] ?? ''),
                        'has_password' => !empty($providerConfig['smtp_password'] ?? $providerConfig['password'] ?? '')
                    ]);
                    
                    // Use the same sending logic as processRealQueue - inline it here
                    $originalMailConfig = config('mail');
                    
                    // Configure mail settings
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.transport' => 'smtp',
                        'mail.mailers.smtp.host' => $providerConfig['smtp_host'] ?? $providerConfig['host'] ?? 'localhost',
                        'mail.mailers.smtp.port' => $providerConfig['smtp_port'] ?? $providerConfig['port'] ?? 587,
                        'mail.mailers.smtp.username' => $providerConfig['smtp_username'] ?? $providerConfig['username'] ?? '',
                        'mail.mailers.smtp.password' => $providerConfig['smtp_password'] ?? $providerConfig['password'] ?? '',
                        'mail.mailers.smtp.encryption' => $providerConfig['smtp_encryption'] ?? $providerConfig['encryption'] ?? 'tls',
                        'mail.mailers.smtp.timeout' => 30,
                        'mail.mailers.smtp.local_domain' => 'localhost',
                        'mail.mailers.smtp.scheme' => null,
                        'mail.mailers.smtp.url' => null,
                    ]);
                    
                    Log::info("Mail config updated", [
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'username' => config('mail.mailers.smtp.username'),
                        'encryption' => config('mail.mailers.smtp.encryption')
                    ]);
                    
                    try {
                        // Build email body from template if template_id is provided
                        $bodyContent = '';
                        $bodyFormat = $email->body_format ?? 'TEXT';
                        $subject = $email->subject;
                        
                        if (!empty($email->template_id)) {
                            $template = \App\Models\EmailTemplate::where('template_id', $email->template_id)
                                ->where('is_active', true)
                                ->first();
                            
                            if ($template) {
                                // Check if we have template_data
                                if (empty($emailData['template_data'])) {
                                    Log::warning("Template ID provided but no template_data found", [
                                        'template_id' => $email->template_id,
                                        'metadata' => $email->metadata
                                    ]);
                                    // Try to use empty template_data or throw error
                                    if (empty($email->body_content)) {
                                        throw new \Exception("Template '{$email->template_id}' requires template_data but none provided in metadata");
                                    }
                                    // Fallback to body_content if available
                                    $bodyContent = $email->body_content ?? '';
                                } else {
                                    Log::info("Rendering template with data", [
                                        'template_id' => $email->template_id,
                                        'template_data' => $emailData['template_data'],
                                        'template_subject' => $template->subject,
                                        'template_html_length' => strlen($template->html_content ?? ''),
                                        'template_text_length' => strlen($template->text_content ?? '')
                                    ]);
                                    
                                    $emailService = app(\App\Services\EmailService::class);
                                    $renderedContent = $emailService->renderTemplate($template, $emailData['template_data']);
                                    $bodyContent = $renderedContent['html'] ?? $renderedContent['text'] ?? '';
                                    $bodyFormat = $template->hasHtmlContent() ? 'HTML' : 'TEXT';
                                    
                                    Log::info("Template rendered", [
                                        'body_length' => strlen($bodyContent),
                                        'body_format' => $bodyFormat,
                                        'has_html' => !empty($renderedContent['html']),
                                        'has_text' => !empty($renderedContent['text']),
                                        'body_preview' => substr($bodyContent, 0, 200)
                                    ]);
                                    
                                    // Render subject
                                    $templateSubject = $subject ?: $template->subject;
                                    if (!empty($emailData['template_data']) && (strpos($templateSubject, '{{') !== false)) {
                                        $subject = $emailService->renderBladeContent($templateSubject, $emailData['template_data']);
                                        Log::info("Subject rendered", [
                                            'original' => $templateSubject,
                                            'rendered' => $subject
                                        ]);
                                    } else {
                                        $subject = $templateSubject;
                                    }
                                }
                            } else {
                                Log::warning("Template not found or inactive", [
                                    'template_id' => $email->template_id
                                ]);
                                // Fallback to body_content
                                $bodyContent = $email->body_content ?? '';
                                if (empty($bodyContent)) {
                                    throw new \Exception("Template '{$email->template_id}' not found or inactive, and no body_content available");
                                }
                            }
                        } else {
                            $bodyContent = $email->body_content ?? '';
                            if (empty($bodyContent)) {
                                throw new \Exception("No template_id or body_content available for email");
                            }
                        }
                        
                        // Validate we have content to send
                        if (empty($bodyContent)) {
                            throw new \Exception("No email body content available. Template rendering may have failed or body_content is empty.");
                        }
                        
                        $recipients = $email->to ?? [];
                        if (empty($recipients)) {
                            throw new \Exception("No recipients specified for email");
                        }
                        
                        $fromEmail = $email->from;
                        if (empty($fromEmail)) {
                            throw new \Exception("No from email address specified");
                        }
                        
                        $fromName = $providerConfig['from_name'] ?? 'Email Service';
                        
                        Log::info("Preparing to send email", [
                            'to' => $recipients,
                            'from' => $fromEmail,
                            'subject' => $subject,
                            'body_length' => strlen($bodyContent),
                            'body_format' => $bodyFormat,
                            'has_attachments' => !empty($emailData['attachments'])
                        ]);
                        
                        // Process attachments
                        Log::info("Processing attachments", [
                            'requested_attachments' => $emailData['attachments'] ?? [],
                            'requested_count' => count($emailData['attachments'] ?? [])
                        ]);
                        
                        $attachments = $this->processAttachmentsFromUrls($emailData['attachments'] ?? []);
                        
                        Log::info("Attachments processed", [
                            'processed_count' => count($attachments),
                            'attachment_details' => array_map(function($att) {
                                return [
                                    'filename' => $att['filename'] ?? 'unknown',
                                    'size' => $att['size'] ?? 0,
                                    'mime_type' => $att['mime_type'] ?? 'unknown'
                                ];
                            }, $attachments)
                        ]);
                        
                        Log::info("Sending email via Mail", [
                            'format' => $bodyFormat,
                            'attachments_count' => count($attachments),
                            'provider' => $activeProvider->provider_name,
                            'will_attach_files' => count($attachments) > 0
                        ]);
                        
                        // Send email with proper error handling
                        try {
                            if (strtoupper($bodyFormat) === 'HTML') {
                                Mail::html($bodyContent, function ($message) use ($recipients, $subject, $fromEmail, $fromName, $attachments) {
                                    $message->to($recipients)->subject($subject);
                                    if ($fromEmail) {
                                        $message->from($fromEmail, $fromName);
                                    }
                                    foreach ($attachments as $attachment) {
                                        if (isset($attachment['content']) && isset($attachment['filename'])) {
                                            Log::info("Attaching file to HTML email", [
                                                'filename' => $attachment['filename'],
                                                'size' => strlen($attachment['content']),
                                                'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream'
                                            ]);
                                            $message->attachData(
                                                $attachment['content'],
                                                $attachment['filename'],
                                                ['mime' => $attachment['mime_type'] ?? 'application/octet-stream']
                                            );
                                            Log::info("File attached successfully to HTML email", ['filename' => $attachment['filename']]);
                                        } else {
                                            Log::warning("Skipping invalid attachment in HTML email", [
                                                'has_content' => isset($attachment['content']),
                                                'has_filename' => isset($attachment['filename']),
                                                'attachment' => $attachment
                                            ]);
                                        }
                                    }
                                });
                                Log::info("HTML email sent successfully via {$activeProvider->provider_name} with " . count($attachments) . " attachments");
                            } else {
                                Mail::raw($bodyContent, function ($message) use ($recipients, $subject, $fromEmail, $fromName, $attachments) {
                                    $message->to($recipients)->subject($subject);
                                    if ($fromEmail) {
                                        $message->from($fromEmail, $fromName);
                                    }
                                    foreach ($attachments as $attachment) {
                                        if (isset($attachment['content']) && isset($attachment['filename'])) {
                                            Log::info("Attaching file to text email", [
                                                'filename' => $attachment['filename'],
                                                'size' => strlen($attachment['content']),
                                                'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream'
                                            ]);
                                            $message->attachData(
                                                $attachment['content'],
                                                $attachment['filename'],
                                                ['mime' => $attachment['mime_type'] ?? 'application/octet-stream']
                                            );
                                            Log::info("File attached successfully to text email", ['filename' => $attachment['filename']]);
                                        } else {
                                            Log::warning("Skipping invalid attachment in text email", [
                                                'has_content' => isset($attachment['content']),
                                                'has_filename' => isset($attachment['filename']),
                                                'attachment' => $attachment
                                            ]);
                                        }
                                    }
                                });
                                Log::info("Text email sent successfully via {$activeProvider->provider_name} with " . count($attachments) . " attachments");
                            }
                        } catch (\Swift_TransportException $e) {
                            Log::error("SMTP transport error sending email", [
                                'error' => $e->getMessage(),
                                'provider' => $activeProvider->provider_name,
                                'smtp_host' => $providerConfig['smtp_host'] ?? 'unknown'
                            ]);
                            throw new \Exception("SMTP Error: " . $e->getMessage());
                        } catch (\Exception $e) {
                            Log::error("Mail sending error", [
                                'error' => $e->getMessage(),
                                'error_type' => get_class($e),
                                'provider' => $activeProvider->provider_name
                            ]);
                            throw $e;
                        }
                        
                        $processingTime = round((microtime(true) - $processingStartTime) * 1000, 2);
                        
                        // Update outbox record
                        $email->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'delivered_at' => now(),
                            'processing_time_ms' => $processingTime,
                            'queue_processed' => true,
                            'body_content' => $bodyContent, // Update with rendered content
                            'provider_response' => array_merge(
                                $email->provider_response ?? [],
                                [
                                    'provider_message_id' => 'sent-' . time() . '-' . $email->id,
                                    'queue_processed' => true,
                                    'processed_at' => now()->toISOString()
                                ]
                            ),
                            'metadata' => array_merge(
                                $email->metadata ?? [],
                                [
                                    'queue_worker' => 'RabbitMQService-Mock',
                                    'queue_name' => $queueName,
                                    'processing_method' => 'mock'
                                ]
                            )
                        ]);
                        
                        $successCount++;
                        $processed++;
                        Log::info("Successfully sent email ID: {$email->id} from mock queue: {$queueName}");
                        
                    } finally {
                        config(['mail' => $originalMailConfig]);
                    }
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $errorMessage = $e->getMessage();
                    Log::error("Failed to process email ID {$email->id}: " . $errorMessage, [
                        'trace' => $e->getTraceAsString(),
                        'error_type' => get_class($e),
                        'email_subject' => $email->subject ?? 'unknown',
                        'email_to' => $email->to ?? 'unknown'
                    ]);
                    
                    // Mark as failed with detailed error
                    try {
                        $email->update([
                            'status' => 'failed',
                            'error_message' => 'Mock processing failed: ' . $errorMessage,
                            'retry_count' => ($email->retry_count ?? 0) + 1,
                            'provider_response' => array_merge(
                                $email->provider_response ?? [],
                                [
                                    'failed_at' => now()->toISOString(),
                                    'error' => $errorMessage,
                                    'error_type' => get_class($e),
                                    'queue_processed' => true
                                ]
                            )
                        ]);
                        Log::info("Email ID {$email->id} marked as failed with error: {$errorMessage}");
                    } catch (\Exception $updateError) {
                        Log::error("Could not update failed email {$email->id}: " . $updateError->getMessage());
                    }
                }
            }

            Log::info("Completed mock queue processing: {$processed} processed, {$successCount} success, {$failedCount} failed");
            return [
                'processed' => $processed,
                'success' => $successCount,
                'failed' => $failedCount,
                'queue' => $queueName,
                'note' => 'Mock processing completed'
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in processMockQueue: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
                'queue_name' => $queueName,
                'max_messages' => $maxMessages
            ]);
            return [
                'processed' => 0,
                'success' => 0,
                'failed' => 0,
                'queue' => $queueName,
                'error' => 'Mock processing failed: ' . $e->getMessage()
            ];
        }
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Process attachments from URLs
     * Fetches file content from URLs provided in the email data
     * 
     * @param array $attachments Array of attachment objects with 'url', 'filename', 'mime_type'
     * @return array Array of attachments with 'content', 'filename', 'mime_type'
     */
    protected function processAttachmentsFromUrls(array $attachments): array
    {
        $processedAttachments = [];
        
        foreach ($attachments as $attachment) {
            try {
                // Validate attachment structure
                if (!isset($attachment['url']) || !isset($attachment['filename'])) {
                    Log::warning('Invalid attachment structure', ['attachment' => $attachment]);
                    continue;
                }
                
                $url = $attachment['url'];
                $filename = $attachment['filename'];
                $mimeType = $attachment['mime_type'] ?? 'application/octet-stream';
                
                // Validate URL to prevent SSRF attacks
                if (!$this->isValidUrl($url)) {
                    Log::error('Invalid attachment URL (blocked by validation)', [
                        'url' => $url,
                        'filename' => $filename
                    ]);
                    continue;
                }
                
                Log::info('Fetching attachment from URL', [
                    'url' => $url,
                    'filename' => $filename,
                    'mime_type' => $mimeType
                ]);
                
                // Fetch file content from URL
                $fileContent = $this->fetchFileFromUrl($url);
                
                if ($fileContent === false) {
                    Log::error('Failed to fetch attachment from URL', [
                        'url' => $url,
                        'filename' => $filename
                    ]);
                    continue;
                }
                
                // Check file size (max 25MB per file)
                $fileSize = strlen($fileContent);
                $maxSize = 25 * 1024 * 1024; // 25MB
                
                if ($fileSize > $maxSize) {
                    Log::warning('Attachment file too large', [
                        'url' => $url,
                        'filename' => $filename,
                        'size' => $fileSize
                    ]);
                    continue;
                }
                
                $processedAttachments[] = [
                    'content' => $fileContent,
                    'filename' => $filename,
                    'mime_type' => $mimeType,
                    'size' => $fileSize,
                    'url' => $url, // Keep original URL for reference
                ];
                
                Log::info('Attachment processed successfully', [
                    'filename' => $filename,
                    'size' => $fileSize,
                    'mime_type' => $mimeType
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error processing attachment', [
                    'attachment' => $attachment,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue with other attachments
                continue;
            }
        }
        
        return $processedAttachments;
    }
    
    /**
     * Fetch file content from URL
     * 
     * @param string $url The URL to fetch from
     * @return string|false File content on success, false on failure
     */
    protected function fetchFileFromUrl(string $url): string|false
    {
        try {
            Log::info('Fetching file from URL', ['url' => $url]);
            
            // Use Laravel's HTTP client with timeout and retry
            // Add headers to handle authentication if needed (for SharePoint, etc.)
            $response = \Illuminate\Support\Facades\Http::timeout(60)
                ->retry(2, 1000) // Retry 2 times with 1 second delay
                ->withHeaders([
                    'Accept' => '*/*',
                    'User-Agent' => 'Email-Service/1.0'
                ])
                ->get($url);
            
            if ($response->successful()) {
                $content = $response->body();
                $size = strlen($content);
                Log::info('File fetched successfully', [
                    'url' => $url,
                    'size' => $size,
                    'content_type' => $response->header('Content-Type')
                ]);
                return $content;
            }
            
            Log::error('Failed to fetch file from URL', [
                'url' => $url,
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 200) // First 200 chars for debugging
            ]);
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Exception while fetching file from URL', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Validate URL to prevent SSRF attacks
     * 
     * @param string $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    protected function isValidUrl(string $url): bool
    {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $parsedUrl = parse_url($url);
        
        // Only allow http and https protocols
        if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'])) {
            return false;
        }
        
        // Block localhost and private IP ranges (SSRF protection)
        $host = $parsedUrl['host'] ?? '';
        
        // Check for localhost
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'])) {
            return false;
        }
        
        // Check for private IP ranges
        $ip = gethostbyname($host);
        if ($ip === $host) {
            // DNS resolution failed or returned hostname
            // Allow it but log for monitoring
            Log::info('URL hostname could not be resolved to IP', ['host' => $host]);
            
            // Allow certain trusted domains (SharePoint, OneDrive, etc.)
            $trustedDomains = [
                'sharepoint.com',
                'onedrive.com',
                'office.com',
                'microsoft.com',
                'live.com',
                'outlook.com'
            ];
            
            $isTrusted = false;
            foreach ($trustedDomains as $trustedDomain) {
                if (str_contains(strtolower($host), $trustedDomain)) {
                    $isTrusted = true;
                    Log::info('Allowing trusted domain', ['host' => $host, 'trusted_domain' => $trustedDomain]);
                    break;
                }
            }
            
            if (!$isTrusted) {
                // For other domains, allow but log
                Log::info('Allowing hostname (could not resolve IP)', ['host' => $host]);
            }
        } else {
            // Check if IP is in private ranges
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                // Allow certain trusted domains even if they resolve to private IPs (for testing)
                $trustedDomains = [
                    'sharepoint.com',
                    'onedrive.com',
                    'office.com',
                    'microsoft.com'
                ];
                
                $isTrusted = false;
                foreach ($trustedDomains as $trustedDomain) {
                    if (str_contains(strtolower($host), $trustedDomain)) {
                        $isTrusted = true;
                        Log::info('Allowing trusted domain even with private IP', ['host' => $host, 'ip' => $ip]);
                        break;
                    }
                }
                
                if (!$isTrusted) {
                    Log::warning('Blocked private IP range URL', ['url' => $url, 'ip' => $ip]);
                    return false;
                }
            }
        }
        
        return true;
    }
} 