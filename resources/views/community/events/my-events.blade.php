@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'My Events')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/events/my-events.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="me-page-header">
    <div class="me-page-header__left">
        <h1 class="me-page-title">My Events</h1>
        <span class="me-page-count">{{ $events->total() }} event{{ $events->total() !== 1 ? 's' : '' }}</span>
    </div>
    <a href="{{ route('events.create') }}" class="me-btn me-btn--primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create Event
    </a>
</div>

{{-- Events Grid --}}
<div class="me-grid">

    @forelse($events as $event)

        @php
            $eventData = e(json_encode([
                'id'                    => $event->id,
                'title'                 => $event->title,
                'status'                => $event->status,
                'start_date'            => $event->start_date->format('Y-m-d'),
                'start_date_display'    => $event->start_date->format('d M Y'),
                'end_date'              => optional($event->end_date)->format('Y-m-d'),
                'end_date_display'      => optional($event->end_date)->format('d M Y'),
                'start_time'            => $event->start_time,
                'start_time_display'    => date('g:i A', strtotime($event->start_time)),
                'end_time'              => $event->end_time,
                'end_time_display'      => $event->end_time ? date('g:i A', strtotime($event->end_time)) : null,
                'location'              => $event->location,
                'event_mode'            => $event->event_mode,
                'category'              => $event->category,
                'description'           => $event->description,
                'event_type'            => $event->event_type,
                'ticket_price'          => $event->ticket_price,
                'total_seats'           => $event->total_seats,
                'registration_deadline' => optional($event->registration_deadline)->format('Y-m-d'),
                'registration_required' => $event->registration_required,
                'registered'            => $event->registered_count ?? 0,
                'banner_image'          => $event->banner_image ? asset('storage/' . $event->banner_image) : null,
                'organiser'             => $event->organiser ?? null,
                'contact_email'         => $event->contact_email ?? null,
                'registration_link'     => $event->registration_link ?? null,
                'update_url'            => route('events.update', $event->id),
                'delete_url'            => route('events.destroy', $event->id),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        @endphp

        <article class="event-card" data-event-id="{{ $event->id }}">

            {{-- Banner --}}
            <div class="event-card__banner">
                @if($event->banner_image)
                    <img src="{{ asset('storage/' . $event->banner_image) }}" alt="{{ $event->title }}" loading="lazy">
                @else
                    <div class="event-card__banner-placeholder">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>No Banner</span>
                    </div>
                @endif
                <span class="event-card__status event-card__status--{{ strtolower($event->status) }}">
                    {{ ucfirst($event->status) }}
                </span>
                @if(($newRegCounts[$event->id] ?? 0) > 0)
                    <span class="event-new-reg-badge event-card__new-badge" data-for-event="{{ $event->id }}">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                        {{ ($newRegCounts[$event->id] ?? 0) > 9 ? '9+' : $newRegCounts[$event->id] }} new
                    </span>
                @endif
            </div>

            {{-- Body --}}
            <div class="event-card__body">
                <h2 class="event-card__title" title="{{ $event->title }}">
                    {{ $event->title }}
                </h2>

                <ul class="event-card__meta" aria-label="Event details">
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <time datetime="{{ $event->start_date->format('Y-m-d') }}">{{ $event->start_date->format('d M Y') }}</time>
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ date('g:i A', strtotime($event->start_time)) }}
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $event->location ?: 'Online Event' }}
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        {{ $event->event_mode }}
                    </li>
                </ul>

                @if($event->total_seats)
                    @php
                        $registered = $event->registered_count ?? 0;
                        $pct = min(100, round(($registered / $event->total_seats) * 100));
                        $nearFull = $pct >= 80;
                        $full = $pct >= 100;
                    @endphp
                    <div class="event-card__seats">
                        <div class="event-card__seats-label">
                            <span>Registrations</span>
                            <span class="{{ $full ? 'event-card__seats-count--full' : ($nearFull ? 'event-card__seats-count--near' : '') }}">
                                {{ $registered }} / {{ $event->total_seats }}
                            </span>
                        </div>
                        <div class="event-card__progress" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Registration capacity">
                            <div class="event-card__progress-bar {{ $full ? 'event-card__progress-bar--full' : ($nearFull ? 'event-card__progress-bar--near' : '') }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="event-card__seats">
                        <div class="event-card__seats-label">
                            <span>Registrations</span>
                            <span>{{ $event->registered_count ?? 0 }} registered</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Actions --}}
            <div class="event-card__footer">
                <button type="button" class="me-btn me-btn--ghost" 
                    data-action="registrations"
                    data-event-id="{{ $event->id }}"
                    data-event-title="{{ $event->title }}"
                    aria-label="View registrations for {{ $event->title }}"
                    style="position:relative;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Registrations
                    @if(($newRegCounts[$event->id] ?? 0) > 0)
                        <span class="event-new-reg-badge" data-for-event="{{ $event->id }}" style="
                            position:absolute;
                            top:-7px;
                            right:-7px;
                            background:#e8640c;
                            color:#fff;
                            font-size:10px;
                            font-weight:800;
                            min-width:18px;
                            height:18px;
                            border-radius:999px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            padding:0 4px;
                            border:2px solid #fff;
                            box-shadow:0 2px 6px rgba(232,100,12,0.5);
                            line-height:1;
                        ">{{ ($newRegCounts[$event->id] ?? 0) > 9 ? '9+' : $newRegCounts[$event->id] }}</span>
                    @endif
                </button>
                <button type="button" class="me-btn me-btn--ghost" data-action="view" data-event="{!! $eventData !!}" aria-label="View {{ $event->title }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>

                <button type="button" class="me-btn me-btn--ghost" data-action="edit" data-event="{!! $eventData !!}" aria-label="Edit {{ $event->title }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>

                <button type="button" class="me-btn me-btn--ghost me-btn--danger" data-action="delete" data-event-id="{{ $event->id }}" data-event-title="{{ $event->title }}" data-delete-url="{{ route('events.destroy', $event->id) }}" aria-label="Delete {{ $event->title }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>

        </article>

    @empty

        <div class="me-empty">
            <div class="me-empty__icon" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="12" y2="18"/></svg>
            </div>
            <h3 class="me-empty__title">No events yet</h3>
            <p class="me-empty__text">Create your first event and start engaging with the ICCR community.</p>
            <a href="{{ route('events.create') }}" class="me-btn me-btn--primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Event
            </a>
        </div>

    @endforelse

