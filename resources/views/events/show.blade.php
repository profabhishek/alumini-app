@extends('layouts.app')

@section('title', $event->title . ' — ICCR Alumni Events')

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

    $canRegister = $statusLabel !== 'Past'
        && !$alreadyRegistered
        && $event->registration_required
        && $seatsLeft !== 0;
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

    {{-- ── HERO ──────────────────────────────────────────────────────── --}}
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

            <div class="ev-detail-hero__date" aria-label="{{ $event->start_date->format('d F Y') }}">
                <div class="ev-detail-hero__date-month">{{ $event->start_date->format('F') }}</div>
                <div class="ev-detail-hero__date-day">{{ $event->start_date->format('d') }}</div>
                <div class="ev-detail-hero__date-year">{{ $event->start_date->format('Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ── LAYOUT ────────────────────────────────────────────────────── --}}
    <div class="ev-detail-layout">

        {{-- Main content --}}
        <div class="ev-detail-main ev-reveal">

            @if($event->category)
                <div class="ev-detail-category">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    {{ $event->category }}
                </div>
            @endif

            <h1 class="ev-detail-title">{{ $event->title }}</h1>

            <div class="ev-detail-organizer">
                @if($organizer?->photo)
                    <a href="{{ $organizer->id ? url('/members/' . $organizer->id) : '#' }}"
                    class="ev-detail-organizer__avatar ev-detail-organizer__avatar--photo">
                        <img src="{{ asset('storage/' . $organizer->photo) }}"
                            alt="{{ $organizerName }}">
                    </a>
                @else
                    <span class="ev-detail-organizer__avatar">{{ $organizerInitials }}</span>
                @endif

                <div>
                    <span class="ev-detail-organizer__label">Organised by</span>
                    @if($organizer?->id)
                        <a href="{{ url('/members/' . $organizer->id) }}"
                        class="ev-detail-organizer__name ev-detail-organizer__name--link">
                            {{ $organizerName }}
                        </a>
                    @else
                        <span class="ev-detail-organizer__name">{{ $organizerName }}</span>
                    @endif
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

                <div class="ev-ticket-tear">
                    <span class="ev-ticket-tear__line"></span>
                </div>

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
                            {{-- ✅ THE FIX: data-register triggers openRegModal() in events-registration.js --}}
                            <a href="#"
                               class="ev-ticket-register"
                               data-register="true"
                               data-event-id="{{ $event->id }}"
                               data-event-title="{{ $event->title }}">
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

    {{-- ── MORE EVENTS ──────────────────────────────────────────────── --}}
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

{{-- ══════════════════════════════════════════════════════════════════
     REGISTRATION MODAL
     IDs must match exactly what events-registration.js expects:
     registrationModal, regModalTitle, regForm, regEventId,
     reg_full_name, reg_email, reg_phone, reg_country,
     reg_batch_year, reg_no_of_people, reg_message,
     regFormError, regSubmitBtn, regBtnText, regBtnSpinner,
     regSuccess, regSuccessMsg, regSuccessEmail, regSuccessEmailAddr
══════════════════════════════════════════════════════════════════ --}}
<div id="registrationModal" class="ev-modal-overlay">
    <div class="ev-modal">

        {{-- Header --}}
        <div class="ev-modal__header">
            <div>
                <p class="ev-modal__header-label">Register for</p>
                <h2 class="ev-modal__header-title" id="regModalTitle">Event Name</h2>
            </div>
            <button class="ev-modal__close" id="regModalClose" aria-label="Close">&times;</button>
        </div>

        {{-- Success state --}}
        <div id="regSuccess" class="ev-modal__success" style="display:none">
            <div class="ev-modal__success-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3>You're Registered!</h3>
            <p id="regSuccessMsg">You have successfully registered for this event.</p>
            <div class="ev-modal__success-email" id="regSuccessEmail" style="display:none">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Confirmation sent to <strong id="regSuccessEmailAddr"></strong>
            </div>
            <button class="ev-modal__done" onclick="closeRegModal()">Done</button>
        </div>

        {{-- Form --}}
        <form id="regForm" novalidate>
            @csrf
            <input type="hidden" id="regEventId" name="event_id" value="">

            <div class="ev-modal__body">
                <div class="ev-modal__grid">
                    <div class="ev-field">
                        <label for="reg_full_name">Full Name <span>*</span></label>
                        <input type="text" id="reg_full_name" name="full_name"
                               placeholder="Your full name" required>
                        <span class="ev-field-error" id="err_full_name"></span>
                    </div>

                    <div class="ev-field">
                        <label for="reg_email">Email <span>*</span></label>
                        <input type="email" id="reg_email" name="email"
                               placeholder="your@email.com" required>
                        <span class="ev-field-error" id="err_email"></span>
                    </div>

                    <div class="ev-field">
                        <label for="reg_phone">Phone Number</label>
                        <input type="text" id="reg_phone" name="phone" placeholder="+91 98765 43210">
                    </div>

                    <div class="ev-field">
                        <label for="reg_country">Country</label>
                        <input type="text" id="reg_country" name="country" placeholder="India">
                    </div>

                    <div class="ev-field">
                        <label for="reg_batch_year">Alumni Batch / Year</label>
                        <input type="text" id="reg_batch_year" name="batch_year" placeholder="e.g. 2018–2020">
                    </div>

                    <div class="ev-field">
                        <label for="reg_no_of_people">No. of People <span>*</span></label>
                        <input type="number" id="reg_no_of_people" name="no_of_people"
                               value="1" min="1" max="20" required>
                        <span class="ev-field-error" id="err_no_of_people"></span>
                    </div>
                </div>

                <div class="ev-field ev-field-full">
                    <label for="reg_message">Message / Special Requirements</label>
                    <textarea id="reg_message" name="message"
                              placeholder="Any special requirements or message for the organiser…"
                              rows="3"></textarea>
                </div>

                <div id="regFormError" class="ev-form-error" style="display:none"></div>
            </div>

            <div class="ev-modal__footer">
                <button type="button" class="ev-modal__cancel" onclick="closeRegModal()">Cancel</button>
                <button type="submit" class="ev-modal__submit" id="regSubmitBtn">
                    <span id="regBtnText">Confirm Registration</span>
                    <span id="regBtnSpinner" style="display:none">Submitting…</span>
                </button>
            </div>
        </form>

    </div>
</div>

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
    // Reading progress — throttled with rAF
    const bar = document.getElementById('evProgress');
    if (bar) {
        let ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    const el = document.documentElement;
                    const pct = el.scrollTop / (el.scrollHeight - el.clientHeight) * 100;
                    bar.style.width = Math.min(100, pct) + '%';
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    // Reveal — no staggered setTimeout, just immediate show
    const els = document.querySelectorAll('.ev-reveal');
    if (!els.length) return;
    const obs = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('ev-reveal--show');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.04, rootMargin: '0px 0px -40px 0px' });
    els.forEach(el => obs.observe(el));
})();
</script>
@endpush