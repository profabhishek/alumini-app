@extends('layouts.app')
@section('title', 'News')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/public-content.css') }}?v=1">
@endpush

@section('content')
<section class="content-page">
    <div class="container">

        <div class="content-hero">
            <div class="tag" style="margin:0 auto 14px;display:inline-block;">News & Views</div>
            <h1 class="section-heading" style="color:#1a1a2e;">Latest from the ICCR Alumni Community</h1>
            <p class="section-sub">Stay up to date with stories, announcements, and highlights from alumni around the world.</p>
        </div>

        @if($categories->isNotEmpty())
        <div class="category-pills">
            <a href="{{ route('news') }}" class="category-pill {{ !request('category') ? 'is-active' : '' }}">All</a>
            @foreach($categories as $cat)
                <a href="{{ route('news', ['category' => $cat->slug]) }}"
                   class="category-pill {{ request('category') === $cat->slug ? 'is-active' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
        @endif

        @if($newsItems->isEmpty())
            <div class="content-empty">
                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>
                <p>No news articles yet.</p>
                <span>Check back soon for updates from the alumni community.</span>
            </div>
        @else
            <div class="notice-grid">
                @foreach($newsItems as $item)
                    <a href="{{ route('news.show', $item) }}" class="notice-card">
                        <div class="notice-card__img">
                            @if($item->category)
                                <span class="notice-card__cat">{{ $item->category->name }}</span>
                            @endif
                            @if($item->image)
                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>
                            @endif
                        </div>
                        <div class="notice-card__body">
                            <span class="notice-card__date">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $item->published_at->format('M j, Y') }} &middot; {{ $item->read_time }} min read
                            </span>
                            <span class="notice-card__title">{{ $item->title }}</span>
                            @if($item->excerpt)
                                <span class="notice-card__excerpt">{{ $item->excerpt }}</span>
                            @endif
                            <span class="notice-card__link">
                                Read More
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="content-pagination">{{ $newsItems->links() }}</div>
        @endif

    </div>
</section>
@endsection