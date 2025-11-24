<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Tenant extends Model
{
    use HasUuids;

    protected $primaryKey = 'tenant_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_name',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // Relationships
    public function emailProviders()
    {
        return $this->hasMany(EmailProvider::class, 'tenant_id', 'tenant_id');
    }

    public function inboxEmails()
    {
        return $this->hasMany(Inbox::class, 'tenant_id', 'tenant_id');
    }

    public function outboxEmails()
    {
        return $this->hasMany(Outbox::class, 'tenant_id', 'tenant_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }
}
