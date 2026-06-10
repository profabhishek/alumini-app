@extends('layouts.community')

@section('title', 'My Applications')

@section('hideRightSidebar')
@endsection

@push('styles')
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/community/jobs/my-applications.css') }}">
@endpush

@section('content')

<div class="ma-page">

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="ma-header">
        <div class="ma-header-left">
            <h1 class="ma-title">My Applications</h1>
            <p class="ma-subtitle">Track every job you've applied to — status, documents, and deadlines.</p>
        </div>

        {{-- Mini stat strip --}}
        <div class="ma-stats">
            <div class="ma-stat">
                <span class="ma-stat-num">{{ $applications->total() }}</span>
                <span class="ma-stat-label">Applied</span>
            </div>
            <div class="ma-stat-div"></div>
            <div class="ma-stat">
                <span class="ma-stat-num ma-stat-num--amber">
                    {{ $applications->getCollection()->where('status','shortlisted')->count() }}
                </span>
                <span class="ma-stat-label">Shortlisted</span>
            </div>
            <div class="ma-stat-div"></div>
            <div class="ma-stat">
                <span class="ma-stat-num ma-stat-num--green">
                    {{ $applications->getCollection()->where('status','hired')->count() }}
                </span>
                <span class="ma-stat-label">Hired</span>
            </div>
        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="ma-alert ma-alert--success">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="ma-alert ma-alert--error">
            <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Empty state ──────────────────────────────────────────────────── --}}
    @if($applications->isEmpty())

        <div class="ma-empty">
            <div class="ma-empty-icon">
                <i class="fas fa-file-circle-question"></i>
            </div>
            <h2>No applications yet</h2>
            <p>You haven't applied to any jobs. Browse open positions and get started.</p>
            <a href="{{ route('jobs.index') }}" class="ma-btn ma-btn--primary">
                <i class="fas fa-magnifying-glass"></i> Browse Jobs
            </a>
        </div>

    @else

        {{-- ── Cards ───────────────────────────────────────────────────── --}}
        <div class="ma-grid">
            @foreach($applications as $app)

                @php
                    $job = $app->job;
                    $sm  = [
                        'submitted'   => ['label'=>'Submitted',    'icon'=>'fa-paper-plane', 'cls'=>'submitted'],
                        'shortlisted' => ['label'=>'Shortlisted',  'icon'=>'fa-star',        'cls'=>'shortlisted'],
                        'hired'       => ['label'=>'Hired',        'icon'=>'fa-trophy',      'cls'=>'hired'],
                        'rejected'    => ['label'=>'Not Selected', 'icon'=>'fa-circle-xmark','cls'=>'rejected'],
                    ][$app->status] ?? ['label'=>ucfirst($app->status),'icon'=>'fa-circle','cls'=>'submitted'];
                @endphp

                <article class="ma-card ma-card--{{ $sm['cls'] }} reveal">

                    <div class="ma-card-body">

                        {{-- Top: logo + title + badge --}}
                        <div class="ma-card-top">

                            <div class="ma-logo">
                                @if($job->banner_image)
                                    <img src="{{ asset('storage/'.$job->banner_image) }}" alt="{{ $job->company_name }}">
                                @else
                                    {{ strtoupper(substr($job->company_name,0,1)) }}
                                @endif
                            </div>

                            <div class="ma-title-group">
                                <h2 class="ma-job-name">{{ $job->title }}</h2>
                                <span class="ma-company">
                                    <i class="fas fa-building"></i> {{ $job->company_name }}
                                </span>
                            </div>

                            <span class="ma-badge ma-badge--{{ $sm['cls'] }}">
                                <i class="fas {{ $sm['icon'] }}"></i>
                                {{ $sm['label'] }}
                            </span>

                        </div>

                        {{-- Chips --}}
                        <div class="ma-chips">
                            @if($job->location)
                                <span class="ma-chip"><i class="fas fa-location-dot"></i> {{ $job->location }}</span>
                            @endif
                            <span class="ma-chip"><i class="fas fa-briefcase"></i> {{ $job->job_type }}</span>
                            <span class="ma-chip"><i class="fas fa-display"></i> {{ $job->work_mode }}</span>
                            <span class="ma-chip"><i class="fas fa-indian-rupee-sign"></i> {{ $job->salaryRange() }}</span>
                        </div>

                        <div class="ma-divider"></div>

                        {{-- Timeline --}}
                        <div class="ma-tl">
                            <div class="ma-tl-item">
                                <span class="ma-tl-key">Applied on</span>
                                <span class="ma-tl-val">{{ $app->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="ma-tl-sep"></div>
                            <div class="ma-tl-item">
                                <span class="ma-tl-key">Last updated</span>
                                <span class="ma-tl-val">{{ $app->updated_at->diffForHumans() }}</span>
                            </div>
                            <div class="ma-tl-sep"></div>
                            <div class="ma-tl-item">
                                <span class="ma-tl-key">Job deadline</span>
                                <span class="ma-tl-val {{ $job->isExpired() ? 'ma-tl-val--expired' : '' }}">
                                    @if($job->application_deadline)
                                        {{ $job->isExpired() ? 'Expired' : $job->application_deadline->format('d M Y') }}
                                    @else
                                        Open
                                    @endif
                                </span>
                            </div>
                            @if($app->phone)
                                <div class="ma-tl-sep"></div>
                                <div class="ma-tl-item">
                                    <span class="ma-tl-key">Phone</span>
                                    <span class="ma-tl-val">{{ $app->phone }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Cover letter --}}
                        @if($app->cover_letter)
                            <div class="ma-cover">
                                <span class="ma-cover-label">
                                    <i class="fas fa-align-left"></i> Cover Letter
                                </span>
                                <p>{{ Str::limit(strip_tags($app->cover_letter), 160) }}</p>
                            </div>
                        @endif

                        {{-- Footer actions --}}
                        <div class="ma-actions">
                            <a href="{{ route('jobs.index') }}" class="ma-btn ma-btn--ghost">
                                <i class="fas fa-eye"></i> View Job
                            </a>
                            @if($app->resume)
                                <a href="{{ asset('storage/'.$app->resume) }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="ma-btn ma-btn--ghost">
                                    <i class="fas fa-eye"></i> View Resume
                                </a>
                                <a href="{{ asset('storage/'.$app->resume) }}"
                                   download
                                   class="ma-btn ma-btn--primary">
                                    <i class="fas fa-download"></i> Download Resume
                                </a>
                            @endif
                            @if($app->status === 'hired')
                                <span class="ma-hired-pill">🎉 Congratulations!</span>
                            @endif
                        </div>

                    </div>

                </article>

            @endforeach
        </div>

        {{-- Pagination --}}
        @if($applications->hasPages())
            <div class="ma-pagination">
                {{ $applications->links() }}
            </div>
        @endif

        <div class="ma-browse-row">
            <a href="{{ route('jobs.index') }}" class="ma-btn ma-btn--ghost">
                <i class="fas fa-magnifying-glass"></i> Browse More Jobs
            </a>
        </div>

    @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('show'); obs.unobserve(e.target); } });
    }, { threshold: 0.06 });
    document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
});
</script>
@endpush

@endsection