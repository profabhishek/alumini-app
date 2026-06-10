<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ChatConversation extends Model
{
    use SoftDeletes;

    protected $table = 'chat_conversations';

    protected $fillable = [
        'type',
        'name',
        'avatar',
        'description',
        'created_by',
        'invite_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'conversation_id')
                    ->whereNull('left_at');
    }

    public function allParticipants()
    {
        return $this->hasMany(ChatParticipant::class, 'conversation_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id')
                    ->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')
                    ->latestOfMany();
    }

    public function creator()
    {
        return $this->belongsTo(AlumniUser::class, 'created_by');
    }

    public function joinRequests()
    {
        return $this->hasMany(ChatGroupJoinRequest::class, 'conversation_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('alumni_id', $userId)->whereNull('left_at');
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function hasParticipant(int $userId): bool
    {
        return $this->participants()
                    ->where('alumni_id', $userId)
                    ->exists();
    }

    public function getParticipantRole(int $userId): ?string
    {
        return $this->participants()
                    ->where('alumni_id', $userId)
                    ->value('role');
    }

    public function isAdmin(int $userId): bool
    {
        return $this->getParticipantRole($userId) === 'admin';
    }

    /**
     * Generate and persist a new invite token.
     */
    public function generateInviteToken(): string
    {
        $token = Str::random(32);
        $this->update(['invite_token' => $token]);
        return $token;
    }

    /**
     * For a direct conversation, return the other participant.
     */
    public function otherParticipant(int $myId): ?AlumniUser
    {
        $participant = $this->participants()
            ->with('alumni')
            ->where('alumni_id', '!=', $myId)
            ->first();

        return $participant?->alumni;
    }

    /**
     * Unread message count for a given user.
     */
    public function unreadCountFor(int $userId): int
    {
        $lastRead = \App\Models\ChatMessageRead::where('conversation_id', $this->id)
            ->where('alumni_id', $userId)
            ->value('last_read_message_id');

        $query = $this->hasMany(ChatMessage::class, 'conversation_id')
            ->whereNull('deleted_at')
            ->where('sender_id', '!=', $userId);

        if ($lastRead) {
            $query->where('id', '>', $lastRead);
        }

        return $query->count();
    }
}