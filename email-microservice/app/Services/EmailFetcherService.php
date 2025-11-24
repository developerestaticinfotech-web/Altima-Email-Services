<?php

namespace App\Services;

use App\Models\InboundEmail;
use App\Models\EmailProvider;
use App\Models\Outbox;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailFetcherService
{
    protected $rabbitMQService;

    public function __construct(RabbitMQService $rabbitMQService)
    {
        $this->rabbitMQService = $rabbitMQService;
    }

    /**
     * Fetch emails from all active providers with IMAP/POP3 configuration
     */
    public function fetchAllInboundEmails(): array
    {
        $results = [];
        $providers = EmailProvider::where('is_active', true)->get();

        foreach ($providers as $provider) {
            // Check if provider has IMAP/POP3 configuration
            if (!$this->hasInboundConfiguration($provider)) {
                Log::info('Skipping provider without IMAP/POP3 configuration', [
                    'provider_name' => $provider->provider_name,
                    'provider_id' => $provider->provider_id
                ]);
                
                $results[$provider->provider_id] = [
                    'success' => true,
                    'skipped' => true,
                    'reason' => 'No IMAP/POP3 configuration',
                    'emails_fetched' => 0,
                    'emails_processed' => 0,
                    'provider_name' => $provider->provider_name
                ];
                continue;
            }

            try {
                $result = $this->fetchEmailsForProvider($provider);
                $results[$provider->provider_id] = $result;
            } catch (\Exception $e) {
                Log::error('Error fetching emails for provider: ' . $provider->provider_name, [
                    'provider_id' => $provider->provider_id,
                    'error' => $e->getMessage()
                ]);
                
                $results[$provider->provider_id] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'emails_fetched' => 0,
                    'emails_processed' => 0,
                    'provider_name' => $provider->provider_name
                ];
            }
        }

        return $results;
    }

    /**
     * Check if provider has IMAP/POP3 configuration
     */
    protected function hasInboundConfiguration(EmailProvider $provider): bool
    {
        $config = $provider->config_json;
        
        // Check for IMAP configuration
        if (isset($config['protocol']) && $config['protocol'] === 'imap') {
            return isset($config['imap_host']) && 
                   isset($config['imap_username']) && 
                   isset($config['imap_password']);
        }
        
        // Check for POP3 configuration
        if (isset($config['protocol']) && $config['protocol'] === 'pop3') {
            return isset($config['pop3_host']) && 
                   isset($config['pop3_username']) && 
                   isset($config['pop3_password']);
        }
        
        // Check for legacy IMAP configuration (without protocol field)
        if (isset($config['imap_host']) && 
            isset($config['imap_username']) && 
            isset($config['imap_password'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Fetch emails for a specific provider
     */
    public function fetchEmailsForProvider(EmailProvider $provider): array
    {
        $config = $provider->config_json;
        $emailsFetched = 0;
        $emailsProcessed = 0;

        try {
            // Determine the email protocol
            $protocol = $config['protocol'] ?? 'imap';
            
            if ($protocol === 'imap') {
                $emails = $this->fetchViaIMAP($provider, $config);
            } elseif ($protocol === 'pop3') {
                $emails = $this->fetchViaPOP3($provider, $config);
            } else {
                throw new \Exception("Unsupported protocol: {$protocol}");
            }

            $emailsFetched = count($emails);

            // Process each email
            foreach ($emails as $emailData) {
                try {
                    $this->processInboundEmail($provider, $emailData);
                    $emailsProcessed++;
                } catch (\Exception $e) {
                    Log::error('Error processing inbound email', [
                        'provider_id' => $provider->provider_id,
                        'message_id' => $emailData['message_id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Email fetching completed for provider', [
                'provider_name' => $provider->provider_name,
                'emails_fetched' => $emailsFetched,
                'emails_processed' => $emailsProcessed
            ]);

            return [
                'success' => true,
                'emails_fetched' => $emailsFetched,
                'emails_processed' => $emailsProcessed,
                'provider_name' => $provider->provider_name
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching emails via ' . $protocol, [
                'provider_id' => $provider->provider_id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'emails_fetched' => $emailsFetched,
                'emails_processed' => $emailsProcessed
            ];
        }
    }

    /**
     * Fetch emails via IMAP
     */
    protected function fetchViaIMAP(EmailProvider $provider, array $config): array
    {
        $host = $config['imap_host'] ?? $config['host'] ?? 'imap.gmail.com';
        $port = $config['imap_port'] ?? $config['port'] ?? 993;
        $username = $config['imap_username'] ?? $config['username'] ?? $config['email'];
        $password = $config['imap_password'] ?? $config['password'];
        $encryption = $config['imap_encryption'] ?? $config['encryption'] ?? 'ssl';
        $folder = $config['folder'] ?? 'INBOX';

        // Build connection string
        $connectionString = "{{$host}:{$port}/{$encryption}/novalidate-cert}";
        if ($folder !== 'INBOX') {
            $connectionString .= $folder;
        }

        $emails = [];

        try {
            $connection = \imap_open($connectionString, $username, $password);
            
            if (!$connection) {
                throw new \Exception('Failed to connect to IMAP server: ' . \imap_last_error());
            }

            // Get message count
            $messageCount = \imap_num_msg($connection);
            
            if ($messageCount === 0) {
                \imap_close($connection);
                return $emails;
            }

            // Fetch recent emails (last 50 messages)
            $start = max(1, $messageCount - 49);
            
            for ($i = $start; $i <= $messageCount; $i++) {
                try {
                    $emailData = $this->parseIMAPMessage($connection, $i);
                    if ($emailData) {
                        $emails[] = $emailData;
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing IMAP message', [
                        'message_number' => $i,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            \imap_close($connection);

        } catch (\Exception $e) {
            throw new \Exception("IMAP connection failed: " . $e->getMessage());
        }

        return $emails;
    }

    /**
     * Fetch emails via POP3
     */
    protected function fetchViaPOP3(EmailProvider $provider, array $config): array
    {
        $host = $config['pop3_host'] ?? $config['host'] ?? 'pop.gmail.com';
        $port = $config['pop3_port'] ?? $config['port'] ?? 995;
        $username = $config['username'] ?? $config['email'];
        $password = $config['password'];
        $encryption = $config['encryption'] ?? 'ssl';

        $emails = [];

        try {
            $connectionString = "{{$host}:{$port}/{$encryption}/novalidate-cert}";
            $connection = \imap_open($connectionString, $username, $password);
            
            if (!$connection) {
                throw new \Exception('Failed to connect to POP3 server: ' . \imap_last_error());
            }

            $messageCount = \imap_num_msg($connection);
            
            if ($messageCount === 0) {
                \imap_close($connection);
                return $emails;
            }

            // Fetch recent emails (last 20 messages for POP3)
            $start = max(1, $messageCount - 19);
            
            for ($i = $start; $i <= $messageCount; $i++) {
                try {
                    $emailData = $this->parseIMAPMessage($connection, $i);
                    if ($emailData) {
                        $emails[] = $emailData;
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing POP3 message', [
                        'message_number' => $i,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            \imap_close($connection);

        } catch (\Exception $e) {
            throw new \Exception("POP3 connection failed: " . $e->getMessage());
        }

        return $emails;
    }

    /**
     * Parse IMAP/POP3 message
     */
    protected function parseIMAPMessage($connection, int $messageNumber): ?array
    {
        try {
            // Get message header
            $header = \imap_headerinfo($connection, $messageNumber);
            
            if (!$header) {
                return null;
            }

            // Get message body
            $body = \imap_body($connection, $messageNumber);
            
            // Get message structure
            $structure = \imap_fetchstructure($connection, $messageNumber);
            
            // Extract basic information
            $messageId = $header->message_id ?? 'unknown-' . $messageNumber;
            $subject = $header->subject ?? 'No Subject';
            $from = $header->from[0] ?? null;
            $to = $header->to ?? [];
            $cc = $header->cc ?? [];
            $bcc = $header->bcc ?? [];
            $date = $header->date ?? date('r');
            $inReplyTo = $header->in_reply_to ?? null;
            $references = $header->references ?? null;

            // Convert to arrays
            $toEmails = [];
            foreach ($to as $recipient) {
                $toEmails[] = $recipient->mailbox . '@' . $recipient->host;
            }

            $ccEmails = [];
            foreach ($cc as $recipient) {
                $ccEmails[] = $recipient->mailbox . '@' . $recipient->host;
            }

            $bccEmails = [];
            foreach ($bcc as $recipient) {
                $bccEmails[] = $recipient->mailbox . '@' . $recipient->host;
            }

            // Determine if this is a reply
            $isReply = !empty($inReplyTo) || !empty($references);

            // Generate thread ID
            $threadId = $inReplyTo ?: $messageId;

            return [
                'message_id' => $messageId,
                'in_reply_to' => $inReplyTo,
                'references' => $references,
                'subject' => $subject,
                'from_email' => $from ? ($from->mailbox . '@' . $from->host) : 'unknown@unknown.com',
                'from_name' => $from ? ($from->personal ?? '') : '',
                'to_emails' => $toEmails,
                'cc_emails' => $ccEmails,
                'bcc_emails' => $bccEmails,
                'body_format' => 'Text',
                'body_content' => $body,
                'is_reply' => $isReply,
                'thread_id' => $threadId,
                'received_at' => Carbon::parse($date),
                'headers' => $this->extractHeaders($header),
                'attachments' => $this->extractAttachments($connection, $messageNumber, $structure)
            ];

        } catch (\Exception $e) {
            Log::warning('Error parsing message', [
                'message_number' => $messageNumber,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extract email headers
     */
    protected function extractHeaders($header): array
    {
        $headers = [];
        
        if (isset($header->message_id)) {
            $headers['Message-ID'] = $header->message_id;
        }
        if (isset($header->in_reply_to)) {
            $headers['In-Reply-To'] = $header->in_reply_to;
        }
        if (isset($header->references)) {
            $headers['References'] = $header->references;
        }
        if (isset($header->subject)) {
            $headers['Subject'] = $header->subject;
        }
        if (isset($header->date)) {
            $headers['Date'] = $header->date;
        }

        return $headers;
    }

    /**
     * Extract attachments
     */
    protected function extractAttachments($connection, int $messageNumber, $structure): array
    {
        $attachments = [];

        if (isset($structure->parts) && is_array($structure->parts)) {
            foreach ($structure->parts as $partNumber => $part) {
                if (isset($part->disposition) && $part->disposition === 'attachment') {
                    $filename = $part->dparameters[0]->value ?? 'attachment';
                    $attachments[] = [
                        'filename' => $filename,
                        'size' => $part->bytes ?? 0,
                        'type' => $part->subtype ?? 'unknown'
                    ];
                }
            }
        }

        return $attachments;
    }

    /**
     * Process and save inbound email
     */
    protected function processInboundEmail(EmailProvider $provider, array $emailData): void
    {
        // Check if email already exists
        $existingEmail = InboundEmail::where('message_id', $emailData['message_id'])->first();
        
        if ($existingEmail) {
            Log::info('Email already exists, skipping', [
                'message_id' => $emailData['message_id']
            ]);
            return;
        }

        // Check if this is a reply to an outbound email
        $repliedToOutbound = null;
        if ($emailData['is_reply'] && !empty($emailData['in_reply_to'])) {
            $repliedToOutbound = Outbox::where('message_id', $emailData['in_reply_to'])->first();
        }

        // Create inbound email record
        $inboundEmail = InboundEmail::create([
            'tenant_id' => $provider->tenant_id,
            'provider_id' => $provider->provider_id,
            'message_id' => $emailData['message_id'],
            'in_reply_to' => $emailData['in_reply_to'],
            'references' => $emailData['references'],
            'subject' => $emailData['subject'],
            'from_email' => $emailData['from_email'],
            'from_name' => $emailData['from_name'],
            'to_emails' => $emailData['to_emails'],
            'cc_emails' => $emailData['cc_emails'],
            'bcc_emails' => $emailData['bcc_emails'],
            'body_format' => $emailData['body_format'],
            'body_content' => $emailData['body_content'],
            'attachments' => $emailData['attachments'],
            'headers' => $emailData['headers'],
            'is_reply' => $emailData['is_reply'],
            'is_forward' => false, // Could be enhanced to detect forwards
            'is_auto_reply' => $this->isAutoReply($emailData),
            'thread_id' => $emailData['thread_id'],
            'received_at' => $emailData['received_at'],
            'source' => 'imap'
        ]);

        // Publish to RabbitMQ queue
        $this->publishToInboundQueue($inboundEmail);

        Log::info('Inbound email processed and queued', [
            'email_id' => $inboundEmail->id,
            'message_id' => $inboundEmail->message_id,
            'is_reply' => $inboundEmail->is_reply,
            'replied_to_outbound' => $repliedToOutbound ? $repliedToOutbound->id : null
        ]);
    }

    /**
     * Check if email is an auto-reply
     */
    protected function isAutoReply(array $emailData): bool
    {
        $subject = strtolower($emailData['subject'] ?? '');
        $body = strtolower($emailData['body_content'] ?? '');
        
        $autoReplyIndicators = [
            'auto-reply', 'automatic reply', 'out of office', 'vacation',
            'away message', 'autoresponder', 'do not reply'
        ];

        foreach ($autoReplyIndicators as $indicator) {
            if (strpos($subject, $indicator) !== false || strpos($body, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Publish inbound email to RabbitMQ queue
     */
    protected function publishToInboundQueue(InboundEmail $inboundEmail): bool
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
