@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'All Events — Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/events/admin-events.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="ae-header">
    <div class="ae-header__left">
        <h1 class="ae-title">All Events</h1>
        <span class="ae-count">{{ $events->total() }} event{{ $events->total() !== 1 ? 's' : '' }}</span>
    </div>
    <a href="{{ route('events.create') }}" class="me-btn me-btn--primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create Event
    </a>
</div>

{{-- Toolbar --}}
<div class="ae-toolbar">
    <form method="GET" action="{{ route('admin.events.index') }}" class="ae-search-form">
        @if(request('filter'))
            <input type="hidden" name="filter" value="{{ request('filter') }}">
        @endif
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search by title or location…"
            autocomplete="off"
        />
        <button type="submit">Search</button>
        @if(request()->hasAny(['search','filter']))
            <a href="{{ route('admin.events.index') }}" class="ae-clear">Clear</a>
        @endif
    </form>

    <div class="ae-filters">
        <a href="{{ route('admin.events.index', array_filter(['search' => request('search')])) }}"
           class="ae-filter {{ request('filter','') === '' ? 'active' : '' }}">All</a>
        <a href="{{ route('admin.events.index', array_filter(['filter' => 'upcoming', 'search' => request('search')])) }}"
           class="ae-filter {{ request('filter') === 'upcoming' ? 'active' : '' }}">Upcoming</a>
        <a href="{{ route('admin.events.index', array_filter(['filter' => 'ongoing', 'search' => request('search')])) }}"
           class="ae-filter {{ request('filter') === 'ongoing' ? 'active' : '' }}">Ongoing</a>
        <a href="{{ route('admin.events.index', array_filter(['filter' => 'past', 'search' => request('search')])) }}"
           class="ae-filter {{ request('filter') === 'past' ? 'active' : '' }}">Past</a>
    </div>
</div>

{{-- Table --}}
@if($events->isEmpty())
    <div class="ae-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <h3>No events found</h3>
        <p>Try a different search or filter.</p>
    </div>
@else
    <div class="ae-table-wrap">
        <table class="ae-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Creator</th>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $i => $event)
                    @php
                        $today = now()->toDateString();
                        if ($event->start_date->toDateString() > $today) {
                            $dateLabel = 'Upcoming';
                            $dateCls   = 'upcoming';
                        } elseif ($event->end_date && $event->end_date->toDateString() >= $today) {
                            $dateLabel = 'Ongoing';
                            $dateCls   = 'ongoing';
                        } else {
                            $dateLabel = 'Past';
                            $dateCls   = 'past';
                        }
                    @endphp
                    <tr data-event-id="{{ $event->id }}">
                        <td class="ae-td--num">{{ $events->firstItem() + $i }}</td>
                        <td class="ae-td--title">
                            <span class="ae-event-title">{{ $event->title }}</span>
                            @if($event->location)
                                <span class="ae-event-location">📍 {{ $event->location }}</span>
                            @endif
                        </td>
                        <td class="ae-td--creator">
                            {{ $event->creator->full_name ?? '—' }}
                        </td>
                        <td class="ae-td--date">
                            <span>{{ $event->start_date->format('d M Y') }}</span>
                            <span class="ae-date-badge ae-date-badge--{{ $dateCls }}">{{ $dateLabel }}</span>
                        </td>
                        <td class="ae-td--mode">{{ $event->event_mode }}</td>
                        <td class="ae-td--status">
                            <span class="ae-status ae-status--{{ strtolower($event->status) }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="ae-td--regs">
                            {{ $event->registered_count ?? 0 }}
                            @if($event->total_seats)
                                <span class="ae-seats-total">/ {{ $event->total_seats }}</span>
                            @endif
                        </td>
                        <td class="ae-td--actions">
                            <button type="button"
                                class="ae-btn-edit"
                                data-event-id="{{ $event->id }}"
                                data-event-title="{{ $event->title }}"
                                data-event-status="{{ $event->status }}"
                                data-event-start="{{ $event->start_date->format('Y-m-d') }}"
                                data-event-end="{{ optional($event->end_date)->format('Y-m-d') }}"
                                data-event-location="{{ $event->location }}"
                                data-event-mode="{{ $event->event_mode }}"
                                data-event-seats="{{ $event->total_seats }}"
                                data-event-banner="{{ $event->banner_image ? asset('storage/'.$event->banner_image) : '' }}"
                                data-update-url="{{ route('admin.events.update', $event->id) }}"
                                aria-label="Edit {{ $event->title }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button type="button"
                                class="ae-btn-delete"
                                data-event-id="{{ $event->id }}"
                                data-event-title="{{ $event->title }}"
                                data-delete-url="{{ route('admin.events.delete', $event->id) }}"
                                aria-label="Delete {{ $event->title }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($events->hasPages())
        <div class="me-pagination">{{ $events->links() }}</div>
    @endif
