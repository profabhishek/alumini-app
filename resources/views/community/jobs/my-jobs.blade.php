@extends('layouts.community')

@section('title', 'My Jobs')

@section('hideRightSidebar')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/jobs/my-jobs.css') }}">
@endpush

@section('content')

<div class="mj-page">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="mj-header">
        <div>
            <h1 class="mj-title">My Jobs</h1>
            <p class="mj-subtitle">Manage your job listings</p>
        </div>
        <a href="{{ route('jobs.create') }}" class="mj-btn mj-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Post a Job
        </a>
    </div>

    {{-- ── Flash ───────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mj-alert mj-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mj-alert mj-alert-error">{{ session('error') }}</div>
    @endif

    {{-- ── Stats ───────────────────────────────────────────────────────── --}}
    <div class="mj-stats">
        <div class="mj-stat">
            <span class="mj-stat__value">{{ $stats['total'] }}</span>
            <span class="mj-stat__label">Total</span>
        </div>
        <div class="mj-stat mj-stat--pending">
            <span class="mj-stat__value">{{ $stats['pending'] }}</span>
            <span class="mj-stat__label">Pending</span>
        </div>
        <div class="mj-stat mj-stat--published">
            <span class="mj-stat__value">{{ $stats['published'] }}</span>
            <span class="mj-stat__label">Published</span>
        </div>
        <div class="mj-stat mj-stat--rejected">
            <span class="mj-stat__value">{{ $stats['rejected'] }}</span>
            <span class="mj-stat__label">Rejected</span>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('jobs.my') }}" class="mj-filters">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Search by title, company, location..."
               class="mj-input mj-search">
        <select name="status" class="mj-input mj-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['pending','published','rejected'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="mj-btn mj-btn-secondary">Search</button>
        @if(request('q') || request('status'))
            <a href="{{ route('jobs.my') }}" class="mj-btn mj-btn-ghost">Clear</a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────────────────────── --}}
    <div class="mj-table-wrap">
        <table class="mj-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Job</th>
                    <th>Type / Mode</th>
                    <th>Salary</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                    <tr id="mj-row-{{ $job->id }}">
                        <td class="mj-td-num">{{ $loop->iteration + ($jobs->currentPage() - 1) * $jobs->perPage() }}</td>
                        <td class="mj-td-job">
                            <span class="mj-job-title">
                                {{ $job->title }}
                                @if(($newApplicantCounts[$job->id] ?? 0) > 0)
                                    <span style="
                                        display:inline-flex;
                                        align-items:center;
                                        gap:3px;
                                        background:rgba(232,100,12,0.1);
                                        color:#e8640c;
                                        font-size:10px;
                                        font-weight:700;
                                        padding:1px 7px;
                                        border-radius:999px;
                                        border:1px solid rgba(232,100,12,0.25);
                                        margin-left:6px;
                                        vertical-align:middle;
                                    ">
                                        🔔 {{ $newApplicantCounts[$job->id] }} new
                                    </span>
                                @endif
                            </span>
                            <span class="mj-job-company">{{ $job->company_name }}</span>
                            @if($job->location)
                                <span class="mj-job-location">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    {{ $job->location }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="mj-tag mj-tag--type">{{ $job->job_type }}</span>
                            <span class="mj-tag mj-tag--mode">{{ $job->work_mode }}</span>
                        </td>
                        <td class="mj-td-salary">{{ $job->salaryRange() }}</td>
                        <td class="mj-td-date">
                            @if($job->application_deadline)
                                <span class="{{ $job->isExpired() ? 'mj-expired' : '' }}">
                                    {{ $job->application_deadline->format('d M Y') }}
                                </span>
                            @else
                                <span class="mj-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="mj-badge mj-badge--{{ $job->status }}">
                                {{ ucfirst($job->status) }}
                            </span>
                            @if($job->status === 'rejected' && $job->rejection_reason)
                                <span class="mj-rejection-hint" title="{{ $job->rejection_reason }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                </span>
                            @endif
                        </td>
                        <td class="mj-td-date mj-muted">{{ $job->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="mj-actions">

                            @if($job->status === 'published')
                                @php $newCount = $newApplicantCounts[$job->id] ?? 0; @endphp
                                <a href="{{ route('jobs.applicants', $job) }}"
                                class="mj-icon-btn"
                                style="color:#E8640C; border-color:rgba(232,100,12,.25); background:rgba(232,100,12,.06); position:relative;"
                                title="{{ $newCount > 0 ? $newCount . ' new applicant(s)' : 'View Applicants' }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 010 7.75"/>
                                    </svg>
                                    @if($newCount > 0)
                                        <span style="
                                            position:absolute;
                                            top:-5px;
                                            right:-5px;
                                            background:#e8640c;
                                            color:#fff;
                                            font-size:9px;
                                            font-weight:700;
                                            min-width:16px;
                                            height:16px;
                                            border-radius:999px;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            padding:0 3px;
                                            line-height:1;
                                            border:1.5px solid #fff;
                                        ">{{ $newCount > 9 ? '9+' : $newCount }}</span>
                                    @endif
                                </a>
                            @endif

                                {{-- View --}}
                                @if($job->status === 'published')
                                    <button class="mj-icon-btn mj-icon-view" title="View"
                                            onclick="openViewModal({{ $job->id }})">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                @endif
                                {{-- Edit --}}
                                @if($job->status !== 'published')
                                    <button class="mj-icon-btn mj-icon-edit" title="Edit"
                                            onclick="openEditModal({{ $job->id }})">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a4 4 0 01-1.414.828l-3 1 1-3a4 4 0 01.828-1.414z"/></svg>
                                    </button>
                                @endif
                                {{-- Delete --}}
                                <button class="mj-icon-btn mj-icon-delete" title="Delete"
                                        onclick="confirmDelete({{ $job->id }}, '{{ addslashes($job->title) }}')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="mj-empty">
                            No jobs found.
                            <a href="{{ route('jobs.create') }}">Post your first job →</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($jobs->hasPages())
        <div class="mj-pagination">{{ $jobs->links() }}</div>
    @endif

</div>


{{-- ════════════════════════════════════════════════════════════════════════
     EDIT MODAL
════════════════════════════════════════════════════════════════════════ --}}
<div class="mj-backdrop" id="editBackdrop" hidden onclick="closeEditModal()"></div>
<div class="mj-modal" id="editModal" hidden>
    <div class="mj-modal__header">
        <h2 class="mj-modal__title">Edit Job</h2>
        <button class="mj-modal__close" onclick="closeEditModal()" aria-label="Close">&times;</button>
    </div>
    <form id="editForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mj-modal__body">

            <div class="mj-form-group">
                <label class="mj-label">Job Title <span class="mj-req">*</span></label>
                <input type="text" name="title" id="edit_title" class="mj-input" required>
                <span class="mj-field-error" id="edit_title_error"></span>
            </div>

            <div class="mj-form-group">
                <label class="mj-label">Company Name <span class="mj-req">*</span></label>
                <input type="text" name="company_name" id="edit_company_name" class="mj-input" required>
                <span class="mj-field-error" id="edit_company_name_error"></span>
            </div>

            <div class="mj-form-row">
                <div class="mj-form-group">
                    <label class="mj-label">Job Type <span class="mj-req">*</span></label>
                    <select name="job_type" id="edit_job_type" class="mj-input mj-select">
                        @foreach(['Full-Time','Part-Time','Contract','Internship'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mj-form-group">
                    <label class="mj-label">Work Mode <span class="mj-req">*</span></label>
                    <select name="work_mode" id="edit_work_mode" class="mj-input mj-select">
                        @foreach(['Remote','On-site','Hybrid'] as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mj-form-group">
                <label class="mj-label">Location</label>
                <input type="text" name="location" id="edit_location" class="mj-input">
            </div>

            <div class="mj-form-row">
                <div class="mj-form-group">
                    <label class="mj-label">Min Salary (₹)</label>
                    <input type="number" name="salary_min" id="edit_salary_min" class="mj-input" min="0">
                </div>
                <div class="mj-form-group">
                    <label class="mj-label">Max Salary (₹)</label>
                    <input type="number" name="salary_max" id="edit_salary_max" class="mj-input" min="0">
                </div>
            </div>

            <div class="mj-form-group">
                <label class="mj-label">Description <span class="mj-req">*</span></label>
                <textarea name="description" id="edit_description" class="mj-input mj-textarea" rows="5" required></textarea>
                <span class="mj-field-error" id="edit_description_error"></span>
            </div>

            <div class="mj-form-group">
                <label class="mj-label">Requirements</label>
                <textarea name="requirements" id="edit_requirements" class="mj-input mj-textarea" rows="4"></textarea>
            </div>

            <div class="mj-form-row">
                <div class="mj-form-group">
                    <label class="mj-label">Application Deadline</label>
                    <input type="date" name="application_deadline" id="edit_application_deadline" class="mj-input">
                </div>
                <div class="mj-form-group">
                    <label class="mj-label">Application Link</label>
                    <input type="url" name="application_link" id="edit_application_link" class="mj-input" placeholder="https://...">
                </div>
            </div>

            {{-- ── Banner Image ──────────────────────────────────────────── --}}
            <div class="mj-form-group">
                <label class="mj-label">Banner Image</label>

                {{-- Current banner preview --}}
                <div id="edit_banner_preview_wrap" class="mj-banner-preview" hidden>
                    <img id="edit_banner_preview_img" src="" alt="Current banner">
                    <button type="button" class="mj-banner-remove" id="edit_banner_remove" title="Remove banner">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Remove
                    </button>
                </div>

                {{-- File input drop zone --}}
                <label class="mj-dropzone" id="edit_banner_dropzone" for="edit_banner_image">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9l4-4 4 4 4-4 4 4"/><path d="M3 15l4 4 4-4 4 4 4-4"/></svg>
                    <span class="mj-dropzone__text">
                        <strong>Click to upload</strong> or drag & drop<br>
                        <small>JPG, PNG, WEBP · max 5 MB</small>
                    </span>
                    <input type="file" name="banner_image" id="edit_banner_image"
                           accept="image/jpg,image/jpeg,image/png,image/webp" class="mj-file-hidden">
                </label>

                {{-- New file preview (before save) --}}
                <div id="edit_new_banner_wrap" class="mj-banner-preview" hidden>
                    <img id="edit_new_banner_img" src="" alt="New banner preview">
                    <button type="button" class="mj-banner-remove" id="edit_new_banner_clear" title="Clear selection">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Clear
                    </button>
                </div>

                <span class="mj-field-error" id="edit_banner_image_error"></span>
            </div>

        </div>
        <div class="mj-modal__footer">
            <button type="button" class="mj-btn mj-btn-ghost" onclick="closeEditModal()">Cancel</button>
            <button type="submit" class="mj-btn mj-btn-primary" id="editSubmitBtn">Save Changes</button>
        </div>
    </form>
</div>


{{-- ════════════════════════════════════════════════════════════════════════
     DELETE MODAL
════════════════════════════════════════════════════════════════════════ --}}
<div class="mj-backdrop" id="deleteBackdrop" hidden onclick="closeDeleteModal()"></div>
<div class="mj-modal mj-modal--sm" id="deleteModal" hidden>
    <div class="mj-modal__header">
        <h2 class="mj-modal__title">Delete Job</h2>
        <button class="mj-modal__close" onclick="closeDeleteModal()" aria-label="Close">&times;</button>
    </div>
    <div class="mj-modal__body">
        <p class="mj-confirm-text">Are you sure you want to delete <strong id="deleteJobTitle"></strong>? This cannot be undone.</p>
    </div>
    <div class="mj-modal__footer">
        <button type="button" class="mj-btn mj-btn-ghost" onclick="closeDeleteModal()">Cancel</button>
        <button type="button" class="mj-btn mj-btn-danger" id="deleteConfirmBtn">Delete</button>
    </div>
</div>

{{-- ════ VIEW MODAL ════ --}}
<div class="mj-backdrop" id="viewBackdrop" hidden onclick="closeViewModal()"></div>
<div class="mj-modal" id="viewModal" hidden>
    <div class="mj-modal__header">
        <h2 class="mj-modal__title">Job Details</h2>
        <div style="display:flex;align-items:center;gap:.75rem;">
            <a id="view_public_link" href="#" target="_blank" class="mj-btn mj-btn-ghost" style="font-size:.8rem;padding:.4rem .9rem;">
                View Public Page ↗
            </a>
            <button class="mj-modal__close" onclick="closeViewModal()" aria-label="Close">&times;</button>
        </div>
    </div>
    <div class="mj-modal__body">

        {{-- Banner --}}
        <div id="view_banner_wrap" class="mj-banner-preview" hidden>
            <img id="view_banner_img" src="" alt="Banner">
        </div>

        <div class="mj-view-grid">
            <div class="mj-view-field">
                <span class="mj-view-label">Job Title</span>
                <span class="mj-view-value" id="view_title"></span>
            </div>
            <div class="mj-view-field">
                <span class="mj-view-label">Company</span>
                <span class="mj-view-value" id="view_company"></span>
            </div>
            <div class="mj-view-field">
                <span class="mj-view-label">Job Type</span>
                <span class="mj-view-value" id="view_job_type"></span>
            </div>
            <div class="mj-view-field">
                <span class="mj-view-label">Work Mode</span>
                <span class="mj-view-value" id="view_work_mode"></span>
            </div>
            <div class="mj-view-field">
                <span class="mj-view-label">Location</span>
                <span class="mj-view-value" id="view_location"></span>
            </div>
            <div class="mj-view-field">
                <span class="mj-view-label">Salary Range</span>
                <span class="mj-view-value" id="view_salary"></span>
            </div>
            <div class="mj-view-field">
                <span class="mj-view-label">Application Deadline</span>
                <span class="mj-view-value" id="view_deadline"></span>
            </div>
            <div class="mj-view-field">
                <span class="mj-view-label">Application Link</span>
                <span class="mj-view-value" id="view_app_link"></span>
            </div>
        </div>

        <div class="mj-view-field mj-view-field--full">
            <span class="mj-view-label">Description</span>
            <div class="mj-view-prose" id="view_description"></div>
        </div>

        <div class="mj-view-field mj-view-field--full" id="view_requirements_wrap">
            <span class="mj-view-label">Requirements</span>
            <div class="mj-view-prose" id="view_requirements"></div>
        </div>

    </div>
    <div class="mj-modal__footer">
        <button type="button" class="mj-btn mj-btn-ghost" onclick="closeViewModal()">Close</button>
    </div>
</div>

@push('styles')
<style>
[hidden] { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
const MJ_CSRF    = '{{ csrf_token() }}';
const MJ_BASE    = '{{ url("/my-jobs") }}';
const MJ_STORAGE = '{{ asset("storage") }}';

// ── Toast ─────────────────────────────────────────────────────────────────
function mjToast(msg, type = 'success') {
    let t = document.getElementById('mjToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'mjToast';
        t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;padding:.75rem 1.25rem;border-radius:8px;font-size:.875rem;z-index:9999;opacity:0;transform:translateY(8px);transition:opacity .25s,transform .25s;pointer-events:none;color:#fff;';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = type === 'error' ? '#dc2626' : '#111827';
    t.style.opacity = '1';
    t.style.transform = 'translateY(0)';
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(8px)'; }, 4000);
}

// ── Modal helpers ─────────────────────────────────────────────────────────
function openEditModal(id) {
    document.getElementById('editBackdrop').hidden = false;
    document.getElementById('editModal').hidden    = false;
    document.body.style.overflow = 'hidden';
    loadEditData(id);
}
function closeEditModal() {
    document.getElementById('editBackdrop').hidden = true;
    document.getElementById('editModal').hidden    = true;
    document.body.style.overflow = '';
    resetBannerUI();
}
function closeDeleteModal() {
    document.getElementById('deleteBackdrop').hidden = true;
    document.getElementById('deleteModal').hidden    = true;
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeEditModal(); closeDeleteModal(); }
});

// ── Clear errors ──────────────────────────────────────────────────────────
function clearEditErrors() {
    document.querySelectorAll('[id^="edit_"][id$="_error"]').forEach(el => el.textContent = '');
}
function showEditErrors(errors) {
    Object.entries(errors).forEach(([field, msgs]) => {
        const el = document.getElementById(`edit_${field}_error`);
        if (el) el.textContent = msgs[0];
    });
}

// ── Banner UI helpers ─────────────────────────────────────────────────────
function resetBannerUI() {
    document.getElementById('edit_banner_preview_wrap').hidden = true;
    document.getElementById('edit_banner_preview_img').src     = '';
    document.getElementById('edit_new_banner_wrap').hidden     = true;
    document.getElementById('edit_new_banner_img').src         = '';
    document.getElementById('edit_banner_image').value         = '';
    document.getElementById('edit_banner_dropzone').hidden     = false;
}

// Show existing banner from server
function showExistingBanner(path) {
    if (!path) return;
    const wrap = document.getElementById('edit_banner_preview_wrap');
    document.getElementById('edit_banner_preview_img').src = `${MJ_STORAGE}/${path}`;
    wrap.hidden = false;
    document.getElementById('edit_banner_dropzone').hidden = true;
}

// Remove existing banner (just hides preview, lets user pick new or leave empty)
document.getElementById('edit_banner_remove').addEventListener('click', () => {
    document.getElementById('edit_banner_preview_wrap').hidden = true;
    document.getElementById('edit_banner_dropzone').hidden     = false;
});

// Preview newly selected file
document.getElementById('edit_banner_image').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('edit_new_banner_img').src = e.target.result;
        document.getElementById('edit_new_banner_wrap').hidden = false;
        // Hide dropzone label (file already chosen)
        document.getElementById('edit_banner_dropzone').hidden = true;
    };
    reader.readAsDataURL(file);
});

// Clear newly selected file
document.getElementById('edit_new_banner_clear').addEventListener('click', () => {
    document.getElementById('edit_banner_image').value     = '';
    document.getElementById('edit_new_banner_wrap').hidden = true;
    document.getElementById('edit_new_banner_img').src     = '';
    document.getElementById('edit_banner_dropzone').hidden = false;
});

// ── Load edit data ────────────────────────────────────────────────────────
let editingJobId = null;

async function loadEditData(id) {
    editingJobId = id;
    clearEditErrors();
    resetBannerUI();
    try {
        const res  = await fetch(`${MJ_BASE}/${id}/edit`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': MJ_CSRF }
        });
        const data = await res.json();

        document.getElementById('edit_title').value                = data.title        ?? '';
        document.getElementById('edit_company_name').value         = data.company_name ?? '';
        document.getElementById('edit_job_type').value             = data.job_type     ?? '';
        document.getElementById('edit_work_mode').value            = data.work_mode    ?? '';
        document.getElementById('edit_location').value             = data.location     ?? '';
        document.getElementById('edit_salary_min').value           = data.salary_min   ?? '';
        document.getElementById('edit_salary_max').value           = data.salary_max   ?? '';
        document.getElementById('edit_description').value          = data.description  ?? '';
        document.getElementById('edit_requirements').value         = data.requirements ?? '';
        document.getElementById('edit_application_deadline').value = data.application_deadline
            ? data.application_deadline.substring(0, 10) : '';
        document.getElementById('edit_application_link').value     = data.application_link ?? '';

        // Banner
        if (data.banner_image) {
            showExistingBanner(data.banner_image);
        }
    } catch {
        mjToast('Failed to load job data.', 'error');
        closeEditModal();
    }
}

// ── Submit edit ───────────────────────────────────────────────────────────
document.getElementById('editForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!editingJobId) return;
    clearEditErrors();

    const btn = document.getElementById('editSubmitBtn');
    btn.disabled    = true;
    btn.textContent = 'Saving...';

    const data = new FormData(this);
    data.append('_method', 'PUT');

    try {
        const res  = await fetch(`${MJ_BASE}/${editingJobId}`, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': MJ_CSRF, 'Accept': 'application/json' },
            body:    data,
        });
        const json = await res.json();
        if (res.ok && json.success) {
            mjToast(json.message);
            closeEditModal();
            setTimeout(() => location.reload(), 800);
        } else {
            if (json.errors) showEditErrors(json.errors);
            else mjToast(json.message || 'Something went wrong.', 'error');
        }
    } catch {
        mjToast('Request failed.', 'error');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Save Changes';
    }
});

