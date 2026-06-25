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

/* ── Home gallery bento hover overlay ── */
.hg-cell {
    position: relative;
    overflow: hidden;
    cursor: pointer;
}
.hg-cell img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.45s ease;
}
.hg-cell:hover img { transform: scale(1.06); }
.hg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.78) 0%, rgba(0,0,0,0.1) 60%, transparent 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: flex-end;
    pointer-events: none;
}
.hg-cell:hover .hg-overlay { opacity: 1; }
.hg-overlay__inner {
    padding: 12px 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}
.hg-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.22);
    color: #fff;
    font-size: 14.5px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 50px;
    white-space: nowrap;
    line-height: 1.4;
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
                            <span class="hero-stat-num">134<span>+</span></span>
                            <span class="hero-stat-label">Universities</span>
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
                            <span class="hero-card-title">ICCR Alumni Network</span>
                        </div>
                        <div class="hero-card-body">
                            <div class="hcb-world">
                                <div class="hcb-world-globe"></div>
                                <div class="hcb-world-label">Spanning</div>
                                <div class="hcb-world-val">140+ Nations</div>
                                <div class="hcb-world-sub">
                                    Alumni united by culture, driven by purpose
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
                                    <div class="hcb-mini-label">Events Hosted</div>
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
                                    <div class="hcb-mini-num">19K+</div>
                                    <div class="hcb-mini-label">Alumni</div>
                                </div>
                            </div>
                            <div
                                class="member-avatars"
                                style="margin-top: 20px"
                            >
                                <div class="m-avatar av-1">RK</div>
                                <div class="m-avatar av-2">NA</div>
                                <div class="m-avatar av-3">PM</div>
                                <div class="m-avatar av-4">SB</div>
                                <div class="m-avatar m-avatar-more">+</div>
                                <span class="m-avatar-label"
                                    ><strong>New alumni</strong> joining daily</span
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
                                <img loading="lazy" src="{{ asset('storage/' . $alum->photo) }}" alt="{{ $alum->full_name }}">
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
                <div class="tag">Why Join Us</div>
                <h2 class="section-heading" style="text-align: center">
                    Everything you need, all in one place
                </h2>
                <p class="section-sub" style="text-align: center; margin: 0 auto">From job opportunities to batchmate reunions — the ICCR Alumni Network keeps you connected to what matters most.</p>
            </div>
            <div class="why-grid">
                <div class="why-card reveal reveal-delay-1">
                    <span class="why-num">01</span>
                    <div class="why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            <line x1="12" y1="12" x2="12" y2="16"/>
                            <line x1="10" y1="14" x2="14" y2="14"/>
                        </svg>
                    </div>
                    <h3 class="why-title">Find Job Opportunities</h3>
                    <p class="why-desc">Browse jobs posted by fellow alumni and trusted organisations. Get referred, apply directly, and land roles that match your skills — all within a network that already knows your worth.</p>
                </div>
                <div class="why-card reveal reveal-delay-2">
                    <span class="why-num">02</span>
                    <div class="why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <h3 class="why-title">Post your Research Papers</h3>
                    <p class="why-desc">Showcase your research and academic contributions to a global community of ICCR alumni. Publish your papers, increase their visibility, connect with fellow researchers, and inspire meaningful collaboration across disciplines.</p>
                </div>
                <div class="why-card reveal reveal-delay-3">
                    <span class="why-num">03</span>
                    <div class="why-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                            <line x1="9" y1="9" x2="9.01" y2="9"/>
                            <line x1="15" y1="9" x2="15.01" y2="9"/>
                        </svg>
                    </div>
                    <h3 class="why-title">Build Your Own Community</h3>
                    <p class="why-desc">Create groups, share stories, post updates, and participate in events. This is your space to grow together — a living community built by alumni, for alumni.</p>
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
                    <div class="about-main-card" style="overflow:hidden;background:linear-gradient(135deg,#1C2331 0%,#2d3a50 60%,#3a1a00 100%);border-radius:20px;padding:32px 28px 24px;display:flex;flex-direction:column;gap:20px;">
                        <div class="amc-tag" style="display:inline-flex;align-items:center;gap:8px;background:rgba(232,100,12,.15);color:#e8640c;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:6px 14px;border-radius:999px;width:fit-content;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#e8640c;display:inline-block;"></span>
                            ICCR Alumni Portal
                        </div>

                        {{-- Globe / network illustration --}}
                        <svg viewBox="0 0 340 200" xmlns="http://www.w3.org/2000/svg" style="width:100%;border-radius:12px;">
                            <rect width="340" height="200" fill="rgba(255,255,255,0.03)" rx="12"/>

                            {{-- Globe outline --}}
                            <circle cx="170" cy="100" r="72" fill="none" stroke="rgba(232,100,12,0.25)" stroke-width="1.5"/>
                            <ellipse cx="170" cy="100" rx="30" ry="72" fill="none" stroke="rgba(232,100,12,0.18)" stroke-width="1"/>
                            <ellipse cx="170" cy="100" rx="72" ry="25" fill="none" stroke="rgba(232,100,12,0.18)" stroke-width="1"/>
                            <line x1="98" y1="100" x2="242" y2="100" stroke="rgba(232,100,12,0.15)" stroke-width="1"/>
                            <line x1="170" y1="28" x2="170" y2="172" stroke="rgba(232,100,12,0.15)" stroke-width="1"/>

                            {{-- Connection dots & lines --}}
                            <line x1="170" y1="100" x2="60" y2="55" stroke="rgba(232,100,12,0.5)" stroke-width="1" stroke-dasharray="4 3"/>
                            <line x1="170" y1="100" x2="290" y2="60" stroke="rgba(232,100,12,0.5)" stroke-width="1" stroke-dasharray="4 3"/>
                            <line x1="170" y1="100" x2="50" y2="155" stroke="rgba(232,100,12,0.5)" stroke-width="1" stroke-dasharray="4 3"/>
                            <line x1="170" y1="100" x2="300" y2="150" stroke="rgba(232,100,12,0.5)" stroke-width="1" stroke-dasharray="4 3"/>
                            <line x1="170" y1="100" x2="170" y2="22" stroke="rgba(232,100,12,0.5)" stroke-width="1" stroke-dasharray="4 3"/>

                            {{-- Centre node --}}
                            <circle cx="170" cy="100" r="10" fill="#e8640c" opacity=".9"/>
                            <circle cx="170" cy="100" r="6" fill="#fff"/>

                            {{-- Outer alumni nodes --}}
                            <circle cx="60" cy="55" r="16" fill="rgba(28,35,49,0.9)" stroke="#e8640c" stroke-width="1.5"/>
                            <text x="60" y="59" text-anchor="middle" fill="#e8e8e8" font-size="8" font-family="sans-serif" font-weight="600">IN</text>

                            <circle cx="290" cy="60" r="16" fill="rgba(28,35,49,0.9)" stroke="#e8640c" stroke-width="1.5"/>
                            <text x="290" y="64" text-anchor="middle" fill="#e8e8e8" font-size="8" font-family="sans-serif" font-weight="600">NG</text>

                            <circle cx="50" cy="155" r="16" fill="rgba(28,35,49,0.9)" stroke="#e8640c" stroke-width="1.5"/>
                            <text x="50" y="159" text-anchor="middle" fill="#e8e8e8" font-size="8" font-family="sans-serif" font-weight="600">BR</text>

                            <circle cx="300" cy="150" r="16" fill="rgba(28,35,49,0.9)" stroke="#e8640c" stroke-width="1.5"/>
                            <text x="300" y="154" text-anchor="middle" fill="#e8e8e8" font-size="8" font-family="sans-serif" font-weight="600">JP</text>

                            <circle cx="170" cy="22" r="16" fill="rgba(28,35,49,0.9)" stroke="#e8640c" stroke-width="1.5"/>
                            <text x="170" y="26" text-anchor="middle" fill="#e8e8e8" font-size="8" font-family="sans-serif" font-weight="600">DE</text>

                            {{-- Pulse ring --}}
                            <circle cx="170" cy="100" r="18" fill="none" stroke="rgba(232,100,12,0.3)" stroke-width="1.5">
                                <animate attributeName="r" from="10" to="28" dur="2s" repeatCount="indefinite"/>
                                <animate attributeName="opacity" from="0.6" to="0" dur="2s" repeatCount="indefinite"/>
                            </circle>

                            {{-- Label --}}
                            <text x="170" y="186" text-anchor="middle" fill="rgba(255,255,255,0.4)" font-size="9" font-family="sans-serif" letter-spacing="2">ALUMNI ACROSS 120+ NATIONS</text>
                        </svg>

                        {{-- Mini stats row --}}
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                            <div style="background:rgba(255,255,255,0.05);border-radius:10px;padding:12px 10px;text-align:center;">
                                <div style="color:#e8640c;font-size:18px;font-weight:800;">25K+</div>
                                <div style="color:rgba(255,255,255,0.5);font-size:11px;margin-top:2px;">Members</div>
                            </div>
                            <div style="background:rgba(255,255,255,0.05);border-radius:10px;padding:12px 10px;text-align:center;">
                                <div style="color:#e8640c;font-size:18px;font-weight:800;">120+</div>
                                <div style="color:rgba(255,255,255,0.5);font-size:11px;margin-top:2px;">Countries</div>
                            </div>
                            <div style="background:rgba(255,255,255,0.05);border-radius:10px;padding:12px 10px;text-align:center;">
                                <div style="color:#e8640c;font-size:18px;font-weight:800;">45+</div>
                                <div style="color:rgba(255,255,255,0.5);font-size:11px;margin-top:2px;">Events</div>
                            </div>
                        </div>
                    </div>
                    <div class="about-float-card">
                        <div class="afc-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div>
                            <div class="afc-num">19,000+</div>
                            <div class="afc-label">Alumni &amp; Growing</div>
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
                    <div class="stat-num">{{ $statAlumni > 0 ? number_format($statAlumni) : '19,000' }}<span>+</span></div>
                    <div class="stat-label">Alumni Members</div>
                </div>
                <div class="stat-item reveal reveal-delay-1">
                    <div class="stat-num">{{ $statCountries > 0 ? $statCountries : 120 }}<span>+</span></div>
                    <div class="stat-label">Countries</div>
                </div>
                <div class="stat-item reveal reveal-delay-2">
                    <div class="stat-num">{{ $statEvents > 0 ? $statEvents : 45 }}<span>+</span></div>
                    <div class="stat-label">Events Hosted</div>
                </div>
                <div class="stat-item reveal reveal-delay-3">
                    <div class="stat-num">{{ $statGallery > 0 ? $statGallery : '∞' }}{{ $statGallery > 0 ? '+' : '' }}</div>
                    <div class="stat-label">Memories Shared</div>
                </div>
            </div>
        </div>
    </div>
    <!-- ════════════════════════════════════
     ⑤ RECENT MEMORIES / PHOTO HIGHLIGHTS
