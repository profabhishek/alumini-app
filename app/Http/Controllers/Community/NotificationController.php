<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlumniUser;

class NotificationController extends Controller
{
    public function markRead(Request $request)
    {
        $user = AlumniUser::find(session('alumni_id'));
        if ($user) {
            $user->notifications_read_at = now();
            $user->save();
        }
        return response()->json(['ok' => true]);
    }

    public function personal()
    {
        $alumniId = session('alumni_id');

        $notifications = \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->with(['actor', 'group'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($n) {
                // Build destination URL
                if ($n->group_id && !$n->post_id) {
                    // Group-level notification (join, invitation)
                    $url = $n->group ? url('/groups/' . $n->group->slug) : url('/groups');
                } elseif ($n->post_id && $n->group_id) {
                    // Post inside a group — link to the group feed, JS will scroll to post
                    $url = $n->group
                        ? url('/groups/' . $n->group->slug) . '?highlight=' . $n->post_id
                        : url('/posts/' . $n->post_id);
                } elseif ($n->post_id) {
                    $url = url('/posts/' . $n->post_id);
                } else {
                    $url = url('/groups/invitations');
                }

                // Group label suffix e.g. " in Tech Hub"
                $groupLabel = $n->group ? ' in ' . $n->group->name : '';

                return [
                    'id'        => $n->id,
                    'type'      => $n->type,
                    'is_read'   => $n->is_read,
                    'message'   => $n->getMessage() . $groupLabel,
                    'preview'   => $n->preview,
                    'url'       => $url,
                    'actor'     => $n->actor?->full_name ?? 'Someone',
                    'avatar'    => $n->actor?->photo ? asset('storage/' . $n->actor->photo) : null,
                    'initials'  => strtoupper(substr($n->actor?->full_name ?? 'A', 0, 1)),
                    'time'      => $n->created_at->diffForHumans(),
                    'post_id'   => $n->post_id,
                    'group_id'  => $n->group_id,
                ];
            });

        $unreadCount = \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    public function markPersonalRead()
    {
        \App\Models\AlumniNotification::where('recipient_id', session('alumni_id'))
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function sidebarBadges()
    {
        $alumniId = session('alumni_id');
        if (!$alumniId) return response()->json([], 401);

        $role = session('alumni_role');
        $isAdmin = in_array($role, ['admin', 'super_admin']);
        $isStaff = in_array($role, ['admin', 'super_admin', 'moderator']);

        // Job badges
        $lastSeen = session('applications_last_seen')
            ? \Carbon\Carbon::parse(session('applications_last_seen')) : now();
        $myApplicationsBadge = \App\Models\JobApplication::where('alumni_id', $alumniId)
            ->where('status', '!=', 'submitted')
            ->where('updated_at', '>', $lastSeen)
            ->count();

        $myJobsLastSeen = session('my_jobs_last_seen')
            ? \Carbon\Carbon::parse(session('my_jobs_last_seen')) : now();
        $myJobsNewApplicantsBadge = \App\Models\JobApplication::whereHas('job', function ($q) use ($alumniId) {
            $q->where('created_by', $alumniId);
        })->where('created_at', '>', $myJobsLastSeen)->count();

        $moderationQueueBadge = $isAdmin ? \App\Models\Job::where('status', 'pending')->count() : 0;
        $jobTotal = $myApplicationsBadge + $moderationQueueBadge + $myJobsNewApplicantsBadge;

        // Event badges
        $myEventIds = \App\Models\Event::where('created_by', $alumniId)->pluck('id');
        $myEventsNewRegsBadge = 0;
        if ($myEventIds->isNotEmpty()) {
            $seenMap = session('events_regs_seen', []);
            foreach ($myEventIds as $eid) {
                $since = isset($seenMap[$eid]) ? \Carbon\Carbon::parse($seenMap[$eid]) : now();
                $myEventsNewRegsBadge += \App\Models\EventRegistration::where('event_id', $eid)
                    ->where('created_at', '>', $since)->count();
            }
        }
        $pendingEventsBadge = $isStaff ? \App\Models\Event::where('status', 'pending')->count() : 0;
        $eventTotal = $myEventsNewRegsBadge + $pendingEventsBadge;

        // Story badges
        $myStoriesLastSeen = session('my_stories_last_seen')
            ? \Carbon\Carbon::parse(session('my_stories_last_seen')) : now();
        $myStoriesUpdatesBadge = \App\Models\Story::where('created_by', $alumniId)
            ->where('status', '!=', 'pending')
            ->where('updated_at', '>', $myStoriesLastSeen)
            ->count();
        $pendingStoriesBadge = $isStaff ? \App\Models\Story::where('status', 'pending')->count() : 0;
        $storyTotal = $myStoriesUpdatesBadge + $pendingStoriesBadge;

        return response()->json([
            'event_total'        => $eventTotal,
            'pending_events'     => $pendingEventsBadge,
            'my_events'          => $myEventsNewRegsBadge,
            'job_total'          => $jobTotal,
            'my_jobs'            => $myJobsNewApplicantsBadge,
            'my_applications'    => $myApplicationsBadge,
            'mod_queue'          => $moderationQueueBadge,
            'story_total'        => $storyTotal,
            'pending_stories'    => $pendingStoriesBadge,
            'my_stories'         => $myStoriesUpdatesBadge,
        ]);
    }

    public function markOneRead(Request $request, $id)
    {
        $alumniId = session('alumni_id');
        if (!$alumniId) {
            return response()->json(['ok' => false], 401);
        }

        $notification = \App\Models\AlumniNotification::where('id', $id)
            ->where('recipient_id', $alumniId)
            ->first();

        if (!$notification) {
            return response()->json(['ok' => false], 404);
        }

        $notification->is_read = true;
        $notification->save();

        $unreadCount = \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->where('is_read', false)
            ->count();

        return response()->json(['ok' => true, 'unread_count' => $unreadCount]);
    }
}