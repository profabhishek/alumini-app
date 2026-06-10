@extends('layouts.community')

@section('title', 'Applicants — ' . $job->title)

@section('hideRightSidebar')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/jobs/my-jobs.css') }}">
<link rel="stylesheet" href="{{ asset('css/community/jobs/applicants.css') }}">
@endpush

@section('content')

<div class="ap-page">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="ap-header">
        <div class="ap-header-left">
            <a href="{{ route('jobs.my') }}" class="ap-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="15,18 9,12 15,6"/>
                </svg>
                My Jobs
            </a>
            <h1 class="ap-title">{{ $job->title }}</h1>
            <p class="ap-subtitle">
                <span class="ap-company">{{ $job->company_name }}</span>
                <span class="ap-dot">·</span>
                <span class="ap-meta">{{ $job->job_type }}</span>
                <span class="ap-dot">·</span>
                <span class="ap-meta">{{ $job->work_mode }}</span>
                @if($job->location)
                    <span class="ap-dot">·</span>
                    <span class="ap-meta">{{ $job->location }}</span>
                @endif
            </p>
        </div>
    </div>

    {{-- ── Flash ───────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mj-alert mj-alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── Stats ───────────────────────────────────────────────────────── --}}
    <div class="ap-stats">
        <div class="ap-stat">
            <span class="ap-stat__num">{{ $stats['total'] }}</span>
            <span class="ap-stat__label">Total</span>
        </div>
        <div class="ap-stat ap-stat--blue">
            <span class="ap-stat__num">{{ $stats['submitted'] }}</span>
            <span class="ap-stat__label">Submitted</span>
        </div>
        <div class="ap-stat ap-stat--amber">
            <span class="ap-stat__num">{{ $stats['shortlisted'] }}</span>
            <span class="ap-stat__label">Shortlisted</span>
        </div>
        <div class="ap-stat ap-stat--green">
            <span class="ap-stat__num">{{ $stats['hired'] }}</span>
            <span class="ap-stat__label">Hired</span>
        </div>
        <div class="ap-stat ap-stat--grey">
            <span class="ap-stat__num">{{ $stats['rejected'] }}</span>
            <span class="ap-stat__label">Rejected</span>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('jobs.applicants', $job) }}" class="mj-filters">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Search by name, email, phone..."
               class="mj-input mj-search">
        <select name="status" class="mj-input mj-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['submitted','shortlisted','hired','rejected'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="mj-btn mj-btn-secondary">Search</button>
        @if(request('q') || request('status'))
            <a href="{{ route('jobs.applicants', $job) }}" class="mj-btn mj-btn-ghost">Clear</a>
        @endif
    </form>

    {{-- ── Table ───────────────────────────────────────────────────────── --}}
    <div class="mj-table-wrap">
        <table class="mj-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Applicant</th>
                    <th>Contact</th>
                    <th>Applied</th>
                    <th>Resume</th>
                    <th>Cover Letter</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                    <tr id="ap-row-{{ $app->id }}">
                        <td class="mj-td-num">
                            {{ $loop->iteration + ($applications->currentPage() - 1) * $applications->perPage() }}
                        </td>

                        <td class="mj-td-job">
                            <div class="ap-applicant">
                                <div class="ap-avatar">
                                    {{ strtoupper(substr($app->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="mj-job-title">{{ $app->full_name }}</span>
                                    <span class="mj-job-company">{{ $app->email }}</span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="ap-phone">{{ $app->phone ?? '—' }}</span>
                        </td>

                        <td class="mj-td-date mj-muted">
                            {{ $app->created_at->format('d M Y') }}
                        </td>

                        <td>
                            @if($app->resume)
                                <div class="ap-resume-btns">
                                    <a href="{{ asset('storage/' . $app->resume) }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="ap-link-btn" title="View resume">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        View
                                    </a>
                                    <a href="{{ asset('storage/' . $app->resume) }}"
                                       download
                                       class="ap-link-btn" title="Download resume">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                            <polyline points="7,10 12,15 17,10"/>
                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            @else
                                <span class="mj-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($app->cover_letter)
                                <button class="ap-link-btn"
                                        onclick="openCoverModal(`{{ addslashes($app->full_name) }}`, `{{ addslashes($app->cover_letter) }}`)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <polyline points="14,2 14,8 20,8"/>
                                    </svg>
                                    Read
                                </button>
                            @else
                                <span class="mj-muted">—</span>
                            @endif
                        </td>

                        <td>
                            <span class="ap-badge ap-badge--{{ $app->status }}" id="ap-badge-{{ $app->id }}">
                                {{ match($app->status) {
                                    'submitted'   => 'Submitted',
                                    'shortlisted' => 'Shortlisted',
                                    'hired'       => 'Hired',
                                    'rejected'    => 'Not Selected',
                                    default       => ucfirst($app->status),
                                } }}
                            </span>
                            @if($app->status === 'rejected' && $app->rejection_reason)
                                <span class="mj-rejection-hint" title="{{ $app->rejection_reason }}">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="mj-actions">
                                <button class="mj-icon-btn ap-icon-action" title="Update Status"
                                        onclick="openStatusModal(
                                            {{ $app->id }},
                                            `{{ addslashes($app->full_name) }}`,
                                            `{{ $app->status }}`,
                                            `{{ addslashes($app->rejection_reason ?? '') }}`
                                        )">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="mj-empty">
                            No applications yet for this job.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($applications->hasPages())
        <div class="mj-pagination">{{ $applications->links() }}</div>
    @endif

</div>


{{-- ════════════════════════════════════════════════════════════════════
     STATUS MODAL
════════════════════════════════════════════════════════════════════ --}}
<div class="mj-backdrop" id="statusBackdrop" hidden onclick="closeStatusModal()"></div>
<div class="mj-modal mj-modal--sm mj-modal--edit" id="statusModal" hidden>

    <div class="mj-modal__header">
        <h2 class="mj-modal__title">Update Application Status</h2>
        <button class="mj-modal__close" onclick="closeStatusModal()" aria-label="Close">&times;</button>
    </div>

    <div class="mj-modal__body">

        <div class="ap-applicant-summary" id="status_applicant_name_row">
            <div class="ap-avatar ap-avatar--lg" id="status_avatar"></div>
            <div>
                <div class="ap-summary-name" id="status_applicant_name"></div>
                <div class="ap-summary-sub">Select the new status below</div>
            </div>
        </div>

        {{-- Status selector --}}
        <div class="ap-status-grid" id="statusGrid">
            <button type="button" class="ap-status-opt" data-value="submitted">
                <span class="ap-status-opt__icon">📬</span>
                <span class="ap-status-opt__label">Submitted</span>
            </button>
            <button type="button" class="ap-status-opt" data-value="shortlisted">
                <span class="ap-status-opt__icon">⭐</span>
                <span class="ap-status-opt__label">Shortlist</span>
            </button>
            <button type="button" class="ap-status-opt" data-value="hired">
                <span class="ap-status-opt__icon">✅</span>
                <span class="ap-status-opt__label">Hire</span>
            </button>
            <button type="button" class="ap-status-opt" data-value="rejected">
                <span class="ap-status-opt__icon">✗</span>
                <span class="ap-status-opt__label">Reject</span>
            </button>
        </div>

        {{-- Rejection reason (shown only when rejected selected) --}}
        <div id="rejectionReasonWrap" hidden>
            <label class="mj-label">
                Reason for Rejection <span class="mj-req">*</span>
            </label>
            <textarea id="rejectionReason"
                      class="mj-input mj-textarea"
                      rows="3"
                      placeholder="Provide a brief, constructive reason (e.g. skills mismatch, position filled)..."></textarea>
            <span class="mj-field-error" id="rejectionReasonError"></span>
        </div>

        {{-- Email notice --}}
        <div class="ap-email-notice" id="emailNotice" hidden>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,12 2,6"/>
            </svg>
            The applicant will be notified by email about this status change.
        </div>

    </div>

    <div class="mj-modal__footer">
        <button type="button" class="mj-btn mj-btn-ghost" onclick="closeStatusModal()">Cancel</button>
        <button type="button" class="mj-btn mj-btn-primary" id="statusSaveBtn" disabled>
            Save Status
        </button>
    </div>

</div>


{{-- ════════════════════════════════════════════════════════════════════
     COVER LETTER MODAL
════════════════════════════════════════════════════════════════════ --}}
<div class="mj-backdrop" id="coverBackdrop" hidden onclick="closeCoverModal()"></div>
<div class="mj-modal" id="coverModal" hidden>
    <div class="mj-modal__header">
        <h2 class="mj-modal__title">Cover Letter</h2>
        <button class="mj-modal__close" onclick="closeCoverModal()" aria-label="Close">&times;</button>
    </div>
    <div class="mj-modal__body">
        <p class="ap-cover-from" id="coverFrom"></p>
        <div class="ap-cover-body" id="coverBody"></div>
    </div>
    <div class="mj-modal__footer">
        <button type="button" class="mj-btn mj-btn-ghost" onclick="closeCoverModal()">Close</button>
    </div>
</div>

@push('styles')
<style>[hidden]{display:none!important}</style>
@endpush

@push('scripts')
<script>
const AP_CSRF    = '{{ csrf_token() }}';
const AP_JOB_ID  = {{ $job->id }};
const AP_BASE    = '{{ url("/my-jobs") }}';

// ── Toast ─────────────────────────────────────────────────────────────────
function apToast(msg, type = 'success') {
    let t = document.getElementById('apToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'apToast';
        t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;padding:.75rem 1.25rem;border-radius:8px;font-size:.875rem;z-index:9999;opacity:0;transform:translateY(8px);transition:opacity .25s,transform .25s;pointer-events:none;color:#fff;max-width:340px;';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = type === 'error' ? '#dc2626' : '#111827';
    t.style.opacity = '1';
    t.style.transform = 'translateY(0)';
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(8px)'; }, 4500);
}

// ── Status modal state ────────────────────────────────────────────────────
let statusAppId   = null;
let selectedStatus = null;

function openStatusModal(id, name, currentStatus, currentReason) {
    statusAppId    = id;
    selectedStatus = null;

    // Reset UI
    document.getElementById('status_applicant_name').textContent = name;
    document.getElementById('status_avatar').textContent         = name.charAt(0).toUpperCase();
    document.getElementById('rejectionReason').value             = currentReason || '';
    document.getElementById('rejectionReasonError').textContent  = '';
    document.getElementById('rejectionReasonWrap').hidden        = true;
    document.getElementById('emailNotice').hidden                = true;
    document.getElementById('statusSaveBtn').disabled            = true;

    // Mark current status
    document.querySelectorAll('.ap-status-opt').forEach(btn => {
        btn.classList.toggle('ap-status-opt--current', btn.dataset.value === currentStatus);
        btn.classList.remove('ap-status-opt--selected');
    });

    document.getElementById('statusBackdrop').hidden = false;
    document.getElementById('statusModal').hidden    = false;
    document.body.style.overflow = 'hidden';
}

function closeStatusModal() {
    document.getElementById('statusBackdrop').hidden = true;
    document.getElementById('statusModal').hidden    = true;
    document.body.style.overflow = '';
    statusAppId    = null;
    selectedStatus = null;
}

// ── Status option selection ───────────────────────────────────────────────
document.querySelectorAll('.ap-status-opt').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.ap-status-opt').forEach(b => b.classList.remove('ap-status-opt--selected'));
        this.classList.add('ap-status-opt--selected');
        selectedStatus = this.dataset.value;

        const isRejected = selectedStatus === 'rejected';
        document.getElementById('rejectionReasonWrap').hidden = !isRejected;
        document.getElementById('emailNotice').hidden         = false;
        document.getElementById('statusSaveBtn').disabled     = false;

        if (!isRejected) {
            document.getElementById('rejectionReasonError').textContent = '';
        }
    });
});

