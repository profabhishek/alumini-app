<?php

namespace App\Mail;

use App\Models\AlumniUser;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobPostingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public Job        $job,
        public AlumniUser $recipient
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💼 New Job Opportunity: ' . $this->job->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.job-posting');
    }
}
