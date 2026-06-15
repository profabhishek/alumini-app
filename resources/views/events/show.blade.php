@extends('layouts.app')

@section('title', $event->title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/events.css') }}" />
@endpush

@section('content')

@php
    $today = now()->toDateString();

    if ($event->start_date->toDateString() > $today) {
        $statusLabel = 'Upcoming';
        $statusColor = '#3b82f6';
    } elseif ($event->end_date && $event->end_date->toDateString() >= $today) {
        $statusLabel = 'Ongoing';
        $statusColor = '#22c55e';
    } else {
        $statusLabel = 'Past';
        $statusColor = '#6b7280';
    }

    $seatsLeft = $event->total_seats
        ? max(0, $event->total_seats - ($event->registered_count ?? 0))
        : null;

    $pct = $event->total_seats
        ? min(100, round((($event->registered_count ?? 0) / $event->total_seats) * 100))
        : null;

    $organizer = $event->creator;
    $organizerName = $organizer->full_name ?? 'ICCR Alumni Community';
    $organizerInitials = $organizer->initials ?? 'IC';
@endphp

<section class="events-page event-detail-page">

    <div class="container">
        <a href="{{ route('events.index') }}" class="event-detail-back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            All Events
        </a>
    </div>

    {{-- HERO --}}
    <div class="container">
        <div class="event-detail-hero reveal">
            @if($event->banner_image)
                <img src="{{ asset('storage/' . $event->banner_image) }}" alt="{{ $event->title }}">
            @else
                <div class="event-detail-hero-placeholder">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            @endif

            <div class="event-detail-hero-overlay"></div>

            <div class="event-detail-hero-badges">
                <span class="event-status" style="background: {{ $statusColor }};">
                    {{ $statusLabel }}
                </span>
                @if($event->event_type === 'Free')
                    <span class="event-free-tag">Free</span>
                @endif
            </div>
        </div>
    </div>

    {{-- DETAILS --}}
    <section class="event-detail-body-section">
        <div class="container">
            <div class="event-detail-layout">

                {{-- MAIN CONTENT --}}
                <div class="event-detail-main reveal">

                    @if($event->category)
                        <div class="event-detail-category">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            {{ $event->category }}
                        </div>
                    @endif

                    <h1 class="event-detail-title">{{ $event->title }}</h1>

                    <div class="event-detail-organizer">
                        <span class="event-detail-organizer-avatar">{{ $organizerInitials }}</span>
                        <div>
                            <span class="event-detail-organizer-label">Organized by</span>
                            <span class="event-detail-organizer-name">{{ $organizerName }}</span>
                        </div>
                    </div>

                    @if($event->description)
                        <div class="event-detail-description">
                            {!! $event->description !!}
                        </div>
                    @endif
                </div>

                {{-- SIDEBAR / TICKET CARD --}}
                <aside class="event-detail-sidebar reveal reveal-delay-1">
                    <div class="event-detail-card">

                        <div class="event-detail-price-row">
                            @if($event->event_type === 'Free')
                                <span class="event-detail-price-value">Free</span>
                            @else
                                <span class="event-detail-price-value">₹{{ number_format($event->ticket_price, 0) }}</span>
                                <span class="event-detail-price-label">per person</span>
                            @endif
                        </div>

                        <ul class="event-detail-facts">
                            <li>
                                <span class="event-detail-fact-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </span>
                                <span class="event-detail-fact-text">
                                    {{ $event->start_date->format('D, d M Y') }}
                                    @if($event->end_date && $event->end_date->ne($event->start_date))
                                        – {{ $event->end_date->format('d M Y') }}
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="event-detail-fact-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </span>
                                <span class="event-detail-fact-text">
                                    {{ date('g:i A', strtotime($event->start_time)) }}
                                    @if($event->end_time)
                                        – {{ date('g:i A', strtotime($event->end_time)) }}
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="event-detail-fact-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </span>
                                <span class="event-detail-fact-text">
                                    {{ $event->location ?: 'Online Event' }}
                                    <span class="event-detail-fact-sub">{{ $event->event_mode }}</span>
                                </span>
                            </li>
                        </ul>

                        @if($event->total_seats)
                            <div class="event-detail-divider"></div>
                            <div class="event-seats">
                                <div class="event-seats-track">
                                    <div class="event-seats-fill {{ $pct >= 100 ? 'full' : ($pct >= 80 ? 'near' : '') }}"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="event-seats-text">
                                    @if($seatsLeft === 0)
                                        <span class="tag-full">Fully Booked</span>
                                    @elseif($seatsLeft <= 10)
                                        <span class="tag-near">Only {{ $seatsLeft }} seats left!</span>
                                    @else
                                        {{ $event->registered_count ?? 0 }} / {{ $event->total_seats }} registered
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="event-detail-register">
                            @if($alreadyRegistered)
                                <span class="event-detail-register-btn event-detail-register-btn--success">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Already Registered
                                </span>
                            @elseif($statusLabel === 'Past')
                                <span class="event-detail-register-btn event-detail-register-btn--closed">
                                    Registration Closed
                                </span>
                            @elseif($seatsLeft === 0)
                                <span class="event-detail-register-btn event-detail-register-btn--closed">
                                    Fully Booked
                                </span>
                            @elseif(!$event->registration_required)
                                <span class="event-detail-register-btn event-detail-register-btn--closed">
                                    Registration Not Required
                                </span>
                            @else
                                <a href="#"
                                   class="event-detail-register-btn"
                                   data-event-id="{{ $event->id }}"
                                   data-event-title="{{ $event->title }}"
                                   data-register="true">
                                    Register Now
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </a>
                            @endif
                        </div>

                        @if($event->registration_deadline)
                            <p class="event-detail-deadline">
                                Registration closes {{ $event->registration_deadline->format('d M Y') }}
                            </p>
                        @endif
                    </div>
                </aside>

            </div>
        </div>
    </section>

    {{-- MORE EVENTS --}}
    @if($relatedEvents->isNotEmpty())
    <section class="events-section related-events-section">
        <div class="container">
            <div class="related-events-header">
                <h2 class="related-events-heading">More Events</h2>
                <a href="{{ route('events.index') }}" class="event-details-link">
                    View All →
                </a>
            </div>

            <div class="events-grid">
                @foreach($relatedEvents as $rel)
                    <article class="event-card reveal">
                        <a href="{{ route('events.show', $rel->slug) }}" class="event-image-link">
                            <div class="event-image-wrap">
                                @if($rel->banner_image)
                                    <img src="{{ asset('storage/' . $rel->banner_image) }}" alt="{{ $rel->title }}" class="event-image" loading="lazy">
                                @else
                                    <div class="event-no-image">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span>No Image</span>
                                    </div>
                                @endif

                                @if($rel->event_type === 'Free')
                                    <span class="event-free-tag">Free</span>
                                @endif
                            </div>
                        </a>

                        <div class="event-content">
                            <div class="event-date">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px; margin-right:4px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $rel->start_date->format('d M Y') }}
                            </div>
                            <a href="{{ route('events.show', $rel->slug) }}" class="event-title-link">
                                <h3 class="event-title">{{ $rel->title }}</h3>
                            </a>
                            <p class="event-location">
                                {{ $rel->location ?: 'Online Event' }}
                            </p>
                            <a href="{{ route('events.show', $rel->slug) }}" class="event-btn">
                                View Event
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

</section>

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
@endpush