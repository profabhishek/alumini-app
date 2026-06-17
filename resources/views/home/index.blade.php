@extends ('layouts.app')

@push ('styles')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endpush

@push('styles')
<style>
/* ── Photo overlays for dynamic cards (Blogs / Stories / Gallery) ── */
.blog-img--photo { position: relative; }
.blog-img--photo::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0) 50%);
    pointer-events: none;
}
.blog-img--photo .blog-cat {
    background: rgba(255,255,255,0.95) !important;
    border-color: transparent !important;
    color: #1e3c64 !important;
    z-index: 2;
}

.story-img--photo { position: relative; }
.story-img--photo img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s var(--ease);
}
.story-card:hover .story-img--photo img { transform: scale(1.05); }
.story-img--photo::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15,23,42,0.15) 0%, rgba(15,23,42,0.65) 100%);
}
.story-img--photo .story-img-date {
    background: rgba(255,255,255,0.95) !important;
    border-color: transparent !important;
    color: var(--dark3, #0f1d30) !important;
    z-index: 1;
}
.story-img--photo .story-img-inner { z-index: 1; }

.gallery-item-photo { position: relative; }
.gallery-item-photo img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s var(--ease);
}
.gallery-item-photo:hover img { transform: scale(1.05); }
.gallery-item-photo::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15,23,42,0) 50%, rgba(15,23,42,0.55) 100%);
}
.gallery-item-photo .gallery-item-icon {
    position: absolute;
    bottom: 12px;
    left: 12px;
    z-index: 1;
    margin: 0;
}

.alumni-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit;
    display: block;
}
</style>
@endpush

@section ('content')
    <section class="hero" id="hero">
        <div class="hero-bg"></div>
        <div class="hero-grid-lines"></div>
        <div class="hero-orbit"></div>
        <div class="hero-dot"></div>
        <div class="container">
            <div class="hero-inner">
                <div class="hero-content reveal">
                    <div class="hero-badge">
                        <div class="hero-badge-dot">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </div>
                        <span>ICCR Alumni Network</span>
                    </div>
                    <h1 class="hero-title" style="color: black">
                        We are the <em>proud</em><br />members of ICCR<br />Alumni
                        forever
                    </h1>
                    <p class="hero-desc">A user-friendly platform that helps alumni easily connect and manage their activities. Sign up, get approved, and join a thriving global community.</p>
                    <div class="hero-actions">
                        <a
                            href="/about-us-section"
                            class="btn-primary"
                        >
                            About Us
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </a>
                        <a
                            href="{{ route('events.index') }}"
                            class="btn-ghost"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            All Events
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <span class="hero-stat-num">19000<span>+</span></span>
                            <span class="hero-stat-label">Alumni's</span>
                        </div>
                        <div class="hero-stat-sep"></div>
                        <div class="hero-stat">
                            <span class="hero-stat-num">30<span>+</span></span>
                            <span class="hero-stat-label">Departments</span>
                        </div>
                        <div class="hero-stat-sep"></div>
                        <div class="hero-stat">
                            <span class="hero-stat-num">45<span>+</span></span>
                            <span class="hero-stat-label">Sessions</span>
                        </div>
                    </div>
                </div>
                <div class="hero-visual reveal reveal-delay-2">
                    <div class="hero-card">
                        <div class="hero-card-header">
                            <div class="hch-dot"></div>
                            <div class="hch-dot"></div>
                            <div class="hch-dot"></div>
                            <span class="hero-card-title"
                                >Alumni Dashboard</span
                            >
                        </div>
                        <div class="hero-card-body">
                            <div class="hcb-world">
                                <div class="hcb-world-globe"></div>
                                <div class="hcb-world-label">Your Network</div>
                                <div class="hcb-world-val">Global</div>
                                <div class="hcb-world-sub">
                                    Connected across countries & cultures
                                </div>
                            </div>
                            <div class="hcb-members">
                                <div class="hcb-mini">
                                    <div class="hcb-mini-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                        </svg>
                                    </div>
                                    <div class="hcb-mini-num">45+</div>
                                    <div class="hcb-mini-label">
                                        Events held
                                    </div>
                                </div>
                                <div class="hcb-mini">
                                    <div class="hcb-mini-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                    </div>
                                    <div class="hcb-mini-num">3+</div>
                                    <div class="hcb-mini-label">Jobs</div>
                                </div>
                            </div>
                            <div
                                class="member-avatars"
                                style="margin-top: 20px"
                            >
                                <div class="m-avatar av-1">OS</div>
                                <div class="m-avatar av-2">LB</div>
                                <div class="m-avatar av-3">EB</div>
                                <div class="m-avatar av-4">MA</div>
                                <div class="m-avatar m-avatar-more">+4</div>
                                <span class="m-avatar-label"
                                    ><strong>8+</strong> alumni joined</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="gold-line-sep"></div>

        <!-- ════════════════════════════════════
     ⑦ NEW ALUMNI
