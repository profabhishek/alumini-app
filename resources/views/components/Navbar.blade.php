<div class="topbar" id="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <a href="mailto:abhishek@ardhas.com" class="topbar-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                </svg>
                abhishek@ardhas.com
            </a>
            <span class="topbar-divider">|</span>
            <a href="tel:+918800040728" class="topbar-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5 19.79 19.79 0 0 1 1.61 4.94 2 2 0 0 1 3.59 2.77h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.1a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 17.5z" /></svg>
                +91-99998 32703
            </a>
        </div>
        <div class="topbar-right">

            @if(session()->has('alumni_id'))

                <a href="#"
                class="topbar-btn topbar-btn--filled" style="display: none;">
                    
                </a>

            @else

                <a href="{{ route('login') }}"
                class="topbar-btn topbar-btn--outline">
                    Sign In
                </a>

                <a href="{{ route('register') }}"
                class="topbar-btn topbar-btn--filled">
                    Join Now
                </a>

            @endif

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
    MAIN NAVIGATION
═══════════════════════════════════════════════════ --}}
<nav class="navbar-main" id="mainNav">
    <div class="container-xl">
        <div class="nav-inner">
            {{-- LOGO --}}
            <div>
                <a href="{{ route('home') }}" class="nav-logo">
                    <div class="nav-logo-mark">
                        <!-- <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="18" stroke="#F4A825" stroke-width="2" fill="none" opacity="0.3"/>
                            <circle cx="20" cy="20" r="10" fill="#F4A825" opacity="0.15"/>
                            <ellipse cx="20" cy="20" rx="18" ry="8" stroke="#F4A825" stroke-width="1.5" fill="none" opacity="0.6"/>
                            <circle cx="20" cy="20" r="3" fill="#F4A825"/>
                            <line x1="2" y1="20" x2="38" y2="20" stroke="#F4A825" stroke-width="1" opacity="0.4"/>
                        </svg> -->
                        <img
                            src="https://iccr.hialumni.com/storage/uploads/Setting/7881769241382.png"
                            alt=""
                        />
                    </div>
                    <!-- <div class="nav-logo-text">
                        <span class="nav-logo-primary">ICCR</span>
                        <span class="nav-logo-secondary">Alumni</span>
                    </div> -->
                </a>
            </div>

            {{-- DESKTOP MENU --}}
            <div>
                <ul class="nav-menu" id="navMenu">
                    <li
                        class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}"
                    >
                        <a href="{{ route('home') }}" class="nav-link-item"
                            >Home</a
                        >
                    </li>
                    <li
                        class="nav-item {{ request()->routeIs('alumni') ? 'active' : '' }}"
                    >
                        <a href="{{ route('alumni') }}" class="nav-link-item"
                            >Alumni</a
                        >
                    </li>
                    <li
                        class="nav-item {{ request()->routeIs('events') ? 'active' : '' }}"
                    >
                        <a href="{{ route('events.index') }}" class="nav-link-item"
                            >Events</a
                        >
                    </li>
                    <li
                        class="nav-item {{ request()->routeIs('news') ? 'active' : '' }}"
                    >
                        <a href="{{ route('news') }}" class="nav-link-item"
                            >News</a
                        >
                    </li>
                    <li
                        class="nav-item {{ request()->routeIs('notice') ? 'active' : '' }}"
                    >
                        <a href="{{ route('notice') }}" class="nav-link-item"
                            >Notice</a
                        >
                    </li>
                    <li class="nav-item nav-dropdown {{ request()->routeIs('community.*') ? 'active' : '' }}">

                        <a href="javascript:void(0)" class="nav-link-item">
                            Community

                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </a>

                        <div class="dropdown-menu">

                            <a href="{{ route('jobs.index') }}"
                                class="dropdown-link">
                                Find Jobs
                            </a>

                            <a href="{{ route('stories.index') }}"
                                class="dropdown-link">
                                Stories
                            </a>

                        </div>

                    </li>
                    <li
                        class="nav-item {{ request()->routeIs('contact') ? 'active' : '' }}"
                    >
                        <a href="{{ route('contact') }}" class="nav-link-item"
                            >Contact Us</a
                        >
                    </li>
                </ul>
            </div>

            {{-- RIGHT ACTIONS --}}
            <div class="nav-actions">
                <button
                    class="nav-search-btn"
                    id="searchToggle"
                    aria-label="Search"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                </button>
                @if(session()->has('alumni_id'))

                    <a href="{{ route('dashboard.home') }}" class="nav-cta">
                        Go To Community
                        <svg width="14" height="14" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>

                @else

                    <a href="{{ route('register') }}" class="nav-cta">
                        Join Network
                        <svg width="14" height="14" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>

                @endif
            </div>

            {{-- HAMBURGER --}}
            <button
                class="hamburger"
                id="hamburger"
                aria-label="Toggle menu"
                aria-expanded="false"
            >
                <span class="ham-line ham-line--top"></span>
                <span class="ham-line ham-line--mid"></span>
                <span class="ham-line ham-line--bot"></span>
            </button>
        </div>
    </div>

    {{-- SEARCH OVERLAY --}}
    <div class="nav-search-overlay" id="searchOverlay">
        <div class="container-xl">
            <div class="nav-search-inner">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input
                    type="text"
                    class="nav-search-input"
                    placeholder="Search alumni, events, news..."
                    autofocus
                />
                <button class="nav-search-close" id="searchClose">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    </div>
