@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'All Stories — Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/stories/admin-stories.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="ae-header">
    <div class="ae-header__left">
        <h1 class="ae-title">All Stories</h1>
        <span class="ae-count">{{ $stories->total() }} stor{{ $stories->total() !== 1 ? 'ies' : 'y' }}</span>
    </div>
    <a href="{{ route('admin.stories.pending') }}" class="me-btn me-btn--outline" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Pending
        @if($counts['pending'] > 0)
            <span class="as-pending-badge">{{ $counts['pending'] }}</span>
        @endif
    </a>
</div>

{{-- Stats --}}
<div class="aj-stats">
    <div class="aj-stat">
        <span class="aj-stat__value">{{ $counts['all'] }}</span>
        <span class="aj-stat__label">Total</span>
    </div>
    <div class="aj-stat aj-stat--pending">
        <span class="aj-stat__value">{{ $counts['pending'] }}</span>
        <span class="aj-stat__label">Pending</span>
    </div>
    <div class="aj-stat aj-stat--published">
        <span class="aj-stat__value">{{ $counts['published'] }}</span>
        <span class="aj-stat__label">Published</span>
    </div>
    <div class="aj-stat aj-stat--rejected">
        <span class="aj-stat__value">{{ $counts['rejected'] }}</span>
        <span class="aj-stat__label">Rejected</span>
    </div>
</div>

{{-- Toolbar --}}
<div class="ae-toolbar">
    <form method="GET" action="{{ route('admin.stories.index') }}" class="ae-search-form">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search by title or author…"
            autocomplete="off"
        >
        <button type="submit">Search</button>
        @if(request()->hasAny(['q', 'status', 'category']))
            <a href="{{ route('admin.stories.index') }}" class="ae-clear">Clear</a>
        @endif
    </form>

    <div class="ae-filters">
        <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}"
           class="ae-filter {{ !request('status') ? 'active' : '' }}">All</a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}"
           class="ae-filter {{ request('status') === 'pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'published']) }}"
           class="ae-filter {{ request('status') === 'published' ? 'active' : '' }}">Published</a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}"
           class="ae-filter {{ request('status') === 'rejected' ? 'active' : '' }}">Rejected</a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'draft']) }}"
           class="ae-filter {{ request('status') === 'draft' ? 'active' : '' }}">Draft</a>
    </div>
</div>

{{-- Table --}}
@if($stories->isEmpty())
    <div class="ae-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
        <h3>No stories found</h3>
        <p>Try a different search or filter.</p>
    </div>
@else
    <div class="ae-table-wrap">
        <table class="ae-table">
            <thead>
                <tr>
                    <th style="width:44px;"></th>
                    <th>Story</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stories as $story)
                    <tr data-story-id="{{ $story->id }}">
                        {{-- Thumb --}}
                        <td style="padding:12px 8px 12px 16px;">
                            @if($story->cover_image)
                                <img src="{{ asset('storage/'.$story->cover_image) }}"
                                     alt="" class="as-thumb">
                            @else
                                <div class="as-thumb-ph">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                                </div>
                            @endif
                        </td>

                        {{-- Title + excerpt --}}
                        <td class="ae-td--title">
                            <span class="ae-event-title">{{ $story->title }}</span>
                            @if($story->excerpt)
                                <span class="ae-event-location" style="display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;max-width:300px;">
                                    {{ $story->excerpt }}
                                </span>
                            @endif
                        </td>

                        {{-- Author --}}
                        <td class="ae-td--creator">
                            {{ $story->creator->full_name ?? '—' }}<br>
                            <span style="font-size:11px;color:#9ca3af;">{{ $story->creator->email ?? '' }}</span>
                        </td>

                        {{-- Category --}}
                        <td>
                            <span class="as-tag">{{ $story->category }}</span>
                        </td>

                        {{-- Date --}}
                        <td class="ae-td--date">
                            {{ $story->created_at->format('d M Y') }}<br>
                            <span style="font-size:11px;color:#9ca3af;">{{ $story->created_at->diffForHumans() }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="ae-td--status">
                            <span class="ae-status ae-status--{{ $story->status }}">
                                {{ ucfirst($story->status) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="ae-td--actions">
                            {{-- Preview --}}
                            <button type="button"
                                class="ae-btn-icon"
                                title="Preview story"
                                data-action="preview"
                                data-story='{{ json_encode([
                                    "id"          => $story->id,
                                    "title"       => $story->title,
                                    "category"    => $story->category,
                                    "body"        => $story->body,
                                    "cover_image" => $story->cover_image ? asset("storage/".$story->cover_image) : null,
                                    "excerpt"     => $story->excerpt,
                                    "created_at"  => $story->created_at->format("d M Y"),
                                    "author"      => $story->creator->full_name ?? "—",
                                    "status"      => $story->status,
                                    "rejection_reason" => $story->rejection_reason,
                                ]) }}'>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>

                            {{-- Edit --}}
                            <button type="button"
                                class="ae-btn-edit"
                                data-story-id="{{ $story->id }}"
                                data-story-title="{{ $story->title }}"
                                data-story-category="{{ $story->category }}"
                                data-story-status="{{ $story->status }}"
                                data-story-excerpt="{{ $story->excerpt }}"
                                data-story-rejection-reason="{{ $story->rejection_reason }}"
                                data-update-url="{{ route('admin.stories.update', $story->id) }}"
                                aria-label="Edit {{ $story->title }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>

                            {{-- Delete --}}
                            <button type="button"
                                class="ae-btn-delete"
                                data-story-id="{{ $story->id }}"
                                data-story-title="{{ $story->title }}"
                                data-delete-url="{{ route('admin.stories.destroy', $story->id) }}"
                                aria-label="Delete {{ $story->title }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($stories->hasPages())
        <div class="me-pagination">{{ $stories->links() }}</div>
    @endif
