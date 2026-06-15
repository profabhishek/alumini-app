@extends('layouts.app')
@section('title', $news->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/public-content.css') }}?v=1">
@endpush

@section('content')
<section class="content-page">
    <div class="container">
        <div class="content-detail">

            <a href="{{ route('news') }}" class="content-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back to News
            </a>

            @if($news->image)
                <img src="{{ $news->image_url }}" alt="{{ $news->title }}" class="content-detail__hero-img">
            @endif

            <div class="content-detail__meta">
                @if($news->category)
                    <span class="notice-card__cat" style="position:static;">{{ $news->category->name }}</span>
                @endif
                <span class="notice-card__date">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ $news->published_at->format('F j, Y') }} &middot; {{ $news->read_time }} min read
                </span>
                @if($news->author)
                    <span class="notice-card__date">By {{ $news->author->full_name }}</span>
                @endif
            </div>

            <h1 class="content-detail__title">{{ $news->title }}</h1>

            <div class="rich-content">{!! $news->body !!}</div>

            @if($related->isNotEmpty())
                <div class="related-section">
                    <h3>More from News</h3>
                    <div class="related-grid">
                        @foreach($related as $item)
                            <a href="{{ route('news.show', $item) }}" class="notice-card">
                                <div class="notice-card__img">
                                    @if($item->image)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2Z"/></svg>
                                    @endif
                                </div>
                                <div class="notice-card__body">
                                    <span class="notice-card__title">{{ $item->title }}</span>
                                    <span class="notice-card__link">
                                        Read More
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</section>
@endsection