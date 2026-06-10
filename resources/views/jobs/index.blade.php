@extends('layouts.app')

@section('title', 'Browse Jobs')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
@endpush

@section('content')

<div class="jobs-page">

    {{-- ── Hero ──────────────────────────────────────────────────────────── --}}
    <div class="jobs-hero">
        <div class="jobs-hero__inner">
            <h1 class="jobs-hero__title">Job Opportunities</h1>
            <p class="jobs-hero__sub">Explore opportunities posted by the ICCR Alumni community</p>

            <form method="GET" action="{{ route('jobs.index') }}" class="jobs-search-form">
                <div class="jobs-search-wrap">
                    <svg class="jobs-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by title, company, or location..."
                        class="jobs-search-input"
                    >
                    <button type="submit" class="jobs-search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="jobs-body">

        {{-- ── Filters ─────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('jobs.index') }}" class="jobs-filters" id="filterForm">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <div class="jobs-filter-group">
                <label class="jobs-filter-label">Job Type</label>
                <div class="jobs-filter-pills">
                    <a href="{{ request()->fullUrlWithQuery(['job_type' => '']) }}"
                       class="jobs-pill {{ !request('job_type') ? 'active' : '' }}">All Types</a>
                    @foreach(['Full-Time', 'Part-Time', 'Contract', 'Internship'] as $type)
                        <a href="{{ request()->fullUrlWithQuery(['job_type' => $type]) }}"
                           class="jobs-pill {{ request('job_type') === $type ? 'active' : '' }}">{{ $type }}</a>
                    @endforeach
                </div>
            </div>

            <div class="jobs-filter-group">
                <label class="jobs-filter-label">Work Mode</label>
                <div class="jobs-filter-pills">
                    <a href="{{ request()->fullUrlWithQuery(['work_mode' => '']) }}"
                       class="jobs-pill {{ !request('work_mode') ? 'active' : '' }}">All Modes</a>
                    @foreach(['Remote', 'On-site', 'Hybrid'] as $mode)
                        <a href="{{ request()->fullUrlWithQuery(['work_mode' => $mode]) }}"
                           class="jobs-pill {{ request('work_mode') === $mode ? 'active' : '' }}">{{ $mode }}</a>
                    @endforeach
                </div>
            </div>

            <div class="jobs-filter-group">
                <label class="jobs-filter-label">Status</label>
                <div class="jobs-filter-pills">
                    <a href="{{ request()->fullUrlWithQuery(['filter' => '']) }}"
                       class="jobs-pill {{ !request('filter') ? 'active' : '' }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'active']) }}"
                       class="jobs-pill {{ request('filter') === 'active' ? 'active' : '' }}">Active</a>
                    <a href="{{ request()->fullUrlWithQuery(['filter' => 'expired']) }}"
                       class="jobs-pill {{ request('filter') === 'expired' ? 'active' : '' }}">Expired</a>
                </div>
            </div>

            @if(request()->hasAny(['search', 'job_type', 'work_mode', 'filter']))
                <a href="{{ route('jobs.index') }}" class="jobs-clear-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Clear filters
                </a>
            @endif
        </form>

        {{-- ── Results count ───────────────────────────────────────────── --}}
        <div class="jobs-meta">
            <span class="jobs-count">{{ $jobs->total() }} job{{ $jobs->total() !== 1 ? 's' : '' }} found</span>
            @if(request('search'))
                <span class="jobs-search-term">for "{{ request('search') }}"</span>
            @endif
        </div>

        {{-- ── Job Cards ───────────────────────────────────────────────── --}}
        @if($jobs->isEmpty())
            <div class="jobs-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                <h3>No jobs found</h3>
                <p>Try adjusting your search or filters.</p>
                <a href="{{ route('jobs.index') }}" class="jobs-btn-outline">Clear all filters</a>
            </div>
        @else
            <div class="jobs-grid">
                @foreach($jobs as $job)
                    <div class="job-card {{ $job->isExpired() ? 'job-card--expired' : '' }}">

                        {{-- Banner --}}
                        <div class="job-card__banner">
                            @if($job->banner_image)
                                <img src="{{ asset('storage/' . $job->banner_image) }}" alt="{{ $job->title }}">
                            @else
                                <div class="job-card__banner-placeholder">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                </div>
                            @endif
                            <div class="job-card__badges">
                                <span class="job-badge job-badge--type">{{ $job->job_type }}</span>
                                <span class="job-badge job-badge--mode">{{ $job->work_mode }}</span>
                                @if($job->isExpired())
                                    <span class="job-badge job-badge--expired">Expired</span>
                                @endif
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="job-card__body">
                            <h3 class="job-card__title">{{ $job->title }}</h3>
                            <p class="job-card__company">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                                {{ $job->company_name }}
                            </p>

                            @if($job->location)
                                <p class="job-card__location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    {{ $job->location }}
                                </p>
                            @endif

                            <p class="job-card__salary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                {{ $job->salaryRange() }}
                            </p>

                            @if($job->application_deadline)
                                <p class="job-card__deadline {{ $job->isExpired() ? 'job-card__deadline--expired' : '' }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    Deadline: {{ $job->application_deadline->format('d M Y') }}
                                </p>
                            @endif

                            <p class="job-card__desc">{{ Str::limit(strip_tags($job->description), 100) }}</p>
                        </div>

                        @php
                            $isLoggedIn = session()->has('alumni_id');
                            $alreadyApplied = $isLoggedIn && in_array($job->id, $appliedJobIds ?? []);
                        @endphp

                        <div class="job-card__footer">
                            @if($isLoggedIn)
                                <button type="button"
                                        class="jobs-btn-outline"
                                        onclick="openJobModal({{ $job->id }})">
                                    View Details
                                </button>
                            @else
                                <a href="{{ route('jobs.show', $job) }}" class="jobs-btn-outline">
                                    View Details
                                </a>
                            @endif

                            @if(!$job->isExpired())
                                @if($isLoggedIn)
                                    @if($alreadyApplied)
                                        <span class="jobs-btn-outline jobs-btn-outline--success" aria-disabled="true">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            Already Registered
                                        </span>
                                    @else
                                        <a href="{{ route('jobs.apply', $job) }}" class="jobs-btn-primary">
                                            Apply Now
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="jobs-btn-primary">
                                        Login to Apply
                                    </a>
                                @endif
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($jobs->hasPages())
                <div class="jobs-pagination">
                    {{ $jobs->links() }}
                </div>
            @endif
        @endif

    </div>
