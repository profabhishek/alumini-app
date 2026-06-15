@extends('layouts.app')

@section('title', $story->title)

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

    $authorName = $story->creator->full_name ?? 'ICCR Alumni';
    $initials = collect(preg_split('/\s+/', trim($authorName)))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $isOwner = session('alumni_id') === $story->created_by;
@endphp

<section class="stories-page story-detail-page">

    @if($story->status !== 'published' && $isOwner)
    <div class="story-preview-banner">
        This story is currently <strong>{{ $story->status }}</strong> and is only visible to you.
        @if($story->status === 'rejected' && $story->rejection_reason)
            <br>Reason: {{ $story->rejection_reason }}
        @elseif($story->status === 'pending')
            It will go live once approved by an admin.
        @endif
    </div>
    @endif


    {{-- HERO --}}
    <section class="stories-hero story-detail-hero">
        <div class="container">

            <a href="{{ route('stories.index') }}" class="story-detail-back">
                ← All Stories
            </a>

            @if($story->category)
                <span class="stories-badge">{{ $story->category }}</span>
            @endif

            <h1 class="stories-title">{{ $story->title }}</h1>

            <div class="story-detail-meta">
                <div class="story-detail-author">
                    <span class="story-detail-avatar">{{ $initials }}</span>
                    <div>
                        <span class="story-detail-author-name">{{ $authorName }}</span>
                        @if($story->creator_role)
                            <span class="story-detail-author-role">{{ ucfirst($story->creator_role) }}</span>
                        @endif
                    </div>
                </div>
                <span class="story-detail-date">{{ $story->created_at->format('F j, Y') }}</span>
            </div>

        </div>
    </section>


    {{-- COVER IMAGE --}}
    <section class="story-detail-cover-section">
        <div class="container">
            @if($story->cover_image)
                <div class="story-detail-cover">
                    <img src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}">
                </div>
            @else
                @php $theme = $storyThemes[$story->id % 3]; @endphp
                <div class="story-detail-cover story-detail-cover--placeholder" style="background: linear-gradient(135deg, {{ $theme['grad'] }});">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">{!! $theme['icon'] !!}</svg>
                </div>
            @endif
        </div>
    </section>


    {{-- BODY --}}
    <section class="story-detail-body-section">
        <div class="container">
            <article class="story-detail-body">
                {!! $story->body !!}
            </article>
        </div>
    </section>


    {{-- RELATED STORIES --}}
    @if($related->isNotEmpty())
    <section class="stories-list-section related-stories-section">
        <div class="container">
            <h2 class="related-stories-heading">More in {{ $story->category }}</h2>

            <div class="stories-grid">
                @foreach($related as $i => $rel)
                    @php
                        $theme = $storyThemes[$i % 3];
                        $excerpt = $rel->excerpt ?: \App\Models\Story::makeExcerpt($rel->body, 120);
                    @endphp
                    <article class="story-card">
                        <div class="story-image-wrap">
                            @if($rel->cover_image)
                                <img src="{{ asset('storage/' . $rel->cover_image) }}" alt="{{ $rel->title }}" class="story-image">
                            @else
                                <div class="story-image-placeholder" style="background: linear-gradient(135deg, {{ $theme['grad'] }});">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{!! $theme['icon'] !!}</svg>
                                </div>
                            @endif
                        </div>
                        <div class="story-content">
                            <div class="story-date">{{ $rel->created_at->format('M Y') }}</div>
                            <h3 class="story-title-card">{{ $rel->title }}</h3>
                            <p class="story-excerpt">{{ $excerpt }}</p>
                            <a href="{{ route('stories.show', $rel) }}" class="story-link">Read More →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif


    {{-- SHARE STORY CTA --}}
    <section class="story-cta-section">
        <div class="container">
            <div class="story-cta-card">
                <div>
                    <h2>Share Your Journey</h2>
                    <p>Inspire fellow alumni by sharing your professional and personal story.</p>
                </div>
                <a href="{{ \Route::has('stories.create') ? route('stories.create') : '#' }}" class="share-story-btn">
                    Submit Story
                </a>
            </div>
        </div>
    </section>

</section>

@endsection