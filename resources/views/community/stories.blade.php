@extends('layouts.app')

@section('title', 'Success Stories')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stories.css') }}">
@endpush

@section('content')

@php
    $storyThemes = [
        ['grad' => 'rgba(244, 168, 37, 0.12), rgba(15, 29, 48, 0.85)', 'icon' => '<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/>'],
        ['grad' => 'rgba(30, 60, 100, 0.8), rgba(244, 168, 37, 0.1)', 'icon' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
        ['grad' => 'rgba(50, 20, 80, 0.8), rgba(244, 168, 37, 0.12)', 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
    ];

    // Treat the most recent story as "featured" only on an unfiltered, first-page view
    $showFeatured = !request()->filled('q') && !request()->filled('category') && $stories->currentPage() == 1;
    $featured = $showFeatured ? $stories->first() : null;
    $featExcerpt = $featured ? ($featured->excerpt ?: \App\Models\Story::makeExcerpt($featured->body, 220)) : null;
@endphp

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


    {{-- SEARCH + CATEGORY FILTERS --}}
    <section class="stories-search-section">
        <div class="container">
            <form class="stories-search-form" action="{{ route('stories.index') }}" method="GET">
                @if(request()->filled('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search stories by title, excerpt or category..."
                >
                <button type="submit">
                    Search
                </button>
            </form>

            @if($categories->isNotEmpty())
            <div class="stories-categories">
                <a href="{{ route('stories.index', array_filter(['q' => request('q')])) }}"
                   class="category-pill {{ !request('category') ? 'active' : '' }}">
                    All
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('stories.index', array_filter(['q' => request('q'), 'category' => $cat])) }}"
                       class="category-pill {{ request('category') === $cat ? 'active' : '' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </section>


    {{-- FEATURED STORY --}}
    @if($featured)
    @php $featTheme = $storyThemes[0]; @endphp
    <section class="featured-story-section">
        <div class="container">
            <article class="featured-story">
                <div class="featured-story-image">
                    @if($featured->cover_image)
                        <img src="{{ asset('storage/' . $featured->cover_image) }}" alt="{{ $featured->title }}">
                    @else
                        <div class="story-image-placeholder" style="background: linear-gradient(135deg, {{ $featTheme['grad'] }});">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{!! $featTheme['icon'] !!}</svg>
                        </div>
                    @endif
                </div>

                <div class="featured-story-content">
                    <span class="featured-tag">
                        Featured Story
                    </span>

                    <h2>{{ $featured->title }}</h2>

                    <p>{{ $featExcerpt }}</p>

                    <a href="{{ route('stories.show', $featured) }}" class="featured-btn">
                        Read Story
                    </a>
                </div>
            </article>
        </div>
    </section>
    @endif


    {{-- STORIES GRID --}}
    <section class="stories-list-section">
        <div class="container">

            @if($stories->isNotEmpty())
            <div class="stories-grid">
                @foreach($stories as $i => $story)
                    @continue($featured && $story->id === $featured->id)
                    @php
                        $theme = $storyThemes[$i % 3];
                        $excerpt = $story->excerpt ?: \App\Models\Story::makeExcerpt($story->body, 140);
                    @endphp
                    <article class="story-card">
                        <div class="story-image-wrap">
                            @if($story->cover_image)
                                <img src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}" class="story-image">
                            @else
                                <div class="story-image-placeholder" style="background: linear-gradient(135deg, {{ $theme['grad'] }});">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{!! $theme['icon'] !!}</svg>
                                </div>
                            @endif
                        </div>

                        <div class="story-content">
                            <div class="story-date">
                                {{ $story->created_at->format('M Y') }}
                                @if($story->category)
                                    <span class="story-category">{{ $story->category }}</span>
                                @endif
                            </div>

                            <h3 class="story-title-card">{{ $story->title }}</h3>

                            <p class="story-excerpt">{{ $excerpt }}</p>

                            <a href="{{ route('stories.show', $story) }}" class="story-link">
                                Read More →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
            @else
            <div class="stories-empty">
                <p>
                    @if(request()->filled('q') || request()->filled('category'))
                        No stories found matching your search.
                    @else
                        No stories have been published yet.
                    @endif
                </p>
            </div>
            @endif

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

                <a href="{{ \Route::has('stories.create') ? route('stories.create') : '#' }}" class="share-story-btn">
                    Submit Story
                </a>
            </div>
        </div>
    </section>


    {{-- PAGINATION --}}
    @if($stories->hasPages())
    <section class="stories-pagination">
        <div class="container">
            <div class="pagination-demo">

                @if($stories->onFirstPage())
                    <span class="page-btn is-disabled">Previous</span>
                @else
                    <a class="page-btn" href="{{ $stories->previousPageUrl() }}">Previous</a>
                @endif

                @foreach($stories->getUrlRange(max(1, $stories->currentPage() - 2), min($stories->lastPage(), $stories->currentPage() + 2)) as $page => $url)
                    <a class="page-btn {{ $page == $stories->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
                @endforeach

                @if($stories->hasMorePages())
                    <a class="page-btn" href="{{ $stories->nextPageUrl() }}">Next</a>
                @else
                    <span class="page-btn is-disabled">Next</span>
                @endif

            </div>
        </div>
    </section>
    @endif

</section>

@endsection