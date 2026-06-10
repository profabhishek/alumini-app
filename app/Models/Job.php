<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JobApplication;
use Illuminate\Support\Str;
use App\Models\AlumniUser;

class Job extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'company_name',
        'location',
        'job_type',
        'work_mode',
        'salary_min',
        'salary_max',
        'description',
        'requirements',
        'application_deadline',
        'application_link',
        'banner_image',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'salary_min'           => 'integer',
        'salary_max'           => 'integer',
    ];

    // ── Auto-generate slug ────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->slug)) {
                $job->slug = Str::slug($job->title) . '-' . uniqid();
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(AlumniUser::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isOwnedBy(int $userId): bool
    {
        return (int) $this->created_by === $userId;
    }

    public function salaryRange(): string
    {
        if ($this->salary_min && $this->salary_max) {
            return '₹' . number_format($this->salary_min) . ' – ₹' . number_format($this->salary_max);
        }

        if ($this->salary_min) {
            return '₹' . number_format($this->salary_min) . '+';
        }

        return 'Not disclosed';
    }

    public function isExpired(): bool
    {
        return $this->application_deadline && $this->application_deadline->isPast();
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}