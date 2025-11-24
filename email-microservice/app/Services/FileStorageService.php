<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileStorageService
{
    protected string $basePath;
    protected string $disk;
    
    public function __construct()
    {
        $this->disk = config('filesystems.default', 'local');
        $this->basePath = 'emails';
    }
    
    /**
     * Store email attachment
     */
    public function storeAttachment(string $content, string $filename, string $mimeType, string $emailId): array
    {
        try {
            $safeFilename = $this->sanitizeFilename($filename);
            $extension = $this->getFileExtension($mimeType);
            $uniqueFilename = $this->generateUniqueFilename($safeFilename, $extension);
            
            $path = $this->buildAttachmentPath($emailId, $uniqueFilename);
            
            // Store the file
            $stored = Storage::disk($this->disk)->put($path, $content);
            
            if (!$stored) {
                throw new \Exception("Failed to store attachment: {$filename}");
            }
            
            // Get file info
            $fileSize = Storage::disk($this->disk)->size($path);
            $fileHash = hash('sha256', $content);
            
            return [
                'filename' => $filename,
                'stored_filename' => $uniqueFilename,
                'path' => $path,
                'size' => $fileSize,
                'mime_type' => $mimeType,
                'hash' => $fileHash,
                'url' => $this->getFileUrl($path),
                'stored_at' => now()->toISOString(),
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to store attachment', [
                'filename' => $filename,
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Store raw EML file
     */
    public function storeRawEmail(string $rawEmail, string $emailId): array
    {
        try {
            $filename = "email_{$emailId}.eml";
            $path = $this->buildRawEmailPath($emailId, $filename);
            
            // Store the raw email
            $stored = Storage::disk($this->disk)->put($path, $rawEmail);
            
            if (!$stored) {
                throw new \Exception("Failed to store raw email: {$emailId}");
            }
            
            return [
                'filename' => $filename,
                'path' => $path,
                'size' => strlen($rawEmail),
                'mime_type' => 'message/rfc822',
                'hash' => hash('sha256', $rawEmail),
                'url' => $this->getFileUrl($path),
                'stored_at' => now()->toISOString(),
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to store raw email', [
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Store inline image
     */
    public function storeInlineImage(string $content, string $contentId, string $mimeType, string $emailId): array
    {
        try {
            $extension = $this->getFileExtension($mimeType);
            $filename = "inline_{$contentId}{$extension}";
            $path = $this->buildInlineImagePath($emailId, $filename);
            
            // Store the inline image
            $stored = Storage::disk($this->disk)->put($path, $content);
            
            if (!$stored) {
                throw new \Exception("Failed to store inline image: {$contentId}");
            }
            
            return [
                'content_id' => $contentId,
                'filename' => $filename,
                'path' => $path,
                'size' => strlen($content),
                'mime_type' => $mimeType,
                'hash' => hash('sha256', $content),
                'url' => $this->getFileUrl($path),
                'stored_at' => now()->toISOString(),
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to store inline image', [
                'content_id' => $contentId,
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Retrieve file content
     */
    public function retrieveFile(string $path): ?string
    {
        try {
            if (!Storage::disk($this->disk)->exists($path)) {
                return null;
            }
            
            return Storage::disk($this->disk)->get($path);
            
        } catch (\Exception $e) {
            Log::error('Failed to retrieve file', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }
    
    /**
     * Delete file
     */
    public function deleteFile(string $path): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->delete($path);
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to delete file', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Clean up email files
     */
    public function cleanupEmailFiles(string $emailId): bool
    {
        try {
            $emailPath = $this->buildEmailBasePath($emailId);
            
            // Delete all files in email directory
            $files = Storage::disk($this->disk)->files($emailPath);
            foreach ($files as $file) {
                Storage::disk($this->disk)->delete($file);
            }
            
            // Delete email directory
            Storage::disk($this->disk)->deleteDirectory($emailPath);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to cleanup email files', [
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Get file URL for web access
     */
    public function getFileUrl(string $path): string
    {
        if ($this->disk === 'public') {
            return Storage::disk($this->disk)->url($path);
        }
        
        // For local disk, return a route-based URL
        return route('email.file.download', ['path' => base64_encode($path)]);
    }
    
    /**
     * Build attachment storage path
     */
    protected function buildAttachmentPath(string $emailId, string $filename): string
    {
        return $this->buildEmailBasePath($emailId) . '/attachments/' . $filename;
    }
    
    /**
     * Build raw email storage path
     */
    protected function buildRawEmailPath(string $emailId, string $filename): string
    {
        return $this->buildEmailBasePath($emailId) . '/raw/' . $filename;
    }
    
    /**
     * Build inline image storage path
     */
    protected function buildInlineImagePath(string $emailId, string $filename): string
    {
        return $this->buildEmailBasePath($emailId) . '/inline/' . $filename;
    }
    
    /**
     * Build base path for email files
     */
    protected function buildEmailBasePath(string $emailId): string
    {
        return $this->basePath . '/' . $emailId;
    }
    
    /**
     * Sanitize filename for safe storage
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove or replace unsafe characters
        $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $safe = preg_replace('/_{2,}/', '_', $safe);
        $safe = trim($safe, '_');
        
        return $safe ?: 'unnamed_file';
    }
    
    /**
     * Get file extension from MIME type
     */
    protected function getFileExtension(string $mimeType): string
    {
        $mimeToExt = [
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp',
            'application/pdf' => '.pdf',
            'application/msword' => '.doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
            'application/vnd.ms-excel' => '.xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
            'text/plain' => '.txt',
            'text/html' => '.html',
            'message/rfc822' => '.eml',
        ];
        
        return $mimeToExt[$mimeType] ?? '';
    }
    
    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(string $filename, string $extension): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $timestamp = time();
        $random = Str::random(8);
        
        return "{$name}_{$timestamp}_{$random}{$extension}";
    }
    
    /**
     * Get storage statistics
     */
    public function getStorageStats(): array
    {
        try {
            $totalSize = 0;
            $fileCount = 0;
            
            $files = Storage::disk($this->disk)->allFiles($this->basePath);
            
            foreach ($files as $file) {
                $totalSize += Storage::disk($this->disk)->size($file);
                $fileCount++;
            }
            
            return [
                'total_files' => $fileCount,
                'total_size' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'disk' => $this->disk,
                'base_path' => $this->basePath,
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to get storage stats', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'total_files' => 0,
                'total_size' => 0,
                'total_size_mb' => 0,
                'disk' => $this->disk,
                'base_path' => $this->basePath,
                'error' => $e->getMessage(),
            ];
        }
    }
} 