@extends('layouts.app')

@section('title', $event->title . ' — ICCR Alumni Events')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/events-redesign.css') }}">
@endpush

@section('content')

@php
    $today = now()->toDateString();

    if ($event->start_date->toDateString() > $today) {
        $statusLabel = 'Upcoming'; $statusClass = 'ev-badge--up';
    } elseif ($event->end_date && $event->end_date->toDateString() >= $today) {
        $statusLabel = 'Ongoing'; $statusClass = 'ev-badge--on';
    } else {
        $statusLabel = 'Past'; $statusClass = 'ev-badge--past';
    }

    $seatsLeft = $event->total_seats
        ? max(0, $event->total_seats - ($event->registered_count ?? 0))
        : null;

    $pct = $event->total_seats
        ? min(100, round((($event->registered_count ?? 0) / $event->total_seats) * 100))
        : null;

    $organizer = $event->creator;
    $organizerName     = $organizer->full_name ?? 'ICCR Alumni Community';
    $organizerInitials = $organizer->initials ?? 'IC';
@endphp

<div class="ev-detail-page">

    {{-- Reading progress --}}
    <div class="ev-progress" id="evProgress" role="progressbar" aria-label="Reading progress"></div>

    {{-- Back link --}}
    <div class="ev-detail-hero-wrap">
        <a href="{{ route('events.index') }}" class="ev-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            All Events
        </a>
    </div>

    {{-- ── HERO ─────────────────────────────────────────────────── --}}
    <div class="ev-detail-hero-wrap">
        <div class="ev-detail-hero ev-reveal">
            @if($event->banner_image)
                <img src="{{ asset('storage/' . $event->banner_image) }}" alt="{{ $event->title }}">
            @else
                <div class="ev-detail-hero__placeholder">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            @endif
            <div class="ev-detail-hero__scrim"></div>

            <div class="ev-detail-hero__badges">
                <span class="ev-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                @if($event->event_type === 'Free')
                    <span class="ev-badge ev-badge--free">Free</span>
                @endif
            </div>

            {{-- Signature date block --}}
            <div class="ev-detail-hero__date" aria-label="{{ $event->start_date->format('d F Y') }}">
                <div class="ev-detail-hero__date-month">{{ $event->start_date->format('F') }}</div>
                <div class="ev-detail-hero__date-day">{{ $event->start_date->format('d') }}</div>
                <div class="ev-detail-hero__date-year">{{ $event->start_date->format('Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ── LAYOUT ───────────────────────────────────────────────── --}}
    <div class="ev-detail-layout">

        {{-- Main --}}
        <div class="ev-detail-main ev-reveal">

            @if($event->category)
                <div class="ev-detail-category">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    {{ $event->category }}
                </div>
            @endif

            <h1 class="ev-detail-title">{{ $event->title }}</h1>

            <div class="ev-detail-organizer">
                <span class="ev-detail-organizer__avatar">{{ $organizerInitials }}</span>
                <div>
                    <span class="ev-detail-organizer__label">Organised by</span>
                    <span class="ev-detail-organizer__name">{{ $organizerName }}</span>
                </div>
            </div>

            @if($event->description)
                <div class="ev-detail-description">
                    {!! $event->description !!}
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="ev-detail-sidebar ev-reveal ev-reveal--delay-1">
            <div class="ev-ticket-card">

                {{-- Ticket header --}}
                <div class="ev-ticket-card__header">
                    <div class="ev-ticket-card__price">
                        @if($event->event_type === 'Free')
                            Free
                        @else
                            ₹{{ number_format($event->ticket_price, 0) }}
                        @endif
                    </div>
                    <div class="ev-ticket-card__price-label">
                        {{ $event->event_type === 'Free' ? 'No registration fee' : 'per person' }}
                    </div>
                </div>

                {{-- Perforated tear --}}
                <div class="ev-ticket-tear">
                    <span class="ev-ticket-tear__line"></span>
                </div>

                {{-- Ticket body --}}
                <div class="ev-ticket-card__body">
                    <ul class="ev-ticket-facts">
                        <li>
                            <span class="ev-ticket-fact-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </span>
                            <span class="ev-ticket-fact-text">
                                {{ $event->start_date->format('D, d M Y') }}
                                @if($event->end_date && $event->end_date->ne($event->start_date))
                                    &mdash; {{ $event->end_date->format('d M Y') }}
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="ev-ticket-fact-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </span>
                            <span class="ev-ticket-fact-text">
                                {{ date('g:i A', strtotime($event->start_time)) }}
                                @if($event->end_time)
                                    &mdash; {{ date('g:i A', strtotime($event->end_time)) }}
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="ev-ticket-fact-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <span class="ev-ticket-fact-text">
                                {{ $event->location ?: 'Online Event' }}
                                <span class="ev-ticket-fact-sub">{{ $event->event_mode }}</span>
                            </span>
                        </li>
                    </ul>

                    @if($event->total_seats && $pct !== null)
                        <div class="ev-ticket-divider"></div>
                        <div class="ev-seats" style="margin:0 0 0;">
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

                    <div class="ev-ticket-divider"></div>

                    <div class="ev-detail-register">
                        @if($alreadyRegistered)
                            <span class="ev-ticket-register ev-ticket-register--success">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Already Registered
                            </span>
                        @elseif($statusLabel === 'Past')
                            <span class="ev-ticket-register ev-ticket-register--closed">
                                Registration Closed
                            </span>
                        @elseif($seatsLeft === 0)
                            <span class="ev-ticket-register ev-ticket-register--closed">
                                Fully Booked
                            </span>
                        @elseif(!$event->registration_required)
                            <span class="ev-ticket-register ev-ticket-register--closed">
                                No Registration Required
                            </span>
                        @else
                            <a href="#"
                               class="ev-ticket-register"
                               data-event-id="{{ $event->id }}"
                               data-event-title="{{ $event->title }}"
                               data-register="true">
                                Register Now
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        @endif
                    </div>

                    @if($event->registration_deadline)
                        <p class="ev-ticket-deadline">
                            Registration closes {{ $event->registration_deadline->format('d M Y') }}
                        </p>
                    @endif
                </div>
            </div>
        </aside>

    </div>{{-- /ev-detail-layout --}}

    {{-- ── MORE EVENTS ─────────────────────────────────────────── --}}
    @if($relatedEvents->isNotEmpty())
    <section class="ev-more">
        <div class="ev-more__inner">
            <div class="ev-more__header">
                <h2 class="ev-more__title">More Events</h2>
                <a href="{{ route('events.index') }}" class="ev-more__link">
                    View all
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                </a>
            </div>

            <div class="ev-grid">
                @foreach($relatedEvents as $rel)
                @php
                    $rd = $rel->start_date;
                    $relUrl = route('events.show', $rel->slug ?? $rel->id);
                @endphp
                <article class="ev-card ev-reveal">
                    <div class="ev-card__img-wrap">
                        <a href="{{ $relUrl }}">
                            @if($rel->banner_image)
                                <img class="ev-card__img" src="{{ asset('storage/' . $rel->banner_image) }}" alt="{{ $rel->title }}" loading="lazy">
                            @else
                                <div class="ev-card__placeholder">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>
                            @endif
                        </a>
                        <div class="ev-card__date-stamp">
                            <span class="ev-card__date-stamp__month">{{ $rd->format('M') }}</span>
                            <span class="ev-card__date-stamp__day">{{ $rd->format('d') }}</span>
                        </div>
                        @if($rel->event_type === 'Free')
                            <span class="ev-card__free"><span class="ev-badge ev-badge--free">Free</span></span>
                        @endif
                    </div>
                    <div class="ev-card__body">
                        <div class="ev-card__eyebrow">
                            @if($rel->category)
                                <span class="ev-card__cat">{{ $rel->category }}</span>
                                <span class="ev-card__dot"></span>
                            @endif
                            <span class="ev-card__eyebrow-text">{{ $rel->event_mode }}</span>
                        </div>
                        <h3 class="ev-card__title">
                            <a href="{{ $relUrl }}">{{ $rel->title }}</a>
                        </h3>
                        <div class="ev-card__location">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $rel->location ?: 'Online Event' }}
                        </div>
                        <div class="ev-card__footer" style="margin-top:auto;padding-top:16px;border-top:1px solid var(--ev-border-lt);">
                            <span class="ev-card__price">
                                {{ $rel->event_type === 'Free' ? 'Free' : '₹'.number_format($rel->ticket_price,0) }}
                                <span class="ev-card__price-label">{{ $rel->event_type === 'Free' ? 'No fee' : 'per person' }}</span>
                            </span>
                            <a href="{{ $relUrl }}" class="ev-card__details">View →</a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

</div>{{-- /ev-detail-page --}}

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
    // Reading progress
    const bar = document.getElementById('evProgress');
    if (bar) {
        window.addEventListener('scroll', function () {
            const el = document.documentElement;
            bar.style.width = Math.min(100, el.scrollTop / (el.scrollHeight - el.clientHeight) * 100) + '%';
        }, { passive: true });
    }
    // Scroll reveal
    const els = document.querySelectorAll('.ev-reveal');
    if (!els.length) return;
    const obs = new IntersectionObserver((entries) => {
        entries.forEach((e, i) => {
            if (e.isIntersecting) {
                setTimeout(() => e.target.classList.add('ev-reveal--show'), i * 90);
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.06 });
    els.forEach(el => obs.observe(el));
})();
</script>
@endpush