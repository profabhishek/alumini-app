@php
    $currentAlumniUser = session('alumni_id')
        ? \App\Models\AlumniUser::find(session('alumni_id'))
        : null;

    $personalUnreadCount = 0;
    if (session('alumni_id')) {
        $personalUnreadCount = \App\Models\AlumniNotification::where('recipient_id', session('alumni_id'))
            ->where('is_read', false)
            ->count();
    }

    // ── Job sidebar badges ──────────────────────────────────────────────
    $myApplicationsBadge  = 0;
    $moderationQueueBadge = 0;
    $myJobsNewApplicantsBadge = 0;

    if (session('alumni_id')) {
        $lastSeen = session('applications_last_seen')
            ? \Carbon\Carbon::parse(session('applications_last_seen'))
            : now()->subDays(7);

        $myApplicationsBadge = \App\Models\JobApplication::where('alumni_id', session('alumni_id'))
            ->where('status', '!=', 'submitted')
            ->where('updated_at', '>', $lastSeen)
            ->count();

        $myJobsLastSeen = session('my_jobs_last_seen')
            ? \Carbon\Carbon::parse(session('my_jobs_last_seen'))
            : now()->subDays(7);

        $myJobsNewApplicantsBadge = \App\Models\JobApplication::whereHas('job', function ($q) {
            $q->where('created_by', session('alumni_id'));
        })
            ->where('created_at', '>', $myJobsLastSeen)
            ->count();

        if (in_array(session('alumni_role'), ['admin', 'super_admin', 'moderator'])) {
            $moderationQueueBadge = \App\Models\Job::where('status', 'pending')->count();
        }
    }

    // ── Event sidebar badges ──────────────────────────────────────────────
    $pendingEventsBadge   = 0;
    $myEventsNewRegsBadge = 0;

    if (session('alumni_id')) {
        $myEventIds = \App\Models\Event::where('created_by', session('alumni_id'))
            ->pluck('id');

        if ($myEventIds->isNotEmpty()) {
            $seenMap = session('events_regs_seen', []);

            foreach ($myEventIds as $eid) {
                $since = isset($seenMap[$eid])
                    ? \Carbon\Carbon::parse($seenMap[$eid])
                    : now()->subDays(7);

                $myEventsNewRegsBadge += \App\Models\EventRegistration::where('event_id', $eid)
                    ->where('created_at', '>', $since)
                    ->count();
            }
        }

        if (in_array(session('alumni_role'), ['admin', 'super_admin', 'moderator'])) {
            $pendingEventsBadge = \App\Models\Event::where('status', 'pending')->count();
        }
    }

    // ── Story sidebar badges ──────────────────────────────────────────────
    $pendingStoriesBadge     = 0;
    $myStoriesUpdatesBadge   = 0;

    if (session('alumni_id')) {
        // My stories with status changes since last visit
        $myStoriesLastSeen = session('my_stories_last_seen')
            ? \Carbon\Carbon::parse(session('my_stories_last_seen'))
            : now()->subDays(7);

        $myStoriesUpdatesBadge = \App\Models\Story::where('created_by', session('alumni_id'))
            ->where('status', '!=', 'pending')
            ->where('updated_at', '>', $myStoriesLastSeen)
            ->count();

        // Pending stories for admin/moderator
        if (in_array(session('alumni_role'), ['admin', 'super_admin', 'moderator'])) {
            $pendingStoriesBadge = \App\Models\Story::where('status', 'pending')->count();
        }
    }

    $emailPrefs = $currentAlumniUser?->email_notifications ?? [];

    $latestJobs = \App\Models\Job::where('status','published')
        ->latest()
        ->take(1)
        ->get();

    $latestNotices = \App\Models\Notice::published()
        ->latest('published_at')
        ->take(1)
        ->get();

    $sidebarUpcomingEvents = \App\Models\Event::where('status', 'published')
        ->whereDate('start_date', '>=', now()->toDateString())
        ->orderBy('start_date')
        ->take(2)
        ->get();


    $notifNews = \App\Models\News::published()
        ->latest('published_at')
        ->take(3)
        ->get();

    // ── Notification feed items, gated by the user's email_notifications
    //    preferences (Settings > Notifications). News & Notices have no
    //    corresponding toggle, so they're always included.

    $notifEvents = ($emailPrefs['events'] ?? true)
        ? \App\Models\Event::where('status', 'published')
            ->latest()
            ->take(3)
            ->get()
        : collect();

    $notifStories = ($emailPrefs['stories'] ?? true)
        ? \App\Models\Story::published()
            ->latest()
            ->take(3)
            ->get()
        : collect();

    $notifJobs = ($emailPrefs['jobs'] ?? true)
        ? \App\Models\Job::where('status', 'published')
            ->latest()
            ->take(3)
            ->get()
        : collect();

    $notificationItems = collect()
        ->merge($notifNews->map(fn($n) => [
            'type'  => 'news',
            'title' => $n->title,
            'date'  => $n->published_at,
            'url'   => route('news.show', $n->slug),
        ]))
        ->merge($latestNotices->map(fn($n) => [
            'type'  => 'notice',
            'title' => $n->title,
            'date'  => $n->published_at,
            'url'   => route('notice.show', $n->slug),
        ]))
        ->merge($notifEvents->map(fn($e) => [
            'type'  => 'event',
            'title' => $e->title,
            'date'  => $e->created_at,
            'url'   => route('events.show', $e->slug ?? $e->id),
        ]))
        ->merge($notifStories->map(fn($s) => [
            'type'  => 'story',
            'title' => $s->title,
            'date'  => $s->created_at,
            'url'   => route('stories.show', $s->slug),
        ]))
        ->merge($notifJobs->map(fn($j) => [
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


    $notificationsReadAt = null;

    if ($currentAlumniUser) {
        $notificationsReadAt = $currentAlumniUser->notifications_read_at
            ? \Carbon\Carbon::parse($currentAlumniUser->notifications_read_at)
            : null;
    }

    $newNotificationsCount = $notificationItems
        ->filter(fn($item) => $item['date'] && (! $notificationsReadAt || $item['date']->gt($notificationsReadAt)))
        ->count();
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

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    {{-- Community base styles --}}
    <link rel="stylesheet" href="{{ asset('css/community/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/community/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/community/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/community/header.css') }}">

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
                <img src="https://iccr.hialumni.com/storage/uploads/Setting/7881769241382.png" alt="ICCR" class="comm-logo__img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="comm-logo__fallback" style="display:none;">
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
                        @php $totalBellCount = $newNotificationsCount + $personalUnreadCount; @endphp
                        @if($totalBellCount > 0)
                            <span class="badge">{{ $totalBellCount > 9 ? '9+' : $totalBellCount }}</span>
                        @endif
                    </button>

                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-dropdown__header">
                            Recent Activity
                        </div>

                            <div class="notif-dropdown__body">
                                <div id="personalNotifList"></div>

                                @if($notificationItems->isNotEmpty())
                                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;padding:8px 16px 4px;border-top:1px solid #f3f4f6;margin-top:4px;">Recent Content</div>
                                @endif

                                @forelse($notificationItems as $item)
                                <a href="{{ $item['url'] }}" class="notif-item">
                                    <span class="notif-item__icon notif-item__icon--{{ $item['type'] }}">
                                        @switch($item['type'])
                                            @case('news')
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>
                                                @break
                                            @case('notice')
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                                                @break
                                            @case('story')
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                                                @break
                                            @case('event')
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            @break
                                            @case('job')
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                                @break
                                        @endswitch
                                    </span>
                                    <span class="notif-item__body">
                                        <span class="notif-item__label">{{ ucfirst($item['type']) }}</span>
                                        <span class="notif-item__title">{{ $item['title'] }}</span>
                                        <span class="notif-item__time">{{ $item['date']->diffForHumans() }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="notif-empty">Nothing new right now.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- User dropdown --}}
                <div class="comm-user-menu" id="userMenuToggle">
                    <div class="avatar avatar--sm">
                        @if(session('alumni_avatar'))
                            <img src="{{ asset('storage/' . session('alumni_avatar')) }}" alt="{{ session('alumni_name') }}">
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
                        @if($eventTotalBadge > 0)
                            <span class="sidebar-child-badge sidebar-child-badge--notif" style="margin-left:6px;margin-right:auto;">
                                {{ $eventTotalBadge > 9 ? '9+' : $eventTotalBadge }}
                            </span>
                        @endif
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
                                @if($pendingEventsBadge > 0)
                                    <span class="sidebar-child-badge sidebar-child-badge--notif">
                                        {{ $pendingEventsBadge > 9 ? '9+' : $pendingEventsBadge }}
                                    </span>
                                @endif
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
                            @if($myEventsNewRegsBadge > 0)
                                <span class="sidebar-child-badge sidebar-child-badge--notif">
                                    {{ $myEventsNewRegsBadge > 9 ? '9+' : $myEventsNewRegsBadge }}
                                </span>
                            @endif
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
                    @if($jobPostTotalBadge > 0)
                        <span class="sidebar-child-badge sidebar-child-badge--notif" style="margin-left:6px;margin-right:auto;">
                            {{ $jobPostTotalBadge > 9 ? '9+' : $jobPostTotalBadge }}
                        </span>
                    @endif
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
                            @if($myJobsNewApplicantsBadge > 0)
                                <span class="sidebar-child-badge sidebar-child-badge--notif">
                                    {{ $myJobsNewApplicantsBadge > 9 ? '9+' : $myJobsNewApplicantsBadge }}
                                </span>
                            @endif
                        </a>

                        <a href="{{ route('jobs.index') }}" class="sidebar-child">
                            <span class="sidebar-child-dot"></span>Browse Jobs
                        </a>

                        <a href="{{ route('jobs.my-applications') }}"
                            class="sidebar-child {{ request()->routeIs('jobs.my-applications') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>My Applications
                            @if($myApplicationsBadge > 0)
                                <span class="sidebar-child-badge sidebar-child-badge--notif">
                                    {{ $myApplicationsBadge > 9 ? '9+' : $myApplicationsBadge }}
                                </span>
                            @endif
                        </a>

                        @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
                        <a href="{{ route('admin.jobs.pending') }}" class="sidebar-child sidebar-child-admin">
                            <span class="sidebar-child-dot"></span>Moderation Queue
                            @if($moderationQueueBadge > 0)
                                <span class="sidebar-child-badge sidebar-child-badge--notif">
                                    {{ $moderationQueueBadge > 9 ? '9+' : $moderationQueueBadge }}
                                </span>
                            @endif
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
                        @if($storiesTotalBadge > 0)
                            <span class="sidebar-child-badge sidebar-child-badge--notif" style="margin-left:6px;margin-right:auto;">
                                {{ $storiesTotalBadge > 9 ? '9+' : $storiesTotalBadge }}
                            </span>
                        @endif
                        <svg class="nav-chevron" id="chev-story" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="9,18 15,12 9,6"/>
                        </svg>
                    </div>
                    <div class="sidebar-children" id="kids-story">

                        @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
                            <a href="{{ route('admin.stories.pending') }}"
                            class="sidebar-child {{ request()->routeIs('admin.stories.pending') ? 'active' : '' }}">
                                <span class="sidebar-child-dot" style="background:#e8640c;"></span>Pending Story
                                @if($pendingStoriesBadge > 0)
                                    <span class="sidebar-child-badge sidebar-child-badge--notif">
                                        {{ $pendingStoriesBadge > 9 ? '9+' : $pendingStoriesBadge }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        <a href="{{ route('stories.create') }}"
                        class="sidebar-child {{ request()->routeIs('stories.create') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>Create Story
                        </a>

                        <a href="{{ route('stories.my') }}"
                        class="sidebar-child {{ request()->routeIs('stories.my') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>My Story
                            @if($myStoriesUpdatesBadge > 0)
                                <span class="sidebar-child-badge sidebar-child-badge--notif">
                                    {{ $myStoriesUpdatesBadge > 9 ? '9+' : $myStoriesUpdatesBadge }}
                                </span>
                            @endif
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
                                                    ₹{{ number_format($job->salary_min) }}
                                                    @if($job->salary_max)
                                                        - ₹{{ number_format($job->salary_max) }}
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
                                            <img src="{{ asset('storage/' . $notice->image) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
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
                const menu     = document.getElementById('notifMenuToggle');
                const dropdown = document.getElementById('notifDropdown');
                if (!menu || !dropdown) return;

                const toggleBtn = menu.querySelector('.comm-icon-btn');

                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    menu.classList.toggle('open');
                });

                document.addEventListener('click', function (e) {
                    if (!menu.contains(e.target)) {
                        menu.classList.remove('open');
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') menu.classList.remove('open');
                });
            })();
        </script>

        <script>
            (function () {
                const wrap  = document.getElementById('commSearch');
                const input = document.getElementById('commSearchInput');
                if (!wrap || !input) return;
            
                const SEARCH_URL = '{{ route('search') }}';
            
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

        <script>
            (function () {
                const menu     = document.getElementById('notifMenuToggle');
                const list     = document.getElementById('personalNotifList');
                if (!menu || !list) return;

                let loaded = false;

                menu.querySelector('.comm-icon-btn').addEventListener('click', function () {
                    if (loaded) return;
                    loaded = true;

                    fetch('{{ route('notifications.personal') }}', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.notifications?.length) return;

                        list.innerHTML = data.notifications.map(n => {
                            const avatarHtml = n.avatar
                                ? `<img src="${n.avatar}" alt="${n.actor}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
                                : `<span style="font-size:11px;font-weight:700;color:#e8640c;">${n.initials}</span>`;

                            const label = n.type === 'reply'
                                ? `<strong>${n.actor}</strong> replied to your comment`
                                : `<strong>${n.actor}</strong> commented on your post`;

                            return `<a href="${n.post_url}" class="notif-item ${n.is_read ? '' : 'notif-item--unread'}">
                                <span class="notif-item__icon" style="background:rgba(232,100,12,.1);color:#e8640c;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                                    ${avatarHtml}
                                </span>
                                <span class="notif-item__body">
                                    <span class="notif-item__title">${label}</span>
                                    ${n.preview ? `<span class="notif-item__label" style="color:#6b7280;">"${n.preview}"</span>` : ''}
                                    <span class="notif-item__time">${n.time}</span>
                                </span>
                            </a>`;
                        }).join('');

                        // Mark as read after viewing
                        fetch('{{ route('notifications.personal.mark-read') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        }).catch(() => {});
                    })
                    .catch(() => {});
                });
            })();
        </script>
        @endif
</body>
</html>