// ── Delete ────────────────────────────────────────────────────────────────
let deletingJobId = null;

function confirmDelete(id, title) {
    deletingJobId = id;
    document.getElementById('deleteJobTitle').textContent    = title;
    document.getElementById('deleteBackdrop').hidden         = false;
    document.getElementById('deleteModal').hidden            = false;
    document.body.style.overflow = 'hidden';
}

document.getElementById('deleteConfirmBtn').addEventListener('click', async function () {
    if (!deletingJobId) return;
    this.disabled    = true;
    this.textContent = 'Deleting...';
    try {
        const res  = await fetch(`${MJ_BASE}/${deletingJobId}`, {
            method:  'DELETE',
            headers: { 'X-CSRF-TOKEN': MJ_CSRF, 'Accept': 'application/json' },
        });
        const json = await res.json();
        if (res.ok && json.success) {
            mjToast(json.message);
            closeDeleteModal();
            const row = document.getElementById(`mj-row-${deletingJobId}`);
            if (row) row.remove();
        } else {
            mjToast(json.message || 'Delete failed.', 'error');
        }
    } catch {
        mjToast('Request failed.', 'error');
    } finally {
        this.disabled    = false;
        this.textContent = 'Delete';
        deletingJobId    = null;
    }
});


// ── View Modal ────────────────────────────────────────────────────────────
const MJ_JOBS_BASE = '{{ url("/jobs") }}';

