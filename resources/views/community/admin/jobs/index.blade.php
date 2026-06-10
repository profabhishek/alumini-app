@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'All Jobs — Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/jobs/admin-jobs.css') }}">
@endpush

@section('content')

{{-- Page Header --}}
<div class="ae-header">
    <div class="ae-header__left">
        <h1 class="ae-title">All Jobs</h1>
        <span class="ae-count">{{ $jobs->total() }} job{{ $jobs->total() !== 1 ? 's' : '' }}</span>
    </div>
    <a href="{{ route('jobs.create') }}" class="me-btn me-btn--primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Post a Job
    </a>
</div>

{{-- Stats --}}
<div class="aj-stats">
    <div class="aj-stat">
        <span class="aj-stat__value">{{ $stats['total'] }}</span>
        <span class="aj-stat__label">Total</span>
    </div>
    <div class="aj-stat aj-stat--pending">
        <span class="aj-stat__value">{{ $stats['pending'] }}</span>
        <span class="aj-stat__label">Pending</span>
    </div>
    <div class="aj-stat aj-stat--published">
        <span class="aj-stat__value">{{ $stats['published'] }}</span>
        <span class="aj-stat__label">Published</span>
    </div>
    <div class="aj-stat aj-stat--rejected">
        <span class="aj-stat__value">{{ $stats['rejected'] }}</span>
        <span class="aj-stat__label">Rejected</span>
    </div>
</div>

{{-- Toolbar --}}
<div class="ae-toolbar">
    <form method="GET" action="{{ route('admin.jobs.index') }}" class="ae-search-form">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        @if(request('job_type'))
            <input type="hidden" name="job_type" value="{{ request('job_type') }}">
        @endif
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search by title, company, location…"
            autocomplete="off"
        >
        <button type="submit">Search</button>
        @if(request()->hasAny(['q', 'status', 'job_type']))
            <a href="{{ route('admin.jobs.index') }}" class="ae-clear">Clear</a>
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
    </div>

    <div class="ae-filters">
        <a href="{{ request()->fullUrlWithQuery(['job_type' => '']) }}"
           class="ae-filter {{ !request('job_type') ? 'active' : '' }}">All Types</a>
        @foreach(['Full-Time','Part-Time','Contract','Internship'] as $type)
            <a href="{{ request()->fullUrlWithQuery(['job_type' => $type]) }}"
               class="ae-filter {{ request('job_type') === $type ? 'active' : '' }}">{{ $type }}</a>
        @endforeach
    </div>
</div>

{{-- Table --}}
@if($jobs->isEmpty())
    <div class="ae-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
        <h3>No jobs found</h3>
        <p>Try a different search or filter.</p>
    </div>
