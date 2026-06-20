@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Pending Events')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/events/my-events.css') }}">
<style>
/* ── Admin Pending Events — extra styles ─── */
.pe-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
.pe-stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.pe-stat-card__number {
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
    color: #111827;
}
.pe-stat-card__label {
    font-size: 12.5px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    font-weight: 500;
}
.pe-stat-card--pending  { border-top: 3px solid #f59e0b; }
.pe-stat-card--approved { border-top: 3px solid #10b981; }
.pe-stat-card--rejected { border-top: 3px solid #ef4444; }
.pe-stat-card--total    { border-top: 3px solid #6366f1; }

.pe-stat-card--pending  .pe-stat-card__number { color: #d97706; }
.pe-stat-card--approved .pe-stat-card__number { color: #059669; }
.pe-stat-card--rejected .pe-stat-card__number { color: #dc2626; }
.pe-stat-card--total    .pe-stat-card__number { color: #4f46e5; }

/* Search bar */
.pe-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.pe-search-wrap {
    position: relative;
    flex: 1;
    min-width: 200px;
}
.pe-search-wrap svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
}
.pe-search {
    width: 100%;
    padding: 9px 13px 9px 38px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    color: #111827;
    background: #fff;
    outline: none;
    transition: border-color .2s;
}
.pe-search:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }

/* Event row card */
.pe-event-row {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 18px 20px;
    margin-bottom: 14px;
    transition: box-shadow .2s, transform .2s;
}
.pe-event-row:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,.07);
    transform: translateY(-1px);
}
.pe-event-row__banner {
    width: 110px;
    height: 80px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d1d5db;
}
.pe-event-row__banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.pe-event-row__body {
    flex: 1;
    min-width: 0;
}
.pe-event-row__title {
    font-size: 15.5px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pe-event-row__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 8px;
}
.pe-event-row__meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #6b7280;
}
.pe-event-row__meta-item svg { flex-shrink: 0; }
.pe-event-row__submitter {
    font-size: 12.5px;
    color: #9ca3af;
}
.pe-event-row__submitter strong { color: #374151; }
.pe-event-row__actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
    align-self: center;
}

/* Action buttons */
.pe-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all .18s;
    white-space: nowrap;
}
.pe-btn--approve {
    background: #ecfdf5;
    color: #059669;
    border-color: #a7f3d0;
}
.pe-btn--approve:hover {
    background: #059669;
    color: #fff;
    border-color: #059669;
}
.pe-btn--reject {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
}
.pe-btn--reject:hover {
    background: #dc2626;
    color: #fff;
    border-color: #dc2626;
}
.pe-btn--view {
    background: #f3f4f6;
    color: #374151;
    border-color: #e5e7eb;
}
.pe-btn--view:hover {
    background: #e5e7eb;
    color: #111827;
}

/* Empty */
.pe-empty {
    text-align: center;
    padding: 72px 24px;
    color: #6b7280;
}
.pe-empty__icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    color: #16a34a;
}
.pe-empty__title { font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 8px; }
.pe-empty__text  { font-size: 14.5px; margin: 0; }

/* Reject reason textarea */
.pe-reject-reason {
    width: 100%;
    padding: 10px 13px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: #111827;
    resize: vertical;
    min-height: 90px;
    outline: none;
    margin-top: 14px;
    transition: border-color .2s;
}
.pe-reject-reason:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }

