@extends ('layouts.app')

@push ('styles')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
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
                            href="https://iccr.hialumni.com/#about-us-section"
                            class="btn-primary"
                        >
                            About Us
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </a>
                        <a
                            href="https://iccr.hialumni.com/all-event"
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
                            <span class="hero-stat-num">1<span>+</span></span>
                            <span class="hero-stat-label">Members</span>
                        </div>
                        <div class="hero-stat-sep"></div>
                        <div class="hero-stat">
                            <span class="hero-stat-num">3<span>+</span></span>
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
                        href="https://iccr.hialumni.com/login"
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
                                    >View All →</span
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
                                href="https://iccr.hialumni.com/login"
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
    <section class="stories-section">
        <div class="container">
            <div class="stories-header reveal">
                <div>
                    <div class="tag">Stories</div>
                    <h2
                        class="section-heading"
                        style="margin-bottom: 0; color: black"
                    >
                        Our Stories
                    </h2>
                </div>
                <a
                    href="https://iccr.hialumni.com/our-news"
                    class="btn-ghost"
                    style="white-space: nowrap"
                >
                    View All
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                </a>
            </div>
            <div class="stories-grid">
                <div class="story-card reveal">
                    <div class="story-img">
                        <div class="story-img-inner">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                                <path d="M18 14h-8" />
                                <path d="M15 18h-5" />
                                <path d="M10 6h8v4h-8V6Z" />
                            </svg>
                        </div>
                        <div class="story-img-date">May 07, 2026</div>
                    </div>
                    <div class="story-body">
                        <div class="story-title">India Africa Forum Summit</div>
                        <p class="story-excerpt">A landmark gathering bringing together ICCR alumni from across the African continent to discuss cultural diplomacy, shared heritage, and future collaborations.</p>
                        <a
                            href="https://iccr.hialumni.com/view-stories/India-Africa-forum-Summit"
                            class="story-link"
                        >
                            Know More
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
                <div class="story-card reveal reveal-delay-1">
                    <div
                        class="story-img"
                        style="
                            background: linear-gradient(
                                135deg,
                                rgba(30, 60, 100, 0.8),
                                rgba(244, 168, 37, 0.1)
                            );
                        "
                    >
                        <div class="story-img-inner">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                            </svg>
                        </div>
                        <div class="story-img-date">April 2026</div>
                    </div>
                    <div class="story-body">
                        <div class="story-title">
                            Alumni Mentorship Program Launch
                        </div>
                        <p class="story-excerpt">Connecting senior alumni with new graduates to build meaningful mentorship pathways across disciplines and borders.</p>
                        <a href="#" class="story-link">
                            Know More
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
                <div class="story-card reveal reveal-delay-2">
                    <div
                        class="story-img"
                        style="
                            background: linear-gradient(
                                135deg,
                                rgba(50, 20, 80, 0.8),
                                rgba(244, 168, 37, 0.12)
                            );
                        "
                    >
                        <div class="story-img-inner">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                        <div class="story-img-date">March 2026</div>
                    </div>
                    <div class="story-body">
                        <div class="story-title">
                            Cultural Exchange Networking Night
                        </div>
                        <p class="story-excerpt">An evening of cross-cultural dialogue and professional networking bringing ICCR scholars together in New Delhi.</p>
                        <a href="#" class="story-link">
                            Know More
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ════════════════════════════════════
     ⑦ NEW ALUMNI
