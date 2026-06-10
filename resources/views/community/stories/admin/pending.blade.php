@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Pending Stories')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/stories/stories.css') }}">
@endpush

@section('content')

<div class="st-page-header">
    <div class="st-page-header__left">
        <h1 class="st-page-title">Pending Stories</h1>
        <span class="st-page-count">{{ $stories->total() }} awaiting review</span>
    </div>
    <a href="{{ route('admin.stories.index') }}" class="st-btn st-btn--outline">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
        All Stories
    </a>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('admin.stories.pending') }}" style="margin-bottom:20px;">
    <div class="st-filters">
        <input type="text" name="q" value="{{ request('q') }}"
               class="st-search-input" placeholder="Search by title…">
        <button type="submit" class="st-btn st-btn--outline">Search</button>
        @if(request('q'))
            <a href="{{ route('admin.stories.pending') }}" class="st-btn st-btn--ghost">Clear</a>
        @endif
    </div>
</form>

@if($stories->isEmpty())

    <div class="st-table-wrap" style="padding:64px 24px;text-align:center;">
        <div style="color:var(--st-gray-300);margin-bottom:16px;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
        </div>
        <p style="font-size:15px;color:var(--st-gray-500);margin:0;">No pending stories right now. All caught up! 🎉</p>
    </div>

@else

    <div class="st-table-wrap">
        <table class="st-table">
            <thead>
                <tr>
                    <th style="width:56px;"></th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stories as $story)
                <tr data-story-row="{{ $story->id }}">
                    <td>
                        @if($story->cover_image)
                            <img src="{{ asset('storage/'.$story->cover_image) }}"
                                 alt="" class="st-table__thumb">
                        @else
                            <div class="st-table__thumb-placeholder">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600;color:var(--st-gray-900);margin-bottom:4px;">{{ $story->title }}</div>
                        @if($story->excerpt)
                            <div style="font-size:12.5px;color:var(--st-gray-400);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;max-width:320px;">{{ $story->excerpt }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="background:var(--st-indigo-50);color:var(--st-indigo-600);padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;">
                            {{ $story->category }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:500;">{{ $story->creator->full_name ?? '—' }}</div>
                        <div style="font-size:12px;color:var(--st-gray-400);">{{ $story->creator->email ?? '' }}</div>
                    </td>
                    <td style="white-space:nowrap;color:var(--st-gray-500);font-size:13px;">
                        {{ $story->created_at->format('d M Y') }}<br>
                        <span style="font-size:12px;">{{ $story->created_at->diffForHumans() }}</span>
                    </td>
                    <td>
                        <div class="st-table__actions">
                            {{-- Preview --}}
                            <button type="button" class="st-btn st-btn--ghost" title="Preview"
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
                                    ]) }}'>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            {{-- Approve --}}
                            <button type="button" class="st-btn st-btn--approve" style="font-size:12.5px;padding:6px 12px;"
                                    data-action="approve"
                                    data-story-id="{{ $story->id }}"
                                    data-approve-url="{{ route('admin.stories.approve', $story->id) }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                Approve
                            </button>
                            {{-- Reject --}}
                            <button type="button" class="st-btn st-btn--ghost st-btn--danger" style="font-size:12.5px;padding:6px 12px;"
                                    data-action="reject"
                                    data-story-id="{{ $story->id }}"
                                    data-story-title="{{ $story->title }}"
                                    data-reject-url="{{ route('admin.stories.reject', $story->id) }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Reject
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($stories->hasPages())
        <div class="st-pagination">{{ $stories->links() }}</div>
    @endif

@endif

{{-- Toast --}}
<div id="stToastContainer" class="st-toast-container" aria-live="polite"></div>

{{-- ============================================================
     PREVIEW MODAL
============================================================ --}}
<div id="previewModal" class="st-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="previewTitle" hidden>
    <div class="st-modal st-modal--lg" role="document">
        <div class="st-modal__header">
            <div style="display:flex;flex-direction:column;gap:6px;min-width:0;">
                <span id="previewCategory" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--st-indigo-600);"></span>
                <h2 id="previewTitle" class="st-modal__title"></h2>
                <span id="previewMeta" style="font-size:12.5px;color:var(--st-gray-400);"></span>
            </div>
            <button type="button" class="st-modal__close" id="closePreviewModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="st-modal__cover" id="previewCoverWrap">
            <img id="previewCoverImg" src="" alt="" hidden>
            <div id="previewCoverPh" class="st-modal__cover-placeholder" hidden>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                <span>No Cover</span>
            </div>
        </div>
        <div class="st-modal__body">
            <div id="previewBody" style="font-size:14.5px;color:var(--st-gray-600);line-height:1.75;white-space:pre-wrap;"></div>
        </div>
        <div class="st-modal__footer">
            <button type="button" class="st-btn st-btn--outline" id="closePreviewFooter">Close</button>
        </div>
    </div>
</div>

