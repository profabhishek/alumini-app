@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', $isEdit ? 'Edit News' : 'Create News')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('css/community/admin-content.css') }}?v=1">
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">{{ $isEdit ? 'Edit News' : 'Create News' }}</h1>
            <p class="admin-page-subtitle">{{ $isEdit ? $news->title : 'Write a new article for the alumni community.' }}</p>
        </div>
        <a href="{{ route('admin.news.index') }}" class="admin-link-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
            Back to News
        </a>
    </div>

    @if($errors->any())
        <div class="admin-alert admin-alert--error">
            Please fix the errors below before saving.
        </div>
    @endif

    <form action="{{ $isEdit ? route('admin.news.update', $news) : route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="admin-content-form">

            {{-- Main column --}}
            <div class="admin-content-main">

                <div class="admin-card">
                    <div class="admin-form-group">
                        <label>Title <span class="req">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $news->title) }}"
                               placeholder="e.g. India Africa Forum Summit Highlights & Details"
                               required maxlength="255"
                               class="admin-input @error('title') is-error @enderror">
                        @error('title')<span class="admin-form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-form-group" style="margin-top:16px;">
                        <label>Excerpt <span class="admin-form-hint">(short summary shown on cards)</span></label>
                        <textarea name="excerpt" rows="2" maxlength="500" id="excerptInput"
                                  placeholder="A short, engaging summary of the article..."
                                  class="admin-input @error('excerpt') is-error @enderror">{{ old('excerpt', $news->excerpt) }}</textarea>
                        <div class="admin-char-count"><span id="excerptCount">{{ strlen($news->excerpt ?? '') }}</span>/500</div>
                        @error('excerpt')<span class="admin-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-form-group">
                        <label>Content <span class="req">*</span></label>
                        <div class="admin-editor-wrap @error('body') is-error @enderror">
                            <div id="bodyEditor"></div>
                        </div>
                        <textarea name="body" id="bodyInput" hidden>{{ old('body', $news->body) }}</textarea>
                        @error('body')<span class="admin-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

            </div>

            {{-- Sidebar column --}}
            <div class="admin-content-aside">

                <div class="admin-card">
                    <div class="admin-aside-group">
                        <label>Publish Status</label>
                        <div class="admin-status-toggle">
                            <input type="radio" name="status" value="draft" id="statusDraft"
                                   {{ old('status', $news->status) === 'draft' ? 'checked' : '' }}>
                            <label for="statusDraft" class="{{ old('status', $news->status) === 'draft' ? 'is-active' : '' }}">Draft</label>

                            <input type="radio" name="status" value="published" id="statusPublished"
                                   {{ old('status', $news->status) === 'published' ? 'checked' : '' }}>
                            <label for="statusPublished" class="{{ old('status', $news->status) === 'published' ? 'is-active' : '' }}">Published</label>
                        </div>
                        @error('status')<span class="admin-form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-aside-group" style="margin-top:14px;">
                        <label>Publish Date <span class="admin-form-hint">(optional)</span></label>
                        <input type="datetime-local" name="published_at" class="admin-input"
                               value="{{ old('published_at', $news->published_at?->format('Y-m-d\TH:i')) }}">
                        <span class="admin-form-hint">Leave blank to use the current date/time when published.</span>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-aside-group">
                        <label>Category</label>
                        <div class="admin-category-select-row">
                            <select name="news_category_id" class="admin-input" data-category-select>
                                <option value="">Uncategorized</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('news_category_id', $news->news_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="manageCategoriesBtn" class="admin-icon-btn" title="Manage categories">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                            </button>
                        </div>
                        @error('news_category_id')<span class="admin-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-aside-group">
                        <label>Featured Image</label>

                        <div class="admin-image-upload" {{ $news->image ? 'hidden' : '' }}>
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p>Click or drag an image here</p>
                            <span>JPG, PNG, WEBP — max 4MB</span>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="admin-image-preview-wrap" {{ $news->image ? '' : 'hidden' }}>
                            @if($news->image)
                                <img src="{{ $news->image_url }}" alt="">
                                <button type="button" class="admin-image-remove-btn" data-action="remove-image" aria-label="Remove image">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            @endif
                        </div>

                        @if($isEdit)
                            <input type="hidden" name="remove_image" value="0">
                        @endif

                        @error('image')<span class="admin-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <button type="submit" class="admin-btn admin-btn--primary" style="width:100%;justify-content:center;padding:12px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/></svg>
                    {{ $isEdit ? 'Save Changes' : 'Publish Article' }}
                </button>

            </div>
        </div>
    </form>
</div>

{{-- Category Manager Modal --}}
<div class="admin-modal-backdrop" id="categoryModal" hidden>
    <div class="admin-modal">
        <div class="admin-modal__header">
            <h3>Manage News Categories</h3>
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
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="{{ asset('js/community/admin-content.js') }}?v=1"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    AdminContent.initEditor('#bodyEditor', '#bodyInput');
    AdminContent.initImageUpload('.admin-image-upload', '.admin-image-preview-wrap', 'input[name="image"]');

    AdminContent.initCategoryModal({
        openBtnSelector: '#manageCategoriesBtn',
        modalSelector: '#categoryModal',
        closeSelector: '#categoryModalClose',
        listUrl: '{{ route("admin.news-categories.index") }}',
        storeUrl: '{{ route("admin.news-categories.store") }}',
        updateUrlTpl: '{{ route("admin.news-categories.update", ["newsCategory" => "__ID__"]) }}',
        toggleUrlTpl: '{{ route("admin.news-categories.toggle", ["newsCategory" => "__ID__"]) }}',
        destroyUrlTpl: '{{ route("admin.news-categories.destroy", ["newsCategory" => "__ID__"]) }}',
        addInputSelector: '#categoryAddInput',
        addBtnSelector: '#categoryAddBtn',
        listSelector: '#categoryList',
        selectSelectors: ['[data-category-select]'],
    });

    // Status pill visual sync
    document.querySelectorAll('.admin-status-toggle input').forEach(input => {
        input.addEventListener('change', () => {
            document.querySelectorAll('.admin-status-toggle label').forEach(l => l.classList.remove('is-active'));
            document.querySelector(`label[for="${input.id}"]`).classList.add('is-active');
        });
    });

    // Excerpt char count
    const excerpt = document.getElementById('excerptInput');
    const excerptCount = document.getElementById('excerptCount');
    excerpt?.addEventListener('input', () => excerptCount.textContent = excerpt.value.length);
});
</script>
@endpush