@endif


{{-- Toast --}}
<div id="aeToastContainer" class="me-toast-container" aria-live="polite" aria-atomic="true"></div>


{{-- ── EDIT MODAL ─────────────────────────────────────────────────── --}}
<div id="aeEditModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="aeEditTitle" hidden>
    <div class="me-modal" role="document">

        <div class="me-modal__header">
            <h2 id="aeEditTitle" class="me-modal__title">Edit Event</h2>
            <button type="button" class="me-modal__close" id="aeCloseEdit" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__body">
            <div id="aeEditError" class="me-alert me-alert--danger" hidden></div>

            <form id="aeEditForm" novalidate>
                <input type="hidden" id="aeEditEventId">
                <input type="hidden" id="aeUpdateUrl">

                <div class="me-form-group">
                    <label class="me-label" for="aeTitle">Title <span class="me-required">*</span></label>
                    <input type="text" id="aeTitle" name="title" class="me-input" required maxlength="255">
                    <span class="me-field-error" id="aeTitleError"></span>
                </div>

                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="aeStatus">Status <span class="me-required">*</span></label>
                        <select id="aeStatus" name="status" class="me-input me-select" required>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="published">Published</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="aeMode">Event Mode <span class="me-required">*</span></label>
                        <select id="aeMode" name="event_mode" class="me-input me-select" required>
                            <option value="In-Person">In-Person</option>
                            <option value="Online">Online</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>

                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="aeStartDate">Start Date <span class="me-required">*</span></label>
                        <input type="date" id="aeStartDate" name="start_date" class="me-input" required>
                        <span class="me-field-error" id="aeStartDateError"></span>
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="aeEndDate">End Date</label>
                        <input type="date" id="aeEndDate" name="end_date" class="me-input">
                    </div>
                </div>

                <div class="me-form-group">
                    <label class="me-label" for="aeLocation">Location</label>
                    <input type="text" id="aeLocation" name="location" class="me-input" maxlength="255">
                </div>

                <div class="me-form-group">
                    <label class="me-label" for="aeSeats">Total Seats</label>
                    <input type="number" id="aeSeats" name="total_seats" class="me-input" min="1" placeholder="Leave blank for unlimited">
                </div>

                <div class="me-form-group">
                    <label class="me-label">Banner Image</label>
                    <div id="aeBannerPreviewWrap" style="display:none;margin-bottom:8px;position:relative;">
                        <img id="aeBannerPreviewImg" src="" alt="" style="width:100%;height:140px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">
                        <button type="button" id="aeBannerRemoveBtn"
                            style="position:absolute;top:6px;right:6px;width:26px;height:26px;border-radius:50%;background:rgba(0,0,0,.55);color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <label id="aeBannerUploadLabel" for="aeBannerImage"
                        style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px dashed #e2e8f0;border-radius:8px;cursor:pointer;font-size:13px;color:#6b7280;transition:border-color .15s;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Click to upload banner image (JPEG, PNG, WebP — max 4 MB)
                    </label>
                    <input type="file" id="aeBannerImage" accept="image/jpeg,image/png,image/jpg,image/webp" style="display:none;">
                </div>

            </form>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="aeCancelEdit">Cancel</button>
            <button type="button" class="me-btn me-btn--primary" id="aeSaveEdit">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Changes
            </button>
        </div>

    </div>
</div>


{{-- ── DELETE MODAL ───────────────────────────────────────────────── --}}
<div id="aeDeleteModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="aeDeleteTitle" hidden>
    <div class="me-modal me-modal--sm" role="document">

        <div class="me-modal__header">
            <h2 id="aeDeleteTitle" class="me-modal__title">Delete Event</h2>
            <button type="button" class="me-modal__close" id="aeCloseDelete" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__body">
            <div class="me-delete-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </div>
            <p class="me-delete-text">
                Are you sure you want to delete <strong id="aeDeleteEventTitle"></strong>?
                This action <strong>cannot be undone</strong> and all registrations will be permanently removed.
            </p>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="aeCancelDelete">Cancel</button>
            <button type="button" class="me-btn me-btn--destructive" id="aeConfirmDelete">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Yes, Delete
            </button>
        </div>

    </div>