@endif

{{-- Toast --}}
<div id="asToastContainer" class="me-toast-container" aria-live="polite" aria-atomic="true"></div>


{{-- ══════════════════════════════════════════════════════════════════
     PREVIEW MODAL
══════════════════════════════════════════════════════════════════ --}}
<div id="asPreviewModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="asPreviewTitle" hidden>
    <div class="me-modal as-preview-modal" role="document">

        <div class="me-modal__header">
            <div style="display:flex;flex-direction:column;gap:5px;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span id="asPreviewCategory" class="as-tag" style="font-size:11px;"></span>
                    <span id="asPreviewStatus" class="ae-status" style="font-size:11px;"></span>
                </div>
                <h2 id="asPreviewTitle" class="me-modal__title" style="font-size:18px;"></h2>
                <span id="asPreviewMeta" style="font-size:12px;color:#9ca3af;"></span>

                <div id="asPreviewRejectionWrap" hidden
                    style="margin-top:10px;padding:10px 12px;
                            background:#fef2f2;
                            border:1px solid #fecaca;
                            border-radius:8px;">

                    <div style="font-size:11px;
                                font-weight:600;
                                color:#dc2626;
                                text-transform:uppercase;
                                letter-spacing:.04em;
                                margin-bottom:4px;">
                        Rejection Reason
                    </div>

                    <div id="asPreviewRejectionReason"
                        style="font-size:13px;color:#7f1d1d;">
                    </div>
                </div>
            </div>
            <button type="button" class="me-modal__close" id="asClosePreview" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Cover image --}}
        <div id="asPreviewCoverWrap" class="as-preview-cover">
            <img id="asPreviewCoverImg" src="" alt="" hidden>
            <div id="asPreviewCoverPh" class="as-preview-cover-ph" hidden>
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                <span>No Cover Image</span>
            </div>
        </div>

        <div class="me-modal__body">
            <div id="asPreviewBody" class="as-preview-body"></div>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="asClosePreviewFooter">Close</button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     EDIT MODAL