</div>

{{-- ════ JOB DETAIL MODAL ════ --}}
<div class="jd-backdrop" id="jdBackdrop" hidden onclick="closeJobModal()"></div>
<div class="jd-modal" id="jdModal" hidden>

    {{-- Header --}}
    <div class="jd-modal__header">
        <div class="jd-modal__header-meta">
            <span class="jd-badge" id="jd_job_type"></span>
            <span class="jd-badge jd-badge--mode" id="jd_work_mode"></span>
            <span class="jd-badge jd-badge--expired" id="jd_expired_badge" hidden>Expired</span>
        </div>
        <button class="jd-modal__close" onclick="closeJobModal()" aria-label="Close">&times;</button>
    </div>

    {{-- Two-column body --}}
    <div class="jd-modal__body">

        {{-- LEFT: job details --}}
        <div class="jd-detail">

            {{-- Banner --}}
            <div class="jd-banner" id="jd_banner_wrap" hidden>
                <img id="jd_banner_img" src="" alt="">
            </div>

            <h2 class="jd-title" id="jd_title"></h2>

                {{-- Already Applied Notice --}}
                <div id="jd_already_applied_notice" hidden>
                    <div class="jd-applied-notice">
                        <svg width="18" height="18" viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>

                        <span>
                            You have already applied for this position.
                        </span>
                    </div>
                </div>

            <div class="jd-meta-row">
                <span class="jd-meta-item" id="jd_company">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                    <span id="jd_company_name"></span>
                </span>
                <span class="jd-meta-item" id="jd_location_wrap" hidden>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span id="jd_location"></span>
                </span>
                <span class="jd-meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    <span id="jd_salary"></span>
                </span>
                <span class="jd-meta-item" id="jd_deadline_wrap" hidden>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Deadline: <span id="jd_deadline"></span>
                </span>
            </div>

            <div class="jd-section">
                <h4 class="jd-section__title">Description</h4>
                <div class="jd-prose" id="jd_description"></div>
            </div>

            <div class="jd-section" id="jd_requirements_wrap" hidden>
                <h4 class="jd-section__title">Requirements</h4>
                <div class="jd-prose" id="jd_requirements"></div>
            </div>

        </div>

        {{-- RIGHT: apply panel --}}
        <div class="jd-apply" id="jd_apply_panel">
            <div class="jd-apply__card">
                <h3 class="jd-apply__title" id="jd_apply_title">Ready to Apply?</h3>
                <p class="jd-apply__sub" id="jd_apply_sub"></p>

                {{-- Active job --}}
                <div id="jd_apply_active">
                    <div id="jd_already_applied" hidden>
                        <div class="jd-status-card">

                            <div class="jd-status-icon">
                                ✓
                            </div>

                            <h3 class="jd-status-title">
                                Application Submitted
                            </h3>

                            <p class="jd-status-text">
                                You have already applied for this position.
                            </p>

                            <div class="jd-status-meta">

                                <div class="jd-status-row">
                                    <span>Applied On</span>
                                    <strong id="jd_applied_date">--</strong>
                                </div>

                                <div class="jd-status-row">
                                    <span>Status</span>
                                    <strong id="jd_application_status">Submitted</strong>
                                </div>

                            </div>

                            <a href="{{ route('jobs.my-applications') }}"
                            class="jd-apply__btn">
                                View My Applications
                            </a>

                        </div>
                    </div>
                        <div id="jd_apply_action_wrap">
                            <a id="jd_apply_btn" href="#" class="jd-apply__btn">
                                Apply Now
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                    <polyline points="15,3 21,3 21,9"/>
                                    <line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                            </a>
                        </div>
                </div>

                {{-- Expired job --}}
                <div id="jd_apply_expired" hidden>
                    <p class="jd-apply__expired-msg">This position is no longer accepting applications.</p>
                </div>

                {{-- No link --}}
                <div id="jd_apply_nolink" hidden>
                    <p class="jd-apply__nolink-msg">Contact the company directly to apply.</p>
                </div>

                <div class="jd-apply__divider"></div>

                <div class="jd-apply__info">
                    <div class="jd-apply__info-row">
                        <span class="jd-apply__info-label">Job Type</span>
                        <span class="jd-apply__info-val" id="jd_side_type"></span>
                    </div>
                    <div class="jd-apply__info-row">
                        <span class="jd-apply__info-label">Work Mode</span>
                        <span class="jd-apply__info-val" id="jd_side_mode"></span>
                    </div>
                    <div class="jd-apply__info-row" id="jd_side_salary_wrap">
                        <span class="jd-apply__info-label">Salary</span>
                        <span class="jd-apply__info-val" id="jd_side_salary"></span>
                    </div>
                    <div class="jd-apply__info-row" id="jd_side_deadline_wrap">
                        <span class="jd-apply__info-label">Deadline</span>
                        <span class="jd-apply__info-val" id="jd_side_deadline"></span>
                    </div>
                </div>

                <a id="jd_fullpage_link" href="#" class="jd-apply__fullpage">
                    View full page ↗
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
const JD_JOBS_URL  = '{{ url("/jobs") }}';
const JD_STORAGE   = '{{ asset("storage") }}';

