@extends('layouts.community')

@section('title', 'Pending Jobs')
@section('hideRightSidebar')
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/jobs/pending-job.css') }}">
@endpush

@section('content')

<div class="admin-page">
    <div class="admin-page__header">
        <div>
            <p class="admin-page__eyebrow">Job Moderation</p>
            <h1 class="admin-page__title">Moderation Queue</h1>
            <p class="admin-page__subtitle">
                Review pending job posts, approve valid listings, and reject unwanted submissions before they go live.
            </p>
        </div>

        <div class="admin-page__actions">
            <a href="{{ route('admin.jobs.index') }}" class="admin-btn admin-btn--ghost">
                All Jobs
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert--success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert--error">
            {{ session('error') }}
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card__label">Pending</div>
            <div class="stat-card__value">{{ $stats['pending'] ?? 0 }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__label">Published</div>
            <div class="stat-card__value">{{ $stats['published'] ?? 0 }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__label">Rejected</div>
            <div class="stat-card__value">{{ $stats['rejected'] ?? 0 }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-card__label">Total</div>
            <div class="stat-card__value">{{ $stats['total'] ?? 0 }}</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head">
            <div>
                <h2 class="panel__title">Pending Job Posts</h2>
                <p class="panel__subtitle">Search by title, company, location, or creator.</p>
            </div>

            <form method="GET" action="{{ route('admin.jobs.pending') }}" class="search-form">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search pending jobs..."
                    class="search-form__input"
                >
                <button type="submit" class="admin-btn admin-btn--primary">Search</button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.jobs.pending') }}" class="admin-btn admin-btn--ghost">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Creator</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        <tr>
                            <td>
                                <div class="job-cell">
                                    <div class="job-cell__title">{{ $job->title }}</div>
                                    <div class="job-cell__meta">
                                        Posted {{ optional($job->created_at)->format('d M Y') ?? 'N/A' }}
                                    </div>
                                </div>
                            </td>
                            <td>{{ $job->company_name ?? '-' }}</td>
                            <td>{{ $job->location ?? '-' }}</td>
                            <td>{{ $job->job_type ?? '-' }}</td>
                            <td>
                                <div class="creator-cell">
                                    <div class="creator-cell__name">
                                        {{ optional($job->creator)->full_name ?? 'Unknown' }}
                                    </div>
                                    <div class="creator-cell__meta">
                                        {{ optional($job->creator)->email ?? '' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($job->application_deadline)
                                    {{ \Carbon\Carbon::parse($job->application_deadline)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--pending">
                                    {{ ucfirst($job->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="action-group">
                                    <form method="POST" action="{{ route('admin.jobs.approve', $job->id) }}" class="inline-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="action-btn action-btn--approve">
                                            Approve
                                        </button>
                                    </form>

                                    <button
                                        type="button"
                                        class="action-btn action-btn--reject"
                                        data-reject-open
                                        data-reject-url="{{ route('admin.jobs.reject', $job->id) }}"
                                        data-job-title="{{ $job->title }}"
                                    >
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state__icon">⏳</div>
                                    <div class="empty-state__title">No pending jobs right now</div>
                                    <div class="empty-state__text">
                                        New submissions will appear here for moderation.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $jobs->links() }}
        </div>
    </div>
</div>

<div class="modal-backdrop" id="rejectModal" hidden>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="rejectModalTitle">
        <div class="modal-card__head">
            <div>
                <h3 class="modal-card__title" id="rejectModalTitle">Reject Job</h3>
                <p class="modal-card__subtitle">
                    Add a reason if needed. The reason is optional, because apparently humans enjoy ambiguity.
                </p>
            </div>

            <button type="button" class="modal-close" data-reject-close aria-label="Close modal">×</button>
        </div>

        <form method="POST" id="rejectForm">
            @csrf
            @method('PATCH')

            <div class="modal-card__body">
                <p class="modal-job-title" id="rejectJobTitle"></p>

                <label for="rejectReason" class="field-label">Reason</label>
                <textarea
                    name="reason"
                    id="rejectReason"
                    class="field-textarea"
                    rows="4"
                    placeholder="Optional reason for rejection"
                ></textarea>
            </div>

            <div class="modal-card__footer">
                <button type="button" class="admin-btn admin-btn--ghost" data-reject-close>
                    Cancel
                </button>
                <button type="submit" class="admin-btn admin-btn--danger">
                    Reject Job
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const title = document.getElementById('rejectJobTitle');
    const reason = document.getElementById('rejectReason');

    if (!modal || !form || !title || !reason) return;

    const closeModal = () => {
        modal.hidden = true;
        reason.value = '';
    };

    document.querySelectorAll('[data-reject-open]').forEach(button => {
        button.addEventListener('click', function () {
            form.action = this.dataset.rejectUrl || '#';
            title.textContent = this.dataset.jobTitle
                ? `Reject: ${this.dataset.jobTitle}`
                : 'Reject this job';

            reason.value = '';
            modal.hidden = false;
            reason.focus();
        });
    });

    document.querySelectorAll('[data-reject-close]').forEach(button => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });
});
</script>
@endpush
