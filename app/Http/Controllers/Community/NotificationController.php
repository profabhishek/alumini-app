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
                // System notifications (no actor)
                $isSystem = in_array($n->type, [
                    'event_approved', 'event_rejected',
                    'job_approved',   'job_rejected',
                    'story_approved', 'story_rejected',
                ]);

                // Group label suffix e.g. " in Tech Hub" (only for social types)
                $groupLabel = (!$isSystem && $n->group) ? ' in ' . $n->group->name : '';

                return [
                    'id'       => $n->id,
                    'type'     => $n->type,
                    'is_read'  => $n->is_read,
                    'message'  => $n->getMessage() . $groupLabel,
                    'preview'  => $n->preview,
                    'url'      => $n->getUrl(),
                    'actor'    => $isSystem ? 'Admin' : ($n->actor?->full_name ?? 'Someone'),
                    'avatar'   => (!$isSystem && $n->actor?->photo) ? asset('storage/' . $n->actor->photo) : null,
                    'initials' => $isSystem ? 'A' : strtoupper(substr($n->actor?->full_name ?? 'A', 0, 1)),
                    'time'     => $n->created_at->diffForHumans(),
                    'post_id'  => $n->post_id,
                    'group_id' => $n->group_id,
                ];
            });

        $socialUnread = \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->where('is_read', false)
            ->count();

        // Content unread: published after bell was last opened
        $user           = AlumniUser::find($alumniId);
        $bellLastOpened = $user?->notifications_read_at ?? now()->subDays(7);
        $contentUnread  = 0;
        $contentUnread += \App\Models\Job::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $contentUnread += \App\Models\Event::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $contentUnread += \App\Models\Story::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $contentUnread += \App\Models\Notice::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $contentUnread += \App\Models\News::where('status','published')->where('published_at','>',$bellLastOpened)->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $socialUnread + $contentUnread,
        ]);
    }

    public function markPersonalRead()
    {
        $alumniId = session('alumni_id');

        // Mark all social notifications read
        \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Stamp notifications_read_at so content badge resets too
        $user = AlumniUser::find($alumniId);
        if ($user) {
            $user->notifications_read_at = now();
            $user->save();
        }

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

    /**
     * Bell dropdown feed — merges:
     * 1. Personal social notifications (likes, comments, replies, group activity)
     * 2. Recent published content (jobs, events, stories, notices, news)
     * All sorted newest-first.
     */
    public function feed()
    {
        $alumniId = session('alumni_id');
        $items = collect();

        // ── Personal social notifications ────────────────────────────────────
        if ($alumniId) {
            $socialTypes = ['post_like','comment_like','comment','reply',
                            'group_invitation','group_join','group_post_pending','group_post_approved','group_member_joined','group_new_post',
                            'chat_join_request','chat_group_invitation'];

            \App\Models\AlumniNotification::where('recipient_id', $alumniId)
                ->whereIn('type', $socialTypes)
                ->with(['actor', 'group'])
                ->latest()
                ->take(10)
                ->get()
                ->each(function ($n) use ($items) {
                    $groupLabel = $n->group ? ' in ' . $n->group->name : '';
                    $items->push([
                        'kind'     => 'social',
                        'type'     => $n->type,
                        'title'    => $n->getMessage() . $groupLabel,
                        'preview'  => $n->preview,
                        'url'      => $n->getUrl(),
                        'avatar'   => $n->actor?->photo ? asset('storage/' . $n->actor->photo) : null,
                        'initials' => strtoupper(substr($n->actor?->full_name ?? 'A', 0, 1)),
                        'is_read'  => $n->is_read,
                        'date'     => $n->updated_at->toISOString(),
                        'time'     => $n->updated_at->diffForHumans(),
                    ]);
                });
        }

        // ── Published content ────────────────────────────────────────────────
        // Detect whether the published_at column exists yet (migration may not have run)
        $hasPubAt = \Illuminate\Support\Facades\Schema::hasColumn('jobs', 'published_at');

        // Jobs
        $jobCols  = $hasPubAt ? ['id','title','slug','published_at','created_at'] : ['id','title','slug','created_at'];
        $jobQuery = \App\Models\Job::where('status', 'published');
        if ($hasPubAt) { $jobQuery->orderByDesc('published_at'); } else { $jobQuery->latest(); }
        $jobQuery->take(5)->get($jobCols)->each(function ($j) use ($items, $hasPubAt) {
            $ts = \Carbon\Carbon::parse(($hasPubAt ? $j->published_at : null) ?? $j->created_at);
            $items->push([
                'kind'  => 'content',
                'type'  => 'job',
                'title' => $j->title,
                'url'   => route('jobs.show', $j->slug ?? $j->id),
                'date'  => $ts->toISOString(),
                'time'  => $ts->diffForHumans(),
            ]);
        });

        // Events
        $hasEvPubAt = \Illuminate\Support\Facades\Schema::hasColumn('events', 'published_at');
        $evCols  = $hasEvPubAt ? ['id','title','slug','published_at','created_at'] : ['id','title','slug','created_at'];
        $evQuery = \App\Models\Event::where('status', 'published');
        if ($hasEvPubAt) { $evQuery->orderByDesc('published_at'); } else { $evQuery->latest(); }
        $evQuery->take(5)->get($evCols)->each(function ($e) use ($items, $hasEvPubAt) {
            $ts = \Carbon\Carbon::parse(($hasEvPubAt ? $e->published_at : null) ?? $e->created_at);
            $items->push([
                'kind'  => 'content',
                'type'  => 'event',
                'title' => $e->title,
                'url'   => route('events.show', $e->slug ?? $e->id),
                'date'  => $ts->toISOString(),
                'time'  => $ts->diffForHumans(),
            ]);
        });

        // Stories
        $hasStPubAt = \Illuminate\Support\Facades\Schema::hasColumn('stories', 'published_at');
        $stCols  = $hasStPubAt ? ['id','title','slug','published_at','created_at'] : ['id','title','slug','created_at'];
        $stQuery = \App\Models\Story::where('status', 'published');
        if ($hasStPubAt) { $stQuery->orderByDesc('published_at'); } else { $stQuery->latest(); }
        $stQuery->take(5)->get($stCols)->each(function ($s) use ($items, $hasStPubAt) {
            $ts = \Carbon\Carbon::parse(($hasStPubAt ? $s->published_at : null) ?? $s->created_at);
            $items->push([
                'kind'  => 'content',
                'type'  => 'story',
                'title' => $s->title,
                'url'   => route('stories.show', $s->slug ?? $s->id),
                'date'  => $ts->toISOString(),
                'time'  => $ts->diffForHumans(),
            ]);
        });

        // Notices
        \App\Models\Notice::where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')->take(5)->get(['id','title','slug','published_at'])
            ->each(function ($n) use ($items) {
                $ts = \Carbon\Carbon::parse($n->published_at);
                $items->push([
                    'kind'  => 'content',
                    'type'  => 'notice',
                    'title' => $n->title,
                    'url'   => route('notice.show', $n->slug),
                    'date'  => $ts->toISOString(),
                    'time'  => $ts->diffForHumans(),
                ]);
            });

        // News
        $newsRows = \App\Models\News::where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')->take(5)->get(['id','title','slug','published_at']);
        \Illuminate\Support\Facades\Log::info('FEED news rows: ' . $newsRows->count() . ' | ' . $newsRows->pluck('title')->implode(', '));
        $newsRows->each(function ($n) use ($items) {
                $ts = \Carbon\Carbon::parse($n->published_at);
                try {
                    $url = route('news.show', $n->slug);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('news.show route error: ' . $e->getMessage() . ' slug=' . $n->slug);
                    $url = '/news/' . $n->slug;
                }
                $items->push([
                    'kind'  => 'content',
                    'type'  => 'news',
                    'title' => $n->title,
                    'url'   => $url,
                    'date'  => $ts->toISOString(),
                    'time'  => $ts->diffForHumans(),
                ]);
            });

        // Build final feed:
        // - Take top 5 social notifications
        // - Take top 2 of EACH content type (so every type always appears)
        // - Merge and sort newest-first
        $social = $items->where('kind', 'social')->sortByDesc('date')->take(5)->values();

        $contentTypes = ['job', 'event', 'story', 'notice', 'news'];
        $content = collect();
        foreach ($contentTypes as $type) {
            $items->where('kind', 'content')->where('type', $type)
                ->sortByDesc('date')->take(2)
                ->each(fn($i) => $content->push($i));
        }

        $merged = $social->merge($content)->sortByDesc('date')->values();

        \Illuminate\Support\Facades\Log::info('FEED merged types: ' . $merged->pluck('type')->implode(', '));

        return response()->json([
            'items' => $merged,
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