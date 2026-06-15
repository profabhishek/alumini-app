<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityGroupMember extends Model
{
    protected $fillable = [
        'group_id',
        'alumni_id',
        'role',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function group()
    {
        return $this->belongsTo(CommunityGroup::class, 'group_id');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * True for both admin and moderator roles.
     */
    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}