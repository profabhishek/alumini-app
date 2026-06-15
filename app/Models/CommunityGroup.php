<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_image',
        'created_by',
        'status',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(AlumniUser::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(CommunityGroupMember::class, 'group_id');
    }

    public function approvedMembers()
    {
        return $this->members()->where('status', 'approved');
    }

    public function pendingMembers()
    {
        return $this->members()->where('status', 'pending');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'group_id');
    }

    // ── Membership helpers ───────────────────────────────────────────────

    /**
     * This alumni's membership row, or null if they've never
     * joined/requested to join.
     */
    public function membership(?int $alumniId): ?CommunityGroupMember
    {
        if (!$alumniId) return null;

        return $this->members()->where('alumni_id', $alumniId)->first();
    }

    public function isApprovedMember(?int $alumniId): bool
    {
        $m = $this->membership($alumniId);
        return $m && $m->status === 'approved';
    }

    public function hasPendingRequest(?int $alumniId): bool
    {
        $m = $this->membership($alumniId);
        return $m && $m->status === 'pending';
    }

    /**
     * Group-level role ('member' | 'moderator' | 'admin') for an approved
     * member, or null if they're not an approved member.
     */
    public function roleFor(?int $alumniId): ?string
    {
        $m = $this->membership($alumniId);
        return ($m && $m->status === 'approved') ? $m->role : null;
    }

    public function isGroupAdmin(?int $alumniId): bool
    {
        return $this->roleFor($alumniId) === 'admin';
    }

    /**
     * True for both group admins and group moderators.
     */
    public function isGroupModerator(?int $alumniId): bool
    {
        return in_array($this->roleFor($alumniId), ['admin', 'moderator']);
    }

    /**
     * Platform-wide admins/super_admins always have oversight, even if
     * they're not a member of this group.
     */
    public function isSiteAdmin(?string $platformRole): bool
    {
        return in_array($platformRole, ['admin', 'super_admin']);
    }

    public function membersCount(): int
    {
        return $this->approvedMembers()->count();
    }

    // ── Serialization ────────────────────────────────────────────────────

    /**
     * Compact representation for the group directory listing.
     */
    public function toCardArray(?int $myId = null): array
    {
        $membership = $this->membership($myId);

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'cover_image'   => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'members_count' => $this->membersCount(),
            'created_at'    => $this->created_at?->toISOString(),
            'is_member'     => $membership && $membership->status === 'approved',
            'is_pending'    => $membership && $membership->status === 'pending',
            'role'          => ($membership && $membership->status === 'approved') ? $membership->role : null,
        ];
    }
}