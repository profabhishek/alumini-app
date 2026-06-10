@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Create Story')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/stories/stories.css') }}">
@endpush

@section('content')

<div class="st-page-header">
    <div class="st-page-header__left">
        <h1 class="st-page-title">Create Story</h1>
    </div>
    <a href="{{ route('stories.my') }}" class="st-btn st-btn--outline">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
        My Stories
    </a>
</div>

@if ($errors->any())
    <div class="st-alert st-alert--danger" style="margin-bottom:20px;">
        {{ $errors->first() }}
    </div>
@endif

<div class="st-form-card" style="border-radius:var(--st-radius-xl);padding:32px;max-width:820px;">

    <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- Title + Category --}}
        <div class="st-form-row" style="margin-bottom:18px;">
            <div class="st-form-group" style="margin-bottom:0;">
                <label class="st-label" for="title">Title <span class="st-required">*</span></label>
                <input type="text" id="title" name="title" class="st-input @error('title') st-input--error @enderror"
                       value="{{ old('title') }}" placeholder="Give your story a title" required maxlength="255">
                @error('title') <span class="st-field-error">{{ $message }}</span> @enderror
            </div>
            <div class="st-form-group" style="margin-bottom:0;">
                <label class="st-label" for="category">Category <span class="st-required">*</span></label>
                <select id="category" name="category" class="st-input st-select @error('category') st-input--error @enderror" required>
                    <option value="">Select category</option>
                    @foreach(['Career', 'Cultural Exchange', 'Education', 'Entrepreneurship', 'Research', 'Social Impact', 'Other'] as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category') <span class="st-field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Excerpt --}}
        <div class="st-form-group">
            <label class="st-label" for="excerpt">Short Excerpt <span style="font-weight:400;color:var(--st-gray-400);">(optional — auto-generated if left blank)</span></label>
            <input type="text" id="excerpt" name="excerpt" class="st-input @error('excerpt') st-input--error @enderror"
                   value="{{ old('excerpt') }}" placeholder="A one-line summary of your story…" maxlength="400">
            @error('excerpt') <span class="st-field-error">{{ $message }}</span> @enderror
        </div>

        {{-- Cover image --}}
        <div class="st-form-group">
            <label class="st-label">Cover Image <span style="font-weight:400;color:var(--st-gray-400);">(optional)</span></label>
            <div>
                <div class="st-file-preview" id="coverPreview" hidden>
                    <img id="coverPreviewImg" src="" alt="Cover preview">
                    <button type="button" class="st-file-remove" id="coverRemove" aria-label="Remove cover">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <label class="st-file-label" for="cover_image" id="coverLabel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span id="coverLabelText">Click to upload cover image</span>
                    <span class="st-file-hint">JPG, PNG, WebP — max 5 MB</span>
                </label>
                <input type="file" id="cover_image" name="cover_image" class="st-file-input" accept="image/jpg,image/jpeg,image/png,image/webp">
            </div>
            @error('cover_image') <span class="st-field-error">{{ $message }}</span> @enderror
        </div>

        {{-- Body --}}
        <div class="st-form-group">
            <label class="st-label" for="body">Your Story <span class="st-required">*</span></label>
            <textarea id="body" name="body" class="st-input st-textarea @error('body') st-input--error @enderror"
                      rows="14" placeholder="Share your journey, achievements, or experience in detail (min 100 characters)…" required>{{ old('body') }}</textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                @error('body')
                    <span class="st-field-error">{{ $message }}</span>
                @else
                    <span class="st-field-hint">Minimum 100 characters</span>
                @enderror
                <span class="st-field-hint" id="bodyCount">0 chars</span>
            </div>
        </div>

        {{-- Info notice --}}
        <div style="background:var(--st-amber-50);border:1px solid var(--st-amber-100);border-radius:var(--st-radius-md);padding:12px 16px;margin-bottom:24px;font-size:13.5px;color:var(--st-amber-700);">
            <strong>📋 Note:</strong> Your story will be submitted for admin review before it appears publicly. You'll be able to see its status on the "My Stories" page.
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <a href="{{ route('stories.my') }}" class="st-btn st-btn--outline">Cancel</a>
            <button type="submit" class="st-btn st-btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Submit for Review
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Cover image preview
    const input   = document.getElementById('cover_image');
    const preview = document.getElementById('coverPreview');
    const img     = document.getElementById('coverPreviewImg');
    const label   = document.getElementById('coverLabel');
    const removeBtn = document.getElementById('coverRemove');

    input.addEventListener('change', function () {
        if (!this.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            preview.removeAttribute('hidden');
            label.setAttribute('hidden', '');
        };
        reader.readAsDataURL(this.files[0]);
    });

    removeBtn.addEventListener('click', () => {
        input.value = '';
        img.src = '';
        preview.setAttribute('hidden', '');
        label.removeAttribute('hidden');
    });

    // Character counter
    const bodyEl    = document.getElementById('body');
    const bodyCount = document.getElementById('bodyCount');
    function updateCount() {
        bodyCount.textContent = bodyEl.value.length + ' chars';
        bodyCount.style.color = bodyEl.value.length < 100
            ? 'var(--st-red-500)'
            : 'var(--st-gray-400)';
    }
    bodyEl.addEventListener('input', updateCount);
    updateCount();
});
</script>
@endpush