</div>

{{-- Pagination --}}
@if($events->hasPages())
    <div class="me-pagination">{{ $events->links() }}</div>
@endif


{{-- =============================================
     TOAST CONTAINER
     ============================================= --}}
<div id="meToastContainer" class="me-toast-container" aria-live="polite" aria-atomic="true"></div>


{{-- =============================================
     VIEW MODAL
     ============================================= --}}
<div id="eventViewModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalEventTitle" hidden>
    <div class="me-modal" role="document">

        <div class="me-modal__header">
            <div class="me-modal__header-left">
                <span id="modalStatusBadge" class="event-card__status"></span>
                <h2 id="modalEventTitle" class="me-modal__title"></h2>
            </div>
            <button type="button" class="me-modal__close" id="closeViewModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__banner" id="modalBannerWrap">
            <img id="modalBannerImg" src="" alt="" hidden>
            <div id="modalBannerPlaceholder" class="event-card__banner-placeholder me-modal__banner-placeholder" hidden>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>No Banner Image</span>
            </div>
        </div>

        <div class="me-modal__body">
            <div class="me-modal__info-grid">
                <div class="me-modal__info-item">
                    <span class="me-modal__info-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Date
                    </span>
                    <span class="me-modal__info-value" id="modalDate"></span>
                </div>
                <div class="me-modal__info-item">
                    <span class="me-modal__info-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Time
                    </span>
                    <span class="me-modal__info-value" id="modalTime"></span>
                </div>
                <div class="me-modal__info-item">
                    <span class="me-modal__info-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Location
                    </span>
                    <span class="me-modal__info-value" id="modalLocation"></span>
                </div>
                <div class="me-modal__info-item">
                    <span class="me-modal__info-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Mode
                    </span>
                    <span class="me-modal__info-value" id="modalMode"></span>
                </div>
                <div class="me-modal__info-item" id="modalOrgWrap">
                    <span class="me-modal__info-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Organiser
                    </span>
                    <span class="me-modal__info-value" id="modalOrg"></span>
                </div>
                <div class="me-modal__info-item" id="modalEmailWrap">
                    <span class="me-modal__info-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Contact
                    </span>
                    <a class="me-modal__info-value me-modal__info-link" id="modalEmail" href=""></a>
                </div>
            </div>

            <div class="me-modal__section">
                <h3 class="me-modal__section-title">Registrations</h3>
                <div class="me-modal__reg" id="modalRegWrap">
                    <div class="me-modal__reg-stats">
                        <div class="me-modal__reg-stat">
                            <span class="me-modal__reg-number" id="modalRegCount">—</span>
                            <span class="me-modal__reg-label">Registered</span>
                        </div>
                        <div class="me-modal__reg-stat" id="modalSeatsStatWrap">
                            <span class="me-modal__reg-number" id="modalSeatsCount">—</span>
                            <span class="me-modal__reg-label">Total Seats</span>
                        </div>
                        <div class="me-modal__reg-stat" id="modalAvailWrap">
                            <span class="me-modal__reg-number" id="modalAvailCount">—</span>
                            <span class="me-modal__reg-label">Available</span>
                        </div>
                    </div>
                    <div id="modalProgressWrap">
                        <div class="event-card__progress" style="height:8px" role="progressbar" aria-label="Registration capacity" id="modalProgressBar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="event-card__progress-bar" id="modalProgressFill" style="width:0%"></div>
                        </div>
                        <p class="me-modal__reg-pct" id="modalPct"></p>
                    </div>
                </div>
            </div>

            <div class="me-modal__section" id="modalDescWrap">
                <h3 class="me-modal__section-title">Description</h3>
                <div class="me-modal__desc" id="modalDesc"></div>
            </div>

            <div id="modalRegLinkWrap" hidden>
                <a id="modalRegLink" href="#" target="_blank" rel="noopener noreferrer" class="me-btn me-btn--primary" style="width:100%; justify-content:center;">
                    Register for this Event
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
            </div>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="closeViewModalFooter">Close</button>
            <button type="button" class="me-btn me-btn--primary" id="viewToEditBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Event
            </button>
        </div>

    </div>
