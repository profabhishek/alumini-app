@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'My Stories')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/stories/stories.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="st-page-header">
    <div class="st-page-header__left">
        <h1 class="st-page-title">My Stories</h1>
        <span class="st-page-count">{{ $stories->total() }} {{ Str::plural('story', $stories->total()) }}</span>
    </div>
    <a href="{{ route('stories.create') }}" class="st-btn st-btn--primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create Story
    </a>
</div>

{{-- Stats strip --}}
<div class="st-stats">
    <div class="st-stat">
        <span class="st-stat__number">{{ $stats['total'] }}</span>
        <span class="st-stat__label">Total</span>
    </div>
    <div class="st-stat">
        <span class="st-stat__number" style="color:var(--st-amber-600);">{{ $stats['pending'] }}</span>
        <span class="st-stat__label">Pending</span>
    </div>
    <div class="st-stat">
        <span class="st-stat__number" style="color:var(--st-green-600);">{{ $stats['published'] }}</span>
        <span class="st-stat__label">Published</span>
    </div>
    <div class="st-stat">
        <span class="st-stat__number" style="color:var(--st-red-600);">{{ $stats['rejected'] }}</span>
        <span class="st-stat__label">Rejected</span>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('stories.my') }}" style="display:contents;">
    <div class="st-filters">
        <a href="{{ route('stories.my') }}"
           class="st-filter-btn {{ !request('status') ? 'active' : '' }}">All</a>
        <a href="{{ route('stories.my', ['status' => 'pending']) }}"
           class="st-filter-btn {{ request('status') === 'pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('stories.my', ['status' => 'published']) }}"
           class="st-filter-btn {{ request('status') === 'published' ? 'active' : '' }}">Published</a>
        <a href="{{ route('stories.my', ['status' => 'rejected']) }}"
           class="st-filter-btn {{ request('status') === 'rejected' ? 'active' : '' }}">Rejected</a>
        <input type="text" name="q" value="{{ request('q') }}"
               class="st-search-input" placeholder="Search stories…">
    </div>
</form>

