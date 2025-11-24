<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmailProvider extends Model
{
    use HasUuids;

    protected $primaryKey = 'provider_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'provider_name',
        'config_json',
        'bounce_email',
        'header_overrides',
        'is_active',
    ];

    protected $casts = [
        'config_json' => 'array',
        'header_overrides' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'tenant_id');
    }

    public function inboxEmails()
    {
        return $this->hasMany(Inbox::class, 'provider_id', 'provider_id');
    }

    public function outboxEmails()
    {
        return $this->hasMany(Outbox::class, 'provider_id', 'provider_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByProviderName($query, $providerName)
    {
        return $query->where('provider_name', $providerName);
    }

    // Helper methods
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config_json;
        }
        
        return data_get($this->config_json, $key);
    }

    public function setConfig($key, $value)
    {
        $config = $this->config_json ?? [];
        $config[$key] = $value;
        $this->update(['config_json' => $config]);
    }

    public function getHeaderOverrides($key = null)
    {
        if ($key === null) {
            return $this->header_overrides;
        }
        
        return data_get($this->header_overrides, $key);
    }

    public function setHeaderOverrides($key, $value)
    {
        $overrides = $this->header_overrides ?? [];
        $overrides[$key] = $value;
        $this->update(['header_overrides' => $overrides]);
    }

    public function isProvider($providerName)
    {
        return strtolower($this->provider_name) === strtolower($providerName);
    }
}
