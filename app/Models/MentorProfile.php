<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorProfile extends Model
{
    protected $fillable = [
        'alumni_user_id', 'bio', 'experience_years', 'expertise',
        'availability', 'max_mentees', 'status', 'rejection_reason',
        'applied_at', 'reviewed_at', 'reviewed_by',
    ];

    protected $casts = [
        'applied_at'   => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function alumni()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(AlumniUser::class, 'reviewed_by');
    }

    public function categories()
    {
        return $this->belongsToMany(MentorCategory::class, 'mentor_profile_categories');
    }

    public function connections()
    {
        return $this->hasMany(MenteeConnection::class);
    }

    public function acceptedConnections()
    {
        return $this->hasMany(MenteeConnection::class)->where('status', 'accepted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Check if this mentor has capacity for more mentees
    public function hasCapacity(): bool
    {
        return $this->acceptedConnections()->count() < $this->max_mentees;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default    => 'Pending Review',
        };
    }
}
