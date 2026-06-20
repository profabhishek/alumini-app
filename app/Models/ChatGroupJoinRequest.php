<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGroupJoinRequest extends Model
{
    protected $table = 'chat_group_join_requests';

    protected $fillable = [
        'conversation_id',
        'alumni_id',
        'status',
        'invited_by',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }

    public function actedBy()
    {
        return $this->belongsTo(AlumniUser::class, 'acted_by');
    }

    public function invitedBy()
    {
        return $this->belongsTo(AlumniUser::class, 'invited_by');
    }

    public function isPending(): bool      { return $this->status === 'pending'; }
    public function isAccepted(): bool     { return $this->status === 'accepted'; }
    public function isRejected(): bool     { return $this->status === 'rejected'; }
    public function isInvitation(): bool   { return !is_null($this->invited_by); }
}