</div>


{{-- =============================================
     EDIT MODAL
     ============================================= --}}
<div id="eventEditModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="editModalTitle" hidden>
    <div class="me-modal me-modal--lg" role="document">

        <div class="me-modal__header">
            <h2 id="editModalTitle" class="me-modal__title">Edit Event</h2>
            <button type="button" class="me-modal__close" id="closeEditModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__body">
            <div id="editErrorBanner" class="me-alert me-alert--danger" hidden></div>

            <form id="editEventForm" novalidate>
                @csrf
                <input type="hidden" id="editEventId" name="_event_id">
                <input type="hidden" id="editUpdateUrl" name="_update_url">

                {{-- Row 1: Title --}}
                <div class="me-form-group">
                    <label class="me-label" for="editTitle">Event Title <span class="me-required">*</span></label>
                    <input type="text" id="editTitle" name="title" class="me-input" placeholder="Enter event title" required maxlength="255">
                    <span class="me-field-error" id="editTitleError"></span>
                </div>

                {{-- Row 2: Category + Mode --}}
                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="editCategory">Category <span class="me-required">*</span></label>
                        <select id="editCategory" name="category" class="me-input me-select" required>
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                        <span class="me-field-error" id="editCategoryError"></span>
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="editEventMode">Event Mode <span class="me-required">*</span></label>
                        <select id="editEventMode" name="event_mode" class="me-input me-select" required>
                            <option value="">Select mode</option>
                            <option value="In-Person">In-Person</option>
                            <option value="Online">Online</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                        <span class="me-field-error" id="editEventModeError"></span>
                    </div>
                </div>

                {{-- Row 3: Location --}}
                <div class="me-form-group">
                    <label class="me-label" for="editLocation">Location</label>
                    <input type="text" id="editLocation" name="location" class="me-input" placeholder="Venue address or online link" maxlength="255">
                </div>

                {{-- Row 4: Dates --}}
                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="editStartDate">Start Date <span class="me-required">*</span></label>
                        <input type="date" id="editStartDate" name="start_date" class="me-input" required>
                        <span class="me-field-error" id="editStartDateError"></span>
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="editEndDate">End Date</label>
                        <input type="date" id="editEndDate" name="end_date" class="me-input">
                    </div>
                </div>

                {{-- Row 5: Times --}}
                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="editStartTime">Start Time <span class="me-required">*</span></label>
                        <input type="time" id="editStartTime" name="start_time" class="me-input" required>
                        <span class="me-field-error" id="editStartTimeError"></span>
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="editEndTime">End Time</label>
                        <input type="time" id="editEndTime" name="end_time" class="me-input">
                    </div>
                </div>

                {{-- Row 6: Event Type + Price --}}
                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="editEventType">Event Type <span class="me-required">*</span></label>
                        <select id="editEventType" name="event_type" class="me-input me-select" required>
                            <option value="Free">Free</option>
                            <option value="Paid">Paid</option>
                        </select>
                        <span class="me-field-error" id="editEventTypeError"></span>
                    </div>
                    <div class="me-form-group" id="editTicketPriceGroup">
                        <label class="me-label" for="editTicketPrice">Ticket Price</label>
                        <input type="number" id="editTicketPrice" name="ticket_price" class="me-input" placeholder="0.00" min="0" step="0.01">
                    </div>
                </div>

                {{-- Row 7: Total Seats + Reg Deadline --}}
                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="editTotalSeats">Total Seats</label>
                        <input type="number" id="editTotalSeats" name="total_seats" class="me-input" placeholder="Leave blank for unlimited" min="1">
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="editRegDeadline">Registration Deadline</label>
                        <input type="date" id="editRegDeadline" name="registration_deadline" class="me-input">
                    </div>
                </div>

                {{-- Row 8: Registration Required --}}
                <div class="me-form-group">
                    <label class="me-label" for="editRegRequired">Registration Required <span class="me-required">*</span></label>
                    <select id="editRegRequired" name="registration_required" class="me-input me-select" required>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>

                {{-- Row 9: Banner Image --}}
                <div class="me-form-group">
                    <label class="me-label" for="editBannerImage">Banner Image</label>
                    <div class="me-file-wrap">
                        <div class="me-file-preview" id="editBannerPreview" hidden>
                            <img id="editBannerPreviewImg" src="" alt="Current banner">
                            <button type="button" class="me-file-remove" id="editBannerRemove" aria-label="Remove banner">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <label class="me-file-label" for="editBannerImage" id="editBannerLabel">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span id="editBannerLabelText">Click to upload or drag & drop</span>
                            <span class="me-file-hint">JPG, PNG, WebP — max 5 MB</span>
                        </label>
                        <input type="file" id="editBannerImage" name="banner_image" class="me-file-input" accept="image/jpg,image/jpeg,image/png,image/webp">
                    </div>
                </div>

                {{-- Row 10: Description --}}
                <div class="me-form-group">
                    <label class="me-label" for="editDescription">Description <span class="me-required">*</span></label>
                    <textarea id="editDescription" name="description" class="me-input me-textarea" rows="5" placeholder="Describe your event…" required></textarea>
                    <span class="me-field-error" id="editDescriptionError"></span>
                </div>

            </form>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="cancelEditBtn">Cancel</button>
            <button type="button" class="me-btn me-btn--primary" id="saveEditBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Changes
            </button>
        </div>

    </div>
