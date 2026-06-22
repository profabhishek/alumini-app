@extends('layouts.app')

@section('title', 'Events — ICCR Alumni')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/events-redesign.css') }}">
@endpush

@section('content')

@php
    $today = now()->toDateString();
    $showFeatured = $events->currentPage() == 1
        && $events->isNotEmpty()
        && !request()->filled('filter')
        && !request()->filled('search');
    $featured = $showFeatured ? $events->first() : null;
@endphp

<div class="ev-root">

    {{-- ── MASTHEAD ─────────────────────────────────────────────── --}}
    <header class="ev-masthead">
        <span class="ev-masthead__ghost" aria-hidden="true">∞</span>
        <div class="ev-masthead__inner">
            <div class="ev-masthead__eyebrow">
                <span class="ev-masthead__dot" aria-hidden="true"></span>
                <span class="ev-masthead__eyebrow-text">ICCR Alumni Network</span>
            </div>
            <h1 class="ev-masthead__title">
                Discover &nbsp;<em>Events</em>
            </h1>
            <p class="ev-masthead__sub">
                Alumni gatherings, networking sessions, cultural celebrations
                and academic forums — across the globe.
            </p>
            <div class="ev-masthead__stats">
                <div class="ev-stat">
                    <span class="ev-stat__num">{{ $events->total() }}</span>
                    <span class="ev-stat__label">Events</span>
                </div>
                <div class="ev-stat">
                    <span class="ev-stat__num">{{ $upcomingCount }}</span>
                    <span class="ev-stat__label">Upcoming</span>
                </div>
                <div class="ev-stat">
                    <span class="ev-stat__num">{{ $ongoingCount }}</span>
                    <span class="ev-stat__label">Ongoing</span>
                </div>
            </div>
        </div>
    </header>

    {{-- ── TOOLBAR ─────────────────────────────────────────────── --}}
    <div class="ev-toolbar">
        <div class="ev-toolbar__inner">
            <form class="ev-search" method="GET" action="{{ route('events.index') }}">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <svg class="ev-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="ev-search__input" type="text" name="search"
                       value="{{ request('search') }}" placeholder="Search events, locations…" autocomplete="off">
                <button class="ev-search__btn" type="submit">Search</button>
            </form>

            <div class="ev-filters">
                <a href="{{ route('events.index', array_filter(['search' => request('search')])) }}"
                   class="ev-chip {{ request('filter','') === '' ? 'ev-chip--active' : '' }}">
                    All
                </a>
                <a href="{{ route('events.index', array_filter(['filter' => 'upcoming', 'search' => request('search')])) }}"
                   class="ev-chip {{ request('filter') === 'upcoming' ? 'ev-chip--active' : '' }}">
                    Upcoming
                </a>
                <a href="{{ route('events.index', array_filter(['filter' => 'ongoing', 'search' => request('search')])) }}"
                   class="ev-chip {{ request('filter') === 'ongoing' ? 'ev-chip--active' : '' }}">
                    Ongoing
                </a>
                <a href="{{ route('events.index', array_filter(['filter' => 'past', 'search' => request('search')])) }}"
                   class="ev-chip {{ request('filter') === 'past' ? 'ev-chip--active' : '' }}">
                    Past
                </a>
            </div>
        </div>
    </div>

    <div class="ev-body">

        {{-- ── FEATURED ─────────────────────────────────────────── --}}
        @if($featured)
        @php
            $fd = $featured->start_date;
            if ($featured->start_date->toDateString() > $today) {
                $fStatus = 'Upcoming'; $fStatusClass = 'ev-badge--up';
            } elseif ($featured->end_date && $featured->end_date->toDateString() >= $today) {
                $fStatus = 'Ongoing'; $fStatusClass = 'ev-badge--on';
            } else {
                $fStatus = 'Past'; $fStatusClass = 'ev-badge--past';
            }
            $fUrl = route('events.show', $featured->slug ?? $featured->id);
        @endphp
        <a href="{{ $fUrl }}" class="ev-feature ev-reveal">
            <div class="ev-feature__image">
                @if($featured->banner_image)
                    <img src="{{ asset('storage/' . $featured->banner_image) }}" alt="{{ $featured->title }}" loading="lazy">
                @else
                    <div class="ev-feature__placeholder"></div>
                @endif
                <div class="ev-feature__scrim"></div>
                <div class="ev-feature__date-stamp" aria-label="{{ $fd->format('d F Y') }}">
                    <span class="ev-feature__date-stamp__month">{{ $fd->format('M Y') }}</span>
                    <span class="ev-feature__date-stamp__day">{{ $fd->format('d') }}</span>
                </div>
            </div>

            <div class="ev-feature__content">
                <div class="ev-feature__eyebrow">
                    <span class="ev-badge ev-badge--feat">Featured</span>
                    <span class="ev-badge {{ $fStatusClass }}">{{ $fStatus }}</span>
                    @if($featured->category)
                        <span class="ev-feature__sep"></span>
                        <span class="ev-feature__category">{{ $featured->category }}</span>
                    @endif
                </div>

                <h2 class="ev-feature__title">{{ $featured->title }}</h2>

                <div class="ev-feature__meta">
                    <div class="ev-feature__meta-row">
                        <span class="ev-feature__meta-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </span>
                        {{ $featured->start_date->format('D, d F Y') }}
                        @if($featured->end_date && $featured->end_date->ne($featured->start_date))
                            &mdash; {{ $featured->end_date->format('d F Y') }}
                        @endif
                        &nbsp;&middot;&nbsp; {{ date('g:i A', strtotime($featured->start_time)) }}
                    </div>
                    <div class="ev-feature__meta-row">
                        <span class="ev-feature__meta-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        {{ $featured->location ?: 'Online Event' }}
                        &nbsp;&middot;&nbsp;
                        <span style="opacity:.6;">{{ $featured->event_mode }}</span>
                    </div>
                </div>

                <div class="ev-feature__footer">
                    <div class="ev-feature__price">
                        {{ $featured->event_type === 'Free' ? 'Free' : '$' . number_format($featured->ticket_price, 0) }}
                        <span>{{ $featured->event_type === 'Free' ? 'No fee' : 'per person' }}</span>
                    </div>
                    <span class="ev-feature__cta">
                        View Event
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                    </span>
                </div>
            </div>
        </a>
        @endif

        {{-- ── SECTION HEAD ─────────────────────────────────────── --}}
        @if($events->isNotEmpty())
        <div class="ev-section-head">
            <span class="ev-section-head__rule"></span>
            <span class="ev-section-head__label">
                @if(request()->filled('search'))
                    Results for "{{ request('search') }}"
                @elseif(request()->filled('filter'))
                    {{ ucfirst(request('filter')) }} Events
                @else
                    All Events
                @endif
                <span class="ev-section-head__count">{{ $events->total() }}</span>
            </span>
            <span class="ev-section-head__rule"></span>
        </div>
        @endif

        {{-- ── GRID ────────────────────────────────────────────── --}}
        @if($events->isNotEmpty())
        <div class="ev-grid">
            @foreach($events as $event)
                @continue($featured && $event->id === $featured->id)
                @php
                    $ed = $event->start_date;
                    if ($ed->toDateString() > $today) {
                        $statusLabel = 'Upcoming'; $statusClass = 'ev-badge--up';
                    } elseif ($event->end_date && $event->end_date->toDateString() >= $today) {
                        $statusLabel = 'Ongoing'; $statusClass = 'ev-badge--on';
                    } else {
                        $statusLabel = 'Past'; $statusClass = 'ev-badge--past';
                    }
                    $daysUntilStart = $ed->toDateString() > $today
                        ? (int) now()->startOfDay()->diffInDays($ed->startOfDay())
                        : null;
                    $startsSoon = $daysUntilStart !== null && $daysUntilStart <= 7;
                    $seatsLeft = $event->total_seats
                        ? max(0, $event->total_seats - ($event->registered_count ?? 0))
                        : null;
                    $eventUrl = route('events.show', $event->slug ?? $event->id);
                    $alreadyRegistered = in_array($event->id, $registeredEventIds ?? []);
                    $pct = $event->total_seats
                        ? min(100, round((($event->registered_count ?? 0) / $event->total_seats) * 100))
                        : null;
                @endphp

                <article class="ev-card ev-reveal">
                    {{-- Image --}}
                    <div class="ev-card__img-wrap">
                        <a href="{{ $eventUrl }}">
                            @if($event->banner_image)
                                <img class="ev-card__img" src="{{ asset('storage/' . $event->banner_image) }}" alt="{{ $event->title }}" loading="lazy">
                            @else
                                <div class="ev-card__placeholder">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <span>No image</span>
                                </div>
                            @endif
                        </a>

                        {{-- Date stamp — signature element --}}
                        <div class="ev-card__date-stamp" aria-label="{{ $ed->format('d F Y') }}">
                            <span class="ev-card__date-stamp__month">{{ $ed->format('M') }}</span>
                            <span class="ev-card__date-stamp__day">{{ $ed->format('d') }}</span>
                        </div>

                        <span class="ev-card__status">
                            <span class="ev-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if($startsSoon)
                                <span class="ev-badge ev-badge--urgent">
                                    {{ $daysUntilStart === 0 ? 'Starts today' : 'Starts in ' . $daysUntilStart . ' ' . ($daysUntilStart === 1 ? 'day' : 'days') }}
                                </span>
                            @endif
                        </span>

                        @if($event->event_type === 'Free')
                            <span class="ev-card__free">
                                <span class="ev-badge ev-badge--free">Free</span>
                            </span>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="ev-card__body">
                        <div class="ev-card__eyebrow">
                            @if($event->category)
                                <span class="ev-card__cat">{{ $event->category }}</span>
                                <span class="ev-card__dot"></span>
                            @endif
                            <span class="ev-card__eyebrow-text">{{ date('g:i A', strtotime($event->start_time)) }}</span>
                            <span class="ev-card__dot"></span>
                            <span class="ev-card__eyebrow-text">{{ $event->event_mode }}</span>
                        </div>

                        <h3 class="ev-card__title">
                            <a href="{{ $eventUrl }}">{{ $event->title }}</a>
                        </h3>

                        <div class="ev-card__location">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $event->location ?: 'Online Event' }}
                        </div>

                        @if($event->description)
                            <p class="ev-card__desc">{{ Str::limit(strip_tags($event->description), 110) }}</p>
                        @endif

                        @if($event->total_seats && $pct !== null)
                            <div class="ev-seats">
                                <div class="ev-seats__track">
                                    <div class="ev-seats__fill {{ $pct >= 100 ? 'ev-seats__fill--full' : ($pct >= 80 ? 'ev-seats__fill--near' : '') }}"
                                         style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="ev-seats__text">
                                    @if($seatsLeft === 0)
                                        <span class="ev-tag-full">Fully Booked</span>
                                    @elseif($seatsLeft <= 10)
                                        <span class="ev-tag-near">Only {{ $seatsLeft }} seats left!</span>
                                    @else
                                        {{ $event->registered_count ?? 0 }} / {{ $event->total_seats }} registered
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="ev-card__footer">
                            <div>
                                <span class="ev-card__price">
                                    {{ $event->event_type === 'Free' ? 'Free' : '$'.number_format($event->ticket_price,0) }}
                                </span>
                                <span class="ev-card__price-label">
                                    {{ $event->event_type === 'Free' ? 'No fee' : 'per person' }}
                                </span>
                            </div>
                            <div class="ev-card__actions">
                                @if($alreadyRegistered)
                                    <span class="ev-card__register ev-card__register--done">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        Registered
                                    </span>
                                @elseif($statusLabel !== 'Past' && $seatsLeft !== 0 && ($event->registration_required ?? true))

                                @endif
                                <a href="{{ $eventUrl }}" class="ev-card__details">Details →</a>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- ── PAGINATION ──────────────────────────────────────── --}}
        @if($events->hasPages())
        <nav class="ev-pagination" aria-label="Events pages">
            @if($events->onFirstPage())
                <span class="ev-page-btn ev-page-btn--disabled">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </span>
            @else
                <a class="ev-page-btn" href="{{ $events->previousPageUrl() }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </a>
            @endif

            @foreach($events->getUrlRange(max(1,$events->currentPage()-2), min($events->lastPage(),$events->currentPage()+2)) as $page => $url)
                <a class="ev-page-btn {{ $page == $events->currentPage() ? 'ev-page-btn--active' : '' }}"
                   href="{{ $url }}">{{ $page }}</a>
            @endforeach

            @if($events->hasMorePages())
                <a class="ev-page-btn" href="{{ $events->nextPageUrl() }}">
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @else
                <span class="ev-page-btn ev-page-btn--disabled">
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </span>
            @endif
        </nav>
        @endif

        @else
        {{-- Empty state --}}
        <div class="ev-empty">
            <div class="ev-empty__icon">
                <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <p class="ev-empty__title">No events found</p>
            <p class="ev-empty__sub">
                @if(request('search'))
                    No results for "<strong>{{ request('search') }}</strong>". Try a different search term.
                @else
                    Nothing in this category yet. Check back soon for upcoming events.
                @endif
            </p>
            @if(request()->hasAny(['search','filter']))
                <a href="{{ route('events.index') }}" class="ev-empty__cta">
                    Clear filters
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6" transform="rotate(180 12 12)"/></svg>
                </a>
            @endif
        </div>
        @endif

    </div>{{-- /ev-body --}}
</div>{{-- /ev-root --}}

@include('events._registration_modal')

@endsection

@push('scripts')
<script>
    window.EVENTS_AUTH = {
        loggedIn:    @json((bool) session('alumni_id')),
        loginUrl:    '{{ route('login') }}',
        alumniName:  @json(session('alumni_name') ?? ''),
        alumniEmail: @json(session('alumni_email') ?? ''),
    };
</script>
<script src="{{ asset('js/events-registration.js') }}"></script>
<script>
(function () {
    // Batch reveal — one observer, fire-and-forget
    const els = document.querySelectorAll('.ev-reveal');
    if (els.length) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('ev-reveal--show');
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.04, rootMargin: '0px 0px -40px 0px' });
        els.forEach(el => obs.observe(el));
    }

    // Pause masthead pulse animation when scrolled out of view
    const dot = document.querySelector('.ev-masthead__dot');
    if (dot) {
        const dotObs = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                dot.style.animationPlayState = e.isIntersecting ? 'running' : 'paused';
            });
        }, { threshold: 0 });
        dotObs.observe(dot);
    }
})();
</script>
@endpush