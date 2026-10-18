<?php

namespace App\Mail;

use App\Models\PasswordResetCode;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetCodeMail extends Mailable
{
    public function __construct(public string $code, public string $name)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'ZooMarket: parolni tiklash kodi');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-reset-code', with: [
            'minutes' => PasswordResetCode::CODE_MINUTES,
        ]);
    }
}
