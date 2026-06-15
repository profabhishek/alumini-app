<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterUnsubscribeMail extends Mailable
{
    use Queueable, SerializesModels;

    public NewsletterSubscriber $subscriber;

    public function __construct(NewsletterSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function build()
    {
        return $this->subject('Confirm your unsubscribe request')
            ->view('emails.newsletter.unsubscribe-confirmation');
    }
}