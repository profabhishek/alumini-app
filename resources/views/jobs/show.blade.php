@extends('layouts.app')
@section('title', $job->title . ' — ICCR Alumni Careers')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
@endpush

@section('content')

@php
    $isLoggedIn  = session()->has('alumni_id');
    $expired     = $job->isExpired();
    $salary      = $job->salaryRange();

    $appliedDate = null;
    $appStatus   = 'Submitted';
    if ($application) {
        $raw = $application->applied_at ?? $application->created_at ?? null;
        $appliedDate = $raw ? \Carbon\Carbon::parse($raw)->format('d F Y') : null;
        $appStatus   = $application->status ?? 'Submitted';
    }

    $letter = strtoupper(substr($job->company_name, 0, 1));
    $gradMap = [
        'A'=>['#1C2331','#3d1a00'],'B'=>['#0f2027','#203a43'],'C'=>['#1a0a2e','#2d1b69'],
        'D'=>['#0d1b2a','#1b4332'],'E'=>['#241526','#6b2737'],'F'=>['#0f1e2d','#1e3a5f'],
        'G'=>['#1a1a0a','#4a3f00'],'H'=>['#11241a','#2d4a22'],'I'=>['#1a0020','#3d2060'],
        'J'=>['#001a1a','#00404a'],'K'=>['#1a0505','#4a1010'],'L'=>['#001020','#1a3a60'],
        'M'=>['#160016','#360040'],'N'=>['#001210','#004040'],'O'=>['#1a1000','#503000'],
        'P'=>['#0a0020','#2a1060'],'Q'=>['#000a1a','#002860'],'R'=>['#1a0008','#500018'],
        'S'=>['#001a08','#005020'],'T'=>['#0a0a1a','#2a2a60'],'U'=>['#001a1a','#005555'],
        'V'=>['#120018','#3d1060'],'W'=>['#0a1a0a','#204020'],'X'=>['#1a0a00','#602010'],
        'Y'=>['#1a1a00','#504000'],'Z'=>['#000a0a','#003030'],
    ];
    [$cg1,$cg2] = $gradMap[$letter] ?? ['#1C2331','#2d3a50'];

    $typeColors = ['Full-Time'=>'saffron','Part-Time'=>'blue','Contract'=>'violet','Internship'=>'amber'];
    $accent = $typeColors[$job->job_type] ?? 'saffron';
@endphp

