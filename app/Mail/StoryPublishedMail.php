<?php

namespace App\Mail;

use App\Models\AlumniUser;
use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StoryPublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public Story      $story,
        public AlumniUser $recipient
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📖 New Alumni Story: ' . $this->story->title,
        );
    }

    public function content(): Content
    {
        // SerializesModels re-fetches the model but drops eager-loaded relationships.
        // Load creator here so the template can safely render $story->creator->full_name.
        $this->story->loadMissing('creator');

        return new Content(view: 'emails.story-published');
    }
}
