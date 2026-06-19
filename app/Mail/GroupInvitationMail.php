<?php

namespace App\Mail;

use App\Models\GroupInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public GroupInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve been invited to join "' . $this->invitation->group->name . '"',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.group-invitation');
    }
}
