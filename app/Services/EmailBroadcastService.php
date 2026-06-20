<?php

namespace App\Services;

use App\Mail\EventAnnouncementMail;
use App\Mail\JobPostingMail;
use App\Mail\StoryPublishedMail;
use App\Models\AlumniUser;
use App\Models\Event;
use App\Models\Job;
use App\Models\Story;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Broadcasts announcement emails to all opted-in alumni users.
 *
 * Querying strategy:
 *   - Users where email_notifications->{type} = true  (explicitly opted in)
 *   - OR  email_notifications IS NULL                  (new users; default is opt-in)
 * Excludes the content creator (they already received a personal notification).
 * Uses chunk(100) so we never load thousands of rows into memory at once.
 * Uses Mail::queue() so each email is dispatched async to the queue worker.
 */
class EmailBroadcastService
{
    // ── Events ─────────────────────────────────────────────────────────────

    public static function broadcastEvent(Event $event, ?int $excludeUserId = null): void
    {
        self::queryRecipients('events', $excludeUserId)
            ->chunk(100, function ($users) use ($event) {
                foreach ($users as $user) {
                    try {
                        Mail::to($user->email)
                            ->queue(new EventAnnouncementMail($event, $user));
                    } catch (\Throwable $e) {
                        Log::error('EmailBroadcast [event] failed for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            });
    }

    // ── Jobs ───────────────────────────────────────────────────────────────

    public static function broadcastJob(Job $job, ?int $excludeUserId = null): void
    {
        self::queryRecipients('jobs', $excludeUserId)
            ->chunk(100, function ($users) use ($job) {
                foreach ($users as $user) {
                    try {
                        Mail::to($user->email)
                            ->queue(new JobPostingMail($job, $user));
                    } catch (\Throwable $e) {
                        Log::error('EmailBroadcast [job] failed for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            });
    }

    // ── Stories ────────────────────────────────────────────────────────────

    public static function broadcastStory(Story $story, ?int $excludeUserId = null): void
    {
        self::queryRecipients('stories', $excludeUserId)
            ->chunk(100, function ($users) use ($story) {
                foreach ($users as $user) {
                    try {
                        Mail::to($user->email)
                            ->queue(new StoryPublishedMail($story, $user));
                    } catch (\Throwable $e) {
                        Log::error('EmailBroadcast [story] failed for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            });
    }

    // ── Shared query builder ───────────────────────────────────────────────

    /**
     * Returns a query for approved users who want emails of the given type.
     * Includes users whose email_notifications is null (default = all opted in).
     *
     * @param string   $type          'events' | 'jobs' | 'stories'
     * @param int|null $excludeUserId Creator's ID — skip them
     */
    private static function queryRecipients(string $type, ?int $excludeUserId)
    {
        return AlumniUser::where('is_approved', true)
            ->where(function ($q) use ($type) {
                // NULL → not yet set; treat as opted-in (matches Settings default)
                $q->whereNull('email_notifications')
                  ->orWhere('email_notifications->' . $type, true);
            })
            ->when($excludeUserId, fn($q) => $q->where('id', '!=', $excludeUserId))
            ->select(['id', 'full_name', 'email']);
    }
}
