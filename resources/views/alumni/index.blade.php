@extends('layouts.app')
@section('title', 'Alumni Directory — ICCR Network')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/alumni.css') }}">
@endpush

@section('content')

<div class="ad-root">

    {{-- ══ MASTHEAD ══════════════════════════════════════════════════ --}}
    <header class="ad-masthead">
        <div class="ad-masthead__bg"></div>
        <div class="ad-masthead__inner">

            <div class="ad-masthead__eyebrow">
                <span class="ad-masthead__rule"></span>
                <span class="ad-masthead__org">ICCR Alumni Network</span>
                <span class="ad-masthead__rule"></span>
            </div>

            <h1 class="ad-masthead__title">Meet Our Alumni</h1>

            <p class="ad-masthead__sub">
                Scholars, artists, diplomats and innovators —
                connected by culture, spread across the world.
            </p>

            <div class="ad-masthead__stats">
                <div class="ad-stat">
                    <span class="ad-stat__num">{{ number_format($totalAlumni) }}</span>
                    <span class="ad-stat__label">Members</span>
                </div>
                <div class="ad-stat__divider"></div>
                <div class="ad-stat">
                    <span class="ad-stat__num">{{ $departments->count() }}</span>
                    <span class="ad-stat__label">Disciplines</span>
                </div>
                <div class="ad-stat__divider"></div>
                <div class="ad-stat">
                    <span class="ad-stat__num">{{ $passingYears->count() }}</span>
                    <span class="ad-stat__label">Cohorts</span>
                </div>
            </div>

        </div>
    </header>

    {{-- ══ STICKY FILTER BAR ═════════════════════════════════════════ --}}
    <div class="ad-filterbar" id="adFilterbar">
        <div class="ad-filterbar__inner">

            {{-- All controls live in ONE form so every field submits together --}}
            <form class="ad-filters" id="adForm" method="GET" action="{{ route('alumni') }}">

                {{-- Search --}}
                <div class="ad-search">
                    <svg class="ad-search__icon" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input class="ad-search__input" id="adSearchInput" type="text"
                           name="search" value="{{ $search }}"
                           placeholder="Search by name, institute, country, batch…"
                           autocomplete="off">
                    {{-- Clear × only when there is a search term --}}
                    <button type="button" class="ad-search__clear" id="adSearchClear"
                            title="Clear search" style="{{ $search ? '' : 'display:none' }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6"  x2="6"  y2="18"/>
                            <line x1="6"  y1="6"  x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                {{-- Department --}}
                <div class="ad-select-wrap">
                    <svg class="ad-select-icon" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                    <select class="ad-select" name="department" id="adDept">
                        <option value="">All Disciplines</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected($department === $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Passing year --}}
                <div class="ad-select-wrap">
                    <svg class="ad-select-icon" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                    <select class="ad-select" name="passing_year" id="adYear">
                        <option value="">All Cohorts</option>
                        @foreach($passingYears as $year)
                            <option value="{{ $year }}" @selected((string)$passingYear === (string)$year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="ad-search-btn" type="submit">Search</button>

                @if($search || $department || $passingYear)
                    <a href="{{ route('alumni') }}" class="ad-reset-btn">Clear all</a>
                @endif

            </form>

            {{-- Result count — sits to the right of the form, outside it --}}
            <div class="ad-result-meta" id="adResultMeta">
                <span class="ad-result-meta__count">{{ $alumni->total() }}</span>
                <span class="ad-result-meta__label">found</span>
                @if($search || $department || $passingYear)
                    <span class="ad-result-meta__filter">filtered</span>
                @endif
            </div>

        </div>
    </div>

    {{-- ══ BODY ═══════════════════════════════════════════════════════ --}}
    <div class="ad-body" id="adBody">

        {{-- Section head (editorial divider matching the rest of the portal) --}}
        <div class="ad-section-head" id="adSectionHead">
            <span class="ad-section-head__rule"></span>
            <span class="ad-section-head__label">
                {{ ($search || $department || $passingYear) ? 'Results' : 'All Members' }}
                <span class="ad-section-head__count">{{ $alumni->total() }}</span>
            </span>
            <span class="ad-section-head__rule"></span>
        </div>

        @if($alumni->count())

        {{-- ── CARD GRID ────────────────────────────────────────────── --}}
        <div class="ad-grid" id="adGrid">
            @foreach($alumni as $member)
            @php
                /* Initials (uses model accessor if available) */
                $initials   = $member->initials ?? strtoupper(substr($member->full_name, 0, 1));
                $isOnline   = $member->isOnline();

                /* Profile URL — redirect-to-login if not authenticated */
                $profileUrl = session('alumni_id')
                    ? route('alumni.profile', $member->id)
                    : route('login') . '?redirect=' . urlencode(route('alumni.profile', $member->id));

                /* Gradient palette for photo-less cards, keyed to first letter */
                $gradMap = [
                    'A' => ['#1C2331','#3d1a00'], 'B' => ['#0f2027','#203a43'],
                    'C' => ['#1a0a2e','#2d1b69'], 'D' => ['#0d1b2a','#1b4332'],
                    'E' => ['#241526','#6b2737'], 'F' => ['#0f1e2d','#1e3a5f'],
                    'G' => ['#1a1a0a','#4a3f00'], 'H' => ['#11241a','#2d4a22'],
                    'I' => ['#1a0020','#3d2060'], 'J' => ['#001a1a','#00404a'],
                    'K' => ['#1a0505','#4a1010'], 'L' => ['#001020','#1a3a60'],
                    'M' => ['#160016','#360040'], 'N' => ['#001210','#004040'],
                    'O' => ['#1a1000','#503000'], 'P' => ['#0a0020','#2a1060'],
                    'Q' => ['#000a1a','#002860'], 'R' => ['#1a0008','#500018'],
                    'S' => ['#001a08','#005020'], 'T' => ['#0a0a1a','#2a2a60'],
                    'U' => ['#001a1a','#005555'], 'V' => ['#120018','#3d1060'],
                    'W' => ['#0a1a0a','#204020'], 'X' => ['#1a0a00','#602010'],
                    'Y' => ['#1a1a00','#504000'], 'Z' => ['#000a0a','#003030'],
                ];
                $letter  = strtoupper(substr($member->full_name, 0, 1));
                [$gradA, $gradB] = $gradMap[$letter] ?? ['#1C2331','#2d3a50'];
            @endphp

            <article class="ad-card">
                <a href="{{ $profileUrl }}" class="ad-card__link">

                    <div class="ad-card__portrait">

                        {{-- Photo or gradient-initials placeholder --}}
                        @if(!empty($member->photo))
                            <img loading="lazy" src="{{ asset('storage/' . $member->photo) }}"
                                 alt="{{ $member->full_name }}"
                                 class="ad-card__photo" loading="lazy">
                        @else
                            <div class="ad-card__initials"
                                 style="background:radial-gradient(circle at 30% 25%, {{ $gradB }}, {{ $gradA }})">
                                <span>{{ $initials }}</span>
                            </div>
                        @endif

                        {{-- Online pulse dot --}}
                        @if($isOnline)
                            <span class="ad-online-dot" title="Online now"></span>
                        @endif

                        {{-- Bottom gradient veil --}}
                        <div class="ad-card__veil"></div>

                        {{-- Info overlay (always visible) --}}
                        <div class="ad-card__info">

                            {{-- Saffron accent rule --}}
                            <span class="ad-card__accent"></span>

                            <h3 class="ad-card__name">{{ $member->full_name }}</h3>

                            {{-- Job title / company OR department --}}
                            @if(!empty($member->current_job_title))
                                <p class="ad-card__role">
                                    {{ Str::limit($member->current_job_title, 36) }}
                                    @if(!empty($member->current_company))
                                        <span class="ad-card__org">, {{ Str::limit($member->current_company, 20) }}</span>
                                    @endif
                                </p>
                            @elseif(!empty($member->department))
                                <p class="ad-card__role">{{ Str::limit($member->department, 40) }}</p>
                            @endif

                            {{-- Country + passing year meta --}}
                            @if(!empty($member->country) || !empty($member->passing_year))
                            <div class="ad-card__meta">
                                @if(!empty($member->country))
                                    <span class="ad-meta-item">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        {{ $member->country }}
                                    </span>
                                @endif
                                @if(!empty($member->country) && !empty($member->passing_year))
                                    <span class="ad-meta-sep"></span>
                                @endif
                                @if(!empty($member->passing_year))
                                    <span class="ad-meta-item">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2">
                                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                                        </svg>
                                        {{ $member->passing_year }}
                                    </span>
                                @endif
                            </div>
                            @endif

                            {{-- "View profile" — fades in on hover via CSS --}}
                            <span class="ad-card__view">
                                View profile
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2.5">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <path d="m13 6 6 6-6 6"/>
                                </svg>
                            </span>

                        </div>{{-- /ad-card__info --}}

                    </div>{{-- /ad-card__portrait --}}

                </a>
            </article>

            @endforeach
        </div>{{-- /ad-grid --}}

        {{-- ── PAGINATION ───────────────────────────────────────────── --}}
        @if($alumni->hasPages())
        <nav class="ad-pagination" aria-label="Alumni pages">

            @if($alumni->onFirstPage())
                <span class="ad-page-btn ad-page-btn--disabled">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m15 18-6-6 6-6"/>
                    </svg> Prev
                </span>
            @else
                <a class="ad-page-btn" href="{{ $alumni->previousPageUrl() }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m15 18-6-6 6-6"/>
                    </svg> Prev
                </a>
            @endif

            @foreach($alumni->getUrlRange(max(1,$alumni->currentPage()-2), min($alumni->lastPage(),$alumni->currentPage()+2)) as $page => $url)
                <a class="ad-page-btn {{ $page == $alumni->currentPage() ? 'ad-page-btn--active' : '' }}"
                   href="{{ $url }}">{{ $page }}</a>
            @endforeach

            @if($alumni->hasMorePages())
                <a class="ad-page-btn" href="{{ $alumni->nextPageUrl() }}">
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            @else
                <span class="ad-page-btn ad-page-btn--disabled">
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </span>
            @endif

        </nav>
        @endif

        @else

        {{-- ── EMPTY STATE ──────────────────────────────────────────── --}}
        <div class="ad-empty">
            <div class="ad-empty__icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <p class="ad-empty__title">No alumni found</p>
            <p class="ad-empty__sub">Try adjusting your filters or broadening your search.</p>
            <a href="{{ route('alumni') }}" class="ad-reset-btn" style="margin-top:8px">Clear all filters</a>
        </div>

        @endif

    </div>{{-- /ad-body --}}

</div>{{-- /ad-root --}}

@push('scripts')
<script>
(function () {
    'use strict';

    const form     = document.getElementById('adForm');
    const input    = document.getElementById('adSearchInput');
    const clearBtn = document.getElementById('adSearchClear');
    const deptSel  = document.getElementById('adDept');
    const yearSel  = document.getElementById('adYear');
    const body     = document.getElementById('adBody');

    if (!form || !body) return;

    /* Selectors to swap on each live-fetch */
    const SELS = ['#adSectionHead', '.ad-grid', '.ad-pagination', '.ad-empty', '#adResultMeta'];

    let timer = null, current = '', ctl = null;

    /* ── Spinner ─────────────────────────────────────────────────── */
    const spinner = document.createElement('div');
    spinner.style.cssText = [
        'position:fixed','inset:0','z-index:9998',
        'display:none','align-items:center','justify-content:center','pointer-events:none',
    ].join(';');
    spinner.innerHTML = `
        <div style="width:44px;height:44px;border:3px solid rgba(232,100,12,.15);
                    border-top-color:#E8640C;border-radius:50%;
                    animation:ad-spin .7s linear infinite"></div>
        <style>@keyframes ad-spin{to{transform:rotate(360deg)}}</style>`;
    document.body.appendChild(spinner);

    /* ── Build URL from current form state ───────────────────────── */
    function buildUrl() {
        const url = new URL(window.location.href);
        const q = input?.value.trim() ?? '';
        q ? url.searchParams.set('search', q)        : url.searchParams.delete('search');
        const d = deptSel?.value ?? '';
        d ? url.searchParams.set('department', d)    : url.searchParams.delete('department');
        const y = yearSel?.value ?? '';
        y ? url.searchParams.set('passing_year', y)  : url.searchParams.delete('passing_year');
        url.searchParams.delete('page'); /* always reset to page 1 on filter change */
        return url;
    }

    /* ── Fetch & swap ────────────────────────────────────────────── */
    async function run(url) {
        const key = url.toString();
        if (key === current) return;
        current = key;
        ctl?.abort();
        ctl = new AbortController();
        spinner.style.display = 'flex';
        try {
            history.replaceState(null, '', url.toString());
            const res = await fetch(url, {
                signal: ctl.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            SELS.forEach(sel => {
                const inc = doc.querySelector(sel);
                const ex  = body.querySelector(sel);
                if (inc && ex)       ex.replaceWith(inc);
                else if (inc)        body.appendChild(inc);
                else if (!inc && ex) ex.remove();
            });
            /* Re-wire pagination smooth-scroll after DOM swap */
            body.querySelectorAll('.ad-pagination a').forEach(a =>
                a.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }))
            );
        } catch (e) {
            if (e.name !== 'AbortError') console.error('[alumni-live]', e);
        } finally {
            spinner.style.display = 'none';
        }
    }

    /* ── Show / hide the clear × button dynamically ──────────────── */
    function syncClearBtn() {
        if (clearBtn) clearBtn.style.display = input?.value.trim() ? '' : 'none';
    }

    /* ── Event listeners ─────────────────────────────────────────── */
    input?.addEventListener('input', function () {
        syncClearBtn();
        clearTimeout(timer);
        timer = setTimeout(() => run(buildUrl()), 340);
    });

    clearBtn?.addEventListener('click', function () {
        if (input) { input.value = ''; syncClearBtn(); input.focus(); }
        clearTimeout(timer);
        run(buildUrl());
    });

    /* Prevent full page reload — live search handles everything */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        run(buildUrl());
    });

    /* Smooth-scroll on initial pagination links */
    body.querySelectorAll('.ad-pagination a').forEach(a =>
        a.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }))
    );
})();
</script>
@endpush

@endsection