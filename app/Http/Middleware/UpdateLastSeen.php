<?php

namespace App\Http\Middleware;

use App\Models\AlumniUser;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Only hit the database at most once every 60 seconds per user.
     */
    private const THROTTLE_SECONDS   = 8;

    /**
     * Only mark deliveries once every 30 seconds per user
     * (more frequent than last_seen since delivery is more time-sensitive).
     */
    private const DELIVERY_THROTTLE  = 2;

    public function handle(Request $request, Closure $next): Response
    {
        $alumniId = session('alumni_id');

        if ($alumniId) {

            // ── 1. Update last_seen_at ( 60s) ─────────────
            $seenKey = "alumni_last_seen_{$alumniId}";
            if (!Cache::has($seenKey)) {
                AlumniUser::where('id', $alumniId)
                    ->update(['last_seen_at' => now()]);
                Cache::put($seenKey, true, self::THROTTLE_SECONDS);
            }

            // ── 2. Mark unread messages as delivered ( 30s) ──
            $deliveryKey = "alumni_delivery_{$alumniId}";
            if (!Cache::has($deliveryKey)) {
                $this->markMessagesDelivered($alumniId);
                Cache::put($deliveryKey, true, self::DELIVERY_THROTTLE);
            }
        }

        return $next($request);
    }

    private function markMessagesDelivered(int $userId): void
    {
        // Get all active conversation IDs for this user
        $conversationIds = ChatParticipant::where('alumni_id', $userId)
            ->whereNull('left_at')
            ->pluck('conversation_id');

        if ($conversationIds->isEmpty()) return;

        // Bulk update: mark undelivered messages from others as delivered
        ChatMessage::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $userId)
            ->whereNull('delivered_at')
            ->whereNull('deleted_at')
            ->update(['delivered_at' => now()]);
    }
}