/* Preview modal extra */
.pe-preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
}
.pe-preview-item { display: flex; flex-direction: column; gap: 3px; }
.pe-preview-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #9ca3af;
}
.pe-preview-value { font-size: 14px; color: #111827; font-weight: 500; }

@media (max-width: 768px) {
    .pe-stats { grid-template-columns: repeat(2, 1fr); }
    .pe-event-row { flex-wrap: wrap; }
    .pe-event-row__banner { width: 100%; height: 140px; }
    .pe-event-row__actions { flex-direction: row; flex-wrap: wrap; }
}
@media (max-width: 480px) {
    .pe-stats { grid-template-columns: 1fr 1fr; }
    .pe-preview-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="me-page-header">
    <div class="me-page-header__left">
        <h1 class="me-page-title">Pending Events</h1>
        <span class="me-page-count">{{ $events->total() }} awaiting review</span>
    </div>
</div>

{{-- Stats --}}
<div class="pe-stats">
    <div class="pe-stat-card pe-stat-card--pending">
        <span class="pe-stat-card__number">{{ $stats['pending'] }}</span>
        <span class="pe-stat-card__label">Pending</span>
    </div>
    <div class="pe-stat-card pe-stat-card--approved">
        <span class="pe-stat-card__number">{{ $stats['approved'] }}</span>
        <span class="pe-stat-card__label">Published</span>
    </div>
    <div class="pe-stat-card pe-stat-card--rejected">
        <span class="pe-stat-card__number">{{ $stats['rejected'] }}</span>
        <span class="pe-stat-card__label">Rejected</span>
    </div>
    <div class="pe-stat-card pe-stat-card--total">
        <span class="pe-stat-card__number">{{ $stats['total'] }}</span>
        <span class="pe-stat-card__label">Total Events</span>
    </div>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('admin.events.pending') }}" class="pe-toolbar">
    <div class="pe-search-wrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
            type="text"
            name="q"
            class="pe-search"
            placeholder="Search by title, category or location…"
            value="{{ request('q') }}">
    </div>
    <button type="submit" class="me-btn me-btn--primary">Search</button>
    @if(request('q'))
        <a href="{{ route('admin.events.pending') }}" class="me-btn me-btn--outline">Clear</a>
    @endif
</form>

