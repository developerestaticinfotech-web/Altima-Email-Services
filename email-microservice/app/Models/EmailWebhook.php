<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailWebhook extends Model
{
    protected $fillable = [
        'event_type',
        'recipient',
        'message_id',
        'provider_message_id',
        'provider',
        'reason',
        'bounce_type',
        'bounce_sub_type',
        'complaint_feedback_type',
        'complaint_user_agent',
        'event_timestamp',
        'raw_data',
        'metadata',
        'ip_address',
        'user_agent',
        'processed',
        'processed_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'metadata' => 'array',
        'processed' => 'boolean',
        'event_timestamp' => 'datetime',
        'processed_at' => 'datetime',
    ];

    protected $dates = [
        'event_timestamp',
        'processed_at',
    ];

    /**
     * Get the email log for this webhook.
     */
    public function emailLog(): BelongsTo
    {
        return $this->belongsTo(EmailLog::class, 'message_id', 'message_id');
    }

    /**
     * Scope to get unprocessed webhooks.
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope to get webhooks by event type.
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to get webhooks by provider.
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to get webhooks within a date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_timestamp', [$startDate, $endDate]);
    }

    /**
     * Check if webhook is a bounce event.
     */
    public function isBounce(): bool
    {
        return $this->event_type === 'Bounce';
    }

    /**
     * Check if webhook is a complaint event.
     */
    public function isComplaint(): bool
    {
        return $this->event_type === 'Complaint';
    }

    /**
     * Check if webhook is a delivery event.
     */
    public function isDelivery(): bool
    {
        return $this->event_type === 'Delivery';
    }

    /**
     * Check if webhook is an open event.
     */
    public function isOpen(): bool
    {
        return $this->event_type === 'Open';
    }

    /**
     * Check if webhook is a click event.
     */
    public function isClick(): bool
    {
        return $this->event_type === 'Click';
    }

    /**
     * Check if bounce is permanent.
     */
    public function isPermanentBounce(): bool
    {
        return $this->isBounce() && $this->bounce_type === 'Permanent';
    }

    /**
     * Check if bounce is transient.
     */
    public function isTransientBounce(): bool
    {
        return $this->isBounce() && $this->bounce_type === 'Transient';
    }

    /**
     * Mark webhook as processed.
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
        ]);
    }
}
