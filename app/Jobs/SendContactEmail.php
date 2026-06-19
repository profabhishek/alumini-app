<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendContactEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        private readonly string $to,
        private readonly string $subject,
        private readonly string $html,
        private readonly string $replyToEmail,
        private readonly string $replyToName,
    ) {}

    public function handle(): void
    {
        $to          = $this->to;
        $subject     = $this->subject;
        $html        = $this->html;
        $replyEmail  = $this->replyToEmail;
        $replyName   = $this->replyToName;

        Mail::html($html, function ($message) use ($to, $subject, $replyEmail, $replyName) {
            $message->to($to)
                    ->subject($subject)
                    ->replyTo($replyEmail, $replyName);
        });

        Log::info('Contact email dispatched successfully to ' . $to);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendContactEmail job failed: ' . $exception->getMessage());
    }
}
