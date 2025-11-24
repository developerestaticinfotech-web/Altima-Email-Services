<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Aws\Ses\SesClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailService
{
    protected ?SesClient $sesClient = null;
    protected string $fromEmail;
    protected string $fromName;
    protected string $bounceDomain;
    protected string $unsubscribeDomain;

    public function __construct()
    {
        $this->fromEmail = config('mail.from.address');
        $this->fromName = config('mail.from.name');
        $this->bounceDomain = config('mail.bounce_domain', 'bounce.mailer.broker.com');
        $this->unsubscribeDomain = config('mail.unsubscribe_domain', 'unsubscribe.mailer.broker.com');
    }

    /**
     * Initialize AWS SES client when needed.
     */
    protected function initializeSESClient(): void
    {
        if ($this->sesClient === null) {
            $accessKey = config('aws.access_key_id');
            $secretKey = config('aws.secret_access_key');
            
            if (empty($accessKey) || empty($secretKey)) {
                throw new \Exception('AWS credentials not configured. Please set AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY in your .env file.');
            }
            
            $this->sesClient = new SesClient([
                'version' => '2010-12-01',
                'region' => config('aws.default_region', 'us-east-1'),
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);
        }
    }

    /**
     * Send email using template and data.
     */
    public function sendEmail(array $emailData): array
    {
        try {
            // Validate required fields
            $this->validateEmailData($emailData);

            // Get template
            $template = $this->getTemplate($emailData['template_id']);
            if (!$template) {
                throw new \Exception("Template '{$emailData['template_id']}' not found");
            }

            // Generate message ID
            $messageId = (string) Str::uuid();

            // Create email log entry
            $emailLog = $this->createEmailLog($emailData, $template, $messageId);

            // Render template content
            $renderedContent = $this->renderTemplate($template, $emailData['data'] ?? []);

            // Prepare email content
            $emailContent = $this->prepareEmailContent($emailData, $renderedContent, $messageId);

            // Send email via AWS SES
            $result = $this->sendViaSES($emailContent);

            // Update email log with success
            $this->updateEmailLogSuccess($emailLog, $result);

            return [
                'success' => true,
                'message_id' => $messageId,
                'provider_message_id' => $result['MessageId'] ?? null,
                'status' => 'sent',
            ];

        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'error' => $e->getMessage(),
                'email_data' => $emailData,
                'trace' => $e->getTraceAsString(),
            ]);

            // Update email log with failure
            if (isset($emailLog)) {
                $this->updateEmailLogFailure($emailLog, $e->getMessage());
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ];
        }
    }

    /**
     * Validate email data.
     */
    protected function validateEmailData(array $emailData): void
    {
        $required = ['template_id', 'to'];
        foreach ($required as $field) {
            if (empty($emailData[$field])) {
                throw new \Exception("Required field '{$field}' is missing");
            }
        }

        // Validate 'to' field structure
        if (is_array($emailData['to'])) {
            foreach ($emailData['to'] as $recipient) {
                if (empty($recipient['email'])) {
                    throw new \Exception("Invalid recipient structure: email is required");
                }
            }
        } elseif (is_string($emailData['to'])) {
            if (!filter_var($emailData['to'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("Invalid email address: {$emailData['to']}");
            }
        } else {
            throw new \Exception("Invalid 'to' field format");
        }
    }

    /**
     * Get email template.
     */
    protected function getTemplate(string $templateId): ?EmailTemplate
    {
        return EmailTemplate::where('template_id', $templateId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create email log entry.
     */
    protected function createEmailLog(array $emailData, EmailTemplate $template, string $messageId): EmailLog
    {
        $toEmails = $this->extractEmails($emailData['to']);
        $ccEmails = isset($emailData['cc']) ? $this->extractEmails($emailData['cc']) : [];
        $bccEmails = isset($emailData['bcc']) ? $this->extractEmails($emailData['bcc']) : [];

        return EmailLog::create([
            'message_id' => $messageId,
            'template_id' => $template->template_id,
            'to_email' => $toEmails[0] ?? '',
            'to_name' => $this->extractNames($emailData['to'])[0] ?? null,
            'cc_emails' => !empty($ccEmails) ? implode(',', $ccEmails) : null,
            'bcc_emails' => !empty($bccEmails) ? implode(',', $bccEmails) : null,
            'subject' => $emailData['subject'] ?? $template->subject,
            'data' => $emailData['data'] ?? [],
            'status' => 'pending',
            'provider' => 'ses',
            'source' => $emailData['source'] ?? 'api',
            'headers' => $emailData['headers'] ?? [],
            'attachments' => $emailData['attachments'] ?? [],
            'tracking' => $emailData['tracking'] ?? [],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Extract emails from recipient data.
     */
    protected function extractEmails($recipients): array
    {
        if (is_string($recipients)) {
            return [$recipients];
        }

        if (is_array($recipients)) {
            return array_map(function ($recipient) {
                return is_array($recipient) ? $recipient['email'] : $recipient;
            }, $recipients);
        }

        return [];
    }

    /**
     * Extract names from recipient data.
     */
    protected function extractNames($recipients): array
    {
        if (is_string($recipients)) {
            return [null];
        }

        if (is_array($recipients)) {
            return array_map(function ($recipient) {
                return is_array($recipient) ? ($recipient['name'] ?? null) : null;
            }, $recipients);
        }

        return [];
    }

    /**
     * Render template with data.
     * Made public to allow RabbitMQService to use it for template-based email processing.
     */
    public function renderTemplate(EmailTemplate $template, array $data): array
    {
        $htmlContent = null;
        $textContent = null;

        if ($template->hasHtmlContent()) {
            $htmlContent = $this->renderBladeContent($template->html_content, $data);
        }

        if ($template->hasTextContent()) {
            $textContent = $this->renderBladeContent($template->text_content, $data);
        }

        return [
            'html' => $htmlContent,
            'text' => $textContent,
        ];
    }

    /**
     * Render Blade content with data.
     */
    protected function renderBladeContent(string $content, array $data): string
    {
        try {
            return \Blade::render($content, $data);
        } catch (\Exception $e) {
            Log::error('Template rendering failed', [
                'error' => $e->getMessage(),
                'content' => $content,
                'data' => $data,
            ]);
            throw new \Exception("Template rendering failed: {$e->getMessage()}");
        }
    }

    /**
     * Prepare email content for sending.
     */
    protected function prepareEmailContent(array $emailData, array $renderedContent, string $messageId): array
    {
        $toEmails = $this->extractEmails($emailData['to']);
        $ccEmails = isset($emailData['cc']) ? $this->extractEmails($emailData['cc']) : [];
        $bccEmails = isset($emailData['bcc']) ? $this->extractEmails($emailData['bcc']) : [];

        return [
            'to' => $toEmails,
            'cc' => $ccEmails,
            'bcc' => $bccEmails,
            'subject' => $emailData['subject'] ?? '',
            'html_content' => $renderedContent['html'],
            'text_content' => $renderedContent['text'],
            'message_id' => $messageId,
            'headers' => $this->prepareHeaders($emailData, $messageId),
            'attachments' => $emailData['attachments'] ?? [],
        ];
    }

    /**
     * Prepare email headers with privacy settings.
     */
    protected function prepareHeaders(array $emailData, string $messageId): array
    {
        $headers = [
            'Message-ID' => "<{$messageId}@{$this->bounceDomain}>",
            'List-Unsubscribe' => "<mailto:{$this->unsubscribeDomain}>, <https://broker.com/email/unsub?u={$messageId}>",
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            'X-Entity-Ref-ID' => $messageId,
        ];

        // Add custom headers if provided
        if (isset($emailData['headers']) && is_array($emailData['headers'])) {
            $headers = array_merge($headers, $emailData['headers']);
        }

        return $headers;
    }

    /**
     * Send email via AWS SES.
     */
    protected function sendViaSES(array $emailContent): array
    {
        $this->initializeSESClient(); // Ensure client is initialized

        $rawMessage = $this->buildRawMessage($emailContent);

        $result = $this->sesClient->sendRawEmail([
            'RawMessage' => ['Data' => $rawMessage],
            'Source' => $this->fromEmail,
        ]);

        return [
            'MessageId' => $result['MessageId'],
            'ResponseMetadata' => $result['ResponseMetadata'],
        ];
    }

    /**
     * Build raw email message.
     */
    protected function buildRawMessage(array $emailContent): string
    {
        $boundary = 'b-' . bin2hex(random_bytes(8));
        $messageId = $emailContent['message_id'];
        $subject = $emailContent['subject'];
        $htmlContent = $emailContent['html_content'];
        $textContent = $emailContent['text_content'];

        $raw = "";
        $raw .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $raw .= "To: " . implode(', ', $emailContent['to']) . "\r\n";
        
        if (!empty($emailContent['cc'])) {
            $raw .= "Cc: " . implode(', ', $emailContent['cc']) . "\r\n";
        }
        
        if (!empty($emailContent['bcc'])) {
            $raw .= "Bcc: " . implode(', ', $emailContent['bcc']) . "\r\n";
        }
        
        $raw .= "Subject: {$subject}\r\n";
        $raw .= "Date: " . date('r') . "\r\n";
        $raw .= "Message-ID: <{$messageId}@{$this->bounceDomain}>\r\n";
        $raw .= "MIME-Version: 1.0\r\n";
        $raw .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $raw .= "\r\n";

        // Add text content
        if ($textContent) {
            $raw .= "--{$boundary}\r\n";
            $raw .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $raw .= "{$textContent}\r\n";
        }

        // Add HTML content
        if ($htmlContent) {
            $raw .= "--{$boundary}\r\n";
            $raw .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $raw .= "{$htmlContent}\r\n";
        }

        $raw .= "--{$boundary}--\r\n";

        return $raw;
    }

    /**
     * Update email log with success.
     */
    protected function updateEmailLogSuccess(EmailLog $emailLog, array $result): void
    {
        $emailLog->update([
            'status' => 'sent',
            'provider_message_id' => $result['MessageId'] ?? null,
            'provider_response' => $result,
            'sent_at' => now(),
        ]);
    }

    /**
     * Update email log with failure.
     */
    protected function updateEmailLogFailure(EmailLog $emailLog, string $errorMessage): void
    {
        $emailLog->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
} 