@else
    <div class="ae-table-wrap">
        <table class="ae-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Job</th>
                    <th>Posted By</th>
                    <th>Type / Mode</th>
                    <th>Salary</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $i => $job)
                    <tr data-job-id="{{ $job->id }}">
                        <td class="ae-td--num">{{ $jobs->firstItem() + $i }}</td>
                        <td class="ae-td--title">
                            <span class="ae-event-title">{{ $job->title }}</span>
                            <span class="ae-event-location">🏢 {{ $job->company_name }}</span>
                            @if($job->location)
                                <span class="ae-event-location">📍 {{ $job->location }}</span>
                            @endif
                        </td>
                        <td class="ae-td--creator">
                            {{ $job->creator->full_name ?? '—' }}<br>
                            <span style="font-size:11px;color:#9ca3af;">{{ $job->creator->email ?? '' }}</span>
                        </td>
                        <td>
                            <span class="aj-tag aj-tag--type">{{ $job->job_type }}</span><br>
                            <span class="aj-tag aj-tag--mode" style="margin-top:4px;">{{ $job->work_mode }}</span>
                        </td>
                        <td class="ae-td--creator">{{ $job->salaryRange() }}</td>
                        <td class="ae-td--date">
                            @if($job->application_deadline)
                                <span class="{{ $job->isExpired() ? 'aj-expired' : '' }}">
                                    {{ $job->application_deadline->format('d M Y') }}
                                </span>
                                @if($job->isExpired())
                                    <span class="ae-date-badge ae-date-badge--past">Expired</span>
                                @else
                                    <span class="ae-date-badge ae-date-badge--upcoming">Active</span>
                                @endif
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td class="ae-td--status">
                            <span class="ae-status ae-status--{{ strtolower($job->status) }}">
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td class="ae-td--actions">
                            <button type="button"
                                class="ae-btn-edit"
                                data-job-id="{{ $job->id }}"
                                data-job-title="{{ $job->title }}"
                                data-job-company="{{ $job->company_name }}"
                                data-job-location="{{ $job->location }}"
                                data-job-type="{{ $job->job_type }}"
                                data-job-mode="{{ $job->work_mode }}"
                                data-job-status="{{ $job->status }}"
                                data-job-deadline="{{ optional($job->application_deadline)->format('Y-m-d') }}"
                                data-update-url="{{ route('admin.jobs.update', $job->id) }}"
                                aria-label="Edit {{ $job->title }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button type="button"
                                class="ae-btn-delete"
                                data-job-id="{{ $job->id }}"
                                data-job-title="{{ $job->title }}"
                                data-delete-url="{{ route('admin.jobs.destroy', $job->id) }}"
                                aria-label="Delete {{ $job->title }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($jobs->hasPages())
        <div class="me-pagination">{{ $jobs->links() }}</div>
    @endif
@endif

{{-- Toast --}}
<div id="ajToastContainer" class="me-toast-container" aria-live="polite" aria-atomic="true"></div>


{{-- ── EDIT MODAL ──────────────────────────────────────────────────── --}}
<div id="ajEditModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="ajEditTitle" hidden>
    <div class="me-modal" role="document">

        <div class="me-modal__header">
            <h2 id="ajEditTitle" class="me-modal__title">Edit Job</h2>
            <button type="button" class="me-modal__close" id="ajCloseEdit" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__body">
            <div id="ajEditError" class="me-alert me-alert--danger" hidden></div>

            <form id="ajEditForm" novalidate>
                <input type="hidden" id="ajEditJobId">
                <input type="hidden" id="ajUpdateUrl">

                <div class="me-form-group">
                    <label class="me-label" for="ajTitle">Job Title <span class="me-required">*</span></label>
                    <input type="text" id="ajTitle" name="title" class="me-input" required maxlength="255">
                    <span class="me-field-error" id="ajTitleError"></span>
                </div>

                <div class="me-form-group">
                    <label class="me-label" for="ajCompany">Company Name <span class="me-required">*</span></label>
                    <input type="text" id="ajCompany" name="company_name" class="me-input" required maxlength="255">
                    <span class="me-field-error" id="ajCompanyError"></span>
                </div>

                <div class="me-form-group">
                    <label class="me-label" for="ajLocation">Location</label>
                    <input type="text" id="ajLocation" name="location" class="me-input" maxlength="255">
                </div>

                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="ajJobType">Job Type <span class="me-required">*</span></label>
                        <select id="ajJobType" name="job_type" class="me-input me-select" required>
                            <option value="Full-Time">Full-Time</option>
                            <option value="Part-Time">Part-Time</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="ajWorkMode">Work Mode <span class="me-required">*</span></label>
                        <select id="ajWorkMode" name="work_mode" class="me-input me-select" required>
                            <option value="Remote">Remote</option>
                            <option value="On-site">On-site</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>

                <div class="me-form-row">
                    <div class="me-form-group">
                        <label class="me-label" for="ajStatus">Status <span class="me-required">*</span></label>
                        <select id="ajStatus" name="status" class="me-input me-select" required>
                            <option value="pending">Pending</option>
                            <option value="published">Published</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="me-form-group">
                        <label class="me-label" for="ajDeadline">Application Deadline</label>
                        <input type="date" id="ajDeadline" name="application_deadline" class="me-input">
                    </div>
                </div>

            </form>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="ajCancelEdit">Cancel</button>
            <button type="button" class="me-btn me-btn--primary" id="ajSaveEdit">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Changes
            </button>
        </div>

    </div>
