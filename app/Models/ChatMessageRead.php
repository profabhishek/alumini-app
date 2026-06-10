<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessageRead extends Model
{
    protected $table = 'chat_message_reads';

    protected $fillable = [
        'conversation_id',
        'alumni_id',
        'last_read_message_id',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }

    public function lastMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'last_read_message_id');
    }

    /**
     * Mark a conversation as read up to a given message for a user.
     * Uses upsert so it's safe to call repeatedly.
     */
    public static function markRead(int $conversationId, int $userId, int $messageId): void
    {
        static::updateOrCreate(
            ['conversation_id' => $conversationId, 'alumni_id' => $userId],
            ['last_read_message_id' => $messageId]
        );
    }
}