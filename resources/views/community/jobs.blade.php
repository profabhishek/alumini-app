@extends('layouts.app')

@section('title', 'Find Jobs')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
@endpush

@section('content')

<section class="jobs-page">

    {{-- HERO --}}
    <section class="jobs-hero">
        <div class="container">
            <span class="jobs-badge">ICCR Alumni Careers</span>
            <h1 class="jobs-title">Find Jobs</h1>
            <p class="jobs-subtitle">
                Discover career opportunities shared by
                alumni and partner organizations worldwide.
            </p>
        </div>
    </section>


    {{-- SEARCH BAR --}}
    <section class="jobs-search-section">
        <div class="container">
            <form class="jobs-search-form" method="GET" action="{{ route('jobs.index') }}">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Job title, keyword or company..."
                >
                <input
                    type="text"
                    name="location"
                    value="{{ request('location') }}"
                    placeholder="Location"
                >
                <button type="submit">Search Jobs</button>
            </form>
        </div>
    </section>


    {{-- FILTERS --}}
    <section class="jobs-filter-section">
        <div class="container">
            <div class="jobs-filters">

                <a href="{{ route('jobs.index', array_merge(request()->except(['job_type', 'work_mode', 'page']), [])) }}"
                   class="filter-btn {{ !request('job_type') && !request('work_mode') ? 'active' : '' }}">
                    All
                </a>

                @foreach(['Full-Time', 'Part-Time', 'Contract', 'Internship'] as $type)
                    <a href="{{ route('jobs.index', array_merge(request()->except(['job_type', 'page']), ['job_type' => $type])) }}"
                       class="filter-btn {{ request('job_type') === $type ? 'active' : '' }}">
                        {{ $type }}
                    </a>
                @endforeach

                @foreach(['Remote', 'On-site', 'Hybrid'] as $mode)
                    <a href="{{ route('jobs.index', array_merge(request()->except(['work_mode', 'page']), ['work_mode' => $mode])) }}"
                       class="filter-btn {{ request('work_mode') === $mode ? 'active' : '' }}">
                        {{ $mode }}
                    </a>
                @endforeach

            </div>
        </div>
    </section>


    {{-- JOB LISTING --}}
    <section class="jobs-list-section">
        <div class="container">

            @if($jobs->isEmpty())
                <div style="text-align:center; padding: 60px 20px; color: #888;">
                    <p style="font-size: 18px; font-weight: 600;">No jobs found.</p>
                    <p style="margin-top: 8px; font-size: 15px;">Try adjusting your search or filters.</p>
                    @if(request()->hasAny(['search', 'location', 'job_type', 'work_mode']))
                        <a href="{{ route('jobs.index') }}" style="display:inline-block; margin-top:16px; background:#111; color:#fff; padding:12px 24px; border-radius:12px; text-decoration:none; font-weight:700;">Clear Filters</a>
                    @endif
                </div>
            @else
                <div class="jobs-grid">
                    @foreach($jobs as $job)
                        <article class="job-card reveal">

                            <div class="job-header">
                                @if($job->banner_image)
                                    <div class="company-logo" style="background: #f3f4f6; padding: 0; overflow: hidden;">
                                        <img src="{{ asset('storage/' . $job->banner_image) }}"
                                             alt="{{ $job->company_name }}"
                                             style="width:100%; height:100%; object-fit:cover; border-radius:18px;">
                                    </div>
                                @else
                                    <div class="company-logo">
                                        {{ strtoupper(substr($job->company_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <h3 class="job-title-card">{{ $job->title }}</h3>
                                    <span class="company-name">{{ $job->company_name }}</span>
                                </div>
                            </div>

                            <div class="job-meta">
                                @if($job->location)
                                    <span>📍 {{ $job->location }}</span>
                                @endif
                                <span>💼 {{ $job->job_type }}</span>
                                <span>🖥️ {{ $job->work_mode }}</span>
                                <span>💰 {{ $job->salaryRange() }}</span>
                                @if($job->application_deadline)
                                    <span class="{{ $job->isExpired() ? 'job-meta-expired' : '' }}">
                                        📅 {{ $job->isExpired() ? 'Expired' : 'Deadline: ' . $job->application_deadline->format('d M Y') }}
                                    </span>
                                @endif
                            </div>

                            <p class="job-description">
                                {{ Str::limit(strip_tags($job->description), 120) }}
                            </p>

                            <div class="job-footer">
                                <span class="job-date">
                                    Posted {{ $job->created_at->diffForHumans() }}
                                </span>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <a href="{{ route('jobs.show', $job) }}" class="job-btn">
                                        View Details
                                    </a>
                                    @auth
                                        @if($job->application_link && !$job->isExpired())
                                            <a href="{{ $job->application_link }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="job-btn"
                                               style="background: #E8640C;">
                                                Apply Now
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="job-btn" style="background: #E8640C;">
                                            Login to Apply
                                        </a>
                                    @endauth
                                </div>
                            </div>

                        </article>
                    @endforeach
                </div>

                {{-- PAGINATION --}}
                @if($jobs->hasPages())
                    <div class="jobs-pagination">
                        {{ $jobs->links() }}
                    </div>
                @endif
            @endif

        </div>
    </section>


    {{-- CTA --}}
    @auth
        <section class="jobs-cta-section">
            <div class="container">
                <div class="jobs-cta-card">
                    <div>
                        <h2>Have a job opportunity?</h2>
                        <p>Share it with the ICCR Alumni community and help fellow alumni find their next role.</p>
                    </div>
                    <a href="{{ route('jobs.create') }}" class="post-job-btn">Post a Job</a>
                </div>
            </div>
        </section>
    @endauth

</section>

@push('scripts')
<script>
    // Scroll reveal
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    reveals.forEach(el => observer.observe(el));
</script>
@endpush

@endsection