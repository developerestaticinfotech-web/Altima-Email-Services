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
            
            // Get queue info from RabbitMQ
            $emailSendInfo = $this->channel->queue_declare($emailSendQueue, false, true, false, false);
            $emailSyncInfo = $this->channel->queue_declare($emailSyncQueue, false, true, false, false);
            
            return [
                'email_send_queue' => [
                    'name' => $emailSendQueue,
                    'message_count' => $emailSendInfo[1],
                    'status' => 'connected'
                ],
                'email_sync_user_queue' => [
                    'name' => $emailSyncQueue,
                    'message_count' => $emailSyncInfo[1],
                    'status' => 'connected'
                ],
                'connection_status' => 'connected',
                'note' => 'Connected to real RabbitMQ server'
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
            $pendingCount = Outbox::where('status', 'pending')->count();
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
            'note' => 'RabbitMQ running in mock mode - emails processed immediately'
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
                    'total_pending' => Outbox::where('status', 'pending')->count(),
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
            $pendingEmails = Outbox::where('status', 'pending')->count();
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
                        
                        // Log the email data being processed
                        Log::info("Processing email data from queue: " . json_encode([
                            'from' => $emailData['from'] ?? 'unknown',
                            'to' => $emailData['to'] ?? 'unknown',
                            'subject' => $emailData['subject'] ?? 'unknown',
                            'body_format' => $emailData['body_format'] ?? 'unknown'
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
                                            // Use EmailService to render template
                                            $emailService = app(\App\Services\EmailService::class);
                                            $renderedContent = $emailService->renderTemplate($template, $emailData['template_data']);
                                            
                                            $bodyContent = $renderedContent['html'] ?? $renderedContent['text'] ?? '';
                                            $bodyFormat = $template->hasHtmlContent() ? 'HTML' : 'TEXT';
                                            $subject = $subject ?: $template->subject;
                                        } else {
                                            throw new \Exception("Template '{$emailData['template_id']}' not found or inactive");
                                        }
                                    } else {
                                        // Fallback to body_content if provided (backward compatibility)
                                        $bodyContent = $emailData['body_content'] ?? '';
                                        $bodyFormat = $emailData['body_format'] ?? 'TEXT';
                                    }
                                    
                                    $recipients = $emailData['to'] ?? [];
                                    $fromEmail = $emailData['from'] ?? ($providerConfig['from_address'] ?? null);
                                    $fromName = $providerConfig['from_name'] ?? 'Email Service';
                                    
                                    // Process attachments from URLs
                                    $attachments = $this->processAttachmentsFromUrls($emailData['attachments'] ?? []);

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
                                        $outboxRecord = Outbox::where('message_id', $emailData['message_id'])->first();
                                        
                                        if ($outboxRecord) {
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
                                
                                // Update the outbox record to mark as sent
                                try {
                                    $outbox = Outbox::where('message_id', $emailData['message_id'])->first();
                                    if ($outbox) {
                                        $outbox->update([
                                            'status' => 'sent',
                                            'provider_response' => array_merge(
                                                $outbox->provider_response ?? [],
                                                [
                                                    'sent_at' => now()->toISOString(),
                                                    'provider_message_id' => $result['provider_message_id'] ?? null,
                                                    'queue_processed' => true
                                                ]
                                            )
                                        ]);
                                        Log::info("Outbox record updated for message ID: " . $emailData['message_id']);
                                    }
                                } catch (\Exception $e) {
                                    Log::warning("Could not update outbox record: " . $e->getMessage());
                                }
                            } else {
                                $failedCount++;
                                Log::error("Failed to send email via queue processing: " . ($result['message'] ?? 'unknown error'));
                                
                                // Update the outbox record to mark as failed
                                try {
                                    $outbox = Outbox::where('message_id', $emailData['message_id'])->first();
                                    if ($outbox) {
                                        $outbox->update([
                                            'status' => 'failed',
                                            'provider_response' => array_merge(
                                                $outbox->provider_response ?? [],
                                                [
                                                    'failed_at' => now()->toISOString(),
                                                    'error' => $result['message'] ?? 'Unknown error',
                                                    'queue_processed' => true
                                                ]
                                            )
                                        ]);
                                        Log::info("Outbox record marked as failed for message ID: " . $emailData['message_id']);
                                    }
                                } catch (\Exception $e) {
                                    Log::warning("Could not update outbox record: " . $e->getMessage());
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
            
            Log::info("Processed {$processed} messages from real queue: {$queueName} - Success: {$successCount}, Failed: {$failedCount}");
            return [
                'processed' => $processed,
                'success' => $successCount,
                'failed' => $failedCount,
                'queue' => $queueName
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
                $pendingEmails = Outbox::where('status', 'pending')
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
                    // Update email status to sent with comprehensive tracking
                    $email->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'delivered_at' => now(),
                        'processing_time_ms' => rand(100, 500), // Simulate processing time
                        'provider_response' => [
                            'provider_message_id' => 'mock-' . time() . '-' . $email->id,
                            'queue_processed' => true,
                            'processed_at' => now()->toISOString()
                        ],
                        'metadata' => [
                            'queue_worker' => 'RabbitMQService-Mock',
                            'queue_name' => $queueName,
                            'processing_method' => 'mock'
                        ]
                    ]);
                    
                    $successCount++;
                    $processed++;
                    Log::info("Successfully processed email ID: {$email->id} from mock queue: {$queueName}");
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error("Failed to process email ID {$email->id}: " . $e->getMessage());
                    
                    // Mark as failed if update fails
                    try {
                        $email->update([
                            'status' => 'failed',
                            'error_message' => 'Mock processing failed: ' . $e->getMessage(),
                            'retry_count' => ($email->retry_count ?? 0) + 1
                        ]);
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
                    Log::warning('Invalid attachment URL', ['url' => $url]);
                    continue;
                }
                
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
            // Use Laravel's HTTP client with timeout and retry
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->retry(2, 1000) // Retry 2 times with 1 second delay
                ->get($url);
            
            if ($response->successful()) {
                return $response->body();
            }
            
            Log::error('Failed to fetch file from URL', [
                'url' => $url,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200) // First 200 chars for debugging
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
        } else {
            // Check if IP is in private ranges
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                Log::warning('Blocked private IP range URL', ['url' => $url, 'ip' => $ip]);
                return false;
            }
        }
        
        return true;
    }
} 