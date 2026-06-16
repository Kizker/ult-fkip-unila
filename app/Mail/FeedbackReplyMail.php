<?php

namespace App\Mail;

use App\Models\FeedbackMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly FeedbackMessage $feedback,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tanggapan Kritik dan Saran ULT FKIP Unila',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback_reply',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
