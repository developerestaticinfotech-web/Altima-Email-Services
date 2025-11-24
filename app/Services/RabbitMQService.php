<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Outbox;

class RabbitMQService
{
    private $config;
    private $isMockMode = true;

    public function __construct()
    {
        $this->config = config('rabbitmq') ?? [];
    }

    public function publishToQueue($queue, $data)
    {
        Log::info("Message published to mock queue: {$queue}");
        return true;
    }

    public function publishMessage($queue, $data)
    {
        Log::info("Message published to mock queue: {$queue}");
        return true;
    }

    public function getQueueStatus()
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error in getQueueStatus: ' . $e->getMessage());
            return [
                'connection_status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getQueueStats()
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error in getQueueStats: ' . $e->getMessage());
            return [
                'queues' => [
                    'email.send' => [
                        'messages' => 0,
                        'consumers' => 0,
                        'status' => 'error'
                    ],
                    'email.sync.user' => [
                        'messages' => 0,
                        'consumers' => 0,
                        'status' => 'error'
                    ],
                    'connection' => [
                        'host' => $this->config['host'] ?? 'localhost',
                        'port' => $this->config['port'] ?? 5672,
                        'status' => 'error'
                    ]
                ],
                'outbox_stats' => [
                    'total_pending' => 0,
                    'total_sent' => 0,
                    'total_failed' => 0,
                    'total' => 0
                ]
            ];
        }
    }

    public function processQueue($queueName = 'email.send', $maxMessages = 10)
    {
        try {
            $pendingEmails = collect();
            try {
                $pendingEmails = Outbox::where('status', 'pending')->limit($maxMessages)->get();
            } catch (\Exception $e) {
                Log::warning('Could not get pending emails: ' . $e->getMessage());
                return 0;
            }
            
            $processed = 0;

            foreach ($pendingEmails as $email) {
                try {
                    $email->update(['status' => 'sent']);
                    $processed++;
                    Log::info("Processed email ID: {$email->id} from queue: {$queueName}");
                } catch (\Exception $e) {
                    Log::error("Failed to process email ID {$email->id}: " . $e->getMessage());
                }
            }

            Log::info("Processed {$processed} messages from mock queue: {$queueName}");
            return $processed;
        } catch (\Exception $e) {
            Log::error('Error in processQueue: ' . $e->getMessage());
            return 0;
        }
    }
}

