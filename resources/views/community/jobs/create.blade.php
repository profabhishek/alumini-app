@extends('layouts.community')

@section('title', 'Post a Job')

@section('hideRightSidebar')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/jobs/create-job.css') }}">
@endpush

@section('content')

<div class="cj-page">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="cj-header">
        <div>
            <h1 class="cj-title">Post a Job</h1>
            <p class="cj-subtitle">Fill in the details below. Your listing will be reviewed before publishing.</p>
        </div>
        <a href="{{ route('jobs.my') }}" class="cj-btn cj-btn-ghost">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,18 9,12 15,6"/></svg>
            My Jobs
        </a>
    </div>

    {{-- ── Form ────────────────────────────────────────────────────────── --}}
    <form action="{{ route('jobs.store') }}" method="POST" enctype="multipart/form-data" class="cj-form">
        @csrf

        <div class="cj-grid">

            {{-- ── LEFT COLUMN ─────────────────────────────────────────── --}}
            <div class="cj-col">

                {{-- Basic Info --}}
                <div class="cj-card">
                    <h2 class="cj-card-title">Basic Information</h2>

                    <div class="cj-form-group">
                        <label class="cj-label" for="title">Job Title <span class="cj-req">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}"
                               class="cj-input @error('title') cj-input--error @enderror"
                               placeholder="e.g. Senior Software Engineer" required maxlength="255">
                        @error('title')<span class="cj-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="cj-form-group">
                        <label class="cj-label" for="company_name">Company Name <span class="cj-req">*</span></label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}"
                               class="cj-input @error('company_name') cj-input--error @enderror"
                               placeholder="e.g. Tata Consultancy Services" required maxlength="255">
                        @error('company_name')<span class="cj-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="cj-form-row">
                        <div class="cj-form-group">
                            <label class="cj-label" for="job_type">Job Type <span class="cj-req">*</span></label>
                            <select id="job_type" name="job_type" required
                                    class="cj-input cj-select @error('job_type') cj-input--error @enderror">
                                <option value="">Select type</option>
                                @foreach(['Full-Time','Part-Time','Contract','Internship'] as $type)
                                    <option value="{{ $type }}" {{ old('job_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('job_type')<span class="cj-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="cj-form-group">
                            <label class="cj-label" for="work_mode">Work Mode <span class="cj-req">*</span></label>
                            <select id="work_mode" name="work_mode" required
                                    class="cj-input cj-select @error('work_mode') cj-input--error @enderror">
                                <option value="">Select mode</option>
                                @foreach(['Remote','On-site','Hybrid'] as $mode)
                                    <option value="{{ $mode }}" {{ old('work_mode') === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                            @error('work_mode')<span class="cj-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="cj-form-group">
                        <label class="cj-label" for="location">Location</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                               class="cj-input @error('location') cj-input--error @enderror"
                               placeholder="e.g. New Delhi, India">
                        @error('location')<span class="cj-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Salary --}}
                <div class="cj-card">
                    <h2 class="cj-card-title">Salary Range</h2>
                    <p class="cj-card-hint">Leave blank to show "Not disclosed"</p>

                    <div class="cj-form-row">
                        <div class="cj-form-group">
                            <label class="cj-label" for="salary_min">Minimum (USD/year)</label>
                            <input type="number" id="salary_min" name="salary_min" value="{{ old('salary_min') }}"
                                   class="cj-input @error('salary_min') cj-input--error @enderror"
                                   placeholder="e.g. 500000" min="0">
                            @error('salary_min')<span class="cj-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="cj-form-group">
                            <label class="cj-label" for="salary_max">Maximum (USD/year)</label>
                            <input type="number" id="salary_max" name="salary_max" value="{{ old('salary_max') }}"
                                   class="cj-input @error('salary_max') cj-input--error @enderror"
                                   placeholder="e.g. 1200000" min="0">
                            @error('salary_max')<span class="cj-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="cj-card">
                    <h2 class="cj-card-title">Job Description <span class="cj-req">*</span></h2>

                    <div class="cj-form-group">
                        <textarea id="description" name="description" rows="8" required
                                  class="cj-input cj-textarea @error('description') cj-input--error @enderror"
                                  placeholder="Describe the role, responsibilities, and what the candidate will be doing...">{{ old('description') }}</textarea>
                        @error('description')<span class="cj-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Requirements --}}
                <div class="cj-card">
                    <h2 class="cj-card-title">Requirements</h2>

                    <div class="cj-form-group">
                        <textarea id="requirements" name="requirements" rows="6"
                                  class="cj-input cj-textarea @error('requirements') cj-input--error @enderror"
                                  placeholder="List qualifications, skills, and experience required...">{{ old('requirements') }}</textarea>
                        @error('requirements')<span class="cj-error">{{ $message }}</span>@enderror
                    </div>
                </div>

            </div>

            {{-- ── RIGHT COLUMN ─────────────────────────────────────────── --}}
            <div class="cj-col">

                {{-- Application Details --}}
                <div class="cj-card">
                    <h2 class="cj-card-title">Application Details</h2>

                    <div class="cj-form-group">
                        <label class="cj-label" for="application_deadline">Application Deadline</label>
                        <input type="date" id="application_deadline" name="application_deadline"
                               value="{{ old('application_deadline') }}"
                               min="{{ now()->toDateString() }}"
                               class="cj-input @error('application_deadline') cj-input--error @enderror">
                        @error('application_deadline')<span class="cj-error">{{ $message }}</span>@enderror
                    </div>

                </div>

                {{-- Banner Image --}}
                <div class="cj-card">
                    <h2 class="cj-card-title">Banner Image</h2>

                    <div class="cj-form-group">
                        <div class="cj-upload-area" id="uploadArea" onclick="document.getElementById('banner_image').click()">
                            <div class="cj-upload-placeholder" id="uploadPlaceholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                                <span>Click to upload image</span>
                                <span class="cj-upload-hint">JPG, PNG, WEBP — max 5MB</span>
                            </div>
                            <img id="imagePreview" src="" alt="Preview" class="cj-upload-preview" hidden>
                        </div>
                        <input type="file" id="banner_image" name="banner_image" accept="image/*" hidden
                               onchange="previewImage(this)">
                        @error('banner_image')<span class="cj-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Submit --}}
                <div class="cj-card cj-submit-card">
                    <div class="cj-notice">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Your job listing will be reviewed by an admin before it goes live.
                    </div>
                    <button type="submit" class="cj-btn cj-btn-primary cj-btn-full">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9"/></svg>
                        Submit for Review
                    </button>
                    <a href="{{ route('jobs.my') }}" class="cj-btn cj-btn-ghost cj-btn-full">Cancel</a>
                </div>

            </div>

        </div>
    </form>

</div>

@endsection

@push('scripts')
<script>
function previewImage(input) {
    const preview   = document.getElementById('imagePreview');
    const placeholder = document.getElementById('uploadPlaceholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.hidden = false;
            placeholder.hidden = true;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush