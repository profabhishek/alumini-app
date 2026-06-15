@extends('layouts.app')

@section('title', $job->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
@endpush

@section('content')

@php
    $isLoggedIn = session()->has('alumni_id');

    $applyTitle = 'Ready to Apply?';
    if ($job->isExpired()) {
        $applyTitle = 'Applications Closed';
    } elseif ($alreadyApplied) {
        $applyTitle = 'Application Status';
    }

    $appliedDate = '--';
    $appStatus   = 'Submitted';
    if ($application) {
        $rawDate = $application->applied_at ?? $application->created_at ?? null;
        if ($rawDate) {
            $appliedDate = \Carbon\Carbon::parse($rawDate)->format('d M Y');
        }
        $appStatus = $application->status ?? 'Submitted';
    }
@endphp

<div class="jobs-page">
    <div class="jobs-body">

        {{-- Back link --}}
        <a href="{{ route('jobs.index') }}" class="job-detail-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Jobs
        </a>

        {{-- Hero --}}
        <div class="job-detail-hero">
            @if($job->banner_image)
                <img src="{{ asset('storage/' . $job->banner_image) }}" alt="{{ $job->title }}">
            @else
                <div class="job-detail-hero-placeholder">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                </div>
            @endif
            <div class="job-detail-hero-overlay"></div>
            <div class="job-detail-hero-badges">
                <span class="job-badge job-badge--type">{{ $job->job_type }}</span>
                <span class="job-badge job-badge--mode">{{ $job->work_mode }}</span>
                @if($job->isExpired())
                    <span class="job-badge job-badge--expired">Expired</span>
                @endif
            </div>
        </div>

        <div class="job-detail-layout">

            {{-- Main content --}}
            <div class="job-detail-main">
                <span class="job-detail-company">
                    <span class="job-detail-company-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                    </span>
                    {{ $job->company_name }}
                </span>

                <h1 class="job-detail-title">{{ $job->title }}</h1>

                <div class="jd-meta-row job-detail-meta-row">
                    @if($job->location)
                        <span class="jd-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $job->location }}
                        </span>
                    @endif

                    <span class="jd-meta-item">
                        {{ $job->salaryRange() }}
                    </span>

                    @if($job->application_deadline)
                        <span class="jd-meta-item {{ $job->isExpired() ? 'job-detail-meta--expired' : '' }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Deadline: {{ $job->application_deadline->format('d M Y') }}
                        </span>
                    @endif
                </div>

                @if($alreadyApplied)
                    <div class="jd-applied-notice">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>You have already applied for this position.</span>
                    </div>
                @endif

                <div class="jd-section">
                    <h4 class="jd-section__title">Description</h4>
                    <div class="jd-prose">{{ $job->description }}</div>
                </div>

                @if($job->requirements)
                    <div class="jd-section">
                        <h4 class="jd-section__title">Requirements</h4>
                        <div class="jd-prose">{{ $job->requirements }}</div>
                    </div>
                @endif
            </div>

            {{-- Apply sidebar --}}
            <aside class="job-detail-sidebar">
                <div class="jd-apply__card">
                    <h3 class="jd-apply__title">{{ $applyTitle }}</h3>
                    <p class="jd-apply__sub">{{ $job->company_name }} · {{ $job->job_type }}</p>

                    @if($job->isExpired())
                        <p class="jd-apply__expired-msg">This position is no longer accepting applications.</p>
                    @elseif(!$isLoggedIn)
                        <a href="{{ route('login') }}" class="jd-apply__btn">
                            Login to Apply
                        </a>
                    @elseif($alreadyApplied)
                        <div class="jd-status-card">
                            <div class="jd-status-icon">✓</div>
                            <h3 class="jd-status-title">Application Submitted</h3>
                            <p class="jd-status-text">You have already applied for this position.</p>
                            <div class="jd-status-meta">
                                <div class="jd-status-row">
                                    <span>Applied On</span>
                                    <strong>{{ $appliedDate }}</strong>
                                </div>
                                <div class="jd-status-row">
                                    <span>Status</span>
                                    <strong>{{ ucfirst($appStatus) }}</strong>
                                </div>
                            </div>
                            <a href="{{ route('jobs.my-applications') }}" class="jd-apply__btn">
                                View My Applications
                            </a>
                        </div>
                    @else
                        <a href="{{ route('jobs.apply', $job) }}" class="jd-apply__btn">
                            Apply Now
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15,3 21,3 21,9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    @endif

                    <div class="jd-apply__divider"></div>

                    <div class="jd-apply__info">
                        <div class="jd-apply__info-row">
                            <span class="jd-apply__info-label">Job Type</span>
                            <span class="jd-apply__info-val">{{ $job->job_type }}</span>
                        </div>
                        <div class="jd-apply__info-row">
                            <span class="jd-apply__info-label">Work Mode</span>
                            <span class="jd-apply__info-val">{{ $job->work_mode }}</span>
                        </div>
                        <div class="jd-apply__info-row">
                            <span class="jd-apply__info-label">Salary</span>
                            <span class="jd-apply__info-val">{{ $job->salaryRange() }}</span>
                        </div>
                        @if($job->application_deadline)
                            <div class="jd-apply__info-row">
                                <span class="jd-apply__info-label">Deadline</span>
                                <span class="jd-apply__info-val">{{ $job->application_deadline->format('d M Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </aside>

        </div>

        {{-- More Jobs --}}
        @if($relatedJobs->isNotEmpty())
            <div class="job-detail-related">
                <div class="related-jobs-header">
                    <h2 class="related-jobs-heading">More Jobs</h2>
                    <a href="{{ route('jobs.index') }}" class="job-detail-viewall">View All →</a>
                </div>

                <div class="jobs-grid">
                    @foreach($relatedJobs as $rel)
                        <div class="job-card {{ $rel->isExpired() ? 'job-card--expired' : '' }}">
                            <div class="job-card__banner">
                                @if($rel->banner_image)
                                    <img src="{{ asset('storage/' . $rel->banner_image) }}" alt="{{ $rel->title }}">
                                @else
                                    <div class="job-card__banner-placeholder">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                    </div>
                                @endif
                                <div class="job-card__badges">
                                    <span class="job-badge job-badge--type">{{ $rel->job_type }}</span>
                                    <span class="job-badge job-badge--mode">{{ $rel->work_mode }}</span>
                                    @if($rel->isExpired())
                                        <span class="job-badge job-badge--expired">Expired</span>
                                    @endif
                                </div>
                            </div>

                            <div class="job-card__body">
                                <h3 class="job-card__title">{{ $rel->title }}</h3>
                                <p class="job-card__company">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                                    {{ $rel->company_name }}
                                </p>
                                @if($rel->location)
                                    <p class="job-card__location">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        {{ $rel->location }}
                                    </p>
                                @endif
                                <p class="job-card__salary">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                    {{ $rel->salaryRange() }}
                                </p>
                            </div>

                            <div class="job-card__footer">
                                <a href="{{ route('jobs.show', $rel->slug ?? $rel->id) }}" class="jobs-btn-outline">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

@endsection