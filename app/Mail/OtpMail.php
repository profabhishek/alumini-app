<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $fullName;

    public function __construct(string $otp, string $fullName)
    {
        $this->otp      = $otp;
        $this->fullName = $fullName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ICCR Alumni Portal — Email Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}