<div class="jr-root">

    {{-- Reading progress bar --}}
    <div class="jr-progress" id="jrProgress"></div>

    {{-- ══ HERO ════════════════════════════════════════════════════════ --}}
    <header class="jd-hero">
        <div class="jd-hero__bg">
            @if($job->banner_image)
                <img src="{{ asset('storage/' . $job->banner_image) }}" alt="{{ $job->title }}">
            @else
                <div class="jd-hero__bg-plain"
                     style="background:radial-gradient(ellipse at 30% 30%, {{ $cg2 }}, {{ $cg1 }})"></div>
            @endif
            <div class="jd-hero__scrim"></div>
        </div>

        <div class="jd-hero__inner">

            <a href="{{ route('jobs.index') }}" class="jd-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                All Jobs
            </a>

            {{-- Company logo + eyebrow --}}
            <div class="jd-hero__company">
                <div class="jd-company-logo"
                     style="background:radial-gradient(circle at 30% 25%, {{ $cg2 }}, {{ $cg1 }})">
                    <span>{{ $letter }}</span>
                </div>
                <div>
                    <p class="jd-hero__company-name">{{ $job->company_name }}</p>
                    <div class="jd-hero__badges">
                        <span class="jd-badge jd-badge--type jd-badge--{{ $accent }}">{{ $job->job_type }}</span>
                        <span class="jd-badge jd-badge--mode">{{ $job->work_mode }}</span>
                        @if($expired)
                            <span class="jd-badge jd-badge--expired">Expired</span>
                        @elseif($job->application_deadline && $job->application_deadline->diffInDays(now()) <= 7)
                            <span class="jd-badge jd-badge--urgent">Closing soon</span>
                        @endif
                    </div>
                </div>
            </div>

            <h1 class="jd-hero__title">{{ $job->title }}</h1>

            {{-- Quick meta facts --}}
            <div class="jd-hero__facts">
                @if($job->location)
                    <span class="jd-hero__fact">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $job->location }}
                    </span>
                @endif
                @if($salary && $salary !== 'Not specified')
                    <span class="jd-hero__fact jd-hero__fact--salary">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        {{ $salary }}
                    </span>
                @endif
                @if($job->application_deadline)
                    <span class="jd-hero__fact {{ $expired ? 'jd-hero__fact--expired' : '' }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $expired ? 'Closed' : 'Deadline' }}: {{ $job->application_deadline->format('d M Y') }}
                    </span>
                @endif
                <span class="jd-hero__fact">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Posted {{ $job->created_at->diffForHumans() }}
                </span>
            </div>

        </div>
    </header>

    {{-- ══ LAYOUT ═══════════════════════════════════════════════════════ --}}
    <div class="jd-layout">

        {{-- ── Main article ─────────────────────────────────────────── --}}
        <article class="jd-article">

            {{-- Applied notice (if already applied) --}}
            @if($alreadyApplied)
            <div class="jd-applied-banner">
                <div class="jd-applied-banner__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div>
                    <p class="jd-applied-banner__title">You've applied for this position</p>
                    @if($appliedDate)
                        <p class="jd-applied-banner__sub">Applied on {{ $appliedDate }} · Status: <strong>{{ ucfirst($appStatus) }}</strong></p>
                    @endif
                </div>
                <a href="{{ route('jobs.my-applications') }}" class="jd-applied-banner__link">
                    View application →
                </a>
            </div>
            @endif

            {{-- Description --}}
            <section class="jd-section">
                <div class="jd-section__head">
                    <span class="jd-section__accent"></span>
                    <h2 class="jd-section__title">About this role</h2>
                </div>
                <div class="jd-prose">{{ $job->description }}</div>
            </section>

            @if($job->requirements)
            <section class="jd-section">
                <div class="jd-section__head">
                    <span class="jd-section__accent"></span>
                    <h2 class="jd-section__title">Requirements</h2>
                </div>
                <div class="jd-prose">{{ $job->requirements }}</div>
            </section>
            @endif

            {{-- Mobile-only CTA --}}
            <div class="jd-mobile-cta">
                @if($expired)
                    <span class="jd-cta-closed">Applications closed</span>
                @elseif(!$isLoggedIn)
                    <a href="{{ route('login') }}" class="jr-btn jr-btn--primary" style="width:100%;justify-content:center;">Login to Apply</a>
                @elseif($alreadyApplied)
                    <a href="{{ route('jobs.my-applications') }}" class="jr-btn jr-btn--outline" style="width:100%;justify-content:center;">View My Applications</a>
                @else
                    <a href="{{ route('jobs.apply', $job) }}" class="jr-btn jr-btn--primary" style="width:100%;justify-content:center;">Apply Now →</a>
                @endif
            </div>

        </article>

        {{-- ── Sticky sidebar ───────────────────────────────────────── --}}
        <aside class="jd-sidebar">
            <div class="jd-sidebar__sticky">

                {{-- Apply card --}}
                <div class="jd-apply-card jd-apply-card--{{ $expired ? 'closed' : ($alreadyApplied ? 'applied' : 'open') }}">

                    @if($expired)
                        <div class="jd-apply-card__status jd-apply-card__status--closed">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                            <span>Applications Closed</span>
                        </div>
                        <p class="jd-apply-card__note">This position is no longer accepting applications.</p>

                    @elseif(!$isLoggedIn)
                        <p class="jd-apply-card__label">Ready to apply?</p>
                        <h3 class="jd-apply-card__title">{{ $job->title }}</h3>
                        <p class="jd-apply-card__company">{{ $job->company_name }}</p>
                        <a href="{{ route('login') }}" class="jd-apply-card__btn">
                            Login to Apply
                        </a>

                    @elseif($alreadyApplied)
                        <div class="jd-apply-card__status jd-apply-card__status--applied">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Application Submitted</span>
                        </div>
                        <div class="jd-apply-card__meta">
                            @if($appliedDate)
                            <div class="jd-meta-row-item">
                                <span class="jd-meta-row-item__label">Applied on</span>
                                <span class="jd-meta-row-item__val">{{ $appliedDate }}</span>
                            </div>
                            @endif
                            <div class="jd-meta-row-item">
                                <span class="jd-meta-row-item__label">Status</span>
                                <span class="jd-meta-row-item__val jd-status--{{ strtolower($appStatus) }}">{{ ucfirst($appStatus) }}</span>
                            </div>
                        </div>
                        <a href="{{ route('jobs.my-applications') }}" class="jd-apply-card__btn jd-apply-card__btn--secondary">
                            View My Applications
                        </a>

                    @else
                        <p class="jd-apply-card__label">Ready to apply?</p>
                        <h3 class="jd-apply-card__title">{{ $job->title }}</h3>
                        <p class="jd-apply-card__company">{{ $job->company_name }}</p>
                        <a href="{{ route('jobs.apply', $job) }}" class="jd-apply-card__btn">
                            Apply Now
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                        </a>
                        @if($job->application_deadline)
                            <p class="jd-apply-card__deadline">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Closes {{ $job->application_deadline->format('d M Y') }}
                            </p>
                        @endif
                    @endif

                    <div class="jd-apply-card__divider"></div>

                    {{-- Quick facts --}}
                    <div class="jd-apply-card__facts">
                        <div class="jd-apply-card__fact">
                            <span class="jd-apply-card__fact-label">Job Type</span>
                            <span class="jd-apply-card__fact-val">{{ $job->job_type }}</span>
                        </div>
                        <div class="jd-apply-card__fact">
                            <span class="jd-apply-card__fact-label">Work Mode</span>
                            <span class="jd-apply-card__fact-val">{{ $job->work_mode }}</span>
                        </div>
                        @if($salary && $salary !== 'Not specified')
                        <div class="jd-apply-card__fact">
                            <span class="jd-apply-card__fact-label">Salary</span>
                            <span class="jd-apply-card__fact-val">{{ $salary }}</span>
                        </div>
                        @endif
                        @if($job->location)
                        <div class="jd-apply-card__fact">
                            <span class="jd-apply-card__fact-label">Location</span>
                            <span class="jd-apply-card__fact-val">{{ $job->location }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Share --}}
                <button class="jd-share-btn" id="jdCopyLink">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    Copy link
                </button>

                <a href="{{ route('jobs.index') }}" class="jd-back-link">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    All jobs
                </a>

            </div>
        </aside>

    </div>{{-- /jd-layout --}}

    {{-- ══ RELATED JOBS ════════════════════════════════════════════════ --}}
    @if($relatedJobs->isNotEmpty())
    <section class="jd-related">
        <div class="jd-related__inner">

            <div class="jr-section-head" style="margin-bottom:28px">
                <span class="jr-section-head__rule"></span>
                <span class="jr-section-head__label">More Opportunities</span>
                <span class="jr-section-head__rule"></span>
            </div>

            <div class="jr-grid" style="margin-bottom:0">
                @foreach($relatedJobs as $rel)
                @php
                    $rExpired = $rel->isExpired();
                    $rLetter  = strtoupper(substr($rel->company_name, 0, 1));
                    [$rc1,$rc2] = $gradMap[$rLetter] ?? ['#1C2331','#2d3a50'];
                    $rAccent  = $typeColors[$rel->job_type] ?? 'saffron';
                    $rSalary  = $rel->salaryRange();
                @endphp
                <article class="jr-card jr-card--{{ $rAccent }}{{ $rExpired ? ' jr-card--expired' : '' }}">
                    @if($rel->banner_image)
                    <div class="jr-card__banner">
                        <img src="{{ asset('storage/' . $rel->banner_image) }}" alt="{{ $rel->company_name }}" loading="lazy">
                        <div class="jr-card__banner-scrim"></div>
                    </div>
                    @endif
                    <div class="jr-card__body">
                        <div class="jr-card__head">
                            <div class="jr-company-logo"
                                 style="background:radial-gradient(circle at 30% 25%, {{ $rc2 }}, {{ $rc1 }})">
                                <span>{{ $rLetter }}</span>
                            </div>
                            <div class="jr-card__head-text">
                                <h3 class="jr-card__title">{{ $rel->title }}</h3>
                                <p class="jr-card__company">{{ $rel->company_name }}</p>
                            </div>
                        </div>
                        <div class="jr-card__facts">
                            @if($rel->location)
                            <span class="jr-fact">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $rel->location }}
                            </span>
                            @endif
                            <span class="jr-fact">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                {{ $rel->job_type }}
                            </span>
                            <span class="jr-fact">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                {{ $rel->work_mode }}
                            </span>
                        </div>
                        <p class="jr-card__desc">{{ Str::limit(strip_tags($rel->description), 90) }}</p>
                        <div class="jr-card__footer">
                            <div class="jr-card__badges">
                                <span class="jr-badge jr-badge--type">{{ $rel->job_type }}</span>
                                @if($rExpired)
                                    <span class="jr-badge jr-badge--expired">Expired</span>
                                @endif
                            </div>
                            <div class="jr-card__actions">
                                <a href="{{ route('jobs.show', $rel->slug ?? $rel->id) }}"
                                   class="jr-btn jr-btn--outline jr-btn--sm">Details</a>
                            </div>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

        </div>
    </section>
    @endif

</div>{{-- /jr-root --}}

@push('scripts')
<script>
// Reading progress
(function () {
    const bar = document.getElementById('jrProgress');
    if (!bar) return;
    window.addEventListener('scroll', function () {
        const el = document.documentElement;
        bar.style.width = Math.min(100, el.scrollTop / (el.scrollHeight - el.clientHeight) * 100) + '%';
    }, { passive: true });
})();

        const orig = this.innerHTML;
        this.textContent = 'Copied!';
        this.style.cssText += ';background:#e8640c;color:#fff;border-color:#e8640c';
        setTimeout(() => { this.innerHTML = orig; this.style.background = ''; this.style.color = ''; this.style.borderColor = ''; }, 2000);
    });
});
</script>
@endpush

@endsection