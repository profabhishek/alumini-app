<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'creator_role',
        'title',
        'slug',
        'category',
        'body',
        'excerpt',
        'cover_image',
        'status',
        'rejection_reason',
    ];

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

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Auto-generate a plain-text excerpt from body if not provided.
     */
    public static function makeExcerpt(string $body, int $length = 200): string
    {
        $plain = strip_tags($body);
        return mb_strlen($plain) > $length
            ? mb_substr($plain, 0, $length) . '…'
            : $plain;
    }
}