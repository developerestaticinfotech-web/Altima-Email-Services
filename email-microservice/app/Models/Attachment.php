<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attachment extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email_id',
        'email_type',
        'filename',
        'mime_type',
        'storage_path',
        'file_size',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    // Relationships
    public function email()
    {
        if ($this->email_type === 'inbox') {
            return $this->belongsTo(Inbox::class, 'email_id', 'id');
        } else {
            return $this->belongsTo(Outbox::class, 'email_id', 'id');
        }
    }

    // Scopes
    public function scopeByEmailType($query, $emailType)
    {
        return $query->where('email_type', $emailType);
    }

    public function scopeByMimeType($query, $mimeType)
    {
        return $query->where('mime_type', $mimeType);
    }

    public function scopeByFileSize($query, $minSize = null, $maxSize = null)
    {
        if ($minSize !== null) {
            $query->where('file_size', '>=', $minSize);
        }
        if ($maxSize !== null) {
            $query->where('file_size', '<=', $maxSize);
        }
        return $query;
    }

    // Helper methods
    public function isImage()
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    public function isDocument()
    {
        $documentTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        return in_array($this->mime_type, $documentTypes);
    }

    public function isArchive()
    {
        $archiveTypes = ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'];
        return in_array($this->mime_type, $archiveTypes);
    }

    public function getFileSizeInMB()
    {
        return round($this->file_size / 1024 / 1024, 2);
    }

    public function getFileSizeInKB()
    {
        return round($this->file_size / 1024, 2);
    }

    public function getStorageUrl()
    {
        if (strpos($this->storage_path, 'http') === 0) {
            return $this->storage_path;
        }
        
        // For local storage, you might want to generate a URL
        return asset('storage/' . $this->storage_path);
    }
}
