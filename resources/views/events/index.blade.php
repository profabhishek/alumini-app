@extends('layouts.app')

@section('title', 'Events')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/events.css') }}" />
@endpush

@section('content')
    <section class="events-page">

        {{-- HERO --}}
        <section class="events-hero">
            <div class="container">
                <h1 class="events-title">Discover Events</h1>
                <p class="events-subtitle">Join alumni gatherings, networking sessions & cultural celebrations.</p>
            </div>
        </section>

        {{-- SEARCH --}}
        <section class="events-toolbar">
            <div class="container">
                <form class="events-search-form" method="GET" action="{{ route('events.index') }}">
                    @if(request('filter'))
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                    @endif
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search events..."
                        autocomplete="off"
                    />
                    <button type="submit">Search</button>
                </form>
            </div>
        </section>

        {{-- FILTERS --}}
        <section class="events-filters">
            <div class="container">
                <div class="filter-list">
                <a href="{{ route('events.index', array_filter(['search' => request('search')])) }}"
                class="filter-btn {{ request('filter', '') === '' ? 'active' : '' }}">All</a>

                <a href="{{ route('events.index', array_filter(['filter' => 'upcoming', 'search' => request('search')])) }}"
                class="filter-btn {{ request('filter') === 'upcoming' ? 'active' : '' }}">Upcoming</a>

                <a href="{{ route('events.index', array_filter(['filter' => 'ongoing', 'search' => request('search')])) }}"
                class="filter-btn {{ request('filter') === 'ongoing' ? 'active' : '' }}">Ongoing</a>

                <a href="{{ route('events.index', array_filter(['filter' => 'past', 'search' => request('search')])) }}"
                class="filter-btn {{ request('filter') === 'past' ? 'active' : '' }}">Past</a>
                </div>
            </div>
        </section>

        {{-- EVENTS GRID --}}
        <section class="events-section">
            <div class="container">

                @if($events->isEmpty())
                    <div class="events-empty">
                        <p class="events-empty-icon">📅</p>
                        <h3>No events found</h3>
                        <p>
                            @if(request('search'))
                                No results for "<strong>{{ request('search') }}</strong>". Try a different search.
                            @else
                                No events in this category yet. Check back soon.
                            @endif
                        </p>
                        @if(request()->hasAny(['search', 'filter']))
                            <a href="{{ route('events.index') }}" class="filter-btn active" style="margin-top:20px;display:inline-block;">
                                Clear filters
                            </a>
                        @endif
                    </div>
                @else
                    <div class="events-grid">
                        @foreach($events as $event)
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

                                $eventUrl = route('events.show', $event->slug ?? $event->id);
                            @endphp

                            <article class="event-card reveal">

                                <a href="{{ $eventUrl }}" class="event-image-link">
                                    <div class="event-image-wrap">
                                        @if($event->banner_image)
                                            <img
                                                src="{{ asset('storage/' . $event->banner_image) }}"
                                                alt="{{ $event->title }}"
                                                class="event-image"
                                                loading="lazy"
                                            />
                                        @else
                                            <div class="event-no-image">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                <span>No Image</span>
                                            </div>
                                        @endif

                                        <span class="event-status" style="background: {{ $statusColor }};">
                                            {{ $statusLabel }}
                                        </span>

                                        @if($event->event_type === 'Free')
                                            <span class="event-free-tag">Free</span>
                                        @endif
                                    </div>
                                </a>

                                <div class="event-content">

                                    <div class="event-date">
                                        📅 {{ $event->start_date->format('d M Y') }}
                                        @if($event->end_date && $event->end_date->ne($event->start_date))
                                            – {{ $event->end_date->format('d M Y') }}
                                        @endif
                                        &nbsp;&nbsp;🕒 {{ date('g:i A', strtotime($event->start_time)) }}
                                    </div>

                                    <a style="text-decoration: none;" href="{{ $eventUrl }}" class="event-title-link">
                                        <h3 class="event-title">{{ $event->title }}</h3>
                                    </a>

                                    <p class="event-location">
                                        📍 {{ $event->location ?: 'Online Event' }}
                                        &nbsp;·&nbsp;
                                        <span style="color:#888;">{{ $event->event_mode }}</span>
                                    </p>

                                    @if($event->description)
                                        <p class="event-desc">
                                            {{ Str::limit(strip_tags($event->description), 110) }}
                                        </p>
                                    @endif

                                    @if($event->total_seats)
                                        @php $pct = min(100, round((($event->registered_count ?? 0) / $event->total_seats) * 100)); @endphp
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

                                    <div class="event-card-actions">
                                        @php $alreadyRegistered = in_array($event->id, $registeredEventIds ?? []); @endphp

                                        @if($alreadyRegistered)
                                            <span class="event-btn event-btn--registered">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                Already Registered
                                            </span>
                                        @else
                                            <a href="#"
                                            class="event-btn"
                                            data-event-id="{{ $event->id }}"
                                            data-event-title="{{ $event->title }}"
                                            data-register="true">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                            </a>
                                        @endif

                                        <a style="text-decoration: none;" href="{{ $eventUrl }}" class="event-details-link">
                                            View Details
                                        </a>
                                    </div>

                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

            </div>
        </section>

        {{-- PAGINATION --}}
        @if($events->hasPages())
            <section class="events-pagination">
                <div class="container">
                    <div class="pagination-demo">

                        @if($events->onFirstPage())
                            <button disabled style="opacity:.4;cursor:not-allowed;">Previous</button>
                        @else
                            <a href="{{ $events->previousPageUrl() }}" class="pag-link">Previous</a>
                        @endif

                        @foreach($events->getUrlRange(1, $events->lastPage()) as $page => $url)
                            @if($page == $events->currentPage())
                                <button class="active">{{ $page }}</button>
                            @else
                                <a href="{{ $url }}" class="pag-link">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($events->hasMorePages())
                            <a href="{{ $events->nextPageUrl() }}" class="pag-link">Next</a>
                        @else
                            <button disabled style="opacity:.4;cursor:not-allowed;">Next</button>
                        @endif

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