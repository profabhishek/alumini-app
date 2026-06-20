<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Job as JobModel;
use App\Models\Story;
use App\Services\EmailBroadcastService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched immediately after approve/publish so the HTTP response
 * returns to the admin instantly. The queue worker picks this up and
 * runs the chunk-based email broadcast in the background.
 */
class BroadcastAnnouncementEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300; // chunked bulk send needs more time

    public function __construct(
        private readonly string $type,   // 'event' | 'job' | 'story'
        private readonly int    $modelId,
        private readonly ?int   $excludeUserId = null,
    ) {}

    public function handle(): void
    {
        match ($this->type) {
            'event' => EmailBroadcastService::broadcastEvent(
                            Event::findOrFail($this->modelId),
                            $this->excludeUserId
                        ),
            'job'   => EmailBroadcastService::broadcastJob(
                            JobModel::findOrFail($this->modelId),
                            $this->excludeUserId
                        ),
            'story' => EmailBroadcastService::broadcastStory(
                            Story::findOrFail($this->modelId),
                            $this->excludeUserId
                        ),
            default => null,
        };
    }
}