</div>


{{-- =============================================
     DELETE CONFIRMATION MODAL
     ============================================= --}}
<div id="eventDeleteModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle" hidden>
    <div class="me-modal me-modal--sm" role="document">
        <div class="me-modal__header">
            <h2 id="deleteModalTitle" class="me-modal__title">Delete Event</h2>
            <button type="button" class="me-modal__close" id="closeDeleteModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__body">
            <div class="me-delete-icon" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </div>
            <p class="me-delete-text">
                Are you sure you want to delete <strong id="deleteEventTitle"></strong>?
                This action <strong>cannot be undone</strong> and all registrations will be permanently removed.
            </p>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="cancelDeleteBtn">Cancel</button>
            <button type="button" class="me-btn me-btn--destructive" id="confirmDeleteBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Yes, Delete Event
            </button>
        </div>
    </div>
</div>


@include('community.events._registrations_view_modal')

@endsection


@push('scripts')
<script>
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Helpers ──────────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);
    const show = el => el?.removeAttribute('hidden');
    const hide = el => el?.setAttribute('hidden', '');

    // ── Focus Trap ────────────────────────────────────────────────────────
    const focusable = 'button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';
    function trapFocus(modal) {
        const els = [...modal.querySelectorAll(focusable)];
        if (!els.length) return;
        const first = els[0], last = els[els.length - 1];
        modal.addEventListener('keydown', e => {
            if (e.key !== 'Tab') return;
            if (e.shiftKey) { if (document.activeElement === first) { e.preventDefault(); last.focus(); } }
            else            { if (document.activeElement === last)  { e.preventDefault(); first.focus(); } }
        });
    }

    let lastFocused;
    function openModal(modal) {
        lastFocused = document.activeElement;
        show(modal);
        document.body.style.overflow = 'hidden';
        const first = modal.querySelector(focusable);
        setTimeout(() => first?.focus(), 50);
        trapFocus(modal);
    }
    function closeModal(modal) {
        hide(modal);
        document.body.style.overflow = '';
        lastFocused?.focus();
    }

    // Close on backdrop click
    document.querySelectorAll('.me-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closeModal(backdrop);
        });
    });

    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.me-modal-backdrop:not([hidden])').forEach(closeModal);
    });


    // ── Toast ─────────────────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const container = $('meToastContainer');
        const toast = document.createElement('div');
        toast.className = `me-toast me-toast--${type}`;
        toast.setAttribute('role', 'alert');

        const icon = type === 'success'
            ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

        toast.innerHTML = `
            <span class="me-toast__icon">${icon}</span>
            <span class="me-toast__message">${message}</span>
            <button class="me-toast__close" aria-label="Dismiss">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>`;

        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('me-toast--show'));

        const dismiss = () => {
            toast.classList.remove('me-toast--show');
            toast.addEventListener('transitionend', () => toast.remove(), { once: true });
        };
        toast.querySelector('.me-toast__close').addEventListener('click', dismiss);
        setTimeout(dismiss, 4500);
    }

    // ── Flash toast from server redirect ─────────────────────────────────
    @if(session('success'))
    showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
    showToast(@json(session('error')), 'error');
    @endif


    // ── VIEW MODAL ────────────────────────────────────────────────────────
    const viewModal = $('eventViewModal');
    let currentViewEvent = null;

    document.querySelectorAll('[data-action="view"]').forEach(btn => {
        btn.addEventListener('click', () => populateViewModal(JSON.parse(btn.dataset.event)));
    });

    function populateViewModal(ev) {
        currentViewEvent = ev;

        const badge = $('modalStatusBadge');
        badge.textContent = ev.status.charAt(0).toUpperCase() + ev.status.slice(1);
        badge.className   = `event-card__status event-card__status--${ev.status.toLowerCase()}`;

        $('modalEventTitle').textContent = ev.title;

        const bannerImg = $('modalBannerImg');
        const bannerPh  = $('modalBannerPlaceholder');
        if (ev.banner_image) {
            show(bannerImg); hide(bannerPh);
            bannerImg.alt = ev.title;
            bannerImg.src = ev.banner_image;
        } else {
            hide(bannerImg); show(bannerPh);
        }

        const dateStr = ev.end_date_display && ev.end_date_display !== ev.start_date_display
            ? `${ev.start_date_display} — ${ev.end_date_display}`
            : ev.start_date_display;
        $('modalDate').textContent = dateStr;

        $('modalTime').textContent = ev.end_time_display
            ? `${ev.start_time_display} — ${ev.end_time_display}`
            : ev.start_time_display;

        $('modalLocation').textContent = ev.location || 'Online Event';
        $('modalMode').textContent      = ev.event_mode;

        ev.organiser ? (show($('modalOrgWrap')), $('modalOrg').textContent = ev.organiser) : hide($('modalOrgWrap'));
        if (ev.contact_email) {
            $('modalEmail').textContent = ev.contact_email;
            $('modalEmail').href = `mailto:${ev.contact_email}`;
            show($('modalEmailWrap'));
        } else { hide($('modalEmailWrap')); }

        $('modalRegCount').textContent = ev.registered;
        if (ev.total_seats) {
            const avail = Math.max(0, ev.total_seats - ev.registered);
            const pct   = Math.min(100, Math.round((ev.registered / ev.total_seats) * 100));
            $('modalSeatsCount').textContent  = ev.total_seats;
            $('modalAvailCount').textContent  = avail;
            $('modalProgressFill').style.width = pct + '%';
            $('modalProgressBar').setAttribute('aria-valuenow', pct);
            $('modalPct').textContent = pct + '% capacity filled';
            const fill = $('modalProgressFill');
            fill.className = 'event-card__progress-bar' +
                (pct >= 100 ? ' event-card__progress-bar--full' : pct >= 80 ? ' event-card__progress-bar--near' : '');
            show($('modalSeatsStatWrap')); show($('modalAvailWrap')); show($('modalProgressWrap'));
        } else {
            hide($('modalSeatsStatWrap')); hide($('modalAvailWrap')); hide($('modalProgressWrap'));
        }

        if (ev.description) { $('modalDesc').innerHTML = ev.description; show($('modalDescWrap')); }
        else { hide($('modalDescWrap')); }

        if (ev.registration_link) { $('modalRegLink').href = ev.registration_link; show($('modalRegLinkWrap')); }
        else { hide($('modalRegLinkWrap')); }

        openModal(viewModal);
    }

    $('closeViewModal').addEventListener('click',       () => closeModal(viewModal));
    $('closeViewModalFooter').addEventListener('click', () => closeModal(viewModal));

    // View → Edit bridge
    $('viewToEditBtn').addEventListener('click', () => {
        closeModal(viewModal);
        if (currentViewEvent) openEditModal(currentViewEvent);
    });


    // ── EDIT MODAL ────────────────────────────────────────────────────────
    const editModal = $('eventEditModal');
    let editingBannerRemoved = false;

    document.querySelectorAll('[data-action="edit"]').forEach(btn => {
        btn.addEventListener('click', () => openEditModal(JSON.parse(btn.dataset.event)));
    });

    function openEditModal(ev) {
        // Reset error state
        hide($('editErrorBanner'));
        document.querySelectorAll('.me-field-error').forEach(el => el.textContent = '');
        document.querySelectorAll('.me-input').forEach(el => el.classList.remove('me-input--error'));

        editingBannerRemoved = false;

        // Populate fields
        $('editEventId').value       = ev.id;
        $('editUpdateUrl').value     = ev.update_url;
        $('editTitle').value         = ev.title ?? '';
        $('editCategory').value      = ev.category ?? '';
        $('editLocation').value      = ev.location ?? '';
        $('editStartDate').value     = ev.start_date ?? '';
        $('editEndDate').value       = ev.end_date ?? '';
        $('editStartTime').value     = ev.start_time ?? '';
        $('editEndTime').value       = ev.end_time ?? '';
        $('editTotalSeats').value    = ev.total_seats ?? '';
        $('editRegDeadline').value   = ev.registration_deadline ?? '';
        $('editDescription').value   = ev.description ?? '';
        $('editTicketPrice').value   = ev.ticket_price ?? '';

        // Selects
        setSelectValue('editEventMode',   ev.event_mode);
        setSelectValue('editEventType',   ev.event_type);
        setSelectValue('editRegRequired', ev.registration_required ? '1' : '0');

        // Toggle price field
        togglePriceField();

        // Banner preview
        const preview = $('editBannerPreview');
        const label   = $('editBannerLabel');
        const input   = $('editBannerImage');
        input.value   = '';
        if (ev.banner_image) {
            $('editBannerPreviewImg').src = ev.banner_image;
            show(preview); hide(label);
        } else {
            hide(preview); show(label);
            $('editBannerLabelText').textContent = 'Click to upload or drag & drop';
        }

        openModal(editModal);
    }

    function setSelectValue(id, value) {
        const sel = $(id);
        if (!sel || value === null || value === undefined) return;
        const opt = [...sel.options].find(o => o.value == value);
        if (opt) sel.value = opt.value;
    }

    // Toggle ticket price field visibility
    function togglePriceField() {
        const type  = $('editEventType').value;
        const group = $('editTicketPriceGroup');
        type === 'Paid' ? show(group) : hide(group);
    }
    $('editEventType').addEventListener('change', togglePriceField);

    // Banner file input
    $('editBannerImage').addEventListener('change', function () {
        if (!this.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            $('editBannerPreviewImg').src = e.target.result;
            show($('editBannerPreview')); hide($('editBannerLabel'));
            $('editBannerLabelText').textContent = this.files[0].name;
            editingBannerRemoved = false;
        };
        reader.readAsDataURL(this.files[0]);
    });

    $('editBannerRemove').addEventListener('click', () => {
        $('editBannerImage').value = '';
        $('editBannerPreviewImg').src = '';
        hide($('editBannerPreview')); show($('editBannerLabel'));
        $('editBannerLabelText').textContent = 'Click to upload or drag & drop';
        editingBannerRemoved = true;
    });

    // Close edit modal
    $('closeEditModal').addEventListener('click', () => closeModal(editModal));
    $('cancelEditBtn').addEventListener('click',  () => closeModal(editModal));

    // ── Save (AJAX) ───────────────────────────────────────────────────────
    $('saveEditBtn').addEventListener('click', async () => {
        const saveBtn = $('saveEditBtn');
        const url     = $('editUpdateUrl').value;
        const eventId = $('editEventId').value;

        // Client-side validation
        let valid = true;
        const required = [
            { id: 'editTitle',     errId: 'editTitleError',     msg: 'Title is required.' },
            { id: 'editCategory',  errId: 'editCategoryError',  msg: 'Category is required.' },
            { id: 'editEventMode', errId: 'editEventModeError', msg: 'Event mode is required.' },
            { id: 'editStartDate', errId: 'editStartDateError', msg: 'Start date is required.' },
            { id: 'editStartTime', errId: 'editStartTimeError', msg: 'Start time is required.' },
            { id: 'editEventType', errId: 'editEventTypeError', msg: 'Event type is required.' },
            { id: 'editDescription', errId: 'editDescriptionError', msg: 'Description is required.' },
        ];
        required.forEach(({ id, errId, msg }) => {
            const el  = $(id);
            const err = $(errId);
            if (!el.value.trim()) {
                el.classList.add('me-input--error');
                err.textContent = msg;
                valid = false;
            } else {
                el.classList.remove('me-input--error');
                err.textContent = '';
            }
        });
        if (!valid) return;

        // Build FormData (supports file upload)
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('_token', CSRF);
        formData.append('title',                 $('editTitle').value.trim());
        formData.append('category',              $('editCategory').value.trim());
        formData.append('event_mode',            $('editEventMode').value);
        formData.append('location',              $('editLocation').value.trim());
        formData.append('start_date',            $('editStartDate').value);
        formData.append('end_date',              $('editEndDate').value);
        formData.append('start_time',            $('editStartTime').value);
        formData.append('end_time',              $('editEndTime').value);
        formData.append('description',           $('editDescription').value.trim());
        formData.append('event_type',            $('editEventType').value);
        formData.append('ticket_price',          $('editTicketPrice').value || '0');
        formData.append('total_seats',           $('editTotalSeats').value);
        formData.append('registration_deadline', $('editRegDeadline').value);
        formData.append('registration_required', $('editRegRequired').value);

        const bannerFile = $('editBannerImage').files[0];
        if (bannerFile) {
            formData.append('banner_image', bannerFile);
        }

        // Loading state
        saveBtn.disabled = true;
        const originalLabel = saveBtn.innerHTML;
        saveBtn.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:me-spin 0.8s linear infinite" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            Saving…`;

        try {
            const res  = await fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: formData,
            });
            const data = await res.json();

            if (!res.ok) {
                // Validation errors from Laravel
                if (res.status === 422 && data.errors) {
                    const firstErrors = Object.values(data.errors).map(e => e[0]);
                    showFieldErrors(data.errors);
                    showEditError(firstErrors.join(' '));
                } else {
                    showEditError(data.message ?? data.error ?? 'Something went wrong. Please try again.');
                }
                return;
            }

            closeModal(editModal);
            showToast('Event updated successfully!', 'success');

            // Refresh the page after a short delay so the card reflects changes
            setTimeout(() => window.location.reload(), 1200);

        } catch (err) {
            showEditError('Network error. Please check your connection and try again.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalLabel;
        }
    });

    function showEditError(msg) {
        const banner = $('editErrorBanner');
        banner.textContent = msg;
        show(banner);
        banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function showFieldErrors(errors) {
        const map = {
            title:                 'editTitleError',
            category:              'editCategoryError',
            event_mode:            'editEventModeError',
            start_date:            'editStartDateError',
            start_time:            'editStartTimeError',
            event_type:            'editEventTypeError',
            description:           'editDescriptionError',
        };
        Object.entries(map).forEach(([field, errId]) => {
            const err = $(errId);
            const inp = document.querySelector(`[name="${field}"]`);
            if (errors[field]) {
                err.textContent = errors[field][0];
                inp?.classList.add('me-input--error');
            }
        });
    }


    // ── DELETE MODAL ──────────────────────────────────────────────────────
    const deleteModal = $('eventDeleteModal');
    let pendingDeleteUrl  = '';
    let pendingDeleteId   = null;

    document.querySelectorAll('[data-action="delete"]').forEach(btn => {
        btn.addEventListener('click', () => {
            $('deleteEventTitle').textContent = btn.dataset.eventTitle;
            pendingDeleteUrl = btn.dataset.deleteUrl;
            pendingDeleteId  = btn.dataset.eventId;
            openModal(deleteModal);
        });
    });

    $('closeDeleteModal').addEventListener('click', () => closeModal(deleteModal));
    $('cancelDeleteBtn').addEventListener('click',  () => closeModal(deleteModal));

    $('confirmDeleteBtn').addEventListener('click', async () => {
        const confirmBtn = $('confirmDeleteBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:me-spin 0.8s linear infinite" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            Deleting…`;

        try {
            const res = await fetch(pendingDeleteUrl, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/json',
                },
            });
            const data = await res.json();

            if (!res.ok) {
                closeModal(deleteModal);
                showToast(data.message ?? 'Could not delete event. Please try again.', 'error');
                return;
            }

            // Remove card from DOM immediately
            const card = document.querySelector(`[data-event-id="${pendingDeleteId}"]`);
            if (card) {
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity    = '0';
                card.style.transform  = 'scale(0.95)';
                setTimeout(() => card.remove(), 300);
            }

            closeModal(deleteModal);
            showToast('Event deleted successfully.', 'success');

        } catch (err) {
            closeModal(deleteModal);
            showToast('Network error. Please try again.', 'error');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = `
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Yes, Delete Event`;
        }
    });

    // ── REGISTRATIONS MODAL ───────────────────────────────────────────────
