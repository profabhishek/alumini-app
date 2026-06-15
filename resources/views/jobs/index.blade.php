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
                            <a href="{{ route('jobs.show', $job->slug ?? $job->id) }}" class="jobs-btn-outline">
                                View Details
                            </a>

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

@endsection