════════════════════════════════════ -->
    <section class="alumni-section">
        <div class="container">
            <div class="reveal">
                <div class="tag">New Alumni</div>
                <div
                    style="
                        display: flex;
                        align-items: flex-end;
                        justify-content: space-between;
                        flex-wrap: wrap;
                        gap: 16px;
                    "
                >
                    <div>
                        <h2 class="section-heading" style="margin-bottom: 8px">
                            Recent Joined Alumni
                        </h2>
                        <p class="section-sub">The Alumni Association leverages the resources, talents, and initiatives of alumni and friends to advise, guide, and support the community.</p>
                    </div>
                </div>
            </div>
            <div class="alumni-grid">
                <div class="alumni-card reveal">
                    <div class="alumni-avatar av-1">OS</div>
                    <div class="alumni-name">Om Srivastava</div>
                    <div class="alumni-dept">Computer Science</div>
                    <span class="alumni-batch">Batch 2018</span>
                </div>
                <div class="alumni-card reveal reveal-delay-1">
                    <div class="alumni-avatar av-2">LB</div>
                    <div class="alumni-name">Laxmi Bhusal</div>
                    <div class="alumni-dept">—</div>
                    <span class="alumni-batch">Batch 2023</span>
                </div>
                <div class="alumni-card reveal reveal-delay-2">
                    <div class="alumni-avatar av-3">EB</div>
                    <div class="alumni-name">Elisha Bhusal</div>
                    <div class="alumni-dept">—</div>
                    <span class="alumni-batch">Batch 2023</span>
                </div>
                <div class="alumni-card reveal reveal-delay-3">
                    <div class="alumni-avatar av-4">MA</div>
                    <div class="alumni-name">Mohammed Abubakar Mohammed</div>
                    <div class="alumni-dept">—</div>
                    <span class="alumni-batch">Batch 2023</span>
                </div>
                <div class="alumni-card reveal">
                    <div class="alumni-avatar av-5">NN</div>
                    <div class="alumni-name">Nguyen Thi Ngoc Lanh</div>
                    <div class="alumni-dept">—</div>
                    <span class="alumni-batch">Batch 2024</span>
                </div>
                <div class="alumni-card reveal reveal-delay-1">
                    <div class="alumni-avatar av-6">LT</div>
                    <div class="alumni-name">Le Thi Thanh Thuy</div>
                    <div class="alumni-dept">—</div>
                    <span class="alumni-batch">Batch 2024</span>
                </div>
                <div class="alumni-card reveal reveal-delay-2">
                    <div class="alumni-avatar av-7">CT</div>
                    <div class="alumni-name">Chi Tran Thi Le</div>
                    <div class="alumni-dept">—</div>
                    <span class="alumni-batch">Batch 2024</span>
                </div>
                <div class="alumni-card reveal reveal-delay-3">
                    <div class="alumni-avatar av-8">MI</div>
                    <div class="alumni-name">Mirwais Ibrahimkhail</div>
                    <div class="alumni-dept">—</div>
                    <span class="alumni-batch">Batch 2020</span>
                </div>
            </div>
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
            <div class="gallery-grid">
                <div class="gallery-item wide reveal">
                    <div class="gallery-item-bg g-bg-1"></div>
                    <div class="gallery-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <circle cx="9" cy="9" r="2" />
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                        </svg>
                    </div>
                </div>
                <div class="gallery-item reveal reveal-delay-1">
                    <div class="gallery-item-bg g-bg-2"></div>
                    <div class="gallery-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                        </svg>
                    </div>
                </div>
                <div class="gallery-item reveal reveal-delay-2">
                    <div class="gallery-item-bg g-bg-3"></div>
                    <div class="gallery-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                        </svg>
                    </div>
                </div>
                <div class="gallery-item reveal reveal-delay-1">
                    <div class="gallery-item-bg g-bg-4"></div>
                    <div class="gallery-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </div>
                </div>
                <div class="gallery-item reveal reveal-delay-2">
                    <div class="gallery-item-bg g-bg-5"></div>
                    <div class="gallery-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z" /></svg>
                    </div>
                </div>
                <div class="gallery-item reveal reveal-delay-3">
                    <div class="gallery-item-bg g-bg-6"></div>
                    <div class="gallery-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <polygon points="23 7 16 12 23 17 23 7" />
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ════════════════════════════════════
     ⑨ BLOGS
