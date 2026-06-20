@php
    $alumniId   = session('alumni_id');
    $alumniRole = session('alumni_role');

    $currentAlumniUser = $alumniId
        ? \App\Models\AlumniUser::find($alumniId)
        : null;

    // ── Personal unread notification count (social + new content) ──────────
    $personalUnreadCount = 0;
    if ($alumniId) {
        // Social unread
        $personalUnreadCount = \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->where('is_read', false)
            ->count();

        // Content unread: count items published after bell was last opened
        $bellLastOpened = $currentAlumniUser?->notifications_read_at
            ?? \Carbon\Carbon::now()->subDays(7); // default: last 7 days

        $newContent = 0;
        $newContent += \App\Models\Job::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $newContent += \App\Models\Event::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $newContent += \App\Models\Story::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $newContent += \App\Models\Notice::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $newContent += \App\Models\News::where('status','published')->where('published_at','>',$bellLastOpened)->count();
        $personalUnreadCount += $newContent;
    }

    // ── Job sidebar badges ───────────────────────────────────────────────
    $myApplicationsBadge      = 0;
    $moderationQueueBadge     = 0;
    $myJobsNewApplicantsBadge = 0;

    if ($alumniId) {
        $appLastSeen = session('applications_last_seen')
            ? \Carbon\Carbon::parse(session('applications_last_seen'))
            : now();

        $myApplicationsBadge = \App\Models\JobApplication::where('alumni_id', $alumniId)
            ->where('status', '!=', 'submitted')
            ->where('updated_at', '>', $appLastSeen)
            ->count();

        $myJobsLastSeen = session('my_jobs_last_seen')
            ? \Carbon\Carbon::parse(session('my_jobs_last_seen'))
            : now();

        // Use a join instead of whereHas to avoid correlated subquery
        $myJobsNewApplicantsBadge = \App\Models\JobApplication::join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->where('jobs.created_by', $alumniId)
            ->where('job_applications.created_at', '>', $myJobsLastSeen)
            ->count();

        if (in_array($alumniRole, ['admin', 'super_admin', 'moderator'])) {
            $moderationQueueBadge = \Cache::remember('pending_jobs_count', 60, fn() =>
                \App\Models\Job::where('status', 'pending')->count()
            );
        }
    }

    // ── Event sidebar badges ─────────────────────────────────────────────
    $pendingEventsBadge   = 0;
    $myEventsNewRegsBadge = 0;

    if ($alumniId) {
        $myEventIds = \App\Models\Event::where('created_by', $alumniId)->pluck('id');

        if ($myEventIds->isNotEmpty()) {
            $seenMap = session('events_regs_seen', []);

            // Find the earliest "since" across all events to bound one query
            $earliestSince = now();
            foreach ($myEventIds as $eid) {
                $s = isset($seenMap[$eid]) ? \Carbon\Carbon::parse($seenMap[$eid]) : now()->subYears(10);
                if ($s->lt($earliestSince)) $earliestSince = $s;
            }

            // ONE query instead of N queries
            $regsGrouped = \App\Models\EventRegistration::whereIn('event_id', $myEventIds)
                ->where('created_at', '>', $earliestSince)
                ->selectRaw('event_id, created_at')
                ->get()
                ->groupBy('event_id');

            foreach ($myEventIds as $eid) {
                $since = isset($seenMap[$eid]) ? \Carbon\Carbon::parse($seenMap[$eid]) : now()->subYears(10);
                if (isset($regsGrouped[$eid])) {
                    $myEventsNewRegsBadge += $regsGrouped[$eid]->filter(fn($r) => $r->created_at->gt($since))->count();
                }
            }
        }

        if (in_array($alumniRole, ['admin', 'super_admin', 'moderator'])) {
            $pendingEventsBadge = \Cache::remember('pending_events_count', 60, fn() =>
                \App\Models\Event::where('status', 'pending')->count()
            );
        }
    }

    // ── Story sidebar badges ─────────────────────────────────────────────
    $pendingStoriesBadge   = 0;
    $myStoriesUpdatesBadge = 0;

    if ($alumniId) {
        $myStoriesLastSeen = session('my_stories_last_seen')
            ? \Carbon\Carbon::parse(session('my_stories_last_seen'))
            : now();

        $myStoriesUpdatesBadge = \App\Models\Story::where('created_by', $alumniId)
            ->where('status', '!=', 'pending')
            ->where('updated_at', '>', $myStoriesLastSeen)
            ->count();

        if (in_array($alumniRole, ['admin', 'super_admin', 'moderator'])) {
            $pendingStoriesBadge = \Cache::remember('pending_stories_count', 60, fn() =>
                \App\Models\Story::where('status', 'pending')->count()
            );
        }
    }

    $emailPrefs = $currentAlumniUser?->email_notifications ?? [];

    // ── Sidebar content — cached for 60 seconds (non-user-specific) ──────
    $sidebarContent = \Cache::remember('sidebar_content', 60, function () {
        return [
            'latestJobs'           => \App\Models\Job::where('status', 'published')->latest()->take(1)->get(),
            'latestNotices'        => \App\Models\Notice::published()->latest('published_at')->take(1)->get(),
            'sidebarUpcomingEvents'=> \App\Models\Event::where('status', 'published')
                                        ->whereDate('start_date', '>=', now()->toDateString())
                                        ->orderBy('start_date')->take(2)->get(),
            'notifNews'            => \App\Models\News::published()->latest('published_at')->take(3)->get(),
            'notifEvents'          => \App\Models\Event::where('status', 'published')->latest()->take(3)->get(),
            'notifStories'         => \App\Models\Story::published()->latest()->take(3)->get(),
            'notifJobs'            => \App\Models\Job::where('status', 'published')->latest()->take(3)->get(),
        ];
    });

    $latestJobs            = $sidebarContent['latestJobs'];
    $latestNotices         = $sidebarContent['latestNotices'];
    $sidebarUpcomingEvents = $sidebarContent['sidebarUpcomingEvents'];
    $notifNews             = $sidebarContent['notifNews'];

    $notifEvents  = ($emailPrefs['events']  ?? true) ? $sidebarContent['notifEvents']  : collect();
    $notifStories = ($emailPrefs['stories'] ?? true) ? $sidebarContent['notifStories'] : collect();
    $notifJobs    = ($emailPrefs['jobs']    ?? true) ? $sidebarContent['notifJobs']    : collect();

    $notificationItems = collect()
        ->merge($notifNews->map(fn($n) => [
            'id'    => 'news_' . $n->id,
            'type'  => 'news',
            'title' => $n->title,
            'date'  => $n->published_at,
            'url'   => route('news.show', $n->slug),
        ]))
        ->merge($latestNotices->map(fn($n) => [
            'id'    => 'notice_' . $n->id,
            'type'  => 'notice',
            'title' => $n->title,
            'date'  => $n->published_at,
            'url'   => route('notice.show', $n->slug),
        ]))
        ->merge($notifEvents->map(fn($e) => [
            'id'    => 'event_' . $e->id,
            'type'  => 'event',
            'title' => $e->title,
            'date'  => $e->created_at,
            'url'   => route('events.show', $e->slug ?? $e->id),
        ]))
        ->merge($notifStories->map(fn($s) => [
            'id'    => 'story_' . $s->id,
            'type'  => 'story',
            'title' => $s->title,
            'date'  => $s->created_at,
            'url'   => route('stories.show', $s->slug),
        ]))
        ->merge($notifJobs->map(fn($j) => [
            'id'    => 'job_' . $j->id,
            'type'  => 'job',
            'title' => $j->title,
            'date'  => $j->created_at,
            'url'   => \Route::has('jobs.show')
                ? route('jobs.show', $j->slug ?? $j->id)
                : route('jobs.index'),
        ]))
        ->sortByDesc('date')
        ->take(8)
        ->values();

    $newNotificationsCount = 0;