{{-- Stories Grid --}}
<div class="st-grid">

    @forelse($stories as $story)

        @php
            $storyData = e(json_encode([
                'id'           => $story->id,
                'title'        => $story->title,
                'category'     => $story->category,
                'status'       => $story->status,
                'excerpt'      => $story->excerpt,
                'body'         => $story->body,
                'cover_image'  => $story->cover_image ? asset('storage/'.$story->cover_image) : null,
                'rejection_reason' => $story->rejection_reason,
                'created_at'   => $story->created_at->format('d M Y'),
                'update_url'   => route('stories.update', $story->id),
                'delete_url'   => route('stories.destroy', $story->id),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        @endphp

        <article class="story-card" data-story-id="{{ $story->id }}">

            {{-- Cover --}}
            <div class="story-card__cover">
                @if($story->cover_image)
                    <img src="{{ asset('storage/'.$story->cover_image) }}"
                         alt="{{ $story->title }}" loading="lazy">
                @else
                    <div class="story-card__cover-placeholder">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                        <span>No Cover</span>
                    </div>
                @endif
                <span class="story-card__status story-card__status--{{ $story->status }}">
                    {{ ucfirst($story->status) }}
                </span>
                
                @if(isset($updatedStoryIds) && in_array($story->id, $updatedStoryIds))
                    <span class="event-card__new-badge" data-for-story="{{ $story->id }}">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                        updated
                    </span>
                @endif
            </div>

            {{-- Body --}}
            <div class="story-card__body">
                <span class="story-card__category">{{ $story->category }}</span>
                <h2 class="story-card__title" title="{{ $story->title }}">{{ $story->title }}</h2>
                <p class="story-card__excerpt">{{ $story->excerpt }}</p>
                <div class="story-card__meta">
                    <span>{{ $story->created_at->format('d M Y') }}</span>
                </div>
            </div>

            {{-- Rejection reason --}}
            @if($story->status === 'rejected' && $story->rejection_reason)
                <div class="story-card__rejection">
                    <strong>Rejected:</strong> {{ $story->rejection_reason }}
                </div>
            @endif

            {{-- Footer Actions --}}
            <div class="story-card__footer">
                <button type="button" class="st-btn st-btn--ghost" data-action="view"
                        data-story="{!! $storyData !!}"
                        aria-label="View {{ $story->title }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>

                @if($story->status !== 'published')
                <button type="button" class="st-btn st-btn--ghost" data-action="edit"
                        data-story="{!! $storyData !!}"
                        aria-label="Edit {{ $story->title }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                @endif

                <button type="button" class="st-btn st-btn--ghost st-btn--danger" data-action="delete"
                        data-story-id="{{ $story->id }}"
                        data-story-title="{{ $story->title }}"
                        data-delete-url="{{ route('stories.destroy', $story->id) }}"
                        aria-label="Delete {{ $story->title }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </div>

        </article>

    @empty

        <div class="st-empty">
            <div class="st-empty__icon" aria-hidden="true">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
            </div>
            <h3 class="st-empty__title">No stories yet</h3>
            <p class="st-empty__text">Share your journey with the ICCR alumni community by writing your first story.</p>
            <a href="{{ route('stories.create') }}" class="st-btn st-btn--primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Story
            </a>
        </div>

    @endforelse

</div>

{{-- Pagination --}}
@if($stories->hasPages())
    <div class="st-pagination">{{ $stories->links() }}</div>
@endif


{{-- Toast container --}}
<div id="stToastContainer" class="st-toast-container" aria-live="polite" aria-atomic="true"></div>


{{-- ============================================================
     VIEW MODAL
============================================================ --}}
<div id="storyViewModal" class="st-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="stViewTitle" hidden>
    <div class="st-modal" role="document">

        <div class="st-modal__header">
            <div style="display:flex;flex-direction:column;gap:8px;min-width:0;">
                <span id="stViewBadge" class="story-card__status"></span>
                <h2 id="stViewTitle" class="st-modal__title"></h2>
            </div>
            <button type="button" class="st-modal__close" id="closeViewModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="st-modal__cover" id="stViewCoverWrap">
            <img id="stViewCoverImg" src="" alt="" hidden>
            <div id="stViewCoverPlaceholder" class="st-modal__cover-placeholder" hidden>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                <span>No Cover Image</span>
            </div>
        </div>

        <div class="st-modal__body">
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--st-gray-400);margin-bottom:4px;">Category</div>
                    <div id="stViewCategory" style="font-size:14.5px;color:var(--st-gray-800);font-weight:500;"></div>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--st-gray-400);margin-bottom:4px;">Submitted</div>
                    <div id="stViewDate" style="font-size:14.5px;color:var(--st-gray-800);font-weight:500;"></div>
                </div>
            </div>

            <div id="stViewRejectionWrap" hidden>
                <div class="st-alert st-alert--danger" id="stViewRejection"></div>
            </div>

            <div>
                <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--st-gray-400);margin-bottom:12px;">Story</div>
                <div id="stViewBody" style="font-size:14.5px;color:var(--st-gray-600);line-height:1.75;white-space:pre-wrap;"></div>
            </div>
        </div>

        <div class="st-modal__footer">
            <button type="button" class="st-btn st-btn--outline" id="closeViewModalFooter">Close</button>
            <button type="button" class="st-btn st-btn--primary" id="viewToEditBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Story
            </button>
        </div>

    </div>
</div>