════════════════════════════════════ -->
    <section class="blogs-section">
        <div class="container">
            <div class="blogs-header reveal">
                <div>
                    <div class="tag">Alumni Blogs</div>
                    <h2 class="section-heading" style="margin-bottom: 6px">
                        News & Views from our community
                    </h2>
                    <p class="section-sub">Explore news, views and perspectives from us and your alumni community.</p>
                </div>
                <a
                    href="https://iccr.hialumni.com/our-news"
                    class="btn-ghost"
                    style="
                        white-space: nowrap;
                        align-self: flex-end;
                        background: #fff;
                    "
                >
                    Explore All Blogs
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                </a>
            </div>
            <div class="blogs-grid">
                <div class="blog-card reveal">
                    <div
                        class="blog-img"
                        style="
                            background: linear-gradient(
                                135deg,
                                rgba(244, 168, 37, 0.12),
                                rgba(20, 40, 70, 0.9)
                            );
                        "
                    >
                        <div
                            style="
                                width: 52px;
                                height: 52px;
                                border-radius: 14px;
                                background: rgba(244, 168, 37, 0.15);
                                border: 1px solid rgba(244, 168, 37, 0.25);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            "
                        >
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F4A825" stroke-width="1.8">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                        <div class="blog-cat">Culture</div>
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
                                May 7, 2026
                            </div>
                            <div class="blog-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                4 min read
                            </div>
                        </div>
                        <h3 class="blog-title">
                            India Africa Forum Summit Highlights & Details
                        </h3>
                        <p class="blog-excerpt">Explore the key moments, discussions and outcomes from the landmark India Africa Forum Summit that brought ICCR alumni together.</p>
                        <div class="blog-footer">
                            <div class="blog-author">
                                <div class="ba-av">IC</div>
                                <span class="ba-name">ICCR Community</span>
                            </div>
                            <a
                                href="https://iccr.hialumni.com/our-news"
                                class="blog-read-more"
                            >
                                Read More
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="blog-card reveal reveal-delay-1">
                    <div
                        class="blog-img"
                        style="
                            background: linear-gradient(
                                135deg,
                                rgba(30, 60, 100, 0.85),
                                rgba(244, 168, 37, 0.08)
                            );
                        "
                    >
                        <div
                            style="
                                width: 52px;
                                height: 52px;
                                border-radius: 14px;
                                background: rgba(96, 165, 250, 0.15);
                                border: 1px solid rgba(96, 165, 250, 0.2);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            "
                        >
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="1.8">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                        <div
                            class="blog-cat"
                            style="
                                background: rgba(96, 165, 250, 0.15);
                                border-color: rgba(96, 165, 250, 0.2);
                                color: #60a5fa;
                            "
                        >
                            Network
                        </div>
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
                                April 2026
                            </div>
                            <div class="blog-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                3 min read
                            </div>
                        </div>
                        <h3 class="blog-title">
                            Building Bridges: Mentorship in the Alumni Network
                        </h3>
                        <p class="blog-excerpt">How ICCR alumni are using the platform to forge meaningful mentorship relationships that transcend borders.</p>
                        <div class="blog-footer">
                            <div class="blog-author">
                                <div
                                    class="ba-av"
                                    style="
                                        background: rgba(96, 165, 250, 0.15);
                                        border-color: rgba(96, 165, 250, 0.2);
                                        color: #60a5fa;
                                    "
                                >
                                    AL
                                </div>
                                <span class="ba-name">Alumni Team</span>
                            </div>
                            <a
                                href="https://iccr.hialumni.com/our-news"
                                class="blog-read-more"
                            >
                                Read More
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="blog-card reveal reveal-delay-2">
                    <div
                        class="blog-img"
                        style="
                            background: linear-gradient(
                                135deg,
                                rgba(50, 20, 80, 0.85),
                                rgba(244, 168, 37, 0.1)
                            );
                        "
                    >
                        <div
                            style="
                                width: 52px;
                                height: 52px;
                                border-radius: 14px;
                                background: rgba(167, 139, 250, 0.15);
                                border: 1px solid rgba(167, 139, 250, 0.2);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            "
                        >
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                            </svg>
                        </div>
                        <div
                            class="blog-cat"
                            style="
                                background: rgba(167, 139, 250, 0.15);
                                border-color: rgba(167, 139, 250, 0.2);
                                color: #a78bfa;
                            "
                        >
                            Global
                        </div>
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
                                March 2026
                            </div>
                            <div class="blog-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                5 min read
                            </div>
                        </div>
                        <h3 class="blog-title">
                            Reconnecting Across Continents: Alumni Stories
                        </h3>
                        <p class="blog-excerpt">Personal stories of ICCR scholars who found each other again through the alumni platform years after graduation.</p>
                        <div class="blog-footer">
                            <div class="blog-author">
                                <div
                                    class="ba-av"
                                    style="
                                        background: rgba(167, 139, 250, 0.15);
                                        border-color: rgba(167, 139, 250, 0.2);
                                        color: #a78bfa;
                                    "
                                >
                                    CR
                                </div>
                                <span class="ba-name">Community</span>
                            </div>
                            <a
                                href="https://iccr.hialumni.com/our-news"
                                class="blog-read-more"
                            >
                                Read More
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push ('scripts')
    <script src="{{ asset('js/index.js') }}"></script>
@endpush