@endphp

<!DOCTYPE html>
@php
    $alumniAppearance = $currentAlumniUser?->appearance ?? 'light';
@endphp
<html lang="en" class="{{ $alumniAppearance === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport"
     content="width=device-width, initial-scale=1,
              viewport-fit=cover,
              interactive-widget=resizes-content">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Community') – ICCR Alumni</title>

    {{-- Google Fonts — non-render-blocking --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@600;700&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@600;700&display=swap"></noscript>

    {{-- Community base styles --}}
    <link rel="stylesheet" href="{{ asset('css/community/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/community/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/community/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/community/header.css') }}">

    <style>.req { color: #e53e3e !important; margin-left: 2px; font-weight: 700; }</style>
    @stack('styles')
</head>
<body class="community-body">

    {{-- ============================================================
         TOP HEADER
    ============================================================ --}}
    <header class="comm-header" id="commHeader">
        <div class="comm-header__inner">

            {{-- Logo --}}
            <a href="{{ url('/home') }}" class="comm-logo">
                <img loading="lazy" src="{{ asset('images/iccr_background.png') }}" alt="ICCR" class="comm-logo__img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="comm-logo__fallback" style="">
                    <span class="comm-logo__abbr">ICCR</span>
                    <span class="comm-logo__full">Alumni Portal</span>
                </div>
            </a>

            {{-- Search --}}
            <div class="comm-search" id="commSearch">
                <svg class="comm-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" class="comm-search__input" id="commSearchInput" placeholder="Search alumni, posts, events..." autocomplete="off">
            </div>

            {{-- Right controls --}}
            <nav class="comm-header__right">
                {{-- Messages --}}
                <a href="{{ route('chat.index') }}" class="comm-icon-btn comm-icon-btn--badge" title="Messages" id="headerMsgBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    <span class="badge" id="headerMsgBadge" style="display:none;">0</span>
                </a>

                {{-- Notifications --}}
                <div class="comm-notif-menu" id="notifMenuToggle">
                    <button type="button" class="comm-icon-btn comm-icon-btn--badge" title="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
                        </svg>
                        @if($personalUnreadCount > 0)
                            <span class="badge">{{ $personalUnreadCount > 9 ? '9+' : $personalUnreadCount }}</span>
                        @endif
                    </button>

                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-dropdown__header">
                            Recent Activity
                            <button type="button" id="notifClearAll" class="notif-clear-btn">Clear all</button>
                        </div>
                        <div class="notif-dropdown__body">
                            <div id="personalNotifList">
                                <div class="notif-empty" style="padding:20px 16px;font-size:13px;color:#9ca3af;text-align:center;">Loading…</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- User dropdown --}}
                <div class="comm-user-menu" id="userMenuToggle">
                    <div class="avatar avatar--sm">
                        @if(session('alumni_avatar'))
                            <img loading="lazy" src="{{ asset('storage/' . session('alumni_avatar')) }}" alt="{{ session('alumni_name') }}">
                        @else
                            <span class="avatar-initials">{{ strtoupper(substr(session('alumni_name', 'A'), 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="comm-user-info">
                        <span class="comm-user-label">Welcome</span>
                        <span class="comm-user-name">{{ session('alumni_name', 'Alumni') }}</span>
                    </div>
                    <svg class="comm-user-caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="6,9 12,15 18,9"/>
                    </svg>

                    {{-- Dropdown --}}
                    <div class="user-dropdown" id="userDropdown">
                        <a href="{{ route('profile.index') }}" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            My Profile
                        </a>
                        <a href="{{ url('/settings') }}" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('logout') }}" class="dropdown-item dropdown-item--danger"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                    </div>
                </div>

                {{-- Mobile hamburger --}}
                <button class="comm-hamburger" id="sidebarToggle" title="Menu">
                    <span></span><span></span><span></span>
                </button>
            </nav>
        </div>
    </header>

    {{-- ============================================================
         APP SHELL
    ============================================================ --}}
    <div class="comm-shell">

        {{-- LEFT SIDEBAR --}}
        <aside class="comm-sidebar" id="commSidebar">

            {{-- Nav items --}}
            <nav class="sidebar-nav">
                @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
                <a href="{{ route('admin.dashboard') }}" class="sidebar-nav__item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </span>
                    <span class="nav-label">Dashboard</span>
                </a>
                @endif
                <a href="{{ url('/home') }}" class="sidebar-nav__item {{ request()->is('home') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                    </span>
                    <span class="nav-label">Home</span>
                </a>

                {{-- MY EVENT --}}
                <div class="sidebar-expandable">
                @php $eventTotalBadge = $myEventsNewRegsBadge + $pendingEventsBadge; @endphp
                    <div class="sidebar-nav__item sidebar-nav__item--expandable" onclick="toggleSidebarMenu('event')">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                        <span class="nav-label">My Event</span>
                        <span id="sb-badge-event-total" class="sidebar-child-badge sidebar-child-badge--notif" style="margin-left:6px;margin-right:auto;{{ $eventTotalBadge > 0 ? '' : 'display:none;' }}">{{ $eventTotalBadge > 9 ? '9+' : $eventTotalBadge }}</span>
                        <svg class="nav-chevron" id="chev-event" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="9,18 15,12 9,6"/>
                        </svg>
                    </div>
                    <div class="sidebar-children" id="kids-event">

                        {{-- Admin/Moderator only links --}}
                        @php
                            $role = session('alumni_role');
                            $perms = session('alumni_permissions', []);
                            $isAdminAbove = in_array($role, ['admin', 'super_admin']);
                            $canApprove = $isAdminAbove || ($role === 'moderator' && !empty($perms['approve_events']));
                            $canCategories = $isAdminAbove || ($role === 'moderator' && !empty($perms['manage_event_categories']));
                        @endphp

                        @if($canApprove)
                            <a href="{{ route('admin.events.pending') }}" class="sidebar-child">
                                <span class="sidebar-child-dot"></span>Pending Events
                                <span id="sb-badge-pending-events" class="sidebar-child-badge sidebar-child-badge--notif" style="{{ $pendingEventsBadge > 0 ? '' : 'display:none;' }}">{{ $pendingEventsBadge > 9 ? '9+' : $pendingEventsBadge }}</span>
                            </a>
                        @endif

                        @if($canCategories)
                            <a href="{{ route('admin.event-categories.index') }}" class="sidebar-child">
                                <span class="sidebar-child-dot"></span>Event Category
                            </a>
                        @endif

                        {{-- All users see these --}}
                        <a href="{{ route('events.create') }}" class="sidebar-child {{ request()->routeIs('events.create') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>Create Event
                        </a>
                        <a href="{{ route('events.my') }}" class="sidebar-child {{ request()->routeIs('events.my') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>My Events
                            <span id="sb-badge-my-events" class="sidebar-child-badge sidebar-child-badge--notif" style="{{ $myEventsNewRegsBadge > 0 ? '' : 'display:none;' }}">{{ $myEventsNewRegsBadge > 9 ? '9+' : $myEventsNewRegsBadge }}</span>
                        </a>
                        <a href="{{ route('events.index') }}" class="sidebar-child {{ request()->routeIs('events.index') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>Browse Events
                        </a>

                        {{-- Admin / Super Admin only --}}
                        @if($isAdminAbove)
                            <a href="{{ route('admin.events.index') }}"
                            class="sidebar-child sidebar-child--admin {{ request()->routeIs('admin.events.index') ? 'active' : '' }}">
                                <span class="sidebar-child-dot"></span>All Events
                                <span class="sidebar-child-badge">Admin</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- JOB POST --}}
                <div class="sidebar-expandable">
                @php
                    $jobPostTotalBadge = $myApplicationsBadge + $moderationQueueBadge + $myJobsNewApplicantsBadge;
                @endphp

                <div class="sidebar-nav__item sidebar-nav__item--expandable" onclick="toggleSidebarMenu('job')">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                            <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                        </svg>
                    </span>
                    <span class="nav-label">Job Post</span>
                    <span id="sb-badge-job-total" class="sidebar-child-badge sidebar-child-badge--notif" style="margin-left:6px;margin-right:auto;{{ $jobPostTotalBadge > 0 ? '' : 'display:none;' }}">{{ $jobPostTotalBadge > 9 ? '9+' : $jobPostTotalBadge }}</span>
                    <svg class="nav-chevron" id="chev-job" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="9,18 15,12 9,6"/>
                    </svg>
                </div>

                    <div class="sidebar-children" id="kids-job">
                        <a href="{{ route('jobs.create') }}" class="sidebar-child">
                            <span class="sidebar-child-dot"></span>Create Job Post
                        </a>

                        <a href="{{ route('jobs.my') }}" class="sidebar-child">
                            <span class="sidebar-child-dot"></span>My Jobs
                            <span id="sb-badge-my-jobs" class="sidebar-child-badge sidebar-child-badge--notif" style="{{ $myJobsNewApplicantsBadge > 0 ? '' : 'display:none;' }}">{{ $myJobsNewApplicantsBadge > 9 ? '9+' : $myJobsNewApplicantsBadge }}</span>
                        </a>

                        <a href="{{ route('jobs.index') }}" class="sidebar-child">
                            <span class="sidebar-child-dot"></span>Browse Jobs
                        </a>

                        <a href="{{ route('jobs.my-applications') }}"
                            class="sidebar-child {{ request()->routeIs('jobs.my-applications') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>My Applications
                            <span id="sb-badge-my-applications" class="sidebar-child-badge sidebar-child-badge--notif" style="{{ $myApplicationsBadge > 0 ? '' : 'display:none;' }}">{{ $myApplicationsBadge > 9 ? '9+' : $myApplicationsBadge }}</span>
                        </a>

                        @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
                        <a href="{{ route('admin.jobs.pending') }}" class="sidebar-child sidebar-child-admin">
                            <span class="sidebar-child-dot"></span>Moderation Queue
                            <span id="sb-badge-mod-queue" class="sidebar-child-badge sidebar-child-badge--notif" style="{{ $moderationQueueBadge > 0 ? '' : 'display:none;' }}">{{ $moderationQueueBadge > 9 ? '9+' : $moderationQueueBadge }}</span>
                        </a>

                            <a href="{{ route('admin.jobs.index') }}" class="sidebar-child sidebar-child-admin">
                                <span class="sidebar-child-dot"></span>Manage Jobs
                            </a>
                        @endif
                    </div>
                </div>

                {{-- STORIES --}}
                <div class="sidebar-expandable">
                @php $storiesTotalBadge = $myStoriesUpdatesBadge + $pendingStoriesBadge; @endphp
                    <div class="sidebar-nav__item sidebar-nav__item--expandable" onclick="toggleSidebarMenu('story')">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Stories</span>
                        <span id="sb-badge-story-total" class="sidebar-child-badge sidebar-child-badge--notif" style="margin-left:6px;margin-right:auto;{{ $storiesTotalBadge > 0 ? '' : 'display:none;' }}">{{ $storiesTotalBadge > 9 ? '9+' : $storiesTotalBadge }}</span>
                        <svg class="nav-chevron" id="chev-story" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="9,18 15,12 9,6"/>
                        </svg>
                    </div>
                    <div class="sidebar-children" id="kids-story">

                        @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
                            <a href="{{ route('admin.stories.pending') }}"
                            class="sidebar-child {{ request()->routeIs('admin.stories.pending') ? 'active' : '' }}">
                                <span class="sidebar-child-dot" style="background:#e8640c;"></span>Pending Story
                                <span id="sb-badge-pending-stories" class="sidebar-child-badge sidebar-child-badge--notif" style="{{ $pendingStoriesBadge > 0 ? '' : 'display:none;' }}">{{ $pendingStoriesBadge > 9 ? '9+' : $pendingStoriesBadge }}</span>
                            </a>
                        @endif

                        @if(in_array(session('alumni_role'), ['admin', 'super_admin', 'moderator']))
                            <a href="{{ route('admin.story-categories.index') }}"
                            class="sidebar-child {{ request()->routeIs('admin.story-categories.*') ? 'active' : '' }}">
                                <span class="sidebar-child-dot"></span>Story Categories
                            </a>
                        @endif

                        <a href="{{ route('stories.create') }}"
                        class="sidebar-child {{ request()->routeIs('stories.create') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>Create Story
                        </a>

                        <a href="{{ route('stories.my') }}"
                        class="sidebar-child {{ request()->routeIs('stories.my') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>My Story
                            <span id="sb-badge-my-stories" class="sidebar-child-badge sidebar-child-badge--notif" style="{{ $myStoriesUpdatesBadge > 0 ? '' : 'display:none;' }}">{{ $myStoriesUpdatesBadge > 9 ? '9+' : $myStoriesUpdatesBadge }}</span>
                        </a>

                        <a href="{{ route('stories.index') }}"
                        class="sidebar-child {{ request()->routeIs('stories.index') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>All Story
                        </a>

                        @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
                            <a href="{{ route('admin.stories.index') }}"
                            class="sidebar-child sidebar-child--admin {{ request()->routeIs('admin.stories.index') ? 'active' : '' }}">
                                <span class="sidebar-child-dot"></span>Manage Stories
                                <span class="sidebar-child-badge">Admin</span>
                            </a>
                        @endif
                    </div>
                </div>


                <a href="{{ route('alumni.directory') }}" class="sidebar-nav__item">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    </span>
                    <span class="nav-label">Alumni</span>
                </a>

                <a href="{{ route('chat.index') }}"
                class="sidebar-nav__item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                    </span>
                    <span class="nav-label">Messages</span>
                    <span class="nav-msg-badge" id="sidebarMsgBadge" style="display:none;"></span>
                </a>
                <a href="/groups" class="sidebar-nav__item {{ request()->is('groups*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                        </svg>
                    </span>
                    <span class="nav-label">Community Groups</span>
                    <span id="sb-badge-groups" class="sidebar-child-badge sidebar-child-badge--notif" style="margin-left:6px;display:none;"></span>
                </a>

                <a href="{{ route('profile.index') }}" class="sidebar-nav__item">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <span class="nav-label">Profile</span>
                </a>
                <a href="{{ route('settings.index') }}" class="sidebar-nav__item">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                    </span>
                    <span class="nav-label">Settings</span>
                </a>

                @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
                    <a href="{{ route('admin.alumni-data.index') }}" class="sidebar-nav__item">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                        </span>
                        <span class="nav-label">Alumni Data</span>
                    </a>
                @endif

                {{-- ============================================================
                     ADMIN PANEL SECTION (admin / super_admin only)
                ============================================================ --}}
                @if(in_array(session('alumni_role'), ['admin', 'super_admin']))

                    {{-- Separator --}}
                    <div class="sidebar-separator">
                        <span class="sidebar-separator__label">Admin Panel</span>
                    </div>

                    <a href="{{ route('admin.users.pending') }}"
                       class="sidebar-nav__item {{ request()->routeIs('admin.users.pending') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <line x1="19" y1="8" x2="19" y2="14"/>
                                <line x1="16" y1="11" x2="22" y2="11"/>
                            </svg>
                        </span>
                        <span class="nav-label">Pending Requests</span>
                        <span id="sb-badge-pending-users" class="sidebar-child-badge sidebar-child-badge--notif" style="display:none;"></span>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       class="sidebar-nav__item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                                <path d="M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                        </span>
                        <span class="nav-label">Manage Admins</span>
                    </a>

                    <a href="{{ route('admin.alumni.index') }}"
                       class="sidebar-nav__item {{ request()->routeIs('admin.alumni.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        </span>
                        <span class="nav-label">Alumni Management</span>
                    </a>

                    <a href="{{ route('admin.news.index') }}"
                       class="sidebar-nav__item {{ request()->routeIs('admin.news.*') || request()->routeIs('admin.news-categories.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>
                        </span>
                        <span class="nav-label">News</span>
                    </a>

                    <a href="{{ route('admin.notices.index') }}"
                       class="sidebar-nav__item {{ request()->routeIs('admin.notices.*') || request()->routeIs('admin.notice-categories.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                        </span>
                        <span class="nav-label">Notices</span>
                    </a>

                    <a href="{{ route('admin.gallery.index') }}"
                        class="sidebar-nav__item {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 00-2.828 0L6 21"/></svg>
                            </span>
                            <span class="nav-label">Image Gallery</span>
                    </a>

                    <a href="{{ route('admin.newsletter.index') }}"
                       class="sidebar-nav__item {{ request()->routeIs('admin.newsletter.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <span class="nav-label">Newsletter</span>
                        <span id="sb-badge-newsletter" class="sidebar-child-badge sidebar-child-badge--notif" style="display:none;"></span>
                    </a>

                    @if(session('alumni_role') === 'super_admin')
                        <a href="{{ route('admin.users.create-admin') }}"
                           class="sidebar-nav__item {{ request()->routeIs('admin.users.create-admin') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="8.5" cy="7" r="4"/>
                                    <line x1="20" y1="8" x2="20" y2="14"/>
                                    <line x1="17" y1="11" x2="23" y2="11"/>
                                    <path d="M1 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/>
                                </svg>
                            </span>
                            <span class="nav-label">Create Admin</span>
                            <span class="sidebar-child-badge">Super</span>
                        </a>
                    @endif

                @endif
            </nav>

            <div class="sidebar-footer">
                <a href="{{ route('logout') }}" class="sidebar-logout"
                   onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="comm-main">
            <div class="comm-content-grid {{ View::hasSection('hideRightSidebar') ? 'comm-content-grid--full' : '' }}">
                {{-- Center --}}
                <div class="comm-center">
                    @yield('content')
                </div>

                @unless(View::hasSection('hideRightSidebar'))
                {{-- RIGHT SIDEBAR --}}
                <aside class="comm-right">

                    {{-- Jobs widget --}}
                    <div class="widget card">
                        <div class="widget-header">
                            <h3 class="widget-title">Jobs</h3>
                            <a href="{{ route('jobs.index') }}" class="widget-see-all">
                                See All
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="9,18 15,12 9,6"/>
                                </svg>
                            </a>
                        </div>

                        <div class="widget-body">

                            @forelse($latestJobs as $job)

                                @php
                                    // jobs.show doesn't exist yet — falls back to the jobs index so this
                                    // never throws a RouteNotFoundException. Once jobs.show is added,
                                    // this automatically starts linking to the real job detail page.
                                    $jobUrl = \Route::has('jobs.show')
                                        ? route('jobs.show', $job->slug ?? $job->id)
                                        : route('jobs.index');
                                @endphp
                                <div class="widget-job-item">
                                    <div class="widget-job-thumb">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                                            <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                        </svg>
                                    </div>

                                    <div class="widget-job-info">

                                        <a href="{{ $jobUrl }}" class="widget-job-title">
                                            {{ $job->title }}
                                        </a>

                                        <span class="widget-job-meta">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                            {{ $job->created_at->format('d M Y') }}
                                        </span>

                                        <div class="widget-job-tags">

                                            <span class="widget-tag">
                                                {{ $job->job_type }}
                                            </span>

                                            @if($job->location)
                                                <span class="widget-tag">
                                                    {{ $job->location }}
                                                </span>
                                            @endif

                                            @if($job->salary_min || $job->salary_max)
                                                <span class="widget-tag">
                                                    ${{ number_format($job->salary_min) }}
                                                    @if($job->salary_max)
                                                        - ${{ number_format($job->salary_max) }}
                                                    @endif
                                                </span>
                                            @endif

                                        </div>

                                        <a href="{{ $jobUrl }}" class="widget-more-link">
                                            More Details
                                        </a>

                                    </div>
                                </div>

                            @empty

                                <div class="text-muted">
                                    No jobs available.
                                </div>

                            @endforelse

                        </div>
                    </div>

                    {{-- Notice widget --}}
                    <div class="widget card">
                        <div class="widget-header">
                            <h3 class="widget-title">Notice</h3>
                            <a href="{{ route('notice') }}" class="widget-see-all">
                                See All
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg>
                            </a>
                        </div>
                        <div class="widget-body">

                            @forelse($latestNotices as $notice)
                                <a href="{{ route('notice.show', $notice->slug) }}" class="widget-notice-item">
                                    <div class="widget-notice-thumb">
                                        @if($notice->image)
                                            <img loading="lazy" src="{{ asset('storage/' . $notice->image) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                                        @else
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                        @endif
                                    </div>
                                    <div class="widget-notice-info">
                                        <span class="widget-notice-date">{{ $notice->published_at->format('M d, Y') }}</span>
                                        <span class="widget-notice-title">{{ $notice->title }}</span>
                                        <span class="widget-notice-desc">
                                            {{ \Illuminate\Support\Str::limit($notice->excerpt ?: strip_tags($notice->body), 70) }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <p style="font-size:13px;color:#9ca3af;padding:8px 0;">No notices available.</p>
                            @endforelse

                        </div>
                    </div>

                    {{-- Upcoming Events widget --}}
                    <div class="widget card">
                        <div class="widget-header">
                            <h3 class="widget-title">Upcoming Events</h3>
                            <a href="{{ route('events.index', ['filter' => 'upcoming']) }}" class="widget-see-all">
                                See All
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg>
                            </a>
                        </div>
                        <div class="widget-body">

                            @forelse($sidebarUpcomingEvents as $upEvent)
                                <a href="{{ route('events.show', $upEvent->slug) }}" class="widget-event-item">
                                    <div class="widget-event-date">
                                        <span class="widget-event-month">{{ $upEvent->start_date->format('M') }}</span>
                                        <span class="widget-event-day">{{ $upEvent->start_date->format('j') }}</span>
                                    </div>
                                    <div class="widget-event-info">
                                        <span class="widget-event-title">{{ $upEvent->title }}</span>
                                        <span class="widget-event-loc">
                                            {{ $upEvent->location ?: 'Online Event' }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <p style="font-size:13px;color:#9ca3af;padding:8px 0;">No upcoming events.</p>
                            @endforelse

                        </div>
                    </div>

                </aside>
                @endunless

            </div>
        </main>
    </div>

    {{-- Sidebar overlay (mobile) --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="{{ asset('js/community/layout.js') }}"></script>
    <script src="{{ asset('js/community/confirm-modal.js') }}"></script>
    @stack('scripts')

       @if(session('alumni_id'))
        <script>
        (function () {
            const URL_SIDEBAR = "{{ route('notifications.sidebar-badges') }}";
            const POLL_MS     = 10000;

            function setSidebarBadge(id, count) {
                const el = document.getElementById(id);
                if (!el) return;
                if (count > 0) {
                    el.textContent   = count > 9 ? '9+' : count;
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            }

            window.fetchSidebarBadges = fetchSidebarBadges;
            function fetchSidebarBadges() {
                fetch(URL_SIDEBAR, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(d => {
                    setSidebarBadge('sb-badge-event-total',      d.event_total);
                    setSidebarBadge('sb-badge-pending-events',   d.pending_events);
                    setSidebarBadge('sb-badge-my-events',        d.my_events);
                    setSidebarBadge('sb-badge-job-total',        d.job_total);
                    setSidebarBadge('sb-badge-my-jobs',          d.my_jobs);
                    setSidebarBadge('sb-badge-my-applications',  d.my_applications);
                    setSidebarBadge('sb-badge-mod-queue',        d.mod_queue);
                    setSidebarBadge('sb-badge-story-total',      d.story_total);
                    setSidebarBadge('sb-badge-pending-stories',  d.pending_stories);
                    setSidebarBadge('sb-badge-my-stories',       d.my_stories);
                })
                .catch(() => {});
            }

            let timer = setInterval(fetchSidebarBadges, POLL_MS);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(timer);
                    timer = null;
                } else {
                    fetchSidebarBadges();
                    timer = setInterval(fetchSidebarBadges, POLL_MS);
                }
            });

            // ── Group unread badge (separate poll) ──────────────────────
            const URL_GROUP_COUNTS = '/groups/unread-counts';
            window.updateGroupSidebarBadge = function(total) {
                setSidebarBadge('sb-badge-groups', total);
            };

            function fetchGroupBadges() {
                fetch(URL_GROUP_COUNTS, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(d => {
                        // d.total = new active posts + pending posts/edits (admin) + join-event notifs
                        // d.pending_invitations = invitations TO the logged-in user
                        const total = (d.total || 0) + (d.pending_invitations || 0);
                        setSidebarBadge('sb-badge-groups', total);
                    })
                    .catch(() => {});
            }

            fetchGroupBadges();
            let groupTimer = setInterval(fetchGroupBadges, POLL_MS);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(groupTimer);
                } else {
                    fetchGroupBadges();
                    groupTimer = setInterval(fetchGroupBadges, POLL_MS);
                }
            });

            @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
            // ── Admin panel badges (Pending Requests + Newsletter) ──────────
            const URL_ADMIN_BADGES = "{{ route('admin.badge-counts') }}";
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            const pendingUsersLink   = document.querySelector('a[href="{{ route('admin.users.pending') }}"]');
            const newsletterLink     = document.querySelector('a[href="{{ route('admin.newsletter.index') }}"]');

            function fetchAdminBadges() {
                fetch(URL_ADMIN_BADGES, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(d => {
                        setSidebarBadge('sb-badge-pending-users', d.pending_users  || 0);
                        setSidebarBadge('sb-badge-newsletter',    d.newsletter_new || 0);
                    })
                    .catch(() => {});
            }

            // Mark as seen when visiting the page, clear badge immediately
            if (pendingUsersLink) {
                pendingUsersLink.addEventListener('click', () => {
                    setSidebarBadge('sb-badge-pending-users', 0);
                    fetch('{{ route('admin.mark-pending-users-seen') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    }).catch(() => {});
                });
            }

            if (newsletterLink) {
                newsletterLink.addEventListener('click', () => {
                    setSidebarBadge('sb-badge-newsletter', 0);
                    fetch('{{ route('admin.mark-newsletter-seen') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    }).catch(() => {});
                });
            }

            // Also mark seen if already on those pages (direct navigation)
            @if(request()->routeIs('admin.users.pending'))
            fetch('{{ route('admin.mark-pending-users-seen') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                credentials: 'same-origin',
            }).catch(() => {});
            @endif
            @if(request()->routeIs('admin.newsletter.*'))
            fetch('{{ route('admin.mark-newsletter-seen') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                credentials: 'same-origin',
            }).catch(() => {});
            @endif

            fetchAdminBadges();
            let adminBadgeTimer = setInterval(fetchAdminBadges, 30000); // 30s — admin data changes slowly

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(adminBadgeTimer);
                } else {
                    fetchAdminBadges();
                    adminBadgeTimer = setInterval(fetchAdminBadges, 30000);
                }
            });
            @endif
        })();
        </script>

        <script>
        (function () {
            const badge = document.getElementById('headerMsgBadge');
            if (!badge) return;
    
            async function fetchUnread() {
                try {
                    const res = await fetch('{{ route('chat.unread-count') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) return;
                    const data  = await res.json();
                    const count = data.count || 0;
    
                    const sidebarBadge = document.getElementById('sidebarMsgBadge');
                    if (count > 0) {
                        badge.textContent            = count > 99 ? '99+' : count;
                        badge.style.display          = '';
                        if (sidebarBadge) {
                            sidebarBadge.textContent   = count > 99 ? '99+' : count;
                            sidebarBadge.style.display = '';
                        }
                    } else {
                        badge.style.display = 'none';
                        if (sidebarBadge) sidebarBadge.style.display = 'none';
                    }
                } catch { /* silent */ }
            }
    
            fetchUnread();
            setInterval(fetchUnread, 10000);
    
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) fetchUnread();
            });
        })();
        </script>


        <script>
            (function () {
                const wrap  = document.getElementById('commSearch');
                const input = document.getElementById('commSearchInput');
                if (!wrap || !input) return;
            
                const SEARCH_URL = "{{ route('search') }}";
            
                let dropdown   = null;
                let debounceId = null;
                let activeIndex = -1;
            
                const ICONS = {
                    alumni: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                    event:  '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
                    story:  '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>',
                    job:    '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>',
                    notice: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>',
                    news:   '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>',
                };
            
                function escapeHtml(str) {
                    const div = document.createElement('div');
                    div.textContent = str ?? '';
                    return div.innerHTML;
                }
            
                function ensureDropdown() {
                    if (dropdown) return dropdown;
                    dropdown = document.createElement('div');
                    dropdown.className = 'comm-search__dropdown';
                    wrap.appendChild(dropdown);
                    return dropdown;
                }
            
                function closeDropdown() {
                    if (dropdown) {
                        dropdown.remove();
                        dropdown = null;
                    }
                    activeIndex = -1;
                }
            
                function renderLoading() {
                    const dd = ensureDropdown();
                    dd.innerHTML = '<div class="comm-search__loading">Searching…</div>';
                }
            
                function renderResults(results) {
                    const dd = ensureDropdown();
            
                    if (!results.length) {
                        dd.innerHTML = '<div class="comm-search__empty">No results found</div>';
                        return;
                    }
            
                    dd.innerHTML = results.map((r) => {
                        let iconHtml;
                        if (r.type === 'alumni') {
                            iconHtml = r.photo
                                ? `<img src="${r.photo}" alt="">`
                                : `<span class="comm-search__item-initials">${escapeHtml(r.initials || '')}</span>`;
                        } else {
                            iconHtml = ICONS[r.type] || '';
                        }
            
                        return `
                            <a href="${r.url}" class="comm-search__item">
                                <span class="comm-search__item-icon comm-search__item-icon--${r.type}">${iconHtml}</span>
                                <span class="comm-search__item-body">
                                    <span class="comm-search__item-title">${escapeHtml(r.title)}</span>
                                    ${r.sub ? `<span class="comm-search__item-sub">${escapeHtml(r.sub)}</span>` : ''}
                                </span>
                            </a>
                        `;
                    }).join('');
            
                    activeIndex = -1;
                }
            
                async function runSearch(q) {
                    renderLoading();
            
                    try {
                        const res = await fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
            
                        if (!res.ok) {
                            closeDropdown();
                            return;
                        }
            
                        const data = await res.json();
                        renderResults(data.results || []);
                    } catch {
                        closeDropdown();
                    }
                }
            
                input.addEventListener('input', function () {
                    const q = this.value.trim();
                    clearTimeout(debounceId);
            
                    if (q.length < 2) {
                        closeDropdown();
                        return;
                    }
            
                    debounceId = setTimeout(() => runSearch(q), 300);
                });
            
                input.addEventListener('keydown', function (e) {
                    if (!dropdown) return;
                    const items = dropdown.querySelectorAll('.comm-search__item');
                    if (!items.length) return;
            
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        activeIndex = Math.min(activeIndex + 1, items.length - 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        activeIndex = Math.max(activeIndex - 1, 0);
                    } else if (e.key === 'Enter') {
                        if (activeIndex >= 0) {
                            e.preventDefault();
                            items[activeIndex].click();
                        }
                        return;
                    } else if (e.key === 'Escape') {
                        closeDropdown();
                        return;
                    } else {
                        return;
                    }
            
                    items.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
                    items[activeIndex]?.scrollIntoView({ block: 'nearest' });
                });
            
                document.addEventListener('click', function (e) {
                    if (!wrap.contains(e.target)) closeDropdown();
                });
            })();
        </script>

        <style>
            .notif-item {
                transition: opacity 0.25s ease, max-height 0.3s ease, padding 0.3s ease;
                overflow: hidden;
                max-height: 200px;
            }
            .notif-item--removing {
                opacity: 0;
                max-height: 0 !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
        </style>
        <script>
        (function () {
            const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const URL_FEED      = "{{ route('notifications.feed') }}";
            const URL_MARK_ALL  = "{{ route('notifications.personal.mark-read') }}";
            const URL_COUNT     = "{{ route('notifications.personal') }}";
            const URL_CLEAR_ALL = "{{ route('notifications.clear-all') }}";
            const POLL_MS       = 10000;

            const menu      = document.getElementById('notifMenuToggle');
            const list      = document.getElementById('personalNotifList');
            const toggleBtn = menu?.querySelector('.comm-icon-btn');
            if (!menu || !list || !toggleBtn) return;

            let pollTimer = null;

            /* Badge */
            function setBadge(n) {
                let b = toggleBtn.querySelector('.badge');
                if (n > 0) {
                    if (!b) { b = document.createElement('span'); b.className = 'badge'; toggleBtn.appendChild(b); }
                    b.textContent = n > 9 ? '9+' : n;
                } else { b && b.remove(); }
            }

            function fetchCount() {
                fetch(URL_COUNT, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(d => setBadge(d.unread_count ?? 0))
                    .catch(() => {});
            }

            function markAllRead() {
                setBadge(0);
                fetch(URL_MARK_ALL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, credentials: 'same-origin' }).catch(() => {});
            }

            /* Icons & colours for content types */
            const ICONS = {
                job:    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>',
                event:  '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
                story:  '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>',
                notice: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>',
                news:   '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>',
            };
            const COLORS = { job:'#16a34a', event:'#7c3aed', story:'#2563eb', notice:'#d97706', news:'#0891b2' };
            const BG     = { job:'rgba(22,163,74,.1)', event:'rgba(124,58,237,.1)', story:'rgba(37,99,235,.1)', notice:'rgba(217,119,6,.1)', news:'rgba(8,145,178,.1)' };

            /* Build a content item (job/event/story/notice/news) */
            function buildContentItem(item) {
                var color = COLORS[item.type] || '#6b7280';
                var bg    = BG[item.type]     || 'rgba(107,114,128,.1)';
                var icon  = ICONS[item.type]  || '';
                var label = item.type.charAt(0).toUpperCase() + item.type.slice(1);
                var el = document.createElement('a');
                el.href = item.url || '#';
                el.className = 'notif-item';
                el.innerHTML =
                    '<span class="notif-item__icon notif-item__icon--' + item.type + '" style="border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">' + icon + '</span>' +
                    '<span class="notif-item__body">' +
                        '<span class="notif-item__label" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:' + color + ';display:block;">' + label + '</span>' +
                        '<span class="notif-item__title" style="display:block;font-size:13px;font-weight:600;line-height:1.35;margin-top:1px;">' + item.title + '</span>' +
                        '<span class="notif-item__time" style="font-size:11px;margin-top:2px;display:block;">' + item.time + '</span>' +
                    '</span>';
                return el;
            }

            function buildSocialItem(item) {
                var initial = ((item.initials || '?').charAt(0)).toUpperCase();
                var hue = (initial.charCodeAt(0) * 47) % 360;
                var iconStyle = item.avatar ? 'background:#f3f4f6;' : ('background:hsl(' + hue + ',60%,88%);');
                var avatarHtml = item.avatar
                    ? '<img src=' + item.avatar + ' alt="" style="width:34px;height:34px;object-fit:cover;border-radius:50%;display:block;">'
                    : '<span style="font-size:13px;font-weight:700;color:hsl(' + hue + ',55%,35%);">' + initial + '</span>';
                var el = document.createElement('a');
                el.href = item.url || '#';
                el.className = 'notif-item' + (item.is_read ? '' : ' notif-item--unread');
                var preview = item.preview ? '<span class="notif-item__preview" style="font-size:12px;display:block;margin-top:1px;">"' + item.preview + '"</span>' : '';
                el.innerHTML =
                    '<span class="notif-item__icon" style="' + iconStyle + 'border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">' + avatarHtml + '</span>' +
                    '<span class="notif-item__body">' +
                        '<span class="notif-item__title" style="display:block;font-size:13px;font-weight:500;line-height:1.35;">' + item.title + '</span>' +
                        preview +
                        '<span class="notif-item__time" style="font-size:11px;margin-top:2px;display:block;">' + item.time + '</span>' +
                    '</span>';
                return el;
            }

            function renderFeed(items) {
                list.innerHTML = '';
                if (!items || !items.length) {
                    list.innerHTML = '<div style="padding:20px 16px;font-size:13px;color:#9ca3af;text-align:center;">Nothing new right now.</div>';
                    return;
                }
                items.forEach(function(item) {
                    list.appendChild(item.kind === 'social' ? buildSocialItem(item) : buildContentItem(item));
                });
            }

            function fetchFeed() {
                fetch(URL_FEED, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                    .then(function(d) { renderFeed(d.items || []); })
                    .catch(function() {});
            }

            /* Clear All */
            var clearAllBtn = document.getElementById('notifClearAll');
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    clearAllBtn.disabled = true;
                    clearAllBtn.textContent = '…';
                    fetch(URL_CLEAR_ALL, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                    .then(function(r) { return r.ok ? r.json() : Promise.reject(); })
                    .then(function() {
                        setBadge(0);
                        renderFeed([]);
                        clearAllBtn.disabled = false;
                        clearAllBtn.textContent = 'Clear all';
                    })
                    .catch(function() {
                        clearAllBtn.disabled = false;
                        clearAllBtn.textContent = 'Clear all';
                    });
                });
            }

            /* Open / close */
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                var opening = !menu.classList.contains('open');
                menu.classList.toggle('open');
                if (opening) { fetchFeed(); markAllRead(); }
            });
            document.addEventListener('click', function(e) { if (!menu.contains(e.target)) menu.classList.remove('open'); });
            document.addEventListener('keydown', function(e) { if (e.key === 'Escape') menu.classList.remove('open'); });

            /* Badge polling */
            fetchCount();
            pollTimer = setInterval(fetchCount, POLL_MS);
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) { clearInterval(pollTimer); pollTimer = null; }
                else { fetchCount(); if (!pollTimer) pollTimer = setInterval(fetchCount, POLL_MS); }
            });
        })();
        </script>

        @endif
</body>
</html>
