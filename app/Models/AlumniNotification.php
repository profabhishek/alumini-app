<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniNotification extends Model
{
    protected $fillable = [
        'recipient_id',
        'actor_id',
        'actor_names',
        'actor_count',
        'type',
        'post_id',
        'comment_id',
        'group_id',
        'preview',
        'is_read',
    ];

    protected $casts = [
        'is_read'     => 'boolean',
        'actor_count' => 'integer',
    ];

    public function actor()
    {
        return $this->belongsTo(AlumniUser::class, 'actor_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function group()
    {
        return $this->belongsTo(CommunityGroup::class, 'group_id');
    }

    /**
     * Human-readable message for the bell dropdown.
     * e.g. "Amit and 3 others liked your post"
     */
    public function getMessage(): string
    {
        $names  = $this->actor_names ?? ($this->actor?->full_name ? explode(' ', $this->actor->full_name)[0] : 'Someone');
        $count  = (int) ($this->actor_count ?? 1);
        $others = $count - 2; // actor_names holds up to 2 first names

        $who = match(true) {
            $count <= 2  => $names,
            $others === 1 => $names . ' and 1 other',
            default      => $names . ' and ' . $others . ' others',
        };

        return match($this->type) {
            'post_like'           => "{$who} liked your post",
            'comment_like'        => "{$who} liked a comment on your post",
            'comment'             => "{$who} commented on your post",
            'reply'               => "{$who} replied to your comment",
            'group_join'          => "{$who} joined your group via invite link",
            'group_post_pending'  => "{$who} posted in your group — pending approval",
            'group_member_joined' => "{$who} joined the group",
            'group_new_post'      => "{$who} posted something new in the group",
            default               => "{$who} interacted with your content",
        };
    }
}
