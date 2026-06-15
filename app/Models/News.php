<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'news_category_id', 'author_id', 'title', 'slug',
        'excerpt', 'body', 'image', 'status', 'published_at',
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

        static::creating(function (self $news) {
            if (empty($news->slug)) {
                $news->slug = static::uniqueSlug($news->title);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'news';
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
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
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

    public function getReadTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->body));
        return max(1, (int) ceil($words / 200));
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published'
            && $this->published_at
            && $this->published_at->lte(now());
    }
}