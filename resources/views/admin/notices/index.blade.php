@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Manage Notices')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('css/community/admin-content.css') }}?v=1">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Notices</h1>
            <p class="admin-page-subtitle">{{ $notices->total() }} notices total</p>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="button" id="manageCategoriesBtn" class="admin-link-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Manage Categories
            </button>
            <a href="{{ route('admin.notices.create') }}" class="admin-link-btn admin-link-btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Notice
            </a>
        </div>
    </div>

    @foreach(['success','error'] as $msg)
        @if(session($msg))<div class="admin-alert admin-alert--{{ $msg }}">{{ session($msg) }}</div>@endif
    @endforeach

    <form method="GET" action="{{ route('admin.notices.index') }}" class="admin-toolbar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="admin-input admin-search-input">
        <select name="category_id" class="admin-input admin-filter-select" data-category-select>
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="admin-input admin-filter-select">
            <option value="">All Status</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
        </select>
        <button type="submit" class="admin-btn admin-btn--primary">Filter</button>
        @if(request('search') || request('category_id') || request('status'))
            <a href="{{ route('admin.notices.index') }}" class="admin-link-btn">Clear</a>
        @endif
    </form>

    @if($notices->isEmpty())
        <div class="admin-empty-state">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
            <p>No notices yet.</p>
            <span>Click "Create Notice" to publish your first announcement.</span>
        </div>
    @else
        <div class="admin-table-wrap" id="noticesTable">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Notice</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="admin-table__actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notices as $item)
                    <tr>
                        <td>
                            <div class="admin-table__title-cell">
                                @if($item->image)
                                    <img src="{{ $item->image_url }}" class="admin-table__thumb" alt="">
                                @else
                                    <div class="admin-table__thumb-placeholder">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
                                    </div>
                                @endif
                                <span class="admin-table__title-text">{{ $item->title }}</span>
                            </div>
                        </td>
                        <td>
                            @if($item->category)
                                <span class="admin-cat-pill">{{ $item->category->name }}</span>
                            @else
                                <span class="admin-cat-pill admin-cat-pill--none">Uncategorized</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="admin-pub-badge admin-pub-badge--{{ $item->status }}"
                                    data-toggle-status-url="{{ route('admin.notices.toggle-status', $item) }}">
                                <span class="admin-pub-badge__dot"></span>
                                <span class="admin-pub-badge__label">{{ ucfirst($item->status) }}</span>
                            </button>
                        </td>
                        <td>{{ ($item->published_at ?? $item->created_at)->format('d M Y') }}</td>
                        <td>
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.notices.edit', $item) }}" class="admin-btn admin-btn--edit">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.notices.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Delete \'{{ addslashes($item->title) }}\'? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn--reject">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $notices->links() }}</div>
    @endif

</div>

{{-- Category Manager Modal --}}
<div class="admin-modal-backdrop" id="categoryModal" hidden>
    <div class="admin-modal">
        <div class="admin-modal__header">
            <h3>Manage Notice Categories</h3>
            <button type="button" class="admin-modal__close" id="categoryModalClose">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="admin-modal__body">
            <div class="admin-cat-add-row">
                <input type="text" id="categoryAddInput" class="admin-input" placeholder="New category name...">
                <button type="button" id="categoryAddBtn" class="admin-btn admin-btn--primary">Add</button>
            </div>
            <div class="admin-cat-list" id="categoryList">
                <div class="admin-empty-cats">Loading...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/community/admin-content.js') }}?v=1"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    AdminContent.initStatusToggles('#noticesTable');

    AdminContent.initCategoryModal({
        openBtnSelector: '#manageCategoriesBtn',
        modalSelector: '#categoryModal',
        closeSelector: '#categoryModalClose',
        listUrl: '{{ route("admin.notice-categories.index") }}',
        storeUrl: '{{ route("admin.notice-categories.store") }}',
        updateUrlTpl: '{{ route("admin.notice-categories.update", ["noticeCategory" => "__ID__"]) }}',
        toggleUrlTpl: '{{ route("admin.notice-categories.toggle", ["noticeCategory" => "__ID__"]) }}',
        destroyUrlTpl: '{{ route("admin.notice-categories.destroy", ["noticeCategory" => "__ID__"]) }}',
        addInputSelector: '#categoryAddInput',
        addBtnSelector: '#categoryAddBtn',
        listSelector: '#categoryList',
        selectSelectors: ['[data-category-select]'],
    });
});
</script>
@endpush