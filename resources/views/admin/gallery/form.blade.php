@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', $isEdit ? 'Edit Gallery Image' : 'Add Gallery Image')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('css/community/admin-content.css') }}?v=1">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">{{ $isEdit ? 'Edit Gallery Image' : 'Add Gallery Image' }}</h1>
            <p class="admin-page-subtitle">Shown in the homepage Image Gallery section.</p>
        </div>
        <a href="{{ route('admin.gallery.index') }}" class="admin-link-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
            Back to Gallery
        </a>
    </div>

    @if($errors->any())
        <div class="admin-alert admin-alert--error">Please fix the errors below before saving.</div>
    @endif

    <div class="admin-card" style="max-width:520px;">
        <form action="{{ $isEdit ? route('admin.gallery.update', $item) : route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="admin-form-group">
                <label>Title <span class="admin-form-hint">(optional, for your reference)</span></label>
                <input type="text" name="title" value="{{ old('title', $item->title) }}"
                       placeholder="e.g. Convocation Ceremony 2026"
                       class="admin-input @error('title') is-error @enderror">
                @error('title')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group" style="margin-top:16px;">
                <label>Image {{ $isEdit ? '' : '*' }}</label>

                <div class="admin-image-upload" {{ $item->image ? 'hidden' : '' }}>
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p>Click or drag an image here</p>
                    <span>JPG, PNG, WEBP — max 4MB</span>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                </div>

                <div class="admin-image-preview-wrap" {{ $item->image ? '' : 'hidden' }}>
                    @if($item->image)
                        <img src="{{ $item->image_url }}" alt="">
                    @endif
                </div>
                @if($isEdit)
                    <span class="admin-form-hint">Leave blank to keep the current image. Uploading a new one replaces it.</span>
                @endif

                @error('image')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group" style="margin-top:16px;">
                <label>Status</label>
                <div class="admin-status-toggle">
                    <input type="radio" name="status" value="draft" id="statusDraft"
                           {{ old('status', $item->status) === 'draft' ? 'checked' : '' }}>
                    <label for="statusDraft" class="{{ old('status', $item->status) === 'draft' ? 'is-active' : '' }}">Hidden (Draft)</label>

                    <input type="radio" name="status" value="published" id="statusPublished"
                           {{ old('status', $item->status) === 'published' ? 'checked' : '' }}>
                    <label for="statusPublished" class="{{ old('status', $item->status) === 'published' ? 'is-active' : '' }}">Visible</label>
                </div>
            </div>

            <div class="admin-form-actions">
                <a href="{{ route('admin.gallery.index') }}" class="admin-link-btn">Cancel</a>
                <button type="submit" class="admin-btn admin-btn--primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/></svg>
                    {{ $isEdit ? 'Save Changes' : 'Add to Gallery' }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/community/admin-content.js') }}?v=1"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    AdminContent.initImageUpload('.admin-image-upload', '.admin-image-preview-wrap', 'input[name="image"]');

    document.querySelectorAll('.admin-status-toggle input').forEach(input => {
        input.addEventListener('change', () => {
            document.querySelectorAll('.admin-status-toggle label').forEach(l => l.classList.remove('is-active'));
            document.querySelector(`label[for="${input.id}"]`).classList.add('is-active');
        });
    });
});
</script>
@endpush