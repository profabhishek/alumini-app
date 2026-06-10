@extends ('layouts.app')

@section('title', 'Notice Board')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/notice.css') }}" />
@endpush

@section('content')
    <section class="notice-page">
        {{-- HERO --}}
        <section class="notice-hero">
            <div class="container">
                <span class="notice-badge"> ICCR Alumni Updates </span>

                <h1 class="notice-title">Notice Board</h1>

                <p class="notice-subtitle">Stay informed about important announcements, circulars, deadlines and community
                    updates.</p>
            </div>
        </section>

        {{-- SEARCH --}}
        <section class="notice-toolbar">
            <div class="container">
                <form class="notice-search-form">
                    <input type="text" placeholder="Search notices..." />

                    <button type="submit">Search</button>
                </form>
            </div>
        </section>

        {{-- NOTICE LIST --}}
        <section class="notice-section">
            <div class="container">
                <div class="notice-grid">
                    @for ($i = 1; $i <= 9; $i++)
                        <article class="notice-card">
                            <div class="notice-top">
                                <span class="notice-date"> 07 May 2026 </span>

                                <span class="notice-tag"> Important </span>
                            </div>

                            <h3 class="notice-heading">
                                India Africa Forum Summit
                            </h3>

                            <p class="notice-description">Alumni are requested to complete registration before the specified
                                deadline and review event guidelines.</p>

                            <div class="notice-footer">
                                <a href="#" class="notice-link">
                                    View Notice
                                </a>
                            </div>
                        </article>
                    @endfor
                </div>
            </div>
        </section>

        {{-- PAGINATION --}}
        <section class="notice-pagination">
            <div class="container">
                <div class="pagination-demo">
                    <button><</button>

                    <button class="active">1</button>

                    <button>2</button>

                    <button>3</button>

                    <button>></button>
                </div>
            </div>
        </section>
    </section>

@endsection