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
                                $statusColors = [
                                    'published' => '#22c55e',
                                    'pending'   => '#f59e0b',
                                    'active'    => '#22c55e',
                                    'completed' => '#6b7280',
                                    'upcoming'  => '#3b82f6',
                                ];
                                $statusColor = $statusColors[strtolower($event->status)] ?? '#22c55e';
                                $seatsLeft   = $event->total_seats
                                    ? max(0, $event->total_seats - ($event->registered_count ?? 0))
                                    : null;
                            @endphp

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
                                @endphp

                            <article class="event-card reveal">

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

                                <div class="event-content">

                                    <div class="event-date">
                                        📅 {{ $event->start_date->format('d M Y') }}
                                        @if($event->end_date && $event->end_date->ne($event->start_date))
                                            – {{ $event->end_date->format('d M Y') }}
                                        @endif
                                        &nbsp;&nbsp;🕒 {{ date('g:i A', strtotime($event->start_time)) }}
                                    </div>

                                    <h3 class="event-title">{{ $event->title }}</h3>

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

                                    @php $alreadyRegistered = in_array($event->id, $registeredEventIds ?? []); @endphp

                                    @if($alreadyRegistered)
                                        <span class="event-btn event-btn--registered">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Already Registered
                                        </span>
                                    @else
                                        <a href="{{ route('events.show', $event->slug ?? $event->id) }}"
                                        class="event-btn"
                                        data-event-id="{{ $event->id }}"
                                        data-event-title="{{ $event->title }}"
                                        data-register="true">
                                            Register Now
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                        </a>
                                    @endif

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
(function () {

    // ── Scroll reveal ──────────────────────────────────────────
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('show'), i * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.07 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));


    // ── Modal elements ─────────────────────────────────────────
    const overlay    = document.getElementById('registrationModal');
    const closeBtn   = document.getElementById('regModalClose');
    const form       = document.getElementById('regForm');
    const titleEl    = document.getElementById('regModalTitle');
    const eventIdEl  = document.getElementById('regEventId');
    const successBox = document.getElementById('regSuccess');
    const successMsg = document.getElementById('regSuccessMsg');
    const formError  = document.getElementById('regFormError');
    const submitBtn  = document.getElementById('regSubmitBtn');
    const btnText    = document.getElementById('regBtnText');
    const btnSpinner = document.getElementById('regBtnSpinner');


document.querySelectorAll('[data-register="true"]').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();

        @if(!session('alumni_id'))
            window.location.href = '{{ route('login') }}';
            return;
        @endif

        const eventId    = this.dataset.eventId;
        const eventTitle = this.dataset.eventTitle;

        titleEl.textContent = eventTitle;
        eventIdEl.value     = eventId;

        form.reset();
        form.querySelector('#reg_full_name').value    = '{{ session('alumni_name') ?? '' }}';
        form.querySelector('#reg_email').value        = '{{ session('alumni_email') ?? '' }}';
        form.querySelector('#reg_no_of_people').value = 1;

        clearErrors();
        formError.style.display  = 'none';
        successBox.style.display = 'none';
        form.style.display       = 'block';

        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    });
});


    // ── Close modal ────────────────────────────────────────────
    window.closeRegModal = function () {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    };

    closeBtn.addEventListener('click', closeRegModal);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeRegModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeRegModal();
    });


    // ── Validation ─────────────────────────────────────────────
    function clearErrors() {
        document.querySelectorAll('.reg-error').forEach(el => el.textContent = '');
        document.querySelectorAll('.reg-field input, .reg-field textarea')
                .forEach(el => el.classList.remove('invalid'));
    }

    function showError(fieldId, msg) {
        const errEl = document.getElementById('err_' + fieldId);
        const input = document.getElementById('reg_' + fieldId);
        if (errEl) errEl.textContent = msg;
        if (input) input.classList.add('invalid');
    }

    function validateForm() {
        clearErrors();
        let valid = true;

        const name = form.querySelector('#reg_full_name').value.trim();
        if (!name) { showError('full_name', 'Full name is required.'); valid = false; }

        const email = form.querySelector('#reg_email').value.trim();
        if (!email) { showError('email', 'Email is required.'); valid = false; }
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('email', 'Enter a valid email address.'); valid = false;
        }

        const people = parseInt(form.querySelector('#reg_no_of_people').value);
        if (!people || people < 1) {
            showError('no_of_people', 'At least 1 person required.'); valid = false;
        } else if (people > 20) {
            showError('no_of_people', 'Maximum 20 people allowed.'); valid = false;
        }

        return valid;
    }


    // ── Submit ─────────────────────────────────────────────────
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!validateForm()) return;

        // Loading state
        submitBtn.disabled  = true;
        btnText.style.display    = 'none';
        btnSpinner.style.display = 'inline';
        formError.style.display  = 'none';

        const eventId  = eventIdEl.value;
        const formData = new FormData(form);

        try {
            const response = await fetch(`/events/${eventId}/register`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                // Show success
                form.style.display       = 'none';
                successMsg.textContent   = data.message;
                successBox.style.display = 'block';

                // Update seat count on the card live
                updateSeatCount(eventId, data.new_count);

                const link = document.querySelector(`[data-event-id="${eventId}"][data-register="true"]`);
                if (link) {
                    link.outerHTML = `
                        <span class="event-btn event-btn--registered">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Already Registered
                        </span>`;
                }

                } else {
                    if (response.status === 409) {
                        // Already registered — close modal, swap button
                        closeRegModal();
                        const link = document.querySelector(`[data-event-id="${eventId}"][data-register="true"]`);
                        if (link) {
                            link.outerHTML = `
                                <span class="event-btn event-btn--registered">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Already Registered
                                </span>`;
                        }
                    } else {
                        formError.textContent   = data.message || 'Something went wrong.';
                        formError.style.display = 'block';
                    }
                }


        } catch (err) {
            formError.textContent   = 'Network error. Please try again.';
            formError.style.display = 'block';
        } finally {
            submitBtn.disabled       = false;
            btnText.style.display    = 'inline';
            btnSpinner.style.display = 'none';
        }
    });


    // ── Live seat count update on card ─────────────────────────
    function updateSeatCount(eventId, newCount) {
        const link = document.querySelector(`[data-event-id="${eventId}"]`);
        if (!link) return;

        const card     = link.closest('.event-card');
        if (!card) return;

        const seatsText = card.querySelector('.event-seats-text');
        const seatsFill = card.querySelector('.event-seats-fill');
        const totalEl   = card.querySelector('.event-seats-track');

        if (!seatsText || !totalEl) return;

        // Read total seats from current text (e.g. "5 / 50 registered")
        const match = seatsText.textContent.match(/\/\s*(\d+)/);
        if (!match) return;

        const total    = parseInt(match[1]);
        const pct      = Math.min(100, Math.round((newCount / total) * 100));
        const seatsLeft = Math.max(0, total - newCount);

        // Update fill bar
        seatsFill.style.width = pct + '%';
        seatsFill.classList.toggle('near', pct >= 80 && pct < 100);
        seatsFill.classList.toggle('full', pct >= 100);

        // Update text
        if (seatsLeft === 0) {
            seatsText.innerHTML = '<span class="tag-full">Fully Booked</span>';
        } else if (seatsLeft <= 10) {
            seatsText.innerHTML = `<span class="tag-near">Only ${seatsLeft} seats left!</span>`;
        } else {
            seatsText.innerHTML = `${newCount} / ${total} registered`;
        }
    }

})();
</script>
@endpush