function openViewModal(id) {
    document.getElementById('viewBackdrop').hidden = false;
    document.getElementById('viewModal').hidden    = false;
    document.body.style.overflow = 'hidden';
    loadViewData(id);
}
function closeViewModal() {
    document.getElementById('viewBackdrop').hidden = true;
    document.getElementById('viewModal').hidden    = true;
    document.body.style.overflow = '';
}

// Update Escape key handler to also close view modal
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeEditModal(); closeDeleteModal(); closeViewModal(); }
});

async function loadViewData(id) {
    // Reset
    document.getElementById('view_banner_wrap').hidden = true;
    document.getElementById('view_banner_img').src     = '';

    try {
        const res  = await fetch(`${MJ_BASE}/${id}/edit`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': MJ_CSRF }
        });
        const d = await res.json();

        document.getElementById('view_title').textContent   = d.title        ?? '—';
        document.getElementById('view_company').textContent = d.company_name ?? '—';
        document.getElementById('view_job_type').textContent  = d.job_type   ?? '—';
        document.getElementById('view_work_mode').textContent = d.work_mode  ?? '—';
        document.getElementById('view_location').textContent  = d.location   ?? '—';

        // Salary
        let salary = 'Not disclosed';
        if (d.salary_min && d.salary_max)
            salary = `₹${Number(d.salary_min).toLocaleString()} – ₹${Number(d.salary_max).toLocaleString()}`;
        else if (d.salary_min)
            salary = `₹${Number(d.salary_min).toLocaleString()}+`;
        document.getElementById('view_salary').textContent = salary;

        // Deadline
        document.getElementById('view_deadline').textContent = d.application_deadline
            ? new Date(d.application_deadline).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'})
            : '—';

        // App link
        const linkEl = document.getElementById('view_app_link');
        if (d.application_link) {
            linkEl.innerHTML = `<a href="${d.application_link}" target="_blank" rel="noopener" style="color:var(--mj-accent);word-break:break-all;">${d.application_link}</a>`;
        } else {
            linkEl.textContent = '—';
        }

        // Prose fields
        document.getElementById('view_description').textContent = d.description ?? '';
        const reqWrap = document.getElementById('view_requirements_wrap');
        if (d.requirements) {
            document.getElementById('view_requirements').textContent = d.requirements;
            reqWrap.hidden = false;
        } else {
            reqWrap.hidden = true;
        }

        // Banner
        if (d.banner_image) {
            document.getElementById('view_banner_img').src = `${MJ_STORAGE}/${d.banner_image}`;
            document.getElementById('view_banner_wrap').hidden = false;
        }

        // Public link in header
        document.getElementById('view_public_link').href = `${MJ_JOBS_BASE}/${id}`;

    } catch {
        mjToast('Failed to load job details.', 'error');
        closeViewModal();
    }
}
</script>
@endpush

@endsection