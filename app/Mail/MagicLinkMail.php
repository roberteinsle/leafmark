<?php

namespace App\Mail;

use App\Models\LoginToken;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MagicLinkMail extends Mailable
{
    public function __construct(public string $url)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('app.magic_link.email_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.magic-link',
            with: ['minutes' => LoginToken::LIFETIME_MINUTES],
        );
    }
}
