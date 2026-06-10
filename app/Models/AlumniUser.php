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
        'is_approved',
        'role',
        'permissions',
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
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'permissions'  => 'array',
        'is_approved'  => 'boolean',
        'hide_email'  => 'boolean',
        'hide_phone'  => 'boolean',
        'email_notifications' => 'array',
    ];

    // ── Role checks ───────────────────────────────────────────────────────

    public function isAlumni(): bool        { return $this->role === 'alumni'; }
    public function isModerator(): bool     { return $this->role === 'moderator'; }
    public function isAdmin(): bool         { return $this->role === 'admin'; }
    public function isSuperAdmin(): bool    { return $this->role === 'super_admin'; }

    public function isAdminOrAbove(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['moderator', 'admin', 'super_admin']);
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

    // ── Accessor ──────────────────────────────────────────────────────────

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->full_name)))
            ->filter()
            ->map(fn($w) => strtoupper($w[0]))
            ->take(2)
            ->join('');
    }
}