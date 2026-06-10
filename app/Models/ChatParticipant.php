<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatParticipant extends Model
{
    protected $table = 'chat_participants';

    protected $fillable = [
        'conversation_id',
        'alumni_id',
        'role',
        'left_at',
        'muted_until',
    ];

    protected $casts = [
        'left_at'     => 'datetime',
        'muted_until' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }

    public function hasLeft(): bool
    {
        return $this->left_at !== null;
    }

    public function isMuted(): bool
    {
        return $this->muted_until && $this->muted_until->isFuture();
    }
}