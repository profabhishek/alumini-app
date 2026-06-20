<?php

namespace App\Mail;

use App\Models\AlumniUser;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventAnnouncementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public Event      $event,
        public AlumniUser $recipient
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📅 New Event: ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.event-announcement');
    }
}
