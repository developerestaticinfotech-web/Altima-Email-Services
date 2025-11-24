<?php

namespace App\Services;

use App\Models\Inbox;
use App\Models\Outbox;
use App\Models\Attachment;
use App\Models\Tenant;
use App\Models\EmailProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailProcessingService
{
    protected MimeParserService $mimeParser;
    protected FileStorageService $fileStorage;
    
    public function __construct(
        MimeParserService $mimeParser,
        FileStorageService $fileStorage
    ) {
        $this->mimeParser = $mimeParser;
        $this->fileStorage = $fileStorage;
    }
    
    /**
     * Process incoming email (from IMAP/POP3 or webhook)
     */
    public function processIncomingEmail(
        string $rawEmail,
        string $tenantId,
        string $providerId,
        string $userId = null,
        array $metadata = []
    ): array {
        try {
            DB::beginTransaction();
            
            // Parse the email
            $parsedEmail = $this->mimeParser->parseEmail($rawEmail);
            
            // Store raw email
            $rawEmailInfo = $this->fileStorage->storeRawEmail($rawEmail, Str::uuid());
            
            // Create inbox record
            $inbox = $this->createInboxRecord($parsedEmail, $tenantId, $providerId, $userId, $metadata);
            
            // Store attachments
            $storedAttachments = $this->storeEmailAttachments($parsedEmail['attachments'], $inbox->id);
            
            // Store inline images
            $storedInlineImages = $this->storeInlineImages($parsedEmail['inline_images'], $inbox->id);
            
            // Update inbox with attachment info
            $inbox->update([
                'attachments' => array_merge($storedAttachments, $storedInlineImages),
                'body_content' => $this->processBodyContent($parsedEmail, $storedInlineImages),
            ]);
            
            DB::commit();
            
            Log::info('Incoming email processed successfully', [
                'inbox_id' => $inbox->id,
                'subject' => $parsedEmail['subject'],
                'attachments_count' => count($storedAttachments),
                'inline_images_count' => count($storedInlineImages),
            ]);
            
            return [
                'success' => true,
                'inbox_id' => $inbox->id,
                'message_id' => $inbox->message_id,
                'subject' => $parsedEmail['subject'],
                'from' => $parsedEmail['from'],
                'attachments' => $storedAttachments,
                'inline_images' => $storedInlineImages,
                'raw_email_info' => $rawEmailInfo,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process incoming email', [
                'tenant_id' => $tenantId,
                'provider_id' => $providerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Process outbound email (for sending)
     */
    public function processOutboundEmail(
        array $emailData,
        string $tenantId,
        string $providerId,
        string $userId = null
    ): array {
        try {
            DB::beginTransaction();
            
            // Create outbox record
            $outbox = $this->createOutboxRecord($emailData, $tenantId, $providerId, $userId);
            
            // Store attachments if any
            $storedAttachments = [];
            if (!empty($emailData['attachments'])) {
                $storedAttachments = $this->storeOutboundAttachments($emailData['attachments'], $outbox->id);
            }
            
            // Update outbox with attachment info
            $outbox->update([
                'attachments' => $storedAttachments,
            ]);
            
            DB::commit();
            
            Log::info('Outbound email processed successfully', [
                'outbox_id' => $outbox->id,
                'subject' => $emailData['subject'],
                'attachments_count' => count($storedAttachments),
            ]);
            
            return [
                'success' => true,
                'outbox_id' => $outbox->id,
                'message_id' => $outbox->message_id,
                'attachments' => $storedAttachments,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process outbound email', [
                'tenant_id' => $tenantId,
                'provider_id' => $providerId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Create inbox record
     */
    protected function createInboxRecord(
        array $parsedEmail,
        string $tenantId,
        string $providerId,
        string $userId = null,
        array $metadata = []
    ): Inbox {
        return Inbox::create([
            'tenant_id' => $tenantId,
            'provider_id' => $providerId,
            'user_id' => $userId ?? 'system',
            'message_id' => $parsedEmail['message_id'] ?: Str::uuid(),
            'subject' => $parsedEmail['subject'],
            'from' => $parsedEmail['from'],
            'to' => $parsedEmail['to'],
            'cc' => $parsedEmail['cc'],
            'bcc' => $parsedEmail['bcc'],
            'body_format' => $this->determineBodyFormat($parsedEmail),
            'body_content' => $this->getBodyContent($parsedEmail),
            'attachments' => [],
            'received_at' => $parsedEmail['date'] ? date('Y-m-d H:i:s', strtotime($parsedEmail['date'])) : now(),
        ]);
    }
    
    /**
     * Create outbox record
     */
    protected function createOutboxRecord(
        array $emailData,
        string $tenantId,
        string $providerId,
        string $userId = null
    ): Outbox {
        return Outbox::create([
            'tenant_id' => $tenantId,
            'provider_id' => $providerId,
            'user_id' => $userId ?? 'system',
            'message_id' => Str::uuid(),
            'subject' => $emailData['subject'],
            'from' => $emailData['from'],
            'to' => $emailData['to'],
            'cc' => $emailData['cc'] ?? [],
            'bcc' => $emailData['bcc'] ?? [],
            'body_format' => $emailData['body_format'] ?? 'HTML',
            'body_content' => $emailData['body_content'],
            'attachments' => [],
            'status' => 'pending',
        ]);
    }
    
    /**
     * Store email attachments
     */
    protected function storeEmailAttachments(array $attachments, string $emailId): array
    {
        $storedAttachments = [];
        
        foreach ($attachments as $attachment) {
            try {
                $storedInfo = $this->fileStorage->storeAttachment(
                    $attachment['content'],
                    $attachment['filename'],
                    $attachment['content_type'],
                    $emailId
                );
                
                // Create attachment record
                $attachmentRecord = Attachment::create([
                    'email_id' => $emailId,
                    'email_type' => 'inbox',
                    'filename' => $attachment['filename'],
                    'mime_type' => $attachment['content_type'],
                    'storage_path' => $storedInfo['path'],
                    'file_size' => $attachment['size'],
                    'metadata' => [
                        'stored_filename' => $storedInfo['stored_filename'],
                        'hash' => $storedInfo['hash'],
                        'encoding' => $attachment['encoding'] ?? null,
                        'original_headers' => $attachment['headers'] ?? [],
                    ],
                ]);
                
                $storedAttachments[] = [
                    'attachment_id' => $attachmentRecord->id,
                    'filename' => $attachment['filename'],
                    'mime_type' => $attachment['content_type'],
                    'size' => $attachment['size'],
                    'storage_path' => $storedInfo['path'],
                    'url' => $storedInfo['url'],
                ];
                
            } catch (\Exception $e) {
                Log::error('Failed to store attachment', [
                    'email_id' => $emailId,
                    'filename' => $attachment['filename'],
                    'error' => $e->getMessage(),
                ]);
                
                // Continue with other attachments
                continue;
            }
        }
        
        return $storedAttachments;
    }
    
    /**
     * Store inline images
     */
    protected function storeInlineImages(array $inlineImages, string $emailId): array
    {
        $storedImages = [];
        
        foreach ($inlineImages as $image) {
            try {
                $storedInfo = $this->fileStorage->storeInlineImage(
                    $image['content'],
                    $image['content_id'],
                    $image['content_type'],
                    $emailId
                );
                
                // Create attachment record for inline image
                $attachmentRecord = Attachment::create([
                    'email_id' => $emailId,
                    'email_type' => 'inbox',
                    'filename' => $image['filename'],
                    'mime_type' => $image['content_type'],
                    'storage_path' => $storedInfo['path'],
                    'file_size' => $image['size'],
                    'metadata' => [
                        'content_id' => $image['content_id'],
                        'stored_filename' => $storedInfo['stored_filename'],
                        'hash' => $storedInfo['hash'],
                        'encoding' => $image['encoding'] ?? null,
                        'is_inline' => true,
                        'original_headers' => $image['headers'] ?? [],
                    ],
                ]);
                
                $storedImages[] = [
                    'attachment_id' => $attachmentRecord->id,
                    'content_id' => $image['content_id'],
                    'filename' => $image['filename'],
                    'mime_type' => $image['content_type'],
                    'size' => $image['size'],
                    'storage_path' => $storedInfo['path'],
                    'url' => $storedInfo['url'],
                ];
                
            } catch (\Exception $e) {
                Log::error('Failed to store inline image', [
                    'email_id' => $emailId,
                    'content_id' => $image['content_id'],
                    'error' => $e->getMessage(),
                ]);
                
                continue;
            }
        }
        
        return $storedImages;
    }
    
    /**
     * Store outbound attachments
     */
    protected function storeOutboundAttachments(array $attachments, string $emailId): array
    {
        $storedAttachments = [];
        
        foreach ($attachments as $attachment) {
            try {
                // For outbound, we expect attachment data to be base64 encoded or file path
                $content = $this->getAttachmentContent($attachment);
                
                $storedInfo = $this->fileStorage->storeAttachment(
                    $content,
                    $attachment['filename'],
                    $attachment['mime_type'],
                    $emailId
                );
                
                // Create attachment record
                $attachmentRecord = Attachment::create([
                    'email_id' => $emailId,
                    'email_type' => 'outbox',
                    'filename' => $attachment['filename'],
                    'mime_type' => $attachment['mime_type'],
                    'storage_path' => $storedInfo['path'],
                    'file_size' => $attachment['size'] ?? strlen($content),
                    'metadata' => [
                        'stored_filename' => $storedInfo['stored_filename'],
                        'hash' => $storedInfo['hash'],
                        'original_data' => $attachment,
                    ],
                ]);
                
                $storedAttachments[] = [
                    'attachment_id' => $attachmentRecord->id,
                    'filename' => $attachment['filename'],
                    'mime_type' => $attachment['mime_type'],
                    'size' => $attachment['size'] ?? strlen($content),
                    'storage_path' => $storedInfo['path'],
                    'url' => $storedInfo['url'],
                ];
                
            } catch (\Exception $e) {
                Log::error('Failed to store outbound attachment', [
                    'email_id' => $emailId,
                    'filename' => $attachment['filename'],
                    'error' => $e->getMessage(),
                ]);
                
                continue;
            }
        }
        
        return $storedAttachments;
    }
    
    /**
     * Get attachment content from various sources
     */
    protected function getAttachmentContent(array $attachment): string
    {
        if (isset($attachment['content'])) {
            return $attachment['content'];
        }
        
        if (isset($attachment['file_path']) && file_exists($attachment['file_path'])) {
            return file_get_contents($attachment['file_path']);
        }
        
        if (isset($attachment['base64_content'])) {
            return base64_decode($attachment['base64_content']);
        }
        
        throw new \Exception("No valid attachment content found");
    }
    
    /**
     * Determine body format
     */
    protected function determineBodyFormat(array $parsedEmail): string
    {
        if (!empty($parsedEmail['html_content'])) {
            return 'HTML';
        }
        
        if (!empty($parsedEmail['text_content'])) {
            return 'Text';
        }
        
        return 'EML';
    }
    
    /**
     * Get body content
     */
    protected function getBodyContent(array $parsedEmail): string
    {
        if (!empty($parsedEmail['html_content'])) {
            return $parsedEmail['html_content'];
        }
        
        if (!empty($parsedEmail['text_content'])) {
            return $parsedEmail['text_content'];
        }
        
        return $parsedEmail['raw_email'];
    }
    
    /**
     * Process body content to replace inline image references
     */
    protected function processBodyContent(array $parsedEmail, array $storedInlineImages): string
    {
        $bodyContent = $this->getBodyContent($parsedEmail);
        
        // Replace inline image references with stored URLs
        foreach ($storedInlineImages as $image) {
            $cid = $image['content_id'];
            $url = $image['url'];
            
            $bodyContent = str_replace(
                "cid:{$cid}",
                $url,
                $bodyContent
            );
        }
        
        return $bodyContent;
    }
    
    /**
     * Get processing statistics
     */
    public function getProcessingStats(): array
    {
        try {
            $inboxCount = Inbox::count();
            $outboxCount = Outbox::count();
            $attachmentCount = Attachment::count();
            
            $storageStats = $this->fileStorage->getStorageStats();
            
            return [
                'emails' => [
                    'inbox' => $inboxCount,
                    'outbox' => $outboxCount,
                    'total' => $inboxCount + $outboxCount,
                ],
                'attachments' => [
                    'total' => $attachmentCount,
                    'inbox' => Attachment::where('email_type', 'inbox')->count(),
                    'outbox' => Attachment::where('email_type', 'outbox')->count(),
                ],
                'storage' => $storageStats,
                'last_processed' => [
                    'inbox' => Inbox::latest('received_at')->first()?->received_at,
                    'outbox' => Outbox::latest('created_at')->first()?->created_at,
                ],
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to get processing stats', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
} 