</div>

@endsection


@push('scripts')
<script>
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const $ = id => document.getElementById(id);
    const show = el => el?.removeAttribute('hidden');
    const hide = el => el?.setAttribute('hidden', '');

    const focusable = 'button:not([disabled]),a[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';
    let lastFocused;

    function openModal(modal) {
        lastFocused = document.activeElement;
        show(modal);
        document.body.style.overflow = 'hidden';
        setTimeout(() => modal.querySelector(focusable)?.focus(), 50);
    }

    function closeModal(modal) {
        hide(modal);
        document.body.style.overflow = '';
        lastFocused?.focus();
    }

    document.querySelectorAll('.me-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closeModal(backdrop);
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.me-modal-backdrop:not([hidden])').forEach(closeModal);
    });


    // ── Toast ─────────────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const container = $('aeToastContainer');
        const toast = document.createElement('div');
        toast.className = `me-toast me-toast--${type}`;
        toast.setAttribute('role', 'alert');

        const icon = type === 'success'
            ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

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

    @if(session('success'))
        showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
        showToast(@json(session('error')), 'error');
    @endif


    // ── EDIT ─────────────────────────────────────────────────────────
    const editModal = $('aeEditModal');
    let pendingUpdateUrl = '';

    // ── Banner image preview in edit modal ───────────────────────────
    const aeBannerInput       = $('aeBannerImage');
    const aeBannerPreviewWrap = $('aeBannerPreviewWrap');
    const aeBannerPreviewImg  = $('aeBannerPreviewImg');
    const aeBannerUploadLabel = $('aeBannerUploadLabel');
    const aeBannerRemoveBtn   = $('aeBannerRemoveBtn');

    function aeShowBannerPreview(src) {
        aeBannerPreviewImg.src = src;
        aeBannerPreviewWrap.style.display = 'block';
        aeBannerUploadLabel.style.display = 'none';
    }
    function aeResetBannerUI() {
        aeBannerInput.value = '';
        aeBannerPreviewImg.src = '';
        aeBannerPreviewWrap.style.display = 'none';
        aeBannerUploadLabel.style.display = 'flex';
    }

    aeBannerInput.addEventListener('change', () => {
        const file = aeBannerInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => aeShowBannerPreview(e.target.result);
        reader.readAsDataURL(file);
    });

    aeBannerRemoveBtn.addEventListener('click', () => aeResetBannerUI());

    document.querySelectorAll('.ae-btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            $('aeEditEventId').value  = btn.dataset.eventId;
            $('aeUpdateUrl').value    = btn.dataset.updateUrl;
            $('aeTitle').value        = btn.dataset.eventTitle;
            $('aeStartDate').value    = btn.dataset.eventStart;
            $('aeEndDate').value      = btn.dataset.eventEnd ?? '';
            $('aeLocation').value     = btn.dataset.eventLocation ?? '';
            $('aeSeats').value        = btn.dataset.eventSeats ?? '';

            // Set selects
            setSelect('aeStatus', btn.dataset.eventStatus);
            setSelect('aeMode',   btn.dataset.eventMode);

            // Reset banner image UI; show existing if any
            const existingBanner = btn.dataset.eventBanner ?? '';
            if (existingBanner) {
                aeShowBannerPreview(existingBanner);
            } else {
                aeResetBannerUI();
            }

            // Reset errors
            hide($('aeEditError'));
            document.querySelectorAll('#aeEditForm .me-field-error')
                    .forEach(el => el.textContent = '');
            document.querySelectorAll('#aeEditForm .me-input')
                    .forEach(el => el.classList.remove('me-input--error'));

            openModal(editModal);
        });
    });

    function setSelect(id, value) {
        const sel = $(id);
        if (!sel || !value) return;
        const opt = [...sel.options].find(o => o.value === value);
        if (opt) sel.value = opt.value;
    }

    $('aeCloseEdit').addEventListener('click',  () => closeModal(editModal));
    $('aeCancelEdit').addEventListener('click', () => closeModal(editModal));

    $('aeSaveEdit').addEventListener('click', async () => {
        const saveBtn = $('aeSaveEdit');
        const url     = $('aeUpdateUrl').value;

        // Validate
        let valid = true;
        const title = $('aeTitle').value.trim();
        if (!title) {
            $('aeTitleError').textContent = 'Title is required.';
            $('aeTitle').classList.add('me-input--error');
            valid = false;
        } else {
            $('aeTitleError').textContent = '';
            $('aeTitle').classList.remove('me-input--error');
        }

        const startDate = $('aeStartDate').value;
        if (!startDate) {
            $('aeStartDateError').textContent = 'Start date is required.';
            $('aeStartDate').classList.add('me-input--error');
            valid = false;
        } else {
            $('aeStartDateError').textContent = '';
            $('aeStartDate').classList.remove('me-input--error');
        }

        if (!valid) return;

        // Loading
        saveBtn.disabled = true;
        const orig = saveBtn.innerHTML;
        saveBtn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:me-spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Saving…`;

        try {
            const fd = new FormData();
            fd.append('_method',     'PUT');
            fd.append('title',       $('aeTitle').value.trim());
            fd.append('status',      $('aeStatus').value);
            fd.append('start_date',  $('aeStartDate').value);
            fd.append('end_date',    $('aeEndDate').value);
            fd.append('location',    $('aeLocation').value.trim());
            fd.append('event_mode',  $('aeMode').value);
            fd.append('total_seats', $('aeSeats').value);
            if (aeBannerInput.files[0]) {
                fd.append('banner_image', aeBannerInput.files[0]);
            }

            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept'       : 'application/json',
                    'X-CSRF-TOKEN' : CSRF,
                },
                body: fd,
            });

            const data = await res.json();

            if (!res.ok) {
                const msg = data.errors
                    ? Object.values(data.errors).map(e => e[0]).join(' ')
                    : (data.message ?? 'Something went wrong.');
                $('aeEditError').textContent = msg;
                show($('aeEditError'));
                return;
            }

            closeModal(editModal);
            showToast('Event updated successfully!', 'success');

            // Update row in table live
            const eventId = $('aeEditEventId').value;
            const row = document.querySelector(`tr[data-event-id="${eventId}"]`);
            if (row) {
                row.querySelector('.ae-event-title').textContent = $('aeTitle').value.trim();
                row.querySelector('.ae-td--mode').textContent    = $('aeMode').value;
                const statusEl = row.querySelector('.ae-status');
                const newStatus = $('aeStatus').value;
                statusEl.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                statusEl.className   = `ae-status ae-status--${newStatus}`;
            }

        } catch (err) {
            $('aeEditError').textContent = 'Network error. Please try again.';
            show($('aeEditError'));
        } finally {
            saveBtn.disabled  = false;
            saveBtn.innerHTML = orig;
        }
    });


    // ── DELETE ────────────────────────────────────────────────────────
    const deleteModal  = $('aeDeleteModal');
    let pendingDeleteUrl = '';
    let pendingDeleteId  = null;

    document.querySelectorAll('.ae-btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            $('aeDeleteEventTitle').textContent = btn.dataset.eventTitle;
            pendingDeleteUrl = btn.dataset.deleteUrl;
            pendingDeleteId  = btn.dataset.eventId;
            openModal(deleteModal);
        });
    });

    $('aeCloseDelete').addEventListener('click',  () => closeModal(deleteModal));
    $('aeCancelDelete').addEventListener('click', () => closeModal(deleteModal));

    $('aeConfirmDelete').addEventListener('click', async () => {
        const confirmBtn = $('aeConfirmDelete');
        confirmBtn.disabled = true;
        const orig = confirmBtn.innerHTML;
        confirmBtn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:me-spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Deleting…`;

        try {
            const res = await fetch(pendingDeleteUrl, {
                method: 'DELETE',
                headers: {
                    'Accept'       : 'application/json',
                    'X-CSRF-TOKEN' : CSRF,
                    'Content-Type' : 'application/json',
                },
            });

            const data = await res.json();

            if (!res.ok) {
                closeModal(deleteModal);
                showToast(data.message ?? 'Could not delete event.', 'error');
                return;
            }

            // Remove row from table
            const row = document.querySelector(`tr[data-event-id="${pendingDeleteId}"]`);
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity    = '0';
                setTimeout(() => row.remove(), 300);
            }

            closeModal(deleteModal);
            showToast('Event deleted successfully.', 'success');

        } catch (err) {
            closeModal(deleteModal);
            showToast('Network error. Please try again.', 'error');
        } finally {
            confirmBtn.disabled  = false;
            confirmBtn.innerHTML = orig;
        }
    });

})();
</script>
@endpush