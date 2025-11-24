<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Outbox extends Model
{
    use HasUuids;

    protected $table = 'outbox';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'provider_id',
        'user_id',
        'message_id',
        'subject',
        'from',
        'to',
        'cc',
        'bcc',
        'body_format',
        'body_content',
        'attachments',
        'status',
        'error_message',
        'provider_response',
        'sent_at',
        'delivered_at',
        // Enhanced tracking fields
        'source',
        'queue_name',
        'processing_method',
        'queue_processed',
        'queued_at',
        'processing_started_at',
        'bounced_at',
        'processing_time_ms',
        'delivery_time_ms',
        'retry_count',
        'metadata',
        'campaign_id',
        'template_id',
        'headers',
        'corrections',
        'bounce_reason',
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
        'provider_response' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'queued_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'bounced_at' => 'datetime',
        'queue_processed' => 'boolean',
        'metadata' => 'array',
        'headers' => 'array',
        'corrections' => 'array',
    ];

    protected $dates = [
        'sent_at',
        'delivered_at',
        'queued_at',
        'processing_started_at',
        'bounced_at',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'tenant_id');
    }

    public function provider()
    {
        return $this->belongsTo(EmailProvider::class, 'provider_id', 'provider_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'email_id', 'id')
            ->where('email_type', 'outbox');
    }

    // Scopes
    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProvider($query, $providerId)
    {
        return $this->where('provider_id', $providerId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeBounced($query)
    {
        return $query->where('status', 'bounced');
    }

    public function scopeSentBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('sent_at', [$startDate, $endDate]);
    }

    // Helper methods
    public function getRecipients()
    {
        return array_merge($this->to ?? [], $this->cc ?? [], $this->bcc ?? []);
    }

    public function hasAttachments()
    {
        return !empty($this->attachments);
    }

    public function getAttachmentCount()
    {
        return count($this->attachments ?? []);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSent()
    {
        return $this->status === 'sent';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isBounced()
    {
        return $this->status === 'bounced';
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsBounced($reason = null)
    {
        $this->update([
            'status' => 'bounced',
            'error_message' => $reason,
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'sent',
            'delivered_at' => now(),
        ]);
    }
}
