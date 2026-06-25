@extends('layouts.app')
@section('title', 'Job Opportunities — ICCR Alumni')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
@endpush

@section('content')

<div class="jr-root">

    {{-- ══ MASTHEAD + SEARCH ═══════════════════════════════════════════ --}}
    <header class="jr-masthead">
        <div class="jr-masthead__bg"></div>
        <div class="jr-masthead__inner">

            <div class="jr-masthead__eyebrow">
                <span class="jr-masthead__rule"></span>
                <span class="jr-masthead__org">ICCR Alumni Careers</span>
                <span class="jr-masthead__rule"></span>
            </div>

            <h1 class="jr-masthead__title">Find Your Next Role</h1>
            <p class="jr-masthead__sub">Opportunities shared by alumni and partner organisations worldwide.</p>

            {{-- Embedded search --}}
            <form class="jr-search-bar" method="GET" action="{{ route('jobs.index') }}">
                {{-- Carry through active filters --}}
                @if(request('job_type'))  <input type="hidden" name="job_type"  value="{{ request('job_type') }}"> @endif
                @if(request('work_mode')) <input type="hidden" name="work_mode" value="{{ request('work_mode') }}"> @endif
                @if(request('filter'))    <input type="hidden" name="filter"    value="{{ request('filter') }}"> @endif

                <div class="jr-search-field">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" id="jrSearchInput"
                           value="{{ request('search') }}"
                           placeholder="Job title, keyword or company…" autocomplete="off">
                </div>
                <button class="jr-search-btn" type="submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Search
                </button>
            </form>

        </div>
    </header>

    {{-- ══ BODY ════════════════════════════════════════════════════════ --}}
    <div class="jr-body">

        {{-- ── Filter toolbar ───────────────────────────────────────── --}}
        <div class="jr-toolbar">
            <div class="jr-pills">

                {{-- Job type --}}
                <a href="{{ request()->fullUrlWithQuery(['job_type' => '', 'page' => '']) }}"
                   class="jr-pill {{ !request('job_type') ? 'jr-pill--active' : '' }}">All</a>

                @foreach(['Full-Time','Part-Time','Contract','Internship'] as $type)
                    <a href="{{ request()->fullUrlWithQuery(['job_type' => $type, 'page' => '']) }}"
                       class="jr-pill {{ request('job_type') === $type ? 'jr-pill--active' : '' }}">{{ $type }}</a>
                @endforeach

                {{-- Work mode --}}
                @foreach(['Remote','On-site','Hybrid'] as $mode)
                    <a href="{{ request()->fullUrlWithQuery(['work_mode' => $mode, 'page' => '']) }}"
                       class="jr-pill {{ request('work_mode') === $mode ? 'jr-pill--active' : '' }}">{{ $mode }}</a>
                @endforeach

                {{-- Status --}}
                <a href="{{ request()->fullUrlWithQuery(['filter' => 'active', 'page' => '']) }}"
                   class="jr-pill {{ request('filter') === 'active' ? 'jr-pill--active' : '' }}">Active</a>
                <a href="{{ request()->fullUrlWithQuery(['filter' => 'expired', 'page' => '']) }}"
                   class="jr-pill {{ request('filter') === 'expired' ? 'jr-pill--active' : '' }}">Expired</a>

                {{-- Clear --}}
                @if(request()->hasAny(['search','job_type','work_mode','filter','date_from','date_to']))
                    <a href="{{ route('jobs.index') }}" class="jr-pill jr-pill--clear">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Clear All
                    </a>
                @endif

            </div>

            <div class="jr-meta">
                <span class="jr-meta__num">{{ $jobs->total() }}</span>
                <span class="jr-meta__label">{{ $jobs->total() === 1 ? 'opportunity' : 'opportunities' }}</span>
                @if(request('search'))
                    <span class="jr-meta__term">for "{{ request('search') }}"</span>
                @endif
            </div>
        </div>

        {{-- ── Date range filter ────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('jobs.index') }}" class="jr-date-filter">
            @foreach(['search','job_type','work_mode','filter'] as $k)
                @if(request($k)) <input type="hidden" name="{{ $k }}" value="{{ request($k) }}"> @endif
            @endforeach
            <span class="jr-date-filter__label">Posted:</span>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="jr-date-input" title="From">
            <span class="jr-date-filter__sep">–</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="jr-date-input" title="To">
            <button type="submit" class="jr-date-filter__btn">Apply</button>
        </form>

        {{-- ── Section head ─────────────────────────────────────────── --}}
        @if($jobs->isNotEmpty())
        <div class="jr-section-head">
            <span class="jr-section-head__rule"></span>
            <span class="jr-section-head__label">
                {{ request()->hasAny(['search','job_type','work_mode','filter']) ? 'Results' : 'Latest Openings' }}
            </span>
            <span class="jr-section-head__rule"></span>
        </div>
        @endif

        {{-- ── Empty state ──────────────────────────────────────────── --}}
        @if($jobs->isEmpty())
        <div class="jr-empty">
            <div class="jr-empty__icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
            </div>
            <p class="jr-empty__title">No opportunities found</p>
            <p class="jr-empty__sub">Try adjusting your search terms or clearing the filters.</p>
            @if(request()->hasAny(['search','job_type','work_mode','filter']))
                <a href="{{ route('jobs.index') }}" class="jr-btn jr-btn--outline">Clear all filters</a>
            @endif
        </div>

        @else

        {{-- ── Jobs grid ────────────────────────────────────────────── --}}
        <div class="jr-grid">
            @foreach($jobs as $job)
            @php
                $expired     = $job->isExpired();
                $isLoggedIn  = session()->has('alumni_id');
                $alreadyApplied = $isLoggedIn && in_array($job->id, $appliedJobIds ?? []);

                $typeColors = [
                    'Full-Time'  => 'saffron',
                    'Part-Time'  => 'blue',
                    'Contract'   => 'violet',
                    'Internship' => 'amber',
                ];
                $accent = $typeColors[$job->job_type] ?? 'saffron';

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
                $salary = $job->salaryRange();
                $daysLeft = (!$expired && $job->application_deadline)
                    ? (int) now()->startOfDay()->diffInDays($job->application_deadline->startOfDay())
                    : null;
                $closingSoon = $daysLeft !== null && $daysLeft <= 7;
            @endphp

            <article class="jr-card jr-card--{{ $accent }}{{ $expired ? ' jr-card--expired' : '' }}">

                {{-- Optional banner image --}}
                @if($job->banner_image)
                <div class="jr-card__banner">
                    <img src="{{ asset('storage/' . $job->banner_image) }}" alt="{{ $job->company_name }}" loading="lazy">
                    <div class="jr-card__banner-scrim"></div>
                </div>
                @endif

                <div class="jr-card__body">

                    {{-- Company logo + title --}}
                    <div class="jr-card__head">
                        <div class="jr-company-logo"
                             style="background:radial-gradient(circle at 30% 25%, {{ $cg2 }}, {{ $cg1 }})">
                            <span>{{ $letter }}</span>
                        </div>
                        <div class="jr-card__head-text">
                            <h3 class="jr-card__title">{{ $job->title }}</h3>
                            <p class="jr-card__company">{{ $job->company_name }}</p>
                        </div>
                    </div>

                    {{-- Facts --}}
                    <div class="jr-card__facts">
                        @if($job->location)
                        <span class="jr-fact">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $job->location }}
                        </span>
                        @endif
                        <span class="jr-fact">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                            {{ $job->job_type }}
                        </span>
                        <span class="jr-fact">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            {{ $job->work_mode }}
                        </span>
                        @if($salary && $salary !== 'Not specified')
                        <span class="jr-fact jr-fact--salary">
                            {{ $salary }}
                        </span>
                        @endif
                    </div>

                    {{-- Description snippet --}}
                    <p class="jr-card__desc">{{ Str::limit(strip_tags($job->description), 110) }}</p>

                    {{-- Footer --}}
                    <div class="jr-card__footer">
                        <div class="jr-card__badges">
                            <span class="jr-badge jr-badge--type">{{ $job->job_type }}</span>
                            <span class="jr-badge jr-badge--mode">{{ $job->work_mode }}</span>
                            @if($expired)
                                <span class="jr-badge jr-badge--expired">Expired</span>
                            @elseif($closingSoon)
                                <span class="jr-badge jr-badge--urgent">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ $daysLeft === 0 ? 'Closes today' : 'Closes in ' . $daysLeft . ' ' . ($daysLeft === 1 ? 'day' : 'days') }}
                                </span>
                            @elseif($job->application_deadline)
                                <span class="jr-badge jr-badge--deadline">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    {{ $job->application_deadline->format('d M') }}
                                </span>
                            @endif
                        </div>

                        <div class="jr-card__actions">
                            <span class="jr-card__posted">{{ $job->created_at->diffForHumans() }}</span>
                            <a href="{{ route('jobs.show', $job->slug ?? $job->id) }}"
                               class="jr-btn jr-btn--outline jr-btn--sm">Details</a>

                            @if(!$expired)
                                @if($isLoggedIn)
                                    @if($alreadyApplied)
                                        <span class="jr-btn jr-btn--applied jr-btn--sm">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            Applied
                                        </span>
                                    @else
                                        <a href="{{ route('jobs.apply', $job) }}"
                                           class="jr-btn jr-btn--primary jr-btn--sm">Apply →</a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}"
                                       class="jr-btn jr-btn--primary jr-btn--sm">Login to Apply</a>
                                @endif
                            @endif
                        </div>
                    </div>

                </div>{{-- /jr-card__body --}}

            </article>
            @endforeach
        </div>

        {{-- ── Pagination ────────────────────────────────────────────── --}}
        @if($jobs->hasPages())
        <nav class="jr-pagination" aria-label="Jobs pages">
            @if($jobs->onFirstPage())
                <span class="jr-page-btn jr-page-btn--disabled">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg> Prev
                </span>
            @else
                <a class="jr-page-btn" href="{{ $jobs->previousPageUrl() }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg> Prev
                </a>
            @endif

            @foreach($jobs->getUrlRange(max(1,$jobs->currentPage()-2), min($jobs->lastPage(),$jobs->currentPage()+2)) as $page => $url)
                <a class="jr-page-btn {{ $page == $jobs->currentPage() ? 'jr-page-btn--active' : '' }}"
                   href="{{ $url }}">{{ $page }}</a>
            @endforeach

            @if($jobs->hasMorePages())
                <a class="jr-page-btn" href="{{ $jobs->nextPageUrl() }}">
                    Next <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @else
                <span class="jr-page-btn jr-page-btn--disabled">
                    Next <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </span>
            @endif
        </nav>
        @endif

        @endif{{-- /jobs not empty --}}

        {{-- ── Post-a-job CTA ───────────────────────────────────────── --}}
        @auth
        <div class="jr-cta">
            <div class="jr-cta__inner">
                <div class="jr-cta__text">
                    <p class="jr-cta__eyebrow">For the community</p>
                    <h2 class="jr-cta__title">Have an opportunity to share?</h2>
                    <p class="jr-cta__sub">Help fellow alumni find their next role by posting from your organisation.</p>
                </div>
                <a href="{{ route('jobs.create') }}" class="jr-btn jr-btn--cta">
                    Post a Job
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                </a>
            </div>
        </div>
        @endauth

    </div>{{-- /jr-body --}}

</div>{{-- /jr-root --}}

@push('scripts')
<script>
(function () {
    // Scroll-reveal on cards
    const cards = document.querySelectorAll('.jr-card');
    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach((e, i) => {
                if (e.isIntersecting) {
                    setTimeout(() => e.target.classList.add('jr-card--visible'), i * 55);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.05 });
        cards.forEach(c => obs.observe(c));
    } else {
        cards.forEach(c => c.classList.add('jr-card--visible'));
    }
})();
</script>
@endpush

@endsection