{{-- ============================================================
     REJECT MODAL
============================================================ --}}
<div id="rejectModal" class="st-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="rejectModalTitle" hidden>
    <div class="st-modal st-modal--sm" role="document">
        <div class="st-modal__header">
            <h2 id="rejectModalTitle" class="st-modal__title">Reject Story</h2>
            <button type="button" class="st-modal__close" id="closeRejectModal" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="st-modal__body">
            <p style="font-size:14px;color:var(--st-gray-600);margin:0 0 4px;">
                Rejecting: <strong id="rejectStoryTitle"></strong>
            </p>
            <p style="font-size:13px;color:var(--st-gray-400);margin:0 0 2px;">Reason <span style="font-weight:400;">(optional — shown to author)</span></p>
            <textarea id="rejectReason" class="st-reject-reason" placeholder="Explain why this story was rejected…" maxlength="500"></textarea>
        </div>
        <div class="st-modal__footer">
            <button type="button" class="st-btn st-btn--outline" id="cancelRejectBtn">Cancel</button>
            <button type="button" class="st-btn st-btn--destructive" id="confirmRejectBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Confirm Reject
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

    let lastFocused;
    function openModal(m)  { lastFocused = document.activeElement; show(m); document.body.style.overflow='hidden'; setTimeout(()=>m.querySelector('button,input,textarea,a')?.focus(),50); }
    function closeModal(m) { hide(m); document.body.style.overflow=''; lastFocused?.focus(); }
    document.querySelectorAll('.st-modal-backdrop').forEach(b => b.addEventListener('click', e => { if(e.target===b) closeModal(b); }));
    document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.st-modal-backdrop:not([hidden])').forEach(closeModal); });

    // Toast
    function showToast(msg, type='success') {
        const c=document.getElementById('stToastContainer');
        const t=document.createElement('div');
        t.className=`st-toast st-toast--${type}`;
        t.setAttribute('role','alert');
        const icon=type==='success'?'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>':'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        t.innerHTML=`<span class="st-toast__icon">${icon}</span><span class="st-toast__message">${msg}</span><button class="st-toast__close" aria-label="Dismiss"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
        c.appendChild(t);
        requestAnimationFrame(()=>t.classList.add('st-toast--show'));
        const d=()=>{t.classList.remove('st-toast--show');t.addEventListener('transitionend',()=>t.remove(),{once:true});};
        t.querySelector('.st-toast__close').addEventListener('click',d);
        setTimeout(d,4500);
    }

    @if(session('success')) showToast(@json(session('success')),'success'); @endif
    @if(session('error'))   showToast(@json(session('error')),  'error');   @endif

    // ── PREVIEW ───────────────────────────────────────────────────────────
    const previewModal = $('previewModal');
    document.querySelectorAll('[data-action="preview"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const s = JSON.parse(btn.dataset.story);
            $('previewTitle').textContent    = s.title;
            $('previewCategory').textContent = s.category;
            $('previewMeta').textContent     = `By ${s.author} · ${s.created_at}`;
            $('previewBody').textContent     = s.body;
            const img = $('previewCoverImg'), ph = $('previewCoverPh');
            if (s.cover_image) { show(img); hide(ph); img.src=s.cover_image; img.alt=s.title; }
            else { hide(img); show(ph); }
            openModal(previewModal);
        });
    });
    $('closePreviewModal').addEventListener('click',  () => closeModal(previewModal));
    $('closePreviewFooter').addEventListener('click', () => closeModal(previewModal));

    // ── APPROVE ───────────────────────────────────────────────────────────
    document.querySelectorAll('[data-action="approve"]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id  = btn.dataset.storyId;
            const url = btn.dataset.approveUrl;
            btn.disabled = true;
            btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:st-spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>`;

            try {
                const res  = await fetch(url, { method:'PATCH', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'} });
                const data = await res.json();
                if (!res.ok) { showToast(data.error ?? 'Could not approve.', 'error'); btn.disabled=false; btn.innerHTML=`<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Approve`; return; }

                // Remove row
                const row = document.querySelector(`[data-story-row="${id}"]`);
                if (row) { row.style.transition='opacity .3s'; row.style.opacity='0'; setTimeout(()=>row.remove(),300); }
                showToast('Story approved and published!', 'success');
            } catch { showToast('Network error.', 'error'); btn.disabled=false; }
        });
    });

    // ── REJECT ────────────────────────────────────────────────────────────
    const rejectModal = $('rejectModal');
    let pendingRejectUrl = '', pendingRejectId = null;

    document.querySelectorAll('[data-action="reject"]').forEach(btn => {
        btn.addEventListener('click', () => {
            $('rejectStoryTitle').textContent = btn.dataset.storyTitle;
            $('rejectReason').value           = '';
            pendingRejectUrl = btn.dataset.rejectUrl;
            pendingRejectId  = btn.dataset.storyId;
            openModal(rejectModal);
        });
    });

    $('closeRejectModal').addEventListener('click', () => closeModal(rejectModal));
    $('cancelRejectBtn').addEventListener('click',  () => closeModal(rejectModal));

    $('confirmRejectBtn').addEventListener('click', async () => {
        const btn = $('confirmRejectBtn');
        btn.disabled = true;
        btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:st-spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Rejecting…`;

        try {
            const res  = await fetch(pendingRejectUrl, {
                method: 'PATCH',
                headers: {'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},
                body: JSON.stringify({ reason: $('rejectReason').value.trim() || null })
            });
            const data = await res.json();
            if (!res.ok) { showToast(data.error ?? 'Could not reject.', 'error'); return; }

            const row = document.querySelector(`[data-story-row="${pendingRejectId}"]`);
            if (row) { row.style.transition='opacity .3s'; row.style.opacity='0'; setTimeout(()=>row.remove(),300); }
            closeModal(rejectModal);
            showToast('Story rejected.', 'success');
        } catch { showToast('Network error.', 'error'); }
        finally { btn.disabled = false; btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Confirm Reject`; }
    });
})();
</script>
@endpush