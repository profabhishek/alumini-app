<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventRegistrationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;
    public EventRegistration $registration;

    public function __construct(Event $event, EventRegistration $registration)
    {
        $this->event = $event;
        $this->registration = $registration;
    }

    public function build()
    {
        return $this->subject('Registration Confirmed: ' . $this->event->title)
            ->view('emails.events.registration-confirmation');
    }
}