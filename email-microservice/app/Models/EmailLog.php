<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailLog extends Model
{
    protected $fillable = [
        'message_id',
        'template_id',
        'to_email',
        'to_name',
        'cc_emails',
        'bcc_emails',
        'subject',
        'html_content',
        'text_content',
        'data',
        'status',
        'provider_message_id',
        'provider',
        'provider_response',
        'error_message',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'source',
        'headers',
        'attachments',
        'tracking',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
        'provider_response' => 'array',
        'headers' => 'array',
        'attachments' => 'array',
        'tracking' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    protected $dates = [
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
    ];

    /**
     * Get the template for this email log.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id', 'template_id');
    }

    /**
     * Get the webhooks for this email log.
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(EmailWebhook::class, 'message_id', 'message_id');
    }

    /**
     * Scope to get emails by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get emails by provider.
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to get emails by source.
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to get emails sent within a date range.
     */
    public function scopeSentBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('sent_at', [$startDate, $endDate]);
    }

    /**
     * Check if email was delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if email failed.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'bounced']);
    }

    /**
     * Check if email was opened.
     */
    public function isOpened(): bool
    {
        return !is_null($this->opened_at);
    }

    /**
     * Check if email was clicked.
     */
    public function isClicked(): bool
    {
        return !is_null($this->clicked_at);
    }

    /**
     * Get CC emails as array.
     */
    public function getCcEmailsArray(): array
    {
        return $this->cc_emails ? explode(',', $this->cc_emails) : [];
    }

    /**
     * Get BCC emails as array.
     */
    public function getBccEmailsArray(): array
    {
        return $this->bcc_emails ? explode(',', $this->bcc_emails) : [];
    }
}