</div>


{{-- ── DELETE MODAL ────────────────────────────────────────────────── --}}
<div id="ajDeleteModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="ajDeleteTitle" hidden>
    <div class="me-modal me-modal--sm" role="document">

        <div class="me-modal__header">
            <h2 id="ajDeleteTitle" class="me-modal__title">Delete Job</h2>
            <button type="button" class="me-modal__close" id="ajCloseDelete" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="me-modal__body">
            <div class="me-delete-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
            </div>
            <p class="me-delete-text">
                Are you sure you want to delete <strong id="ajDeleteJobTitle"></strong>?
                This action <strong>cannot be undone</strong>.
            </p>
        </div>

        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="ajCancelDelete">Cancel</button>
            <button type="button" class="me-btn me-btn--destructive" id="ajConfirmDelete">
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

    // ── Focus trap + modal open/close ─────────────────────────────────
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
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closeModal(backdrop);
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.me-modal-backdrop:not([hidden])').forEach(closeModal);
    });

    // ── Toast ─────────────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const container = $('ajToastContainer');
        const toast = document.createElement('div');
        toast.className = `me-toast me-toast--${type}`;
        toast.setAttribute('role', 'alert');

        const icon = type === 'success'
            ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`
            : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

        toast.innerHTML = `
            <span class="me-toast__icon">${icon}</span>
            <span class="me-toast__message">${message}</span>
            <button class="me-toast__close" aria-label="Dismiss">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>`;

        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('me-toast--show'));

        const dismiss = () => {
            toast.classList.remove('me-toast--show');
            toast.addEventListener('transitionend', () => toast.remove(), { once: true });
        };
        toast.querySelector('.me-toast__close').addEventListener('click', dismiss);
        setTimeout(dismiss, 4500);
    }

    @if(session('success'))
        showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
        showToast(@json(session('error')), 'error');
    @endif

    // ── Helper: set select value ──────────────────────────────────────
    function setSelect(id, value) {
        const sel = $(id);
        if (!sel || !value) return;
        const opt = [...sel.options].find(o => o.value === value);
        if (opt) sel.value = opt.value;
    }

    // ── EDIT ──────────────────────────────────────────────────────────
    const editModal = $('ajEditModal');

    document.querySelectorAll('.ae-btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            $('ajEditJobId').value  = btn.dataset.jobId;
            $('ajUpdateUrl').value  = btn.dataset.updateUrl;
            $('ajTitle').value      = btn.dataset.jobTitle     ?? '';
            $('ajCompany').value    = btn.dataset.jobCompany   ?? '';
            $('ajLocation').value   = btn.dataset.jobLocation  ?? '';
            $('ajDeadline').value   = btn.dataset.jobDeadline  ?? '';

            setSelect('ajJobType',  btn.dataset.jobType);
            setSelect('ajWorkMode', btn.dataset.jobMode);
            setSelect('ajStatus',   btn.dataset.jobStatus);

            // Reset errors
            hide($('ajEditError'));
            document.querySelectorAll('#ajEditForm .me-field-error')
                    .forEach(el => el.textContent = '');
            document.querySelectorAll('#ajEditForm .me-input')
                    .forEach(el => el.classList.remove('me-input--error'));

            openModal(editModal);
        });
    });

    $('ajCloseEdit').addEventListener('click',  () => closeModal(editModal));
    $('ajCancelEdit').addEventListener('click', () => closeModal(editModal));

    $('ajSaveEdit').addEventListener('click', async () => {
        const saveBtn = $('ajSaveEdit');

        // Validate
        let valid = true;
        const title = $('ajTitle').value.trim();
        if (!title) {
            $('ajTitleError').textContent = 'Title is required.';
            $('ajTitle').classList.add('me-input--error');
            valid = false;
        } else {
            $('ajTitleError').textContent = '';
            $('ajTitle').classList.remove('me-input--error');
        }

        const company = $('ajCompany').value.trim();
        if (!company) {
            $('ajCompanyError').textContent = 'Company name is required.';
            $('ajCompany').classList.add('me-input--error');
            valid = false;
        } else {
            $('ajCompanyError').textContent = '';
            $('ajCompany').classList.remove('me-input--error');
        }

        if (!valid) return;

        saveBtn.disabled = true;
        const orig = saveBtn.innerHTML;
        saveBtn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:me-spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Saving…`;

        try {
            const res = await fetch($('ajUpdateUrl').value, {
                method: 'POST',
                headers: {
                    'Accept'       : 'application/json',
                    'X-CSRF-TOKEN' : CSRF,
                    'Content-Type' : 'application/json',
                },
                body: JSON.stringify({
                    _method              : 'PUT',
                    title                : $('ajTitle').value.trim(),
                    company_name         : $('ajCompany').value.trim(),
                    location             : $('ajLocation').value.trim() || null,
                    job_type             : $('ajJobType').value,
                    work_mode            : $('ajWorkMode').value,
                    status               : $('ajStatus').value,
                    application_deadline : $('ajDeadline').value || null,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                const msg = data.errors
                    ? Object.values(data.errors).map(e => e[0]).join(' ')
                    : (data.message ?? 'Something went wrong.');
                $('ajEditError').textContent = msg;
                show($('ajEditError'));
                return;
            }

            closeModal(editModal);
            showToast('Job updated successfully!', 'success');

            // Update row live
            const jobId = $('ajEditJobId').value;
            const row   = document.querySelector(`tr[data-job-id="${jobId}"]`);
            if (row) {
                row.querySelector('.ae-event-title').textContent = $('ajTitle').value.trim();
                row.querySelectorAll('.ae-event-location')[0].textContent = '🏢 ' + $('ajCompany').value.trim();
                const statusEl  = row.querySelector('.ae-status');
                const newStatus = $('ajStatus').value;
                statusEl.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                statusEl.className   = `ae-status ae-status--${newStatus}`;
            }

        } catch (err) {
            $('ajEditError').textContent = 'Network error. Please try again.';
            show($('ajEditError'));
        } finally {
            saveBtn.disabled  = false;
            saveBtn.innerHTML = orig;
        }
    });

    // ── DELETE ────────────────────────────────────────────────────────
    const deleteModal    = $('ajDeleteModal');
    let pendingDeleteUrl = '';
    let pendingDeleteId  = null;

    document.querySelectorAll('.ae-btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
            $('ajDeleteJobTitle').textContent = btn.dataset.jobTitle;
            pendingDeleteUrl = btn.dataset.deleteUrl;
            pendingDeleteId  = btn.dataset.jobId;
            openModal(deleteModal);
        });
    });

    $('ajCloseDelete').addEventListener('click',  () => closeModal(deleteModal));
    $('ajCancelDelete').addEventListener('click', () => closeModal(deleteModal));

    $('ajConfirmDelete').addEventListener('click', async () => {
        const confirmBtn = $('ajConfirmDelete');
        confirmBtn.disabled = true;
        const orig = confirmBtn.innerHTML;
        confirmBtn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:me-spin 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Deleting…`;

        try {
            const res = await fetch(pendingDeleteUrl, {
                method: 'DELETE',
                headers: {
                    'Accept'       : 'application/json',
                    'X-CSRF-TOKEN' : CSRF,
                    'Content-Type' : 'application/json',
                },
            });

            const data = await res.json();

            if (!res.ok) {
                closeModal(deleteModal);
                showToast(data.message ?? 'Could not delete job.', 'error');
                return;
            }

            const row = document.querySelector(`tr[data-job-id="${pendingDeleteId}"]`);
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity    = '0';
                setTimeout(() => row.remove(), 300);
            }

            closeModal(deleteModal);
            showToast('Job deleted successfully.', 'success');

        } catch (err) {
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