<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InboundEmail extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'provider_id',
        'message_id',
        'in_reply_to',
        'references',
        'subject',
        'from_email',
        'from_name',
        'to_emails',
        'cc_emails',
        'bcc_emails',
        'body_format',
        'body_content',
        'attachments',
        'headers',
        'status',
        'priority',
        'is_reply',
        'is_forward',
        'is_auto_reply',
        'thread_id',
        'received_at',
        'processed_at',
        'delivered_at',
        'error_message',
        'provider_response',
        'metadata',
        'source',
        'queue_name',
        'queue_processed',
        'retry_count',
    ];

    protected $casts = [
        'to_emails' => 'array',
        'cc_emails' => 'array',
        'bcc_emails' => 'array',
        'attachments' => 'array',
        'headers' => 'array',
        'provider_response' => 'array',
        'metadata' => 'array',
        'is_reply' => 'boolean',
        'is_forward' => 'boolean',
        'is_auto_reply' => 'boolean',
        'queue_processed' => 'boolean',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the tenant that owns the inbound email.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Get the email provider that received the inbound email.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(EmailProvider::class, 'provider_id', 'provider_id');
    }

    /**
     * Get the outbound email this is replying to (if any).
     */
    public function repliedToOutbound(): BelongsTo
    {
        return $this->belongsTo(Outbox::class, 'in_reply_to', 'message_id');
    }

    /**
     * Scope to filter by tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter replies.
     */
    public function scopeReplies($query)
    {
        return $query->where('is_reply', true);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('received_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by thread.
     */
    public function scopeInThread($query, $threadId)
    {
        return $query->where('thread_id', $threadId);
    }

    /**
     * Check if this is a reply to an outbound email.
     */
    public function isReplyToOutbound(): bool
    {
        return !empty($this->in_reply_to) && $this->is_reply;
    }

    /**
     * Get the conversation thread for this email.
     */
    public function getConversationThread()
    {
        if ($this->thread_id) {
            return self::where('thread_id', $this->thread_id)
                ->orderBy('received_at')
                ->get();
        }
        
        return collect([$this]);
    }

    /**
     * Mark as processed.
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as queued.
     */
    public function markAsQueued(string $queueName = 'email.inbound'): void
    {
        $this->update([
            'status' => 'queued',
            'queue_name' => $queueName,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as delivered to CRM.
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Get the display name for the sender.
     */
    public function getSenderDisplayName(): string
    {
        return $this->from_name ?: $this->from_email;
    }

    /**
     * Get a preview of the email content.
     */
    public function getContentPreview(int $length = 100): string
    {
        $content = strip_tags($this->body_content);
        return Str::limit($content, $length);
    }

    /**
     * Check if this email has attachments.
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments) && count($this->attachments) > 0;
    }

    /**
     * Get the number of attachments.
     */
    public function getAttachmentCount(): int
    {
        return $this->hasAttachments() ? count($this->attachments) : 0;
    }
}