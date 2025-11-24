<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Inbox extends Model
{
    use HasUuids;

    protected $table = 'inbox';
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
        'received_at',
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
        'received_at' => 'datetime',
    ];

    protected $dates = [
        'received_at',
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

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'email_id', 'id')
            ->where('email_type', 'inbox');
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
        return $query->where('provider_id', $providerId);
    }

    public function scopeByBodyFormat($query, $format)
    {
        return $query->where('body_format', $format);
    }

    public function scopeReceivedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('received_at', [$startDate, $endDate]);
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

    public function isHtml()
    {
        return strtoupper($this->body_format) === 'HTML';
    }

    public function isText()
    {
        return strtoupper($this->body_format) === 'TEXT';
    }

    public function isEm()
    {
        return strtoupper($this->body_format) === 'EML';
    }

    public function isJson()
    {
        return strtoupper($this->body_format) === 'JSON';
    }
}