const regsModal     = $('eventRegsModal');
let currentRegsEventId    = null;
let currentRegsEventTitle = null;
let currentRegsData       = null;

document.querySelectorAll('[data-action="registrations"]').forEach(btn => {
    btn.addEventListener('click', () => {
        currentRegsEventId    = btn.dataset.eventId;
        currentRegsEventTitle = btn.dataset.eventTitle;

        document.querySelectorAll(`.event-new-reg-badge[data-for-event="${currentRegsEventId}"]`)
            .forEach(badge => badge.remove());

        fetch(`/my-events/${currentRegsEventId}/mark-seen`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
        }).then(() => {
            if (typeof window.fetchSidebarBadges === 'function') window.fetchSidebarBadges();
        }).catch(() => {});

        openRegsModal();
    });
});


function openRegsModal() {
    // Reset state
    $('regsModalTitle').textContent = currentRegsEventTitle;
    $('regsModalMeta').textContent  = '';
    $('regsExportBtn').style.display = 'none';
    currentRegsData = null;

    // Show loading, hide everything else
    $('regsLoading').style.display  = 'flex';
    $('regsError').setAttribute('hidden', '');
    $('regsEmpty').setAttribute('hidden', '');
    $('regsStats').setAttribute('hidden', '');
    $('regsTableWrap').setAttribute('hidden', '');

    openModal(regsModal);
    fetchRegistrations();
}

