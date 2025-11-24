<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    protected $fillable = [
        'template_id',
        'name',
        'subject',
        'html_content',
        'text_content',
        'variables',
        'category',
        'language',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'variables' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'language' => 'en',
        'category' => 'general',
    ];

    /**
     * Get the email logs for this template.
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'template_id', 'template_id');
    }

    /**
     * Scope to get only active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get templates by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get templates by language.
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Get template variables as an array.
     */
    public function getVariablesArray(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Check if template has HTML content.
     */
    public function hasHtmlContent(): bool
    {
        return !empty($this->html_content);
    }

    /**
     * Check if template has text content.
     */
    public function hasTextContent(): bool
    {
        return !empty($this->text_content);
    }
}
