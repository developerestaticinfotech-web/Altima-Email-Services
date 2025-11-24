<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DirectEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $bodyContent;
    public $fromEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $bodyContent, $fromEmail = null)
    {
        $this->subject = $subject;
        $this->bodyContent = $bodyContent;
        $this->fromEmail = $fromEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            from: $this->fromEmail ?: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.direct-email',
            with: [
                'subject' => $this->subject,
                'bodyContent' => $this->bodyContent,
                'sentAt' => now()->format('Y-m-d H:i:s')
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
