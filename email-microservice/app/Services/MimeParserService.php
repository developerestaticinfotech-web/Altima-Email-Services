<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MimeParserService
{
    /**
     * Parse a raw email (EML file or MIME message)
     */
    public function parseEmail(string $rawEmail): array
    {
        try {
            // Parse headers and body
            $parts = $this->parseHeadersAndBody($rawEmail);
            
            // Parse MIME structure
            $mimeStructure = $this->parseMimeStructure($parts['body'], $parts['headers']);
            
            // Extract attachments
            $attachments = $this->extractAttachments($mimeStructure);
            
            // Extract inline images
            $inlineImages = $this->extractInlineImages($mimeStructure);
            
            // Get text and HTML content
            $textContent = $this->extractTextContent($mimeStructure);
            $htmlContent = $this->extractHtmlContent($mimeStructure);
            
            return [
                'headers' => $parts['headers'],
                'subject' => $parts['headers']['subject'] ?? '',
                'from' => $parts['headers']['from'] ?? '',
                'to' => $this->parseEmailAddresses($parts['headers']['to'] ?? ''),
                'cc' => $this->parseEmailAddresses($parts['headers']['cc'] ?? ''),
                'bcc' => $this->parseEmailAddresses($parts['headers']['bcc'] ?? ''),
                'date' => $parts['headers']['date'] ?? '',
                'message_id' => $parts['headers']['message-id'] ?? '',
                'text_content' => $textContent,
                'html_content' => $htmlContent,
                'attachments' => $attachments,
                'inline_images' => $inlineImages,
                'mime_structure' => $mimeStructure,
                'raw_email' => $rawEmail,
            ];
            
        } catch (\Exception $e) {
            Log::error('MIME parsing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new \Exception("Failed to parse email: {$e->getMessage()}");
        }
    }
    
    /**
     * Parse email headers and body
     */
    protected function parseHeadersAndBody(string $rawEmail): array
    {
        $lines = explode("\n", $rawEmail);
        $headers = [];
        $body = '';
        $inHeaders = true;
        
        foreach ($lines as $line) {
            if ($inHeaders) {
                if (trim($line) === '') {
                    $inHeaders = false;
                    continue;
                }
                
                if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                    $headerName = strtolower(trim($matches[1]));
                    $headerValue = trim($matches[2]);
                    
                    if (isset($headers[$headerName])) {
                        if (is_array($headers[$headerName])) {
                            $headers[$headerName][] = $headerValue;
                        } else {
                            $headers[$headerName] = [$headers[$headerName], $headerValue];
                        }
                    } else {
                        $headers[$headerName] = $headerValue;
                    }
                } elseif (isset($headerName)) {
                    // Multi-line header value
                    $headers[$headerName] .= ' ' . trim($line);
                }
            } else {
                $body .= $line . "\n";
            }
        }
        
        return [
            'headers' => $headers,
            'body' => $body,
        ];
    }
    
    /**
     * Parse MIME structure recursively
     */
    protected function parseMimeStructure(string $body, array $headers): array
    {
        $contentType = $headers['content-type'] ?? 'text/plain';
        $boundary = $this->extractBoundary($contentType);
        
        if (!$boundary) {
            // Single part message
            return [
                'type' => $this->extractMimeType($contentType),
                'subtype' => $this->extractMimeSubtype($contentType),
                'encoding' => $headers['content-transfer-encoding'] ?? '7bit',
                'content' => $body,
                'headers' => $headers,
                'parts' => [],
            ];
        }
        
        // Multi-part message
        $parts = $this->splitByBoundary($body, $boundary);
        $mimeParts = [];
        
        foreach ($parts as $part) {
            if (trim($part) === '--' . $boundary || trim($part) === '--' . $boundary . '--') {
                continue;
            }
            
            $partData = $this->parseHeadersAndBody($part);
            $mimeParts[] = $this->parseMimeStructure($partData['body'], $partData['headers']);
        }
        
        return [
            'type' => $this->extractMimeType($contentType),
            'subtype' => $this->extractMimeSubtype($contentType),
            'encoding' => $headers['content-transfer-encoding'] ?? '7bit',
            'boundary' => $boundary,
            'content' => $body,
            'headers' => $headers,
            'parts' => $mimeParts,
        ];
    }
    
    /**
     * Extract boundary from Content-Type header
     */
    protected function extractBoundary(string $contentType): ?string
    {
        if (preg_match('/boundary="?([^";\s]+)"?/', $contentType, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Extract MIME type from Content-Type header
     */
    protected function extractMimeType(string $contentType): string
    {
        if (preg_match('/^([^\/;]+)/', $contentType, $matches)) {
            return strtolower(trim($matches[1]));
        }
        return 'text';
    }
    
    /**
     * Extract MIME subtype from Content-Type header
     */
    protected function extractMimeSubtype(string $contentType): string
    {
        if (preg_match('/\/([^;]+)/', $contentType, $matches)) {
            return strtolower(trim($matches[1]));
        }
        return 'plain';
    }
    
    /**
     * Split body by MIME boundary
     */
    protected function splitByBoundary(string $body, string $boundary): array
    {
        $boundaryPattern = '/--' . preg_quote($boundary, '/') . '(?:--)?\r?\n/';
        return preg_split($boundaryPattern, $body);
    }
    
    /**
     * Extract attachments from MIME structure
     */
    protected function extractAttachments(array $mimeStructure): array
    {
        $attachments = [];
        $this->findAttachments($mimeStructure, $attachments);
        return $attachments;
    }
    
    /**
     * Recursively find attachments in MIME structure
     */
    protected function findAttachments(array $mimeStructure, array &$attachments): void
    {
        if (isset($mimeStructure['headers']['content-disposition'])) {
            $disposition = $mimeStructure['headers']['content-disposition'];
            
            if (strpos($disposition, 'attachment') !== false) {
                $filename = $this->extractFilename($mimeStructure['headers']);
                $content = $this->decodeContent($mimeStructure['content'], $mimeStructure['encoding']);
                
                $attachments[] = [
                    'filename' => $filename,
                    'content_type' => $mimeStructure['type'] . '/' . $mimeStructure['subtype'],
                    'content' => $content,
                    'size' => strlen($content),
                    'encoding' => $mimeStructure['encoding'],
                    'headers' => $mimeStructure['headers'],
                ];
            }
        }
        
        // Recursively check parts
        foreach ($mimeStructure['parts'] as $part) {
            $this->findAttachments($part, $attachments);
        }
    }
    
    /**
     * Extract inline images from MIME structure
     */
    protected function extractInlineImages(array $mimeStructure): array
    {
        $inlineImages = [];
        $this->findInlineImages($mimeStructure, $inlineImages);
        return $inlineImages;
    }
    
    /**
     * Recursively find inline images in MIME structure
     */
    protected function findInlineImages(array $mimeStructure, array &$inlineImages): void
    {
        if (isset($mimeStructure['headers']['content-disposition'])) {
            $disposition = $mimeStructure['headers']['content-disposition'];
            
            if (strpos($disposition, 'inline') !== false) {
                $contentId = $this->extractContentId($mimeStructure['headers']);
                $filename = $this->extractFilename($mimeStructure['headers']);
                $content = $this->decodeContent($mimeStructure['content'], $mimeStructure['encoding']);
                
                $inlineImages[] = [
                    'content_id' => $contentId,
                    'filename' => $filename,
                    'content_type' => $mimeStructure['type'] . '/' . $mimeStructure['subtype'],
                    'content' => $content,
                    'size' => strlen($content),
                    'encoding' => $mimeStructure['encoding'],
                    'headers' => $mimeStructure['headers'],
                ];
            }
        }
        
        // Recursively check parts
        foreach ($mimeStructure['parts'] as $part) {
            $this->findInlineImages($part, $inlineImages);
        }
    }
    
    /**
     * Extract text content from MIME structure
     */
    protected function extractTextContent(array $mimeStructure): string
    {
        return $this->findContentByType($mimeStructure, 'text/plain');
    }
    
    /**
     * Extract HTML content from MIME structure
     */
    protected function extractHtmlContent(array $mimeStructure): string
    {
        return $this->findContentByType($mimeStructure, 'text/html');
    }
    
    /**
     * Find content by MIME type in structure
     */
    protected function findContentByType(array $mimeStructure, string $targetType): string
    {
        $currentType = $mimeStructure['type'] . '/' . $mimeStructure['subtype'];
        
        if ($currentType === $targetType) {
            return $this->decodeContent($mimeStructure['content'], $mimeStructure['encoding']);
        }
        
        // Check parts
        foreach ($mimeStructure['parts'] as $part) {
            $content = $this->findContentByType($part, $targetType);
            if ($content !== '') {
                return $content;
            }
        }
        
        return '';
    }
    
    /**
     * Extract filename from headers
     */
    protected function extractFilename(array $headers): string
    {
        if (isset($headers['content-disposition'])) {
            if (preg_match('/filename="?([^";\s]+)"?/', $headers['content-disposition'], $matches)) {
                return $matches[1];
            }
        }
        
        if (isset($headers['content-type'])) {
            if (preg_match('/name="?([^";\s]+)"?/', $headers['content-type'], $matches)) {
                return $matches[1];
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Extract Content-ID from headers
     */
    protected function extractContentId(array $headers): ?string
    {
        if (isset($headers['content-id'])) {
            return trim($headers['content-id'], '<>');
        }
        return null;
    }
    
    /**
     * Parse email addresses from header
     */
    protected function parseEmailAddresses(string $addresses): array
    {
        $parsed = [];
        $addressList = explode(',', $addresses);
        
        foreach ($addressList as $address) {
            $address = trim($address);
            if (preg_match('/"?([^"<]+)"?\s*<([^>]+)>/', $address, $matches)) {
                $parsed[] = [
                    'name' => trim($matches[1]),
                    'email' => trim($matches[2]),
                ];
            } elseif (preg_match('/^([^@\s]+@[^@\s]+\.[^@\s]+)$/', $address, $matches)) {
                $parsed[] = [
                    'name' => '',
                    'email' => trim($matches[1]),
                ];
            }
        }
        
        return $parsed;
    }
    
    /**
     * Decode content based on encoding
     */
    protected function decodeContent(string $content, string $encoding): string
    {
        switch (strtolower($encoding)) {
            case 'base64':
                return base64_decode($content);
            case 'quoted-printable':
                return quoted_printable_decode($content);
            case '7bit':
            case '8bit':
            default:
                return $content;
        }
    }
} 