async function fetchRegistrations() {
    try {
        const res = await fetch(`/my-events/${currentRegsEventId}/registrations`, {
            headers: {
                'Accept'       : 'application/json',
                'X-CSRF-TOKEN' : CSRF,
            },
        });

        // Handle auth / permission errors
        if (res.status === 401) {
            showRegsError('You must be logged in to view registrations.');
            return;
        }
        if (res.status === 403) {
            showRegsError('You do not have permission to view these registrations.');
            return;
        }
        if (res.status === 404) {
            showRegsError('Event not found.');
            return;
        }
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            showRegsError(data.message ?? 'Failed to load registrations. Please try again.');
            return;
        }

        const data = await res.json();

        if (!data.success) {
            showRegsError(data.message ?? 'Something went wrong.');
            return;
        }

        currentRegsData = data;
        renderRegistrations(data);

    } catch (err) {
        showRegsError('Network error. Please check your connection and try again.');
    }
}

function showRegsError(msg) {
    $('regsLoading').style.display = 'none';
    const errEl = $('regsError');
    errEl.textContent = msg;
    errEl.removeAttribute('hidden');
}

function renderRegistrations(data) {
    $('regsLoading').style.display = 'none';

    // Always hide all states first, then show what's needed
    $('regsError').setAttribute('hidden', '');
    $('regsEmpty').setAttribute('hidden', '');
    $('regsStats').setAttribute('hidden', '');
    $('regsTableWrap').setAttribute('hidden', '');
    $('regsExportBtn').style.display = 'none';

    // Meta line
    $('regsModalMeta').textContent = data.count === 0
        ? 'No registrations yet'
        : `${data.count} registrant${data.count !== 1 ? 's' : ''} · ${data.total} people total`;

    if (parseInt(data.count) === 0) {
        $('regsEmpty').removeAttribute('hidden');
        return;
    }

    // Stats
    $('regsStatRegistrants').textContent = parseInt(data.count);
    $('regsStatPeople').textContent      = parseInt(data.total);
    $('regsStats').removeAttribute('hidden');

    // Build table rows
    const tbody = $('regsTableBody');
    tbody.innerHTML = '';

    data.registrations.forEach((reg, i) => {
        const tr = document.createElement('tr');
        const avatarHtml = reg.photo
            ? `<img src="${escHtml(reg.photo)}" alt="${escHtml(reg.name)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">`
            : `<span style="font-size:13px;font-weight:700;color:#e8640c;">${escHtml(reg.initials)}</span>`;

        const profileHref = reg.profile_url || '#';

        tr.innerHTML = `
            <td>${i + 1}</td>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <a href="${escHtml(profileHref)}"
                       style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#fde9d6,#fbd0b0);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;text-decoration:none;transition:opacity .15s;"
                       title="View profile"
                       onmouseover="this.style.opacity='.8'"
                       onmouseout="this.style.opacity='1'">
                        ${avatarHtml}
                    </a>
                    <a href="${escHtml(profileHref)}"
                       style="font-weight:600;color:#1f2937;text-decoration:none;font-size:13.5px;"
                       onmouseover="this.style.color='#c9622a';this.style.textDecoration='underline'"
                       onmouseout="this.style.color='#1f2937';this.style.textDecoration='none'">
                        ${escHtml(reg.name)}
                    </a>
                </div>
            </td>
            <td><a href="mailto:${escHtml(reg.email)}" ...
            <td><a href="mailto:${escHtml(reg.email)}" style="color:#E8640C;text-decoration:none;">${escHtml(reg.email)}</a></td>
            <td>${reg.phone !== '—' ? escHtml(reg.phone) : '<span class="regs-empty-cell">—</span>'}</td>
            <td>${reg.country !== '—' ? escHtml(reg.country) : '<span class="regs-empty-cell">—</span>'}</td>
            <td>${reg.batch !== '—' ? escHtml(reg.batch) : '<span class="regs-empty-cell">—</span>'}</td>
            <td style="text-align:center;font-weight:600;">${reg.people}</td>
            <td class="regs-message-cell">${reg.message !== '—' ? escHtml(reg.message) : '<span class="regs-empty-cell">—</span>'}</td>
            <td style="white-space:nowrap;color:#9ca3af;font-size:12px;">${escHtml(reg.registered)}</td>
        `;
        tbody.appendChild(tr);
    });

    $('regsTableWrap').removeAttribute('hidden');
    $('regsExportBtn').style.display = 'inline-flex';
}

// ── Export CSV ────────────────────────────────────────────────────────
$('regsExportBtn').addEventListener('click', () => {
    if (!currentRegsEventId) return;
    window.location.href = `/my-events/${currentRegsEventId}/registrations?export=csv`;
});

// ── XSS guard ─────────────────────────────────────────────────────────
function escHtml(str) {
    if (str === null || str === undefined) return '—';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ── Close ─────────────────────────────────────────────────────────────
$('closeRegsModal').addEventListener('click',       () => closeModal(regsModal));
$('closeRegsModalFooter').addEventListener('click', () => closeModal(regsModal));

})();
</script>
@endpush