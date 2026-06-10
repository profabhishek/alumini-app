@extends('layouts.app')

@section('title', 'Success Stories')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stories.css') }}">
@endpush

@section('content')

<section class="stories-page">

    {{-- HERO --}}
    <section class="stories-hero">

        <div class="container">

            <span class="stories-badge">
                ICCR Alumni Network
            </span>

            <h1 class="stories-title">
                Alumni Stories
            </h1>

            <p class="stories-subtitle">
                Inspiring journeys, achievements and experiences
                shared by alumni from around the world.
            </p>

        </div>

    </section>


    {{-- SEARCH --}}
    <section class="stories-search-section">

        <div class="container">

            <form class="stories-search-form">

                <input
                    type="text"
                    placeholder="Search stories..."
                >

                <button type="submit">
                    Search
                </button>

            </form>

        </div>

    </section>


    {{-- FEATURED STORY --}}
    <section class="featured-story-section">

        <div class="container">

            <article class="featured-story">

                <div class="featured-story-image">

                    <img
                        src="https://picsum.photos/1000/700"
                        alt="Featured Story"
                    >

                </div>

                <div class="featured-story-content">

                    <span class="featured-tag">
                        Featured Story
                    </span>

                    <h2>
                        From ICCR Scholar to Global
                        Technology Leader
                    </h2>

                    <p>
                        Discover how international alumni
                        leveraged cultural exchange,
                        academic excellence and networking
                        opportunities to build remarkable careers.
                    </p>

                    <a href="#" class="featured-btn">
                        Read Story
                    </a>

                </div>

            </article>

        </div>

    </section>


    {{-- STORIES GRID --}}
    <section class="stories-list-section">

        <div class="container">

            <div class="stories-grid">

                @for($i = 1; $i <= 9; $i++)

                <article class="story-card">

                    <div class="story-image-wrap">

                        <img
                            src="https://picsum.photos/600/400?random={{ $i }}"
                            alt="Story"
                            class="story-image"
                        >

                    </div>

                    <div class="story-content">

                        <div class="story-date">
                            May 2026
                        </div>

                        <h3 class="story-title-card">
                            India Africa Forum Summit
                        </h3>

                        <p class="story-excerpt">

                            Alumni leaders discuss
                            cultural diplomacy,
                            international cooperation
                            and future opportunities.

                        </p>

                        <a href="#" class="story-link">
                            Read More →
                        </a>

                    </div>

                </article>

                @endfor

            </div>

        </div>

    </section>


    {{-- SHARE STORY CTA --}}
    <section class="story-cta-section">

        <div class="container">

            <div class="story-cta-card">

                <div>

                    <h2>
                        Share Your Journey
                    </h2>

                    <p>
                        Inspire fellow alumni by sharing
                        your professional and personal story.
                    </p>

                </div>

                <a href="#" class="share-story-btn">
                    Submit Story
                </a>

            </div>

        </div>

    </section>


    {{-- PAGINATION --}}
    <section class="stories-pagination">

        <div class="container">

            <div class="pagination-demo">

                <button>
                    Previous
                </button>

                <button class="active">
                    1
                </button>

                <button>
                    2
                </button>

                <button>
                    3
                </button>

                <button>
                    Next
                </button>

            </div>

        </div>

    </section>

</section>

@endsection