<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    protected $table = 'user_blocks';

    // Only created_at, no updated_at
    const UPDATED_AT = null;

    protected $fillable = ['blocker_id', 'blocked_id'];

    // ── Relationships ────────────────────────────────────────────────────

    public function blocker()
    {
        return $this->belongsTo(AlumniUser::class, 'blocker_id');
    }

    public function blocked()
    {
        return $this->belongsTo(AlumniUser::class, 'blocked_id');
    }

    // ── Static helpers ───────────────────────────────────────────────────

    /**
     * IDs that $myId has blocked.
     */
    public static function blockedByMe(int $myId): array
    {
        return self::where('blocker_id', $myId)->pluck('blocked_id')->all();
    }

    /**
     * IDs that have blocked $myId.
     */
    public static function whoBlockedMe(int $myId): array
    {
        return self::where('blocked_id', $myId)->pluck('blocker_id')->all();
    }

    /**
     * Union of both directions — any user that $myId should not interact with.
     */
    public static function mutualIds(int $myId): array
    {
        return array_unique(array_merge(
            self::blockedByMe($myId),
            self::whoBlockedMe($myId)
        ));
    }

    /**
     * True if $blockerId has blocked $blockedId.
     */
    public static function isBlocking(int $blockerId, int $blockedId): bool
    {
        return self::where('blocker_id', $blockerId)
            ->where('blocked_id', $blockedId)
            ->exists();
    }
}
