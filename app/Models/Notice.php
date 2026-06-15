<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notice extends Model
{
    protected $table = 'notices';

    protected $fillable = [
        'notice_category_id', 'author_id', 'title', 'slug',
        'description', 'image', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $notice) {
            if (empty($notice->slug)) {
                $notice->slug = static::uniqueSlug($notice->title);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'notice';
        $slug = $base;
        $i = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(NoticeCategory::class, 'notice_category_id');
    }

    public function author()
    {
        return $this->belongsTo(AlumniUser::class, 'author_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    // ── Accessors ────────────────────────────────────────────────────────

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->description), 140);
    }
}