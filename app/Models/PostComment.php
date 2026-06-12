<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'post_id',
        'alumni_id',
        'parent_id',
        'body',
        'likes_count',
        'replies_count',
    ];

    protected $casts = [
        'likes_count'   => 'integer',
        'replies_count' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function author()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }

    public function parent()
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    /**
     * One level only — replies to this comment (parent_id = this comment's id).
     */
    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id')->orderBy('created_at');
    }

    public function likes()
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    public function isLikedBy(?int $alumniId): bool
    {
        if (!$alumniId) return false;
        return $this->likes()->where('alumni_id', $alumniId)->exists();
    }

    public function toApiArray(?int $myId = null, bool $withReplies = true): array
    {
        $author = $this->author;

        $data = [
            'id'            => $this->id,
            'post_id'       => $this->post_id,
            'parent_id'     => $this->parent_id,
            'body'          => $this->body,
            'created_at'    => $this->created_at?->toISOString(),
            'created_human' => $this->created_at?->diffForHumans(),
            'likes_count'   => $this->likes_count,
            'replies_count' => $this->replies_count,
            'is_liked'      => $this->isLikedBy($myId),
            'is_mine'       => $myId && (int) $this->alumni_id === $myId,
            'author' => [
                'id'       => $author?->id,
                'name'     => $author?->full_name ?? 'Unknown',
                'avatar'   => $author?->photo ? asset('storage/' . $author->photo) : null,
                'initials' => $author?->initials ?? '?',
            ],
        ];

        if ($withReplies && !$this->isReply()) {
            $data['replies'] = $this->replies->map(
                fn($r) => $r->toApiArray($myId, false)
            )->values();
        }

        return $data;
    }
}