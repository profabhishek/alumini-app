<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'alumni_id',
        'body',
        'type',
        'shared_post_id',
        'group_id',
        'status',
        'likes_count',
        'comments_count',
        'shares_count',
        'pending_body',
    ];

    protected $casts = [
        'likes_count'    => 'integer',
        'comments_count' => 'integer',
        'shares_count'   => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function author()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }

    public function media()
    {
        return $this->hasMany(PostMedia::class, 'post_id')->orderBy('position');
    }

    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    public function group()
    {
        return $this->belongsTo(CommunityGroup::class, 'group_id');
    }

    public function shares()
    {
        return $this->hasMany(Post::class, 'shared_post_id');
    }

    public function comments()
    {
        // Top-level comments only — replies are loaded via the comment's own relation
        return $this->hasMany(PostComment::class, 'post_id')
            ->whereNull('parent_id')
            ->orderByDesc('created_at');
    }

    public function allComments()
    {
        return $this->hasMany(PostComment::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function saves()
    {
        return $this->hasMany(PostSave::class, 'post_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopeFeed(Builder $query, ?int $groupId = null): Builder
    {
        $query = $query
            ->with([
                'author',
                'media',
                'sharedPost.author',
                'sharedPost.media',
            ])
            ->orderByDesc('created_at');

        return $groupId
            ? $query->where('group_id', $groupId)
            : $query->whereNull('group_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function isShare(): bool
    {
        return !is_null($this->shared_post_id);
    }

    public function isLikedBy(?int $alumniId): bool
    {
        if (!$alumniId) return false;
        return $this->likes()->where('alumni_id', $alumniId)->exists();
    }

    public function isSavedBy(?int $alumniId): bool
    {
        if (!$alumniId) return false;
        return $this->saves()->where('alumni_id', $alumniId)->exists();
    }

    /**
     * Serialize a post for the feed JSON / Blade rendering.
     * $myId = current logged-in alumni id (for liked/saved flags).
     */
    public function toFeedArray(?int $myId = null, bool $viewerIsGroupMod = false): array
    {
        $author = $this->author;

        $base = [
            'id'             => $this->id,
            'type'           => $this->type,
            'body'           => $this->body,
            'group_id'       => $this->group_id,
            'status'           => $this->status,
            'is_pending_review'=> $this->status === 'pending_review',
            'has_pending_edit' => $this->pending_body !== null,
            'pending_body'     => $this->pending_body,
            'can_review_edit'  => ($this->pending_body !== null || $this->status === 'pending_review')
                && (int) $this->alumni_id !== $myId
                && $viewerIsGroupMod,
            'can_delete'       => $myId && ((int) $this->alumni_id === $myId || $viewerIsGroupMod),
            'can_edit'         => $myId && ((int) $this->alumni_id === $myId || $viewerIsGroupMod),
            'created_at'     => $this->created_at?->toISOString(),
            'created_human'  => $this->created_at?->diffForHumans(),
            'likes_count'    => $this->likes_count,
            'comments_count' => $this->comments_count,
            'shares_count'   => $this->shares_count,
            'is_liked'       => $this->isLikedBy($myId),
            'is_saved'       => $this->isSavedBy($myId),
            'is_mine'        => $myId && (int) $this->alumni_id === $myId,
            'author' => [
                'id'          => $author?->id,
                'name'        => $author?->full_name ?? 'Unknown',
                'avatar'      => $author?->photo ? asset('storage/' . $author->photo) : null,
                'initials'    => $author?->initials ?? '?',
                'job_title'   => $author?->current_job_title,
                'company'     => $author?->current_company,
                'batch'       => $author?->passing_year,
                'group_role'  => $this->groupRoleFor($author?->id),
                'profile_url' => $author?->id ? url('/members/' . $author->id) : null,
            ],
            'media' => $this->media->map(fn($m) => $m->toApiArray())->values(),
        ];

        // If this post is a share, attach the original post (one level — shares of shares
        // still reference the true original because shared_post_id always points to a
        // non-share post; enforced in the controller).
        if ($this->isShare() && $this->sharedPost) {
            $base['shared_post'] = $this->sharedPost->toFeedArray($myId);
        } else {
            $base['shared_post'] = null;
        }

        return $base;
    }
    
    public function groupRoleFor(?int $alumniId): ?string
    {
        if (!$this->group_id || !$alumniId) {
            return null;
        }

        return CommunityGroupMember::where('group_id', $this->group_id)
            ->where('alumni_id', $alumniId)
            ->where('status', 'approved')
            ->value('role');
    }
}