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
        // System/admin notifications (no actor name needed)
        return match($this->type) {
            'event_approved'  => 'Your event has been approved and published.',
            'event_rejected'  => 'Your event was not approved.',
            'job_approved'    => 'Your job listing has been approved and published.',
            'job_rejected'    => 'Your job listing was not approved.',
            'story_approved'  => 'Your story has been approved and published.',
            'story_rejected'  => 'Your story was not approved.',
            default           => $this->getSocialMessage(),
        };
    }

    private function getSocialMessage(): string
    {
        $names  = $this->actor_names ?? ($this->actor?->full_name ? explode(' ', $this->actor->full_name)[0] : 'Someone');
        $count  = (int) ($this->actor_count ?? 1);
        $others = $count - 2;

        $who = match(true) {
            $count <= 2   => $names,
            $others === 1 => $names . ' and 1 other',
            default       => $names . ' and ' . $others . ' others',
        };

        return match($this->type) {
            'post_like'           => "{$who} liked your post",
            'comment_like'        => "{$who} liked a comment on your post",
            'comment'             => "{$who} commented on your post",
            'reply'               => "{$who} replied to your comment",
            'group_invitation'    => "{$who} invited you to join a community group",
            'group_join'          => "{$who} joined your group via invite link",
            'chat_join_request'     => "{$who} requested to join your group chat",
            'chat_group_invitation' => "{$who} invited you to join a group chat",
            'group_post_pending'    => "{$who} posted in your group — pending approval",
            'group_post_approved'   => "{$who} approved your post",
            'group_member_joined' => "{$who} joined the group",
            'group_new_post'      => "{$who} posted something new in the group",
            default               => "{$who} interacted with your content",
        };
    }

    /**
     * Build the destination URL for this notification.
     */
    public function getUrl(): string
    {
        return match($this->type) {
            'event_approved', 'event_rejected' => route('events.my'),
            'job_approved',   'job_rejected'   => route('jobs.my'),
            'story_approved', 'story_rejected' => route('stories.my'),
            'group_invitation' => url('/groups/invitations'),
            'group_join', 'group_post_pending', 'group_member_joined', 'group_new_post' =>
                $this->group ? url('/groups/' . $this->group->slug) : url('/groups'),
            'group_post_approved' =>
                $this->group ? url('/groups/' . $this->group->slug) : url('/groups'),
            'chat_join_request'     => url('/chat'),
            'chat_group_invitation' => url('/chat/invitations'),
            default =>
                $this->post_id ? url('/posts/' . $this->post_id) : url('/home'),
        };
    }
}