</nav>

{{-- MOBILE DRAWER --}}
<div class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer-header">
        <div class="nav-logo">
            <div class="nav-logo-mark">
                <svg width="28" height="28" viewBox="0 0 40 40" fill="none">
                    <circle cx="20" cy="20" r="18" stroke="#F4A825" stroke-width="2" fill="none" opacity="0.3" />
                    <ellipse cx="20" cy="20" rx="18" ry="8" stroke="#F4A825" stroke-width="1.5" fill="none" opacity="0.6" />
                    <circle cx="20" cy="20" r="3" fill="#F4A825" />
                    <line x1="2" y1="20" x2="38" y2="20" stroke="#F4A825" stroke-width="1" opacity="0.4" />
                </svg>
            </div>
            <div class="nav-logo-text">
                <span class="nav-logo-primary">ICCR</span>
                <span class="nav-logo-secondary">Alumni</span>
            </div>
        </div>
        <button class="drawer-close" id="drawerClose" aria-label="Close menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12" /></svg>
        </button>
    </div>
    <ul class="mobile-nav-menu">
        <li>
            <a
                href="{{ route('home') }}"
                class="mobile-nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                Home
            </a>
        </li>
        <li>
            <a
                href="{{ route('alumni') }}"
                class="mobile-nav-link {{ request()->routeIs('alumni') ? 'active' : '' }}"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                Alumni
            </a>
        </li>
        <li>
            <a
                href="{{ route('events.index') }}"
                class="mobile-nav-link {{ request()->routeIs('events') ? 'active' : '' }}"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Events
            </a>
        </li>
        <li>
            <a
                href="{{ route('news') }}"
                class="mobile-nav-link {{ request()->routeIs('news') ? 'active' : '' }}"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                    <path d="M18 14h-8" />
                    <path d="M15 18h-5" />
                    <path d="M10 6h8v4h-8V6Z" />
                </svg>
                News
            </a>
        </li>
        <li>
            <a
                href="{{ route('notice') }}"
                class="mobile-nav-link {{ request()->routeIs('notice') ? 'active' : '' }}"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" /></svg>
                Notice
            </a>
        </li>
        <li>
            <li>
                <a href="{{ route('jobs.index') }}"
                class="mobile-nav-link">
                    Find Jobs
                </a>
            </li>

            <li>
                <a href="{{ route('stories.index') }}"
                class="mobile-nav-link">
                    Stories
                </a>
            </li>
        </li>
        <li>
            <a
                href="{{ route('contact') }}"
                class="mobile-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5" />
                    <path d="M1.61 4.94A19.79 19.79 0 0 0 4.69 13.5M22 7l-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                </svg>
                Contact Us
            </a>
        </li>
    </ul>
    <div class="mobile-drawer-footer">
        @if(session()->has('alumni_id'))

            <a href="#"
            class="mobile-cta-btn">
                Go To Community
            </a>

        @else

            <a href="{{ route('login') }}"
            class="mobile-signin-btn">
                Sign In
            </a>

            <a href="{{ route('register') }}"
            class="mobile-cta-btn">
                Join the Network
            </a>

        @endif
    </div>
</div>
<div class="drawer-overlay" id="drawerOverlay"></div>