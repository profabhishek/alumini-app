@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Image Gallery')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('css/community/admin-content.css') }}?v=1">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Image Gallery</h1>
            <p class="admin-page-subtitle">{{ $items->count() }} images &mdash; shown on the homepage gallery section.</p>
        </div>
        <a href="{{ route('admin.gallery.create') }}" class="admin-link-btn admin-link-btn--primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Image
        </a>
    </div>

    @foreach(['success','error'] as $msg)
        @if(session($msg))<div class="admin-alert admin-alert--{{ $msg }}">{{ session($msg) }}</div>@endif
    @endforeach

    @if($items->isEmpty())
        <div class="admin-empty-state">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 00-2.828 0L6 21"/></svg>
            <p>No gallery images yet.</p>
            <span>Click "Add Image" to upload the first photo.</span>
        </div>
    @else
        <p class="admin-form-hint" style="margin-bottom:14px;">The first published image appears as the large "wide" tile on the homepage; the rest fill the grid in order. Use the arrows to reorder.</p>

        <div class="admin-gallery-grid">
            @foreach($items as $i => $item)
                <div class="admin-gallery-card">
                    <div class="admin-gallery-card__img">
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                        @if($i === 0)
                            <span class="admin-gallery-card__badge">Wide tile</span>
                        @endif
                    </div>
                    <div class="admin-gallery-card__body">
                        <div class="admin-gallery-card__title">{{ $item->title ?: 'Untitled' }}</div>

                        <button type="button" class="admin-pub-badge admin-pub-badge--{{ $item->status }}"
                                style="margin-bottom:8px;"
                                onclick="document.getElementById('toggle-{{ $item->id }}').submit()">
                            <span class="admin-pub-badge__dot"></span>
                            <span class="admin-pub-badge__label">{{ ucfirst($item->status) }}</span>
                        </button>
                        <form id="toggle-{{ $item->id }}" action="{{ route('admin.gallery.toggle-status', $item) }}" method="POST" hidden>
                            @csrf @method('PATCH')
                        </form>

                        <div class="admin-table__actions">
                            <form action="{{ route('admin.gallery.move-up', $item) }}" method="POST">
                                @csrf
                                <button type="submit" class="admin-icon-btn" title="Move up" {{ $i === 0 ? 'disabled' : '' }}>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5,12 12,5 19,12"/></svg>
                                </button>
                            </form>
                            <form action="{{ route('admin.gallery.move-down', $item) }}" method="POST">
                                @csrf
                                <button type="submit" class="admin-icon-btn" title="Move down" {{ $i === $items->count() - 1 ? 'disabled' : '' }}>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="5,12 12,19 19,12"/></svg>
                                </button>
                            </form>
                            <a href="{{ route('admin.gallery.edit', $item) }}" class="admin-btn admin-btn--edit">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </a>
                            <form action="{{ route('admin.gallery.destroy', $item) }}" method="POST"
                                  data-confirm="Delete this image? This cannot be undone."
                                  data-confirm-ok="Delete">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn--reject">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Confirm Modal --}}
<div class="admin-modal-backdrop" id="adminConfirmModal" hidden>
    <div class="admin-modal">
        <div class="admin-modal__header">
            <h3>Confirm Action</h3>
            <button type="button" class="admin-modal__close" id="adminConfirmClose">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="admin-modal__body">
            <p id="adminConfirmMessage" style="margin:0 0 1.5rem;"></p>
            <div style="display:flex;gap:.625rem;justify-content:flex-end;">
                <button type="button" class="admin-btn admin-btn--ghost" id="adminConfirmCancel">Cancel</button>
                <button type="button" class="admin-btn admin-btn--reject" id="adminConfirmOk">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modal    = document.getElementById('adminConfirmModal');
    var msgEl    = document.getElementById('adminConfirmMessage');
    var okBtn    = document.getElementById('adminConfirmOk');
    var closeEl  = document.getElementById('adminConfirmClose');
    var cancelEl = document.getElementById('adminConfirmCancel');
    var _resolve = null;
    function open(msg, okLabel) {
        msgEl.textContent = msg;
        okBtn.textContent = okLabel || 'Confirm';
        modal.hidden = false;
        return new Promise(function (res) { _resolve = res; });
    }
    function close(result) {
        modal.hidden = true;
        var r = _resolve; _resolve = null;
        if (r) r(result);
    }
    window.adminConfirm = open;
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('form[data-confirm]');
        if (!form) return;
        e.preventDefault();
        open(form.dataset.confirm, form.dataset.confirmOk || 'Confirm').then(function (ok) {
            if (ok) { form.removeAttribute('data-confirm'); form.submit(); }
        });
    });
    okBtn.addEventListener('click', function () { close(true); });
    closeEl.addEventListener('click', function () { close(false); });
    cancelEl.addEventListener('click', function () { close(false); });
    modal.addEventListener('click', function (e) { if (e.target === modal) close(false); });
})();
</script>
@endpush