// ── Save status ───────────────────────────────────────────────────────────
document.getElementById('statusSaveBtn').addEventListener('click', async function () {
    if (!statusAppId || !selectedStatus) return;

    const reason = document.getElementById('rejectionReason').value.trim();
    document.getElementById('rejectionReasonError').textContent = '';

    if (selectedStatus === 'rejected' && !reason) {
        document.getElementById('rejectionReasonError').textContent = 'Please provide a reason for rejection.';
        return;
    }

    this.disabled    = true;
    this.textContent = 'Saving...';

    try {
        const res  = await fetch(`${AP_BASE}/${AP_JOB_ID}/applicants/${statusAppId}/status`, {
            method:  'PATCH',
            headers: {
                'Content-Type':  'application/json',
                'Accept':        'application/json',
                'X-CSRF-TOKEN':  AP_CSRF,
            },
            body: JSON.stringify({
                status:           selectedStatus,
                rejection_reason: selectedStatus === 'rejected' ? reason : null,
            }),
        });

        const json = await res.json();

        if (res.ok && json.success) {
            apToast(json.message);
            closeStatusModal();
            updateRowBadge(statusAppId, json.status, reason);
        } else {
            if (json.errors?.rejection_reason) {
                document.getElementById('rejectionReasonError').textContent = json.errors.rejection_reason[0];
            } else {
                apToast(json.message || 'Something went wrong.', 'error');
            }
        }
    } catch {
        apToast('Request failed. Please try again.', 'error');
    } finally {
        this.disabled    = false;
        this.textContent = 'Save Status';
    }
});