{{-- ============================================================
     EDIT MODAL
============================================================ --}}
<div id="storyEditModal" class="st-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="stEditTitle" hidden>
    <div class="st-modal st-modal--lg" role="document">

        <div class="st-modal__header">
            <h2 id="stEditTitle" class="st-modal__title">Edit Story</h2>
            <button type="button" class="st-modal__close" id="closeEditModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="st-modal__body">
            <div id="editErrorBanner" class="st-alert st-alert--danger" hidden></div>

            <form id="editStoryForm" novalidate>
                @csrf
                <input type="hidden" id="editStoryId" name="_story_id">
                <input type="hidden" id="editUpdateUrl" name="_update_url">

                <div class="st-form-row">
                    <div class="st-form-group">
                        <label class="st-label" for="editTitle">Title <span class="st-required">*</span></label>
                        <input type="text" id="editTitle" name="title" class="st-input" required maxlength="255">
                        <span class="st-field-error" id="editTitleError"></span>
                    </div>
                    <div class="st-form-group">
                        <label class="st-label" for="editCategory">Category <span class="st-required">*</span></label>
                        <select id="editCategory" name="category" class="st-input st-select" required>
                            @foreach(['Career', 'Cultural Exchange', 'Education', 'Entrepreneurship', 'Research', 'Social Impact', 'Other'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="st-form-group">
                    <label class="st-label" for="editExcerpt">Short Excerpt</label>
                    <input type="text" id="editExcerpt" name="excerpt" class="st-input" maxlength="400" placeholder="Auto-generated if blank">
                </div>

                <div class="st-form-group">
                    <label class="st-label">Cover Image</label>
                    <div>
                        <div class="st-file-preview" id="editCoverPreview" hidden>
                            <img id="editCoverPreviewImg" src="" alt="Cover preview">
                            <button type="button" class="st-file-remove" id="editCoverRemove" aria-label="Remove cover">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                        <label class="st-file-label" for="editCoverImage" id="editCoverLabel">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span id="editCoverLabelText">Click to upload or drag & drop</span>
                            <span class="st-file-hint">JPG, PNG, WebP — max 5 MB</span>
                        </label>
                        <input type="file" id="editCoverImage" name="cover_image" class="st-file-input" accept="image/jpg,image/jpeg,image/png,image/webp">
                    </div>
                </div>

                <div class="st-form-group">
                    <label class="st-label" for="editBody">Your Story <span class="st-required">*</span></label>
                    <textarea id="editBody" name="body" class="st-input st-textarea" rows="12" required placeholder="Min 100 characters…"></textarea>
                    <span class="st-field-error" id="editBodyError"></span>
                </div>

                <div class="st-alert" style="background:var(--st-amber-50);border:1px solid var(--st-amber-100);color:var(--st-amber-700);font-size:13px;">
                    ⚠️ Saving will re-submit this story for admin approval.
                </div>
            </form>
        </div>

        <div class="st-modal__footer">
            <button type="button" class="st-btn st-btn--outline" id="cancelEditBtn">Cancel</button>
            <button type="button" class="st-btn st-btn--primary" id="saveEditBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save & Resubmit
            </button>
        </div>
    </div>
</div>


{{-- ============================================================
     DELETE MODAL
============================================================ --}}
<div id="storyDeleteModal" class="st-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="stDeleteTitle" hidden>
    <div class="st-modal st-modal--sm" role="document">
        <div class="st-modal__header">
            <h2 id="stDeleteTitle" class="st-modal__title">Delete Story</h2>
            <button type="button" class="st-modal__close" id="closeDeleteModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="st-modal__body">
            <div class="st-delete-icon" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </div>
            <p class="st-delete-text">
                Are you sure you want to delete <strong id="deleteStoryTitle"></strong>?
                This action <strong>cannot be undone</strong>.
            </p>
        </div>
        <div class="st-modal__footer">
            <button type="button" class="st-btn st-btn--outline" id="cancelDeleteBtn">Cancel</button>
            <button type="button" class="st-btn st-btn--destructive" id="confirmDeleteBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
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
    const $  = id => document.getElementById(id);
    const show = el => el?.removeAttribute('hidden');
    const hide = el => el?.setAttribute('hidden', '');

    // ── Focus trap + modal helpers ────────────────────────────────────────
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
    document.querySelectorAll('.st-modal-backdrop').forEach(b => {
        b.addEventListener('click', e => { if (e.target === b) closeModal(b); });
    });
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.st-modal-backdrop:not([hidden])').forEach(closeModal);
    });

    // ── Toast ─────────────────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const c = $('stToastContainer');
        const t = document.createElement('div');
        t.className = `st-toast st-toast--${type}`;
        t.setAttribute('role', 'alert');
        const icon = type === 'success'
            ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
        t.innerHTML = `<span class="st-toast__icon">${icon}</span><span class="st-toast__message">${message}</span><button class="st-toast__close" aria-label="Dismiss"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
        c.appendChild(t);
        requestAnimationFrame(() => t.classList.add('st-toast--show'));
        const dismiss = () => { t.classList.remove('st-toast--show'); t.addEventListener('transitionend', () => t.remove(), {once:true}); };
        t.querySelector('.st-toast__close').addEventListener('click', dismiss);
        setTimeout(dismiss, 4500);
    }

    @if(session('success')) showToast(@json(session('success')), 'success'); @endif
    @if(session('error'))   showToast(@json(session('error')),   'error');   @endif

    // ── VIEW MODAL ────────────────────────────────────────────────────────
    const viewModal = $('storyViewModal');
    let currentViewStory = null;

    document.querySelectorAll('[data-action="view"]').forEach(btn => {
        btn.addEventListener('click', () => populateView(JSON.parse(btn.dataset.story)));
    });

    function populateView(s) {
        currentViewStory = s;

        const badge = $('stViewBadge');
        badge.textContent = s.status.charAt(0).toUpperCase() + s.status.slice(1);
        badge.className   = `story-card__status story-card__status--${s.status}`;

        $('stViewTitle').textContent    = s.title;
        $('stViewCategory').textContent = s.category;
        $('stViewDate').textContent     = s.created_at;
        $('stViewBody').textContent     = s.body;

        const coverImg = $('stViewCoverImg');
        const coverPh  = $('stViewCoverPlaceholder');
        if (s.cover_image) { show(coverImg); hide(coverPh); coverImg.src = s.cover_image; coverImg.alt = s.title; }
        else               { hide(coverImg); show(coverPh); }

        if (s.status === 'rejected' && s.rejection_reason) {
            $('stViewRejection').textContent = '❌ Reason: ' + s.rejection_reason;
            show($('stViewRejectionWrap'));
        } else { hide($('stViewRejectionWrap')); }

        // Hide edit btn if published
        $('viewToEditBtn').style.display = s.status === 'published' ? 'none' : '';

        openModal(viewModal);
    }

    $('closeViewModal').addEventListener('click',       () => closeModal(viewModal));
    $('closeViewModalFooter').addEventListener('click', () => closeModal(viewModal));
    $('viewToEditBtn').addEventListener('click', () => {
        closeModal(viewModal);
        if (currentViewStory) openEditModal(currentViewStory);
    });

    // ── EDIT MODAL ────────────────────────────────────────────────────────
    const editModal = $('storyEditModal');

    document.querySelectorAll('[data-action="edit"]').forEach(btn => {
        btn.addEventListener('click', () => openEditModal(JSON.parse(btn.dataset.story)));
    });

    function openEditModal(s) {
        hide($('editErrorBanner'));
        document.querySelectorAll('.st-field-error').forEach(el => el.textContent = '');
        document.querySelectorAll('.st-input').forEach(el => el.classList.remove('st-input--error'));

        $('editStoryId').value    = s.id;
        $('editUpdateUrl').value  = s.update_url;
        $('editTitle').value      = s.title    ?? '';
        $('editExcerpt').value    = s.excerpt  ?? '';
        $('editBody').value       = s.body     ?? '';

        // Category select
        const sel = $('editCategory');
        [...sel.options].forEach(o => { o.selected = o.value === s.category; });

        // Cover preview
        const preview = $('editCoverPreview');
        const label   = $('editCoverLabel');
        $('editCoverImage').value = '';
        if (s.cover_image) {
            $('editCoverPreviewImg').src = s.cover_image;
            show(preview); hide(label);
        } else { hide(preview); show(label); }

        openModal(editModal);
    }

    // Cover image input
    $('editCoverImage').addEventListener('change', function () {
        if (!this.files[0]) return;
        const r = new FileReader();
        r.onload = e => { $('editCoverPreviewImg').src = e.target.result; show($('editCoverPreview')); hide($('editCoverLabel')); };
        r.readAsDataURL(this.files[0]);
    });
    $('editCoverRemove').addEventListener('click', () => {
        $('editCoverImage').value = '';
        $('editCoverPreviewImg').src = '';
        hide($('editCoverPreview')); show($('editCoverLabel'));
    });

    $('closeEditModal').addEventListener('click', () => closeModal(editModal));
    $('cancelEditBtn').addEventListener('click',  () => closeModal(editModal));

    $('saveEditBtn').addEventListener('click', async () => {
        const btn = $('saveEditBtn');
        let valid = true;
        [['editTitle','editTitleError','Title is required.'],['editBody','editBodyError','Story body is required (min 100 chars).']]
            .forEach(([id, errId, msg]) => {
                const el = $(id); const err = $(errId);
                if (!el.value.trim() || (id === 'editBody' && el.value.trim().length < 100)) {
                    el.classList.add('st-input--error'); err.textContent = msg; valid = false;
                } else { el.classList.remove('st-input--error'); err.textContent = ''; }
            });
        if (!valid) return;

        const fd = new FormData();
        fd.append('_method',  'PUT');
        fd.append('_token',   CSRF);
        fd.append('title',    $('editTitle').value.trim());
        fd.append('category', $('editCategory').value);
        fd.append('excerpt',  $('editExcerpt').value.trim());
        fd.append('body',     $('editBody').value.trim());
        const file = $('editCoverImage').files[0];
        if (file) fd.append('cover_image', file);

        btn.disabled = true;
        const orig = btn.innerHTML;
        btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:st-spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Saving…`;

        try {
            const res  = await fetch($('editUpdateUrl').value, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}, body:fd });
            const data = await res.json();
            if (!res.ok) { $('editErrorBanner').textContent = data.message ?? data.error ?? 'Error saving.'; show($('editErrorBanner')); return; }
            closeModal(editModal);
            showToast('Story updated and re-submitted for approval!', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } catch { $('editErrorBanner').textContent = 'Network error. Please try again.'; show($('editErrorBanner')); }
        finally   { btn.disabled = false; btn.innerHTML = orig; }
    });

    // ── DELETE MODAL ──────────────────────────────────────────────────────
    const deleteModal = $('storyDeleteModal');
    let pendingDeleteUrl = '', pendingDeleteId = null;

    document.querySelectorAll('[data-action="delete"]').forEach(btn => {
        btn.addEventListener('click', () => {
            $('deleteStoryTitle').textContent = btn.dataset.storyTitle;
            pendingDeleteUrl = btn.dataset.deleteUrl;
            pendingDeleteId  = btn.dataset.storyId;
            openModal(deleteModal);
        });
    });

    $('closeDeleteModal').addEventListener('click', () => closeModal(deleteModal));
    $('cancelDeleteBtn').addEventListener('click',  () => closeModal(deleteModal));

    $('confirmDeleteBtn').addEventListener('click', async () => {
        const btn = $('confirmDeleteBtn');
        btn.disabled = true;
        btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:st-spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Deleting…`;
        try {
            const res  = await fetch(pendingDeleteUrl, {method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}});
            const data = await res.json();
            if (!res.ok) { closeModal(deleteModal); showToast(data.message ?? 'Could not delete.', 'error'); return; }
            const card = document.querySelector(`[data-story-id="${pendingDeleteId}"]`);
            if (card) { card.style.transition = 'opacity .3s,transform .3s'; card.style.opacity='0'; card.style.transform='scale(.95)'; setTimeout(()=>card.remove(),300); }
            closeModal(deleteModal);
            showToast('Story deleted successfully.', 'success');
        } catch { closeModal(deleteModal); showToast('Network error. Please try again.', 'error'); }
        finally { btn.disabled = false; btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg> Yes, Delete`; }
    });

})();
</script>
@endpush