function openJobModal(id) {
    document.getElementById('jdBackdrop').hidden = false;
    document.getElementById('jdModal').hidden    = false;
    document.body.style.overflow = 'hidden';
    loadJobData(id);
}
function closeJobModal() {
    document.getElementById('jdBackdrop').hidden = true;
    document.getElementById('jdModal').hidden    = true;
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeJobModal();
});

async function loadJobData(id) {
    // Reset UI
    document.getElementById('jd_banner_wrap').hidden      = true;
    document.getElementById('jd_location_wrap').hidden    = true;
    document.getElementById('jd_deadline_wrap').hidden    = true;
    document.getElementById('jd_requirements_wrap').hidden = true;
    document.getElementById('jd_apply_active').hidden     = true;
    document.getElementById('jd_apply_expired').hidden    = true;
    document.getElementById('jd_apply_nolink').hidden     = true;
    document.getElementById('jd_expired_badge').hidden    = true;
    document.getElementById('jd_title').textContent       = 'Loading...';
    document.getElementById('jd_apply_panel').hidden = false;
    document.getElementById('jd_already_applied_notice').hidden = true;
    document.getElementById('jd_apply_title').textContent = 'Ready to Apply?';
    document.getElementById('jd_apply_action_wrap').hidden = false;
    document.getElementById('jd_already_applied').hidden = true;

    try {
        const res = await fetch(`${JD_JOBS_URL}/${id}`, {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error();
        const d = await res.json();

        // Header badges
        document.getElementById('jd_job_type').textContent  = d.job_type;
        document.getElementById('jd_work_mode').textContent = d.work_mode;

        // Banner
        if (d.banner_image) {
            document.getElementById('jd_banner_img').src   = `${JD_STORAGE}/${d.banner_image}`;
            document.getElementById('jd_banner_wrap').hidden = false;
        }

        // Title & company
        document.getElementById('jd_title').textContent        = d.title;
        document.getElementById('jd_company_name').textContent = d.company_name;

        // Location
        if (d.location) {
            document.getElementById('jd_location').textContent = d.location;
            document.getElementById('jd_location_wrap').hidden = false;
        }

        // Salary
        let salary = 'Not disclosed';
        if (d.salary_min && d.salary_max)
            salary = `₹${Number(d.salary_min).toLocaleString()} – ₹${Number(d.salary_max).toLocaleString()}`;
        else if (d.salary_min)
            salary = `₹${Number(d.salary_min).toLocaleString()}+`;
        document.getElementById('jd_salary').textContent = salary;

        // Deadline
        const isExpired = d.application_deadline && new Date(d.application_deadline) < new Date();
        if (d.application_deadline) {
            const fmt = new Date(d.application_deadline).toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'});
            document.getElementById('jd_deadline').textContent      = fmt;
            document.getElementById('jd_deadline_wrap').hidden      = false;
            document.getElementById('jd_side_deadline').textContent = fmt;
        } else {
            document.getElementById('jd_side_deadline_wrap') && (document.getElementById('jd_side_deadline_wrap').hidden = true);
        }

        if (isExpired) document.getElementById('jd_expired_badge').hidden = false;

        // Prose
        document.getElementById('jd_description').textContent = d.description;
        if (d.requirements) {
            document.getElementById('jd_requirements').textContent  = d.requirements;
            document.getElementById('jd_requirements_wrap').hidden  = false;
        }

        // Side panel
        document.getElementById('jd_side_type').textContent   = d.job_type;
        document.getElementById('jd_side_mode').textContent   = d.work_mode;
        document.getElementById('jd_side_salary').textContent = salary;
        document.getElementById('jd_apply_sub').textContent   = `${d.company_name} · ${d.job_type}`;
        document.getElementById('jd_fullpage_link').href = `/jobs/${id}/apply`;
        document.getElementById('jd_already_applied').hidden = true;

        // Apply state
        if (isExpired) {

            document.getElementById('jd_apply_expired').hidden = false;

        } 
        else if (d.already_applied) {

            document.getElementById(
                'jd_already_applied_notice'
            ).hidden = false;

            document.getElementById(
                'jd_already_applied'
            ).hidden = false;

            document.getElementById(
                'jd_apply_active'
            ).hidden = false;

            document.getElementById(
                'jd_apply_title'
            ).textContent = 'Application Status';

            document.getElementById(
                'jd_apply_action_wrap'
            ).hidden = true;

            if (d.application) {

                document.getElementById(
                    'jd_applied_date'
                ).textContent =
                    d.application.applied_at || '--';

                document.getElementById(
                    'jd_application_status'
                ).textContent =
                    d.application.status || 'Submitted';
            }
        }
        else {

            document.getElementById('jd_apply_btn').href =
                `/jobs/${d.id}/apply`;

            document.getElementById('jd_apply_active').hidden = false;
        }


    } catch {
        document.getElementById('jd_title').textContent = 'Failed to load job details.';
    }
}
</script>
@endpush

@endsection