// ── Update badge in table row without reload ──────────────────────────────
function updateRowBadge(appId, status, reason) {
    const badge = document.getElementById(`ap-badge-${appId}`);
    if (!badge) return;

    const labels = {
        submitted:   'Submitted',
        shortlisted: 'Shortlisted',
        hired:       'Hired',
        rejected:    'Not Selected',
    };

    // Remove all status classes and set new one
    badge.className = `ap-badge ap-badge--${status}`;
    badge.textContent = labels[status] ?? status;

    // Update reason tooltip on the row if rejected
    const row = document.getElementById(`ap-row-${appId}`);
    if (!row) return;

    // Remove old hint if present
    const oldHint = row.querySelector('.mj-rejection-hint');
    if (oldHint) oldHint.remove();

    if (status === 'rejected' && reason) {
        const hint = document.createElement('span');
        hint.className = 'mj-rejection-hint';
        hint.title     = reason;
        hint.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
        badge.insertAdjacentElement('afterend', hint);
    }
}

// ── Cover letter modal ────────────────────────────────────────────────────
function openCoverModal(name, text) {
    document.getElementById('coverFrom').textContent = `From: ${name}`;
    document.getElementById('coverBody').textContent = text;
    document.getElementById('coverBackdrop').hidden  = false;
    document.getElementById('coverModal').hidden     = false;
    document.body.style.overflow = 'hidden';
}

function closeCoverModal() {
    document.getElementById('coverBackdrop').hidden = true;
    document.getElementById('coverModal').hidden    = true;
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeStatusModal(); closeCoverModal(); }
});
</script>
@endpush

@endsection