════════════════════════════════════ -->
    <section class="alumni-section">
        <div class="container">
            <div class="reveal">
                <div class="tag">New Alumni</div>
                <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h2 class="section-heading gold-text" style="margin-bottom: 8px">
                            Recent Joined Alumni
                        </h2>
                        <p class="section-sub">The Alumni Association leverages the resources, talents, and initiatives of alumni and friends to advise, guide, and support the community.</p>
                    </div>
                    {{-- Optional: remove this link, or point it at your real alumni index route --}}
                    <a href="{{ route('alumni') }}" class="alumni-view-all">
                        View All Alumni
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            @if($recentAlumni->isNotEmpty())
            <div class="alumni-grid">
                @foreach($recentAlumni as $i => $alum)
                    <div class="alumni-card reveal {{ $i === 0 ? 'featured' : '' }} {{ $i > 0 ? 'reveal-delay-' . ($i % 4) : '' }}">
                        @if($i === 0)
                            <span class="featured-ribbon">Newest Member</span>
                        @endif

                        <div class="alumni-avatar av-{{ ($i % 8) + 1 }}">
                            @if($alum->photo)
                                <img src="{{ asset('storage/' . $alum->photo) }}" alt="{{ $alum->full_name }}">
                            @else
                                {{ $alum->initials }}
                            @endif
                        </div>

                        <div>
                            <div class="alumni-name">{{ $alum->full_name }}</div>
                            <div class="alumni-dept">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                {{ $alum->department ?: '—' }}
                            </div>
                            <span class="alumni-batch">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12.5V17c0 1 2.5 2 6 2s6-1 6-2v-4.5"/></svg>
                                Batch {{ $alum->passing_year }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <div class="reveal" style="text-align:center; padding: 40px 0; color: var(--txt3);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px; display:block; opacity:0.5;"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 1.5 9 1.5 12 0v-5"/></svg>
                <p style="font-size:15px; font-weight:600; margin:0;">No alumni have joined yet.</p>
            </div>
            @endif
        </div>
    </section>

    <!-- ════════════════════════════════════
     ② WHY JOIN US
════════════════════════════════════ -->
    <section class="why-section" id="why">
        <div class="container">
            <div class="why-header reveal">
                <div class="tag">Join With Community</div>
                <h2 class="section-heading" style="text-align: center">
                    Why you should join us
                </h2>
                <p class="section-sub" style="text-align: center; margin: 0 auto">Build lasting connections, advance your career, and reconnect with lifelong friends through the ICCR Alumni Network.</p>
            </div>
            <div class="why-grid">
                <div class="why-card reveal reveal-delay-1">
                    <span class="why-num">01</span>
                    <div class="why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </div>
                    <h3 class="why-title">Attend Events</h3>
                    <p class="why-desc">ICCR Alumni will help you connect with your fellow classmates from time to time, where you can meet, network, and grow together through meaningful in-person experiences.</p>
                </div>
                <div class="why-card reveal reveal-delay-2">
                    <span class="why-num">02</span>
                    <div class="why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <h3 class="why-title">Advance Your Career</h3>
                    <p class="why-desc">Meet your fellow alumni and collaborate for a better future. Leverage shared experiences and industry connections to unlock new professional opportunities.</p>
                </div>
                <div class="why-card reveal reveal-delay-3">
                    <span class="why-num">03</span>
                    <div class="why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <h3 class="why-title">Reconnect Your Friends</h3>
                    <p class="why-desc">Friendship never dies — but we need to time and again keep it alive by meeting and reconnecting. Find your classmates and revive bonds that last a lifetime.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- ════════════════════════════════════
     ③ ABOUT US
════════════════════════════════════ -->
    <section class="about-section" id="about-us-section">
        <div class="container">
            <div class="about-inner">
                <div class="about-visual reveal">
                    <div class="about-main-card">
                        <div class="amc-tag">
                            <span></span>
                            Active Community
                        </div>
                        <img
                            src="https://media.istockphoto.com/id/1366320219/vector/family-design-element.jpg?s=612x612&w=0&k=20&c=zUT5zZczcWHGUDuTZvNNFB9v-OGx02AFesHVQ6wlRgQ="
                            alt=""
                        />
                    </div>
                    <div class="about-float-card">
                        <div class="afc-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                            </svg>
                        </div>
                        <div>
                            <div class="afc-num">Global</div>
                            <div class="afc-label">Alumni Network</div>
                        </div>
                    </div>
                </div>
                <div class="about-content reveal reveal-delay-1">
                    <div class="tag">About Us</div>
                    <h2 style="color: black" class="section-heading">
                        About ICCR Alumni
                    </h2>
                    <div class="about-points">
                        <div class="about-point">
                            <div class="ap-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></svg>
                            </div>
                            <p class="ap-text">Alumni Association provides and supports alumni programs and services, facilitates communication with alumni, and seeks to strengthen alumni bonds of fellowship, professional association and university affiliation.</p>
                        </div>
                        <div class="about-point">
                            <div class="ap-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <p class="ap-text">Connect alumni with mentors or coaches who can offer guidance, advice, or feedback on personal or professional goals — helping them expand their network and overcome challenges.</p>
                        </div>
                        <div class="about-point">
                            <div class="ap-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                                </svg>
                            </div>
                            <p class="ap-text">The Alumni Association leverages the resources, talents, and initiatives of alumni and friends to advise, guide, advocate for and support the Association and the university in achieving their respective missions and goals.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ════════════════════════════════════
     ④ STATS BANNER
════════════════════════════════════ -->
    <div class="stats-banner">
        <div class="container">
            <div class="stats-inner">
                <div class="stat-item reveal">
                    <div class="stat-num">1<span>+</span></div>
                    <div class="stat-label">Members</div>
                </div>
                <div class="stat-item reveal reveal-delay-1">
                    <div class="stat-num">3<span>+</span></div>
                    <div class="stat-label">Departments</div>
                </div>
                <div class="stat-item reveal reveal-delay-2">
                    <div class="stat-num">45<span>+</span></div>
                    <div class="stat-label">Sessions</div>
                </div>
                <div class="stat-item reveal reveal-delay-3">
                    <div class="stat-num">∞</div>
                    <div class="stat-label">Connections Made</div>
                </div>
            </div>
        </div>
    </div>
    <!-- ════════════════════════════════════
     ⑤ GLOBAL NETWORK CTA
════════════════════════════════════ -->
    <section class="network-section">
        <div class="network-bg"></div>
        <div class="container">
            <div class="network-inner">
                <div class="network-content reveal">
                    <div class="tag">Your network around the globe</div>
                    <h2 class="section-heading">
                        Connect. Collaborate.<br />Grow Together.
                    </h2>
                    <p class="section-sub">Connect alumni with mentors or coaches who can offer them guidance, advice, or feedback on their personal or professional goals. They can also help them expand their network and explore new opportunities.</p>
                    <a
                        href="{{ route('register') }}"
                        class="btn-primary"
                        style="margin-top: 8px"
                    >
                        Join Community
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="reveal reveal-delay-2">
                    <div
                        style="
                            background: var(--dark3);
                            border: 1px solid var(--border2);
                            border-radius: 24px;
                            padding: 36px;
                            position: relative;
                            overflow: hidden;
                        "
                    >
                        <div
                            style="
                                position: absolute;
                                top: -40px;
                                right: -40px;
                                width: 160px;
                                height: 160px;
                                border-radius: 50%;
                                border: 1px dashed rgba(244, 168, 37, 0.15);
                            "
                        ></div>
                        <div
                            style="
                                position: absolute;
                                top: -10px;
                                right: -10px;
                                width: 90px;
                                height: 90px;
                                border-radius: 50%;
                                border: 1px dashed rgba(244, 168, 37, 0.1);
                            "
                        ></div>
                        <div
                            style="
                                font-size: 12px;
                                font-weight: 700;
                                color: var(--gold);
                                letter-spacing: 0.15em;
                                text-transform: uppercase;
                                margin-bottom: 20px;
                            "
                        >
                            Network at a Glance
                        </div>
                        <div
                            style="
                                display: flex;
                                flex-direction: column;
                                gap: 14px;
                            "
                        >
                            <div
                                style="
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
                                    padding: 14px 16px;
                                    background: var(--surface);
                                    border: 1px solid var(--border);
                                    border-radius: 12px;
                                "
                            >
                                <div
                                    style="
                                        display: flex;
                                        align-items: center;
                                        gap: 10px;
                                    "
                                >
                                    <div
                                        style="
                                            width: 8px;
                                            height: 8px;
                                            border-radius: 50%;
                                            background: #4ade80;
                                            box-shadow: 0 0 8px
                                                rgba(74, 222, 128, 0.5);
                                        "
                                    ></div>
                                    <span style="font-size: 13px; color: white"
                                        >Active Members</span
                                    >
                                </div>
                                <span
                                    style="
                                        font-size: 14px;
                                        font-weight: 700;
                                        color: #fff;
                                    "
                                    >Online</span
                                >
                            </div>
                            <div
                                style="
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
                                    padding: 14px 16px;
                                    background: var(--surface);
                                    border: 1px solid var(--border);
                                    border-radius: 12px;
                                "
                            >
                                <div
                                    style="
                                        display: flex;
                                        align-items: center;
                                        gap: 10px;
                                    "
                                >
                                    <div
                                        style="
                                            width: 8px;
                                            height: 8px;
                                            border-radius: 50%;
                                            background: var(--gold);
                                        "
                                    ></div>
                                    <span style="font-size: 13px; color: white"
                                        >Upcoming Events</span
                                    >
                                </div>
                                <span
                                    style="
                                        font-size: 14px;
                                        font-weight: 700;
                                        color: var(--gold);
                                    "
                                    > <a href="{{ route('events.index') }}">View All →</a></span
                                >
                            </div>
                            <div
                                style="
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
                                    padding: 14px 16px;
                                    background: var(--surface);
                                    border: 1px solid var(--border);
                                    border-radius: 12px;
                                "
                            >
                                <div
                                    style="
                                        display: flex;
                                        align-items: center;
                                        gap: 10px;
                                    "
                                >
                                    <div
                                        style="
                                            width: 8px;
                                            height: 8px;
                                            border-radius: 50%;
                                            background: #60a5fa;
                                        "
                                    ></div>
                                    <span style="font-size: 13px; color: white"
                                        >Global Chapters</span
                                    >
                                </div>
                                <span
                                    style="
                                        font-size: 14px;
                                        font-weight: 700;
                                        color: #fff;
                                    "
                                    >Growing</span
                                >
                            </div>
                        </div>
                        <div
                            style="
                                margin-top: 24px;
                                padding-top: 20px;
                                border-top: 1px solid var(--border);
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                            "
                        >
                            <span style="font-size: 12px; color: white"
                                >Approval-based access</span
                            >
                            <a
                                href="{{ route('register') }}"
                                style="
                                    font-size: 12px;
                                    font-weight: 600;
                                    color: var(--gold);
                                    display: flex;
                                    align-items: center;
                                    gap: 5px;
                                "
                            >
                                Apply Now
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════════════════════════
     ⑥ STORIES
════════════════════════════════════ -->
    @php
        $storyThemes = [
            [
                'grad' => null, // use default .story-img gradient
                'icon' => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/>',
            ],
            [
                'grad' => 'rgba(30, 60, 100, 0.8), rgba(244, 168, 37, 0.1)',
                'icon' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
            ],
            [
                'grad' => 'rgba(50, 20, 80, 0.8), rgba(244, 168, 37, 0.12)',
                'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            ],
        ];
    @endphp
    <section class="stories-section">
        <div class="container">
            <div class="stories-header reveal">
                <div>
                    <div class="tag">Stories</div>
                    <h2 class="section-heading" style="margin-bottom: 0; color: black">
                        Our Stories
                    </h2>
                </div>
                <a href="{{ \Route::has('stories.index') ? route('stories.index') : '#' }}" class="btn-ghost" style="white-space: nowrap">
                    View All
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                </a>
            </div>

            @if($stories->isNotEmpty())
            <div class="stories-grid">
                @foreach($stories as $i => $story)
                    @php
                        $theme = $storyThemes[$i % 3];
                        $excerpt = $story->excerpt ?: \App\Models\Story::makeExcerpt($story->body, 160);
                        $storyUrl = \Route::has('stories.show') ? route('stories.show', $story) : (\Route::has('stories.index') ? route('stories.index') : '#');
                    @endphp
                    <div class="story-card reveal {{ $i > 0 ? 'reveal-delay-' . $i : '' }}">
                        <div class="story-img {{ $story->cover_image ? 'story-img--photo' : '' }}"
                             @if(!$story->cover_image && $theme['grad'])
                                 style="background: linear-gradient(135deg, {{ $theme['grad'] }});"
                             @endif>
                            @if($story->cover_image)
                                <img src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}">
                            @else
                                <div class="story-img-inner">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{!! $theme['icon'] !!}</svg>
                                </div>
                            @endif
                            <div class="story-img-date">{{ $story->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="story-body">
                            <div class="story-title">{{ $story->title }}</div>
                            <p class="story-excerpt">{{ $excerpt }}</p>
                            <a href="{{ $storyUrl }}" class="story-link">
                                Know More
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <div class="reveal" style="text-align:center; padding: 40px 0; color: var(--txt3);">
                <p style="font-size:15px; font-weight:600; margin:0;">No stories published yet.</p>
            </div>
            @endif
        </div>
    </section>

    <!-- ════════════════════════════════════
     ⑧ GALLERY
════════════════════════════════════ -->
    <section class="gallery-section">
        <div class="container">
            <div class="reveal" style="text-align: center; margin-bottom: 0">
                <div class="tag">Gallery</div>
                <h2 class="section-heading" style="color: black">
                    Image Gallery
                </h2>
                <p class="section-sub" style="margin: 0 auto; color: grey">The Alumni Association leverages the resources, talents, and initiatives of alumni and friends to advise.</p>
            </div>

            @if($galleryItems->isNotEmpty())
            <div class="gallery-grid">
                @foreach($galleryItems as $i => $item)
                    <div class="gallery-item gallery-item-photo reveal {{ $i > 0 ? 'reveal-delay-' . (($i - 1) % 3 + 1) : '' }} {{ $i === 0 ? 'wide' : '' }}">
                        <img src="{{ $item->image_url }}" alt="{{ $item->title ?? 'Gallery image' }}" loading="lazy">
                        <div class="gallery-item-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 00-2.828 0L6 21"/></svg>
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <div class="reveal" style="text-align:center; padding: 40px 0; color: grey;">
                <p style="font-size:15px; font-weight:600; margin:0;">Gallery photos coming soon.</p>
            </div>
            @endif
        </div>
    </section>

    <!-- ════════════════════════════════════
     ⑨ BLOGS
════════════════════════════════════ -->
    @php
        $blogThemes = [
            ['grad' => 'rgba(244, 168, 37, 0.12), rgba(20, 40, 70, 0.9)', 'icon_bg' => 'rgba(244, 168, 37, 0.15)', 'icon_border' => 'rgba(244, 168, 37, 0.25)', 'icon_color' => '#F4A825', 'cat_style' => '', 'av_style' => ''],
            ['grad' => 'rgba(30, 60, 100, 0.85), rgba(244, 168, 37, 0.08)', 'icon_bg' => 'rgba(96, 165, 250, 0.15)', 'icon_border' => 'rgba(96, 165, 250, 0.2)', 'icon_color' => '#60a5fa', 'cat_style' => 'background: rgba(96, 165, 250, 0.15); border-color: rgba(96, 165, 250, 0.2); color: #60a5fa;', 'av_style' => 'background: rgba(96, 165, 250, 0.15); border-color: rgba(96, 165, 250, 0.2); color: #60a5fa;'],
            ['grad' => 'rgba(50, 20, 80, 0.85), rgba(244, 168, 37, 0.1)', 'icon_bg' => 'rgba(167, 139, 250, 0.15)', 'icon_border' => 'rgba(167, 139, 250, 0.2)', 'icon_color' => '#a78bfa', 'cat_style' => 'background: rgba(167, 139, 250, 0.15); border-color: rgba(167, 139, 250, 0.2); color: #a78bfa;', 'av_style' => 'background: rgba(167, 139, 250, 0.15); border-color: rgba(167, 139, 250, 0.2); color: #a78bfa;'],
        ];
    @endphp
    <section class="blogs-section">
        <div class="container">
            <div class="blogs-header reveal">
                <div>
                    <div class="tag">Alumni Blogs</div>
                    <h2 class="section-heading" style="margin-bottom: 6px">
                        News for our community
                    </h2>
                    <p class="section-sub">Explore news, views and perspectives for your alumni community.</p>
                </div>
                <a href="{{ route('news') }}" class="btn-ghost" style="white-space: nowrap; align-self: flex-end; background: #fff;">
                    Explore All Blogs
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                </a>
            </div>

            @if($latestNews->isNotEmpty())
            <div class="blogs-grid">
                @foreach($latestNews as $i => $item)
                    @php
                        $theme = $blogThemes[$i % 3];
                        $authorName = $item->author->full_name ?? 'ICCR Community';
                        $initials = collect(preg_split('/\s+/', trim($authorName)))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                        $newsExcerpt = $item->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($item->body), 140);
                    @endphp
                    <div class="blog-card reveal {{ $i > 0 ? 'reveal-delay-' . $i : '' }}">
                        <div class="blog-img {{ $item->image ? 'blog-img--photo' : '' }}"
                             style="{{ $item->image
                                ? 'background-image:url(\'' . $item->image_url . '\');background-size:cover;background-position:center;'
                                : 'background: linear-gradient(135deg, ' . $theme['grad'] . ');' }}">
                            @if(!$item->image)
                                <div style="width: 52px; height: 52px; border-radius: 14px; background: {{ $theme['icon_bg'] }}; border: 1px solid {{ $theme['icon_border'] }}; display: flex; align-items: center; justify-content: center;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $theme['icon_color'] }}" stroke-width="1.8"><path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>
                                </div>
                            @endif
                            <div class="blog-cat" style="{{ $theme['cat_style'] }}">{{ $item->category->name ?? 'News' }}</div>
                        </div>
                        <div class="blog-body">
                            <div class="blog-meta">
                                <div class="blog-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    {{ $item->published_at->format('M j, Y') }}
                                </div>
                                <div class="blog-meta-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    {{ $item->read_time }} min read
                                </div>
                            </div>
                            <h3 class="blog-title">{{ $item->title }}</h3>
                            <p class="blog-excerpt">{{ $newsExcerpt }}</p>
                            <div class="blog-footer">
                                <div class="blog-author">
                                    <div class="ba-av" style="{{ $theme['av_style'] }}">{{ $initials }}</div>
                                    <span class="ba-name">{{ $authorName }}</span>
                                </div>
                                <a href="{{ route('news.show', $item) }}" class="blog-read-more">
                                    Read More
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <div class="reveal" style="text-align:center; padding: 40px 0; color: var(--txt3);">
                <p style="font-size:15px; font-weight:600; margin:0 0 4px;">No news articles published yet.</p>
                <p style="font-size:13.5px; margin:0;">Check back soon for updates from the alumni community.</p>
            </div>
            @endif
        </div>
    </section>
@endsection

@push ('scripts')
    <script src="{{ asset('js/index.js') }}"></script>
@endpush