<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public JobApplication $application;
    public string $posterName;
    public string $posterEmail;

    public function __construct(
        JobApplication $application,
        string $posterName,
        string $posterEmail
    ) {
        $this->application   = $application;
        $this->posterName    = $posterName;
        $this->posterEmail   = $posterEmail;
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->application->status) {
            'shortlisted' => "You've been Shortlisted — " . $this->application->job->title,
            'hired'       => "Congratulations! You're Hired — " . $this->application->job->title,
            'rejected'    => "Application Update — " . $this->application->job->title,
            default       => "Application Status Update — " . $this->application->job->title,
        };

        return new Envelope(
            subject: $subject
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.application-status');
    }
}