════════════════════════════════════ -->
    <section style="padding: 80px 0; background: var(--dark, #0d0d14);">
        <div class="container">

            {{-- Section header --}}
            <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:36px;">
                <div class="reveal">
                    <div class="tag">Gallery</div>
                    <h2 class="section-heading" style="margin-bottom:6px;color:#fff;">
                        Memories That Matter
                    </h2>
                    <p style="color:rgba(255,255,255,0.45);font-size:15px;max-width:440px;margin:0;line-height:1.6;">
                        Events, reunions, cultural celebrations — captured moments from our global alumni community.
                    </p>
                </div>
                @if(\Route::has('gallery'))
                <a href="{{ route('gallery') }}" class="btn-ghost reveal reveal-delay-1" style="white-space:nowrap;color:var(--gold);border-color:rgba(244,168,37,0.35);">
                    View Full Gallery
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                @endif
            </div>

            @if($recentPhotos->isNotEmpty())
            @php
                $featuredPhoto = $recentPhotos->first();
                $thumbPhotos   = $recentPhotos->skip(1)->take(6);
            @endphp

            {{-- 7-photo bento grid --}}
            <div style="
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                grid-template-rows: 240px 240px;
                gap: 10px;
                border-radius: 20px;
                overflow: hidden;
            " class="reveal">

                {{-- Featured: spans 2 cols × 2 rows --}}
                <div style="grid-column:1/3;grid-row:1/3;position:relative;overflow:hidden;cursor:pointer;"
                     onclick="homeLbOpen(0)"
                     onmouseenter="hgIn(this)" onmouseleave="hgOut(this)">
                    <img loading="lazy" src="{{ asset('storage/' . $featuredPhoto->image) }}"
                         alt="{{ $featuredPhoto->title ?? 'Gallery' }}"
                         style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.5s ease;">
                    <div class="hg-overlay" style="opacity:0;transition:opacity 0.3s ease;">
                        <div class="hg-overlay__inner">
                            @if($featuredPhoto->event_name)
                            <span class="hg-chip">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $featuredPhoto->event_name }}
                            </span>
                            @endif
                            @if($featuredPhoto->event_date)
                            <span class="hg-chip">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ \Carbon\Carbon::parse($featuredPhoto->event_date)->format('d M Y') }}
                            </span>
                            @endif
                            @if($featuredPhoto->country)
                            <span class="hg-chip">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $featuredPhoto->country }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div style="position:absolute;top:14px;left:14px;background:var(--gold);color:#000;font-size:10px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;padding:4px 10px;border-radius:50px;pointer-events:none;">
                        Latest
                    </div>
                </div>

                {{-- 6 thumbnails filling the remaining 2 cols × 2 rows (3 per column) --}}
                @foreach($thumbPhotos as $idx => $photo)
                <div style="position:relative;overflow:hidden;cursor:pointer;"
                     onclick="homeLbOpen({{ $idx + 1 }})"
                     onmouseenter="hgIn(this)" onmouseleave="hgOut(this)">
                    <img loading="lazy" src="{{ asset('storage/' . $photo->image) }}"
                         alt="{{ $photo->title ?? 'Gallery' }}"
                         style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.4s ease;">
                    <div class="hg-overlay" style="opacity:0;transition:opacity 0.3s ease;">
                        <div class="hg-overlay__inner">
                            @if($photo->event_name)
                            <span class="hg-chip">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $photo->event_name }}
                            </span>
                            @endif
                            @if($photo->event_date)
                            <span class="hg-chip">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ \Carbon\Carbon::parse($photo->event_date)->format('d M Y') }}
                            </span>
                            @endif
                            @if($photo->country)
                            <span class="hg-chip">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $photo->country }}
                            </span>
                            @endif
                        </div>
                    </div>
                    {{-- last thumb: "View All" overlay --}}
                    @if($idx === 5 && $statGallery > 7)
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.6);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;pointer-events:none;">
                        <span style="font-size:22px;font-weight:800;color:#fff;">+{{ $statGallery - 7 }}</span>
                        <span style="font-size:11px;color:rgba(255,255,255,0.7);letter-spacing:0.05em;">more photos</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Inline lightbox for home gallery --}}
            <div id="homeLb" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.92);z-index:9998;align-items:center;justify-content:center;" onclick="if(event.target===this)homeLbClose()">
                <button onclick="homeLbClose()" style="position:absolute;top:20px;right:24px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:#fff;width:40px;height:40px;border-radius:50%;cursor:pointer;font-size:18px;line-height:1;display:flex;align-items:center;justify-content:center;">✕</button>
                <button onclick="homeLbNav(-1)" style="position:absolute;left:20px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);color:#fff;width:44px;height:44px;border-radius:50%;cursor:pointer;font-size:20px;display:flex;align-items:center;justify-content:center;">‹</button>
                <img id="homeLbImg" src="" alt="" style="max-width:90vw;max-height:88vh;border-radius:14px;object-fit:contain;box-shadow:0 30px 80px rgba(0,0,0,0.8);">
                <button onclick="homeLbNav(1)" style="position:absolute;right:20px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);color:#fff;width:44px;height:44px;border-radius:50%;cursor:pointer;font-size:20px;display:flex;align-items:center;justify-content:center;">›</button>
            </div>
            <script>
            function hgIn(el) {
                var img = el.querySelector('img');
                var ov  = el.querySelector('.hg-overlay');
                if (img) img.style.transform = 'scale(1.06)';
                if (ov)  ov.style.opacity    = '1';
            }
            function hgOut(el) {
                var img = el.querySelector('img');
                var ov  = el.querySelector('.hg-overlay');
                if (img) img.style.transform = 'scale(1)';
                if (ov)  ov.style.opacity    = '0';
            }
            const _hlPhotos = [
                @foreach($recentPhotos as $p)
                "{{ asset('storage/' . $p->image) }}",
                @endforeach
            ];
            let _hlIdx = 0;
            function homeLbOpen(i) { _hlIdx=i; document.getElementById('homeLbImg').src=_hlPhotos[i]; document.getElementById('homeLb').style.display='flex'; document.body.style.overflow='hidden'; }
            function homeLbClose() { document.getElementById('homeLb').style.display='none'; document.body.style.overflow=''; }
            function homeLbNav(d) { _hlIdx=(_hlIdx+d+_hlPhotos.length)%_hlPhotos.length; document.getElementById('homeLbImg').src=_hlPhotos[_hlIdx]; }
            document.addEventListener('keydown',e=>{ if(document.getElementById('homeLb').style.display==='flex'){ if(e.key==='Escape')homeLbClose(); if(e.key==='ArrowLeft')homeLbNav(-1); if(e.key==='ArrowRight')homeLbNav(1); }});
            </script>

            @else
            {{-- Empty placeholder --}}
            <div style="background:var(--dark3,#141420);border:1px dashed rgba(255,255,255,0.1);border-radius:20px;padding:60px;text-align:center;color:rgba(255,255,255,0.3);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="margin:0 auto 14px;display:block;opacity:0.3;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                <p style="margin:0;font-size:14px;">Gallery photos will appear here once added.</p>
            </div>
            @endif

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
                                <img loading="lazy" src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}">
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