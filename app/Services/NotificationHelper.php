<?php

namespace App\Services;

use App\Models\AlumniNotification;
use App\Models\AlumniUser;
use Illuminate\Support\Facades\Log;

/**
 * Central helper for firing alumni notifications.
 *
 * For "grouped" types (post_like, comment_like, comment) we upsert a single
 * notification per (recipient, type, post_id, comment_id) combination so that
 * multiple actors collapse into one bell item — Facebook-style.
 *
 * For "single" types (reply, group_join, group_post_pending, group_member_joined)
 * we always insert a fresh row.
 */
class NotificationHelper
{
    // Types that collapse multiple actors into one notification
    const GROUPED = ['post_like', 'comment_like', 'comment'];

    /**
     * Fire a notification. Safe to call from anywhere — never throws.
     *
     * @param int         $recipientId  Who receives the notification
     * @param int         $actorId      Who triggered it
     * @param string      $type         post_like | comment_like | comment | reply |
     *                                  group_join | group_post_pending | group_member_joined
     * @param int|null    $postId
     * @param int|null    $commentId    For comment/reply/comment_like
     * @param string|null $preview      Short text preview
     * @param int|null    $groupId      Community group context
     */
    public static function fire(
        int    $recipientId,
        int    $actorId,
        string $type,
        ?int   $postId    = null,
        ?int   $commentId = null,
        ?string $preview  = null,
        ?int   $groupId   = null
    ): void {
        // Never notify yourself
        if ($recipientId === $actorId) {
            return;
        }

        try {
            $actor = AlumniUser::find($actorId);
            $actorFirstName = $actor ? explode(' ', $actor->full_name)[0] : 'Someone';

            if (in_array($type, self::GROUPED)) {
                // For comment_like: group by post_id only (all comment likes on a post collapse)
                $groupByCommentId = ($type !== 'comment_like') ? $commentId : null;
                self::upsertGrouped(
                    $recipientId, $actorId, $actorFirstName,
                    $type, $postId, $groupByCommentId, $preview, $groupId
                );
            } else {
                AlumniNotification::create([
                    'recipient_id' => $recipientId,
                    'actor_id'     => $actorId,
                    'actor_names'  => $actorFirstName,
                    'actor_count'  => 1,
                    'type'         => $type,
                    'post_id'      => $postId,
                    'comment_id'   => $commentId,
                    'preview'      => $preview,
                    'group_id'     => $groupId,
                    'is_read'      => false,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationHelper::fire failed: ' . $e->getMessage());
        }
    }

    private static function upsertGrouped(
        int $recipientId, int $actorId, string $actorFirstName,
        string $type, ?int $postId, ?int $commentId, ?string $preview, ?int $groupId
    ): void {
        // Find an existing unread grouped notification for this exact (recipient, type, post, comment)
        $existing = AlumniNotification::where('recipient_id', $recipientId)
            ->where('type', $type)
            ->where('post_id', $postId)
            ->where('comment_id', $commentId)
            ->where('is_read', false)
            ->first();

        if ($existing) {
            // Don't double-count the same actor
            $existingActorId = $existing->actor_id;
            if ($existingActorId === $actorId) {
                return; // same person acted twice (e.g. un-like then re-like)
            }

            $count = $existing->actor_count + 1;
            // Keep two names max: "Amit, Rahul"
            $names = $existing->actor_names ?? '';
            $nameList = array_filter(array_map('trim', explode(',', $names)));
            if (!in_array($actorFirstName, $nameList)) {
                $nameList[] = $actorFirstName;
            }
            $displayNames = implode(', ', array_slice($nameList, 0, 2));

            $existing->update([
                'actor_id'    => $actorId,   // most recent actor
                'actor_names' => $displayNames,
                'actor_count' => $count,
                'preview'     => $preview ?? $existing->preview,
                'is_read'     => false,       // re-mark unread on new activity
                'updated_at'  => now(),
            ]);
        } else {
            AlumniNotification::create([
                'recipient_id' => $recipientId,
                'actor_id'     => $actorId,
                'actor_names'  => $actorFirstName,
                'actor_count'  => 1,
                'type'         => $type,
                'post_id'      => $postId,
                'comment_id'   => $commentId,
                'preview'      => $preview,
                'group_id'     => $groupId,
                'is_read'      => false,
            ]);
        }
    }
}