══════════════════════════════════════════════════════════════════ --}}
<div id="asEditModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="asEditTitle" hidden>
    <div class="me-modal" role="document">

        <div class="me-modal__header">
            <h2 id="asEditTitle" class="me-modal__title">Edit Story</h2>
            <button type="button" class="me-modal__close" id="asCloseEdit" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__body">
            <div id="asEditError" class="me-alert me-alert--danger" hidden></div>

            <div id="asEditForm">
                <input type="hidden" id="asEditStoryId">
                <input type="hidden" id="asUpdateUrl">

                <div class="me-form-group">
                    <label class="me-label" for="asTitle">Title <span class="me-required">*</span></label>
                    <input type="text" id="asTitle" class="me-input" maxlength="255" required>
                    <span class="me-field-error" id="asTitleError"></span>
                </div>

                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="asCategory">Category <span class="me-required">*</span></label>
                        <select id="asCategory" class="me-input me-select" required>
                            @foreach(['Career','Cultural Exchange','Education','Travel','Personal Growth','Community','Other'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="asStatus">Status <span class="me-required">*</span></label>
                        <select id="asStatus" class="me-input me-select" required>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="published">Published</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="me-form-group" id="asRejectionWrap">
                    <label class="me-label" for="asRejectionReason">Rejection Reason <span style="font-weight:400;color:#9ca3af;">(shown to author)</span></label>
                    <textarea id="asRejectionReason" class="me-input" rows="3" maxlength="500" style="resize:vertical;" placeholder="Explain why this story was rejected…"></textarea>
                </div>

                <div class="me-form-group">
                    <label class="me-label" for="asExcerpt">Excerpt</label>
                    <textarea id="asExcerpt" class="me-input" rows="3" maxlength="400" style="resize:vertical;" placeholder="Short summary shown in listings…"></textarea>
                    <span style="font-size:11px;color:#9ca3af;">Max 400 characters</span>
                </div>
            </div>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="asCancelEdit">Cancel</button>
            <button type="button" class="me-btn me-btn--primary" id="asSaveEdit">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Changes
            </button>
        </div>

    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════
     DELETE MODAL
══════════════════════════════════════════════════════════════════ --}}
<div id="asDeleteModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="asDeleteTitle" hidden>
    <div class="me-modal me-modal--sm" role="document">

        <div class="me-modal__header">
            <h2 id="asDeleteTitle" class="me-modal__title">Delete Story</h2>
            <button type="button" class="me-modal__close" id="asCloseDelete" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__body">
            <div class="me-delete-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
            </div>
            <p class="me-delete-text">
                Are you sure you want to permanently delete <strong id="asDeleteStoryTitle"></strong>?
                This action <strong>cannot be undone</strong>.
            </p>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="asCancelDelete">Cancel</button>
            <button type="button" class="me-btn me-btn--destructive" id="asConfirmDelete">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
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
    const $    = id => document.getElementById(id);
    const show = el => el?.removeAttribute('hidden');
    const hide = el => el?.setAttribute('hidden', '');

    // ── Modal helpers ─────────────────────────────────────────────────
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
        backdrop.addEventListener('click', e => { if (e.target === backdrop) closeModal(backdrop); });
    });

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.me-modal-backdrop:not([hidden])').forEach(closeModal);
    });

    // ── Toast ─────────────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const container = $('asToastContainer');
        const toast = document.createElement('div');
        toast.className = `me-toast me-toast--${type}`;
        toast.setAttribute('role', 'alert');
        const icon = type === 'success'
            ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
        toast.innerHTML = `<span class="me-toast__icon">${icon}</span><span class="me-toast__message">${message}</span><button class="me-toast__close" aria-label="Dismiss"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('me-toast--show'));
        const dismiss = () => { toast.classList.remove('me-toast--show'); toast.addEventListener('transitionend', () => toast.remove(), { once: true }); };
        toast.querySelector('.me-toast__close').addEventListener('click', dismiss);
        setTimeout(dismiss, 4500);
    }

    @if(session('success')) showToast(@json(session('success')), 'success'); @endif
    @if(session('error'))   showToast(@json(session('error')),   'error');   @endif

    // ── Helpers ───────────────────────────────────────────────────────
    function setSelect(id, value) {
        const sel = $(id);
        if (!sel || value == null) return;
        const opt = [...sel.options].find(o => o.value === value);
        if (opt) sel.value = opt.value;
    }

    // ── PREVIEW ───────────────────────────────────────────────────────
    const previewModal = $('asPreviewModal');

    document.querySelectorAll('[data-action="preview"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const s = JSON.parse(btn.dataset.story);

            $('asPreviewTitle').textContent    = s.title;
            $('asPreviewCategory').textContent = s.category;
            $('asPreviewMeta').textContent     = `By ${s.author} · ${s.created_at}`;
            $('asPreviewBody').textContent     = s.body;

            const rejectionWrap = $('asPreviewRejectionWrap');
            const rejectionText = $('asPreviewRejectionReason');

            if (s.status === 'rejected' && s.rejection_reason) {
                rejectionText.textContent = s.rejection_reason;
                show(rejectionWrap);
            } else {
                rejectionText.textContent = '';
                hide(rejectionWrap);
            }

            // Status badge
            const statusEl = $('asPreviewStatus');
            statusEl.textContent = s.status.charAt(0).toUpperCase() + s.status.slice(1);
            statusEl.className   = `ae-status ae-status--${s.status}`;

            // Cover image
            const img = $('asPreviewCoverImg'), ph = $('asPreviewCoverPh');
            const wrap = $('asPreviewCoverWrap');
            if (s.cover_image) {
                show(wrap); show(img); hide(ph);
                img.src = s.cover_image;
                img.alt = s.title;
            } else {
                show(wrap); hide(img); show(ph);
            }

            openModal(previewModal);
        });
    });

    $('asClosePreview').addEventListener('click',       () => closeModal(previewModal));
    $('asClosePreviewFooter').addEventListener('click', () => closeModal(previewModal));

    // ── EDIT ──────────────────────────────────────────────────────────
    const editModal = $('asEditModal');

    function toggleRejectionField() {
        const wrap = $('asRejectionWrap');
        if ($('asStatus').value === 'rejected') {
            show(wrap);
        } else {
            hide(wrap);
        }
    }

    $('asStatus').addEventListener('change', toggleRejectionField);

    document.querySelectorAll('.ae-btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            $('asEditStoryId').value    = btn.dataset.storyId;
            $('asUpdateUrl').value      = btn.dataset.updateUrl;
            $('asTitle').value          = btn.dataset.storyTitle       ?? '';
            $('asExcerpt').value        = btn.dataset.storyExcerpt     ?? '';
            $('asRejectionReason').value = btn.dataset.storyRejectionReason ?? '';

            setSelect('asCategory', btn.dataset.storyCategory);
            setSelect('asStatus',   btn.dataset.storyStatus);

            toggleRejectionField();

            // Reset errors
            hide($('asEditError'));
            document.querySelectorAll('#asEditForm .me-field-error')
                    .forEach(el => el.textContent = '');
            document.querySelectorAll('#asEditForm .me-input')
                    .forEach(el => el.classList.remove('me-input--error'));

            openModal(editModal);
        });
    });

    $('asCloseEdit').addEventListener('click',  () => closeModal(editModal));
    $('asCancelEdit').addEventListener('click', () => closeModal(editModal));

    $('asSaveEdit').addEventListener('click', async () => {
        const saveBtn = $('asSaveEdit');

        // Validate
        let valid = true;
        const title = $('asTitle').value.trim();
        if (!title) {
            $('asTitleError').textContent = 'Title is required.';
            $('asTitle').classList.add('me-input--error');
            valid = false;
        } else {
            $('asTitleError').textContent = '';
            $('asTitle').classList.remove('me-input--error');
        }
        if (!valid) return;

        saveBtn.disabled = true;
        const orig = saveBtn.innerHTML;
        saveBtn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:me-spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Saving…`;

        try {
            const res = await fetch($('asUpdateUrl').value, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    _method           : 'PUT',
                    title             : $('asTitle').value.trim(),
                    category          : $('asCategory').value,
                    status            : $('asStatus').value,
                    excerpt           : $('asExcerpt').value.trim() || null,
                    rejection_reason  : $('asStatus').value === 'rejected' ? ($('asRejectionReason').value.trim() || null) : null,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                const msg = data.errors
                    ? Object.values(data.errors).map(e => e[0]).join(' ')
                    : (data.message ?? 'Something went wrong.');
                $('asEditError').textContent = msg;
                show($('asEditError'));
                return;
            }

            closeModal(editModal);
            showToast('Story updated successfully!', 'success');

            // Live row update
            const storyId = $('asEditStoryId').value;
            const row     = document.querySelector(`tr[data-story-id="${storyId}"]`);
            if (row) {
                row.querySelector('.ae-event-title').textContent = $('asTitle').value.trim();
                const statusEl  = row.querySelector('.ae-status');
                const newStatus = $('asStatus').value;
                statusEl.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                statusEl.className   = `ae-status ae-status--${newStatus}`;
                const tagEl = row.querySelector('.as-tag');
                if (tagEl) tagEl.textContent = $('asCategory').value;
            }

        } catch {
            $('asEditError').textContent = 'Network error. Please try again.';
            show($('asEditError'));
        } finally {
            saveBtn.disabled  = false;
            saveBtn.innerHTML = orig;
        }
    });

    // ── DELETE ────────────────────────────────────────────────────────
    const deleteModal    = $('asDeleteModal');
    let pendingDeleteUrl = '';
    let pendingDeleteId  = null;

    document.querySelectorAll('.ae-btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            $('asDeleteStoryTitle').textContent = btn.dataset.storyTitle;
            pendingDeleteUrl = btn.dataset.deleteUrl;
            pendingDeleteId  = btn.dataset.storyId;
            openModal(deleteModal);
        });
    });

    $('asCloseDelete').addEventListener('click',  () => closeModal(deleteModal));
    $('asCancelDelete').addEventListener('click', () => closeModal(deleteModal));

    $('asConfirmDelete').addEventListener('click', async () => {
        const confirmBtn = $('asConfirmDelete');
        confirmBtn.disabled = true;
        const orig = confirmBtn.innerHTML;
        confirmBtn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:me-spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Deleting…`;

        try {
            const res = await fetch(pendingDeleteUrl, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            });
            const data = await res.json();

            if (!res.ok) {
                closeModal(deleteModal);
                showToast(data.message ?? 'Could not delete story.', 'error');
                return;
            }

            const row = document.querySelector(`tr[data-story-id="${pendingDeleteId}"]`);
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity    = '0';
                setTimeout(() => row.remove(), 300);
            }

            closeModal(deleteModal);
            showToast('Story permanently deleted.', 'success');

        } catch {
            closeModal(deleteModal);
            showToast('Network error. Please try again.', 'error');
        } finally {
            confirmBtn.disabled  = false;
            confirmBtn.innerHTML = orig;
            pendingDeleteId      = null;
        }
    });

})();
</script>
@endpush