{{-- Events List --}}
<div id="peEventList">
    @forelse($events as $event)
        @php
            $eventJson = e(json_encode([
                'id'          => $event->id,
                'title'       => $event->title,
                'category'    => $event->category,
                'event_mode'  => $event->event_mode,
                'location'    => $event->location,
                'start_date'  => $event->start_date->format('d M Y'),
                'end_date'    => optional($event->end_date)->format('d M Y'),
                'start_time'  => date('g:i A', strtotime($event->start_time)),
                'end_time'    => $event->end_time ? date('g:i A', strtotime($event->end_time)) : null,
                'description' => $event->description,
                'event_type'  => $event->event_type,
                'total_seats' => $event->total_seats,
                'banner_image'=> $event->banner_image ? asset('storage/' . $event->banner_image) : null,
                'creator'     => $event->creator?->full_name ?? 'Unknown',
                'created_at'  => $event->created_at->format('d M Y'),
                'approve_url' => route('admin.events.approve', $event->id),
                'reject_url'  => route('admin.events.reject',  $event->id),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        @endphp

        <div class="pe-event-row" id="pe-row-{{ $event->id }}">

            {{-- Banner --}}
            <div class="pe-event-row__banner">
                @if($event->banner_image)
                    <img src="{{ asset('storage/' . $event->banner_image) }}" alt="{{ $event->title }}" loading="lazy">
                @else
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                @endif
            </div>

            {{-- Body --}}
            <div class="pe-event-row__body">
                <h3 class="pe-event-row__title" title="{{ $event->title }}">{{ $event->title }}</h3>
                <div class="pe-event-row__meta">
                    <span class="pe-event-row__meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $event->start_date->format('d M Y') }}
                    </span>
                    <span class="pe-event-row__meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $event->location ?: 'Online' }}
                    </span>
                    <span class="pe-event-row__meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        {{ $event->event_mode }}
                    </span>
                    <span class="pe-event-row__meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $event->category }}
                    </span>
                </div>
                <p class="pe-event-row__submitter">
                    Submitted by <strong>{{ $event->creator?->full_name ?? 'Unknown' }}</strong>
                    · {{ $event->created_at->diffForHumans() }}
                </p>
            </div>

            {{-- Actions --}}
            <div class="pe-event-row__actions">
                <button type="button" class="pe-btn pe-btn--view"
                    data-action="preview"
                    data-event="{!! $eventJson !!}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Preview
                </button>
                <button type="button" class="pe-btn pe-btn--approve"
                    data-action="approve"
                    data-event-id="{{ $event->id }}"
                    data-event-title="{{ $event->title }}"
                    data-approve-url="{{ route('admin.events.approve', $event->id) }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Approve
                </button>
                <button type="button" class="pe-btn pe-btn--reject"
                    data-action="reject"
                    data-event-id="{{ $event->id }}"
                    data-event-title="{{ $event->title }}"
                    data-reject-url="{{ route('admin.events.reject', $event->id) }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Reject
                </button>
            </div>

        </div>

    @empty
        <div class="pe-empty">
            <div class="pe-empty__icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="pe-empty__title">All caught up!</h3>
            <p class="pe-empty__text">No pending events to review right now.</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($events->hasPages())
    <div class="me-pagination">{{ $events->links() }}</div>
@endif


{{-- Toast --}}
<div id="peToastContainer" class="me-toast-container" aria-live="polite"></div>


{{-- =============================================
     PREVIEW MODAL
     ============================================= --}}
<div id="pePreviewModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="pePreviewTitle" hidden>
    <div class="me-modal" role="document">
        <div class="me-modal__header">
            <h2 id="pePreviewTitle" class="me-modal__title">Event Preview</h2>
            <button type="button" class="me-modal__close" id="closePreviewModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__banner">
            <img id="previewBannerImg" src="" alt="" hidden>
            <div id="previewBannerPh" class="event-card__banner-placeholder me-modal__banner-placeholder" hidden>
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>No Banner</span>
            </div>
        </div>

        <div class="me-modal__body">
            <div class="pe-preview-grid">
                <div class="pe-preview-item">
                    <span class="pe-preview-label">Date</span>
                    <span class="pe-preview-value" id="previewDate"></span>
                </div>
                <div class="pe-preview-item">
                    <span class="pe-preview-label">Time</span>
                    <span class="pe-preview-value" id="previewTime"></span>
                </div>
                <div class="pe-preview-item">
                    <span class="pe-preview-label">Location</span>
                    <span class="pe-preview-value" id="previewLocation"></span>
                </div>
                <div class="pe-preview-item">
                    <span class="pe-preview-label">Mode</span>
                    <span class="pe-preview-value" id="previewMode"></span>
                </div>
                <div class="pe-preview-item">
                    <span class="pe-preview-label">Category</span>
                    <span class="pe-preview-value" id="previewCategory"></span>
                </div>
                <div class="pe-preview-item">
                    <span class="pe-preview-label">Type</span>
                    <span class="pe-preview-value" id="previewType"></span>
                </div>
                <div class="pe-preview-item">
                    <span class="pe-preview-label">Total Seats</span>
                    <span class="pe-preview-value" id="previewSeats"></span>
                </div>
                <div class="pe-preview-item">
                    <span class="pe-preview-label">Submitted By</span>
                    <span class="pe-preview-value" id="previewCreator"></span>
                </div>
            </div>

            <div class="me-modal__section">
                <h3 class="me-modal__section-title">Description</h3>
                <div class="me-modal__desc" id="previewDesc"></div>
            </div>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="closePreviewFooter">Close</button>
            <button type="button" class="pe-btn pe-btn--reject" id="previewRejectBtn" style="padding:8px 18px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
            </button>
            <button type="button" class="pe-btn pe-btn--approve" id="previewApproveBtn" style="padding:8px 18px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
        </div>
    </div>
</div>


{{-- =============================================
     APPROVE CONFIRM MODAL
     ============================================= --}}
<div id="peApproveModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="approveModalTitle" hidden>
    <div class="me-modal me-modal--sm" role="document">
        <div class="me-modal__header">
            <h2 id="approveModalTitle" class="me-modal__title">Approve Event</h2>
            <button type="button" class="me-modal__close" id="closeApproveModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="me-modal__body">
            <div class="me-delete-icon me-approve-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <p class="me-delete-text">
                Approve <strong id="approveEventTitle"></strong>?
                It will be <strong>published</strong> and visible to all alumni immediately.
            </p>
        </div>
        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="cancelApproveBtn">Cancel</button>
            <button type="button" class="pe-btn pe-btn--approve" id="confirmApproveBtn" style="padding:8px 20px;font-size:13.5px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Yes, Approve
            </button>
        </div>
    </div>
</div>


{{-- =============================================
     REJECT MODAL
     ============================================= --}}
<div id="peRejectModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="rejectModalTitle" hidden>
    <div class="me-modal me-modal--sm" role="document">
        <div class="me-modal__header">
            <h2 id="rejectModalTitle" class="me-modal__title">Reject Event</h2>
            <button type="button" class="me-modal__close" id="closeRejectModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="me-modal__body">
            <div class="me-delete-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <p class="me-delete-text">
                Reject <strong id="rejectEventTitle"></strong>?
                The organiser will be notified.
            </p>
            <textarea
                id="rejectReason"
                class="pe-reject-reason"
                placeholder="Optional: provide a reason for rejection (visible to organiser)…"
                maxlength="500"
                rows="3"></textarea>
        </div>
        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="cancelRejectBtn">Cancel</button>
            <button type="button" class="me-btn me-btn--destructive" id="confirmRejectBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Yes, Reject
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

    // ── Toast ─────────────────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const container = $('peToastContainer');
        const toast = document.createElement('div');
        toast.className = `me-toast me-toast--${type}`;
        toast.setAttribute('role', 'alert');
        const icon = type === 'success'
            ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
        toast.innerHTML = `<span class="me-toast__icon">${icon}</span><span class="me-toast__message">${message}</span><button class="me-toast__close" aria-label="Dismiss"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('me-toast--show'));
        const dismiss = () => {
            toast.classList.remove('me-toast--show');
            toast.addEventListener('transitionend', () => toast.remove(), { once: true });
        };
        toast.querySelector('.me-toast__close').addEventListener('click', dismiss);
        setTimeout(dismiss, 4500);
    }

    // ── Flash from server ─────────────────────────────────────────────────
    @if(session('success'))
    showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
    showToast(@json(session('error')), 'error');
    @endif

    // ── Modal helpers ─────────────────────────────────────────────────────
    const focusable = 'button:not([disabled]),a[href],input,select,textarea,[tabindex]:not([tabindex="-1"])';
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
    document.querySelectorAll('.me-modal-backdrop').forEach(b => {
        b.addEventListener('click', e => { if (e.target === b) closeModal(b); });
    });
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.me-modal-backdrop:not([hidden])').forEach(closeModal);
    });

    // ── Remove row from DOM ───────────────────────────────────────────────
    function removeRow(id) {
        const row = document.getElementById(`pe-row-${id}`);
        if (!row) return;
        row.style.transition = 'opacity .3s, transform .3s';
        row.style.opacity    = '0';
        row.style.transform  = 'scale(0.97)';
        setTimeout(() => row.remove(), 320);
    }

    // ── Spinner helper ────────────────────────────────────────────────────
    const spinSvg = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:me-spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>`;

    // ─────────────────────────────────────────────────────────────────────
    // PREVIEW MODAL
    // ─────────────────────────────────────────────────────────────────────
    const previewModal = $('pePreviewModal');
    let previewCurrentEvent = null;

    document.querySelectorAll('[data-action="preview"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const ev = JSON.parse(btn.dataset.event);
            openPreview(ev);
        });
    });

    function openPreview(ev) {
        previewCurrentEvent = ev;
        $('pePreviewTitle').textContent = ev.title;

        const img = $('previewBannerImg');
        const ph  = $('previewBannerPh');
        if (ev.banner_image) { img.src = ev.banner_image; img.alt = ev.title; show(img); hide(ph); }
        else                 { hide(img); show(ph); }

        const dateStr = ev.end_date && ev.end_date !== ev.start_date
            ? `${ev.start_date} — ${ev.end_date}` : ev.start_date;
        $('previewDate').textContent     = dateStr;
        $('previewTime').textContent     = ev.end_time ? `${ev.start_time} — ${ev.end_time}` : ev.start_time;
        $('previewLocation').textContent = ev.location || 'Online';
        $('previewMode').textContent     = ev.event_mode;
        $('previewCategory').textContent = ev.category;
        $('previewType').textContent     = ev.event_type;
        $('previewSeats').textContent    = ev.total_seats ? ev.total_seats : 'Unlimited';
        $('previewCreator').textContent  = ev.creator;
        $('previewDesc').innerHTML       = ev.description || '—';

        openModal(previewModal);
    }

    $('closePreviewModal').addEventListener('click',  () => closeModal(previewModal));
    $('closePreviewFooter').addEventListener('click', () => closeModal(previewModal));

    // Preview → Approve / Reject bridges
    $('previewApproveBtn').addEventListener('click', () => {
        closeModal(previewModal);
        if (previewCurrentEvent) openApproveModal(previewCurrentEvent.id, previewCurrentEvent.title, previewCurrentEvent.approve_url);
    });
    $('previewRejectBtn').addEventListener('click', () => {
        closeModal(previewModal);
        if (previewCurrentEvent) openRejectModal(previewCurrentEvent.id, previewCurrentEvent.title, previewCurrentEvent.reject_url);
    });

    // ─────────────────────────────────────────────────────────────────────
    // APPROVE MODAL
    // ─────────────────────────────────────────────────────────────────────
    const approveModal  = $('peApproveModal');
    let pendingApprove  = {};

    document.querySelectorAll('[data-action="approve"]').forEach(btn => {
        btn.addEventListener('click', () =>
            openApproveModal(btn.dataset.eventId, btn.dataset.eventTitle, btn.dataset.approveUrl)
        );
    });

    function openApproveModal(id, title, url) {
        pendingApprove = { id, title, url };
        $('approveEventTitle').textContent = title;
        openModal(approveModal);
    }

    $('closeApproveModal').addEventListener('click', () => closeModal(approveModal));
    $('cancelApproveBtn').addEventListener('click',  () => closeModal(approveModal));

    $('confirmApproveBtn').addEventListener('click', async () => {
        const btn = $('confirmApproveBtn');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `${spinSvg} Approving…`;

        try {
            const res  = await fetch(pendingApprove.url, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            });
            const data = await res.json();

            if (!res.ok) {
                showToast(data.message ?? 'Could not approve event.', 'error');
                return;
            }

            closeModal(approveModal);
            removeRow(pendingApprove.id);
            showToast(data.message ?? 'Event approved and published!', 'success');

        } catch {
            showToast('Network error. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });

    // ─────────────────────────────────────────────────────────────────────
    // REJECT MODAL
    // ─────────────────────────────────────────────────────────────────────
    const rejectModal  = $('peRejectModal');
    let pendingReject  = {};

    document.querySelectorAll('[data-action="reject"]').forEach(btn => {
        btn.addEventListener('click', () =>
            openRejectModal(btn.dataset.eventId, btn.dataset.eventTitle, btn.dataset.rejectUrl)
        );
    });

    function openRejectModal(id, title, url) {
        pendingReject = { id, title, url };
        $('rejectEventTitle').textContent = title;
        $('rejectReason').value = '';
        openModal(rejectModal);
    }

    $('closeRejectModal').addEventListener('click', () => closeModal(rejectModal));
    $('cancelRejectBtn').addEventListener('click',  () => closeModal(rejectModal));

    $('confirmRejectBtn').addEventListener('click', async () => {
        const btn = $('confirmRejectBtn');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `${spinSvg} Rejecting…`;

        try {
            const res  = await fetch(pendingReject.url, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason: $('rejectReason').value.trim() }),
            });
            const data = await res.json();

            if (!res.ok) {
                showToast(data.message ?? 'Could not reject event.', 'error');
                return;
            }

            closeModal(rejectModal);
            removeRow(pendingReject.id);
            showToast(data.message ?? 'Event rejected.', 'success');

        } catch {
            showToast('Network error. Please try again.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });

})();
</script>
@endpush