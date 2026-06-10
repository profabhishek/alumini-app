@php
$latestJobs = \App\Models\Job::where('status','published')
    ->latest()
    ->take(5)
    ->get();
@endphp

<!DOCTYPE html>
@php
    $alumniAppearance = \App\Models\AlumniUser::find(session('alumni_id'))?->appearance ?? 'light';
@endphp
<html lang="en" class="{{ $alumniAppearance === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <input type="text" class="comm-search__input" placeholder="Search alumni, posts, events...">
            </div>

            {{-- Right controls --}}
            <nav class="comm-header__right">
                {{-- Messages --}}
                <a href="#" class="comm-icon-btn" title="Messages">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                </a>

                {{-- Notifications --}}
                <a href="#" class="comm-icon-btn comm-icon-btn--badge" title="Notifications">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                    <span class="badge">3</span>
                </a>

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
                    <div class="sidebar-nav__item sidebar-nav__item--expandable" onclick="toggleSidebarMenu('event')">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                        <span class="nav-label">My Event</span>
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
                        </a>
                        <a href="{{ route('events.index') }}" class="sidebar-child {{ request()->routeIs('events.index') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>Browse Events
                        </a>
                        <a href="#" class="sidebar-child">
                            <span class="sidebar-child-dot"></span>My Ticket
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
                    <div class="sidebar-nav__item sidebar-nav__item--expandable" onclick="toggleSidebarMenu('job')">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2"/>
                                <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                            </svg>
                        </span>
                        <span class="nav-label">Job Post</span>
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
                        </a>

                        <a href="{{ route('jobs.index') }}" class="sidebar-child">
                            <span class="sidebar-child-dot"></span>Browse Jobs
                        </a>

                        <a href="{{ route('jobs.my-applications') }}"
                            class="sidebar-child {{ request()->routeIs('jobs.my-applications') ? 'active' : '' }}">
                            <span class="sidebar-child-dot"></span>My Applications
                        </a>

                        @if(session('alumni_role') === 'admin')
                            <a href="{{ route('admin.jobs.pending') }}" class="sidebar-child sidebar-child-admin">
                                <span class="sidebar-child-dot"></span>Moderation Queue
                            </a>

                            <a href="{{ route('admin.jobs.index') }}" class="sidebar-child sidebar-child-admin">
                                <span class="sidebar-child-dot"></span>Manage Jobs
                            </a>
                        @endif
                    </div>
                </div>
                {{-- STORIES --}}
                <div class="sidebar-expandable">
                    <div class="sidebar-nav__item sidebar-nav__item--expandable" onclick="toggleSidebarMenu('story')">
                        <span class="nav-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
                            </svg>
                        </span>
                        <span class="nav-label">Stories</span>
                        <svg class="nav-chevron" id="chev-story" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="9,18 15,12 9,6"/>
                        </svg>
                    </div>
                        <div class="sidebar-children" id="kids-story">
                        
                            {{-- Admin / Super Admin only --}}
                            @if(in_array(session('alumni_role'), ['admin', 'super_admin']))
                                <a href="{{ route('admin.stories.pending') }}"
                                class="sidebar-child {{ request()->routeIs('admin.stories.pending') ? 'active' : '' }}">
                                    <span class="sidebar-child-dot" style="background:#e8640c;"></span>Pending Story
                                </a>
                            @endif
                        
                            {{-- All authenticated users --}}
                            <a href="{{ route('stories.create') }}"
                            class="sidebar-child {{ request()->routeIs('stories.create') ? 'active' : '' }}">
                                <span class="sidebar-child-dot"></span>Create Story
                            </a>
                        
                            <a href="{{ route('stories.my') }}"
                            class="sidebar-child {{ request()->routeIs('stories.my') ? 'active' : '' }}">
                                <span class="sidebar-child-dot"></span>My Story
                            </a>
                        
                            <a href="{{ route('stories.index') }}"
                            class="sidebar-child {{ request()->routeIs('stories.index') ? 'active' : '' }}">
                                <span class="sidebar-child-dot"></span>All Story
                            </a>
                        
                            {{-- Admin overview link --}}
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
            </nav>

            <div class="sidebar-footer">
                <span class="sidebar-version">Version 3.6</span>
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

                                <div class="widget-job-item">
                                    <div class="widget-job-thumb">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                                            <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                        </svg>
                                    </div>

                                    <div class="widget-job-info">

                                        <span class="widget-job-title">
                                            {{ $job->title }}
                                        </span>

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

                                        <a href="{{ route('jobs.index', $job->id) }}"
                                        class="widget-more-link">
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
                            <a href="#" class="widget-see-all">See All <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg></a>
                        </div>
                        <div class="widget-body">
                            <div class="widget-notice-item">
                                <div class="widget-notice-thumb">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.4"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                </div>
                                <div class="widget-notice-info">
                                    <span class="widget-notice-date">May 07, 2026</span>
                                    <span class="widget-notice-title">India Africa Forum Summit</span>
                                    <span class="widget-notice-desc">Annual summit connecting alumni across Africa</span>
                                    <a href="#" class="widget-more-link">More Details</a>
                                </div>
                            </div>
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
                                <div class="widget-event-item">
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
                                </div>
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
    @stack('scripts')
</body>
</html>