@extends('layouts.app')

@section('title', 'Alumni Directory')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/alumni.css') }}">
@endpush

@section('content')
<section class="alumni-page">

    {{-- Hero Section --}}
    <section class="alumni-hero">
        <div class="container">
            <span class="alumni-badge">ICCR Alumni Network</span>
            <h1 class="alumni-title">Meet Our Global Alumni</h1>
            <p class="alumni-subtitle">
                Connect with alumni from different countries, disciplines and generations.
            </p>
        </div>
    </section>

    {{-- Search & Filters --}}
    <section class="alumni-toolbar">
        <div class="container">
            <form method="GET" action="{{ route('alumni') }}" class="toolbar-form">
                <div class="search-box">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search alumni by name, email, institute, department, batch or country..."
                    >
                </div>

                <div class="filter-box">
                    <select name="department">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" @selected($department === $dept)>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-box">
                    <select name="passing_year">
                        <option value="">All Passing Years</option>
                        @foreach($passingYears as $year)
                            <option value="{{ $year }}" @selected((string)$passingYear === (string)$year)>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="search-btn">Search</button>

                <a href="{{ route('alumni') }}" class="reset-btn">Reset</a>
            </form>
        </div>
    </section>

    {{-- Result Summary --}}
    <section class="alumni-summary">
        <div class="container">
            <p>
                Showing {{ $alumni->count() }} of {{ $totalAlumni }} alumni
            </p>
        </div>
    </section>

    {{-- Alumni Listing --}}
    <section class="alumni-list-section">
        <div class="container">

            @if($alumni->count())

                <div class="alumni-grid">
                    @foreach($alumni as $member)
                        @php
                            $parts = preg_split('/\s+/', trim($member->full_name));
                            $firstInitial = strtoupper(substr($parts[0] ?? '', 0, 1));
                            $lastInitial = strtoupper(substr($parts[count($parts) - 1] ?? '', 0, 1));
                            $initials = $firstInitial . $lastInitial;
                        @endphp

                        <article class="alumni-card">
                            @if(session('alumni_id'))
                                <a href="{{ route('alumni.profile', $member->id) }}" class="card-link">
                            @else
                                <a href="{{ route('login') }}?redirect={{ urlencode(route('alumni.profile', $member->id)) }}" class="card-link">
                            @endif
                                <div class="alumni-image-wrap">
                                    @if(!empty($member->photo))
                                        <img
                                            src="{{ asset('storage/' . $member->photo) }}"
                                            alt="{{ $member->full_name }}"
                                            class="alumni-image"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="alumni-placeholder">
                                            <span>{{ $initials }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="alumni-content">
                                    <h3 class="alumni-name">{{ $member->full_name }}</h3>
                                    @if(!empty($member->department))
                                        <p class="alumni-department">{{ $member->department }}</p>
                                    @endif

                                    <div class="alumni-meta">
                                        @if(!empty($member->country))
                                            <span>🌍 {{ $member->country }}</span>
                                        @endif

                                        @if(!empty($member->passing_year))
                                            <span>🎓 {{ $member->passing_year }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

            @else
                <div class="empty-state">
                    <h3>No Alumni Found</h3>
                    <p>No records matched your search criteria.</p>
                </div>
            @endif

        </div>
    </section>

    {{-- Pagination --}}
    @if($alumni->hasPages())
        <section class="pagination-section">
            <div class="container">
                {{ $alumni->withQueryString()->links() }}
            </div>
        </section>
    @endif

</section>
@endsection

@push('scripts')
<script src="{{ asset('js/alumni.js') }}"></script>
@endpush