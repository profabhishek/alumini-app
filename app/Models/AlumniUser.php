<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AlumniUser extends Authenticatable
{
    protected $table = 'alumni_users';

    protected $fillable = [
        'full_name',
        'batch_name',
        'phone',
        'email',
        'department',
        'passing_year',
        'roll_number',
        'attachment',
        'birth_date',
        'gender',
        'institute',
        'country',
        'photo',
        'password',
        'bio',
        'linkedin_url',
        'twitter_url',
        'facebook_url',
        'website_url',
        'current_job_title',
        'current_company',
        'current_city',
        'hide_email',
        'hide_phone',
        'email_notifications',
        'appearance',
        'profile_visibility',
        'last_seen_at',
        'notifications_read_at',
        'applications_last_seen',
        'my_jobs_last_seen',
        'my_stories_last_seen',
        'events_regs_seen_at',
        'pending_users_last_seen',
        'newsletter_last_seen',
        'nationality',
        'is_iccr_alumni',
        'current_position',
        'remarks',
        'hide_photo',
    ];

    // Sensitive fields explicitly excluded from mass-assignment.
    // These must only be set via direct attribute assignment: $user->role = ...
    protected $guarded = [
        'role',
        'is_approved',
        'permissions',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'permissions'         => 'array',
        'is_approved'         => 'boolean',
        'hide_email'          => 'boolean',
        'hide_phone'          => 'boolean',
        'hide_photo'          => 'boolean',
        'email_notifications' => 'array',
        'last_seen_at'          => 'datetime',
        'notifications_read_at' => 'datetime',
        'applications_last_seen'=> 'datetime',
        'my_jobs_last_seen'     => 'datetime',
        'my_stories_last_seen'     => 'datetime',
        'events_regs_seen_at'      => 'datetime',
        'is_iccr_alumni'           => 'boolean',
        'pending_users_last_seen'  => 'datetime',
        'newsletter_last_seen'     => 'datetime',
    ];

    // ── Role checks ───────────────────────────────────────────────────────

    public function isAlumni(): bool        { return $this->role === 'alumni'; }
    public function isModerator(): bool     { return $this->role === 'moderator'; }
    public function isAdmin(): bool         { return $this->role === 'admin'; }
    public function isSuperAdmin(): bool    { return $this->role === 'super_admin'; }
    public function isZonalHq(): bool       { return $this->role === 'zonal_hq'; }
    public function isMission(): bool       { return $this->role === 'mission'; }

    public function isAdminOrAbove(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'zonal_hq', 'mission']);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['moderator', 'admin', 'super_admin', 'zonal_hq', 'mission']);
    }

    // ── Permission checks ─────────────────────────────────────────────────

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdminOrAbove()) return true;
        if ($this->isModerator()) return (bool) ($this->permissions[$permission] ?? false);
        return false;
    }

    public function canApproveEvents(): bool         { return $this->hasPermission('approve_events'); }
    public function canManageEventCategories(): bool  { return $this->hasPermission('manage_event_categories'); }
    public function canManageUsers(): bool            { return $this->hasPermission('manage_users'); }
    public function canCreateAdmins(): bool           { return $this->isSuperAdmin(); }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->full_name)))
            ->filter()
            ->map(fn($w) => strtoupper($w[0]))
            ->take(2)
            ->join('');
    }

    // ── Online status ─────────────────────────────────────────────────────

    public function isOnline(): bool
    {
        if (!$this->last_seen_at) return false;
        return $this->last_seen_at->gt(now()->subSeconds(10));
    }

    public function lastSeenHuman(): string
    {
        if (!$this->last_seen_at) return 'Never seen';
        if ($this->isOnline())    return 'Online';

        $ts = $this->last_seen_at;

        if ($ts->isToday())     return 'Last seen today at '     . $ts->format('H:i');
        if ($ts->isYesterday()) return 'Last seen yesterday at ' . $ts->format('H:i');

        return 'Last seen ' . $ts->format('j M') . ' at ' . $ts->format('H:i');
    }

    public function onlineStatusArray(): array
    {
        return [
            'id'               => $this->id,
            'is_online'        => $this->isOnline(),
            'last_seen_at'     => $this->last_seen_at?->toISOString(),
            'last_seen_human'  => $this->lastSeenHuman(),
        ];
    }
}