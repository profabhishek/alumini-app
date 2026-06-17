@extends('layouts.app')
@section('title', $news->title . ' — ICCR Alumni News')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/news-redesign.css') }}">
@endpush

@section('content')

@php
    $author     = $news->author;
    $authorName = $author?->full_name ?? 'ICCR Alumni';
    $initials   = collect(preg_split('/\s+/', trim($authorName)))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $profileUrl = $author?->id ? url('/members/' . $author->id) : null;
@endphp

<div class="nr-root">

    {{-- ── READING PROGRESS ─────────────────────────────────────────── --}}
    <div class="nr-progress" id="nrProgress" role="progressbar" aria-label="Reading progress"></div>

    {{-- ── HERO ─────────────────────────────────────────────────────── --}}
    <header class="nd-hero">
        <div class="nd-hero__bg">
            @if($news->image)
                <img src="{{ $news->image_url }}" alt="{{ $news->title }}">
            @else
                <div class="nd-hero__bg-plain"></div>
            @endif
            <div class="nd-hero__scrim"></div>
        </div>

        <div class="nd-hero__inner">

            <a href="{{ route('news') }}" class="nd-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                All News
            </a>

            <div class="nd-hero__eyebrow">
                @if($news->category)
                    <a href="{{ route('news', ['category' => $news->category->slug]) }}" class="nd-cat">{{ $news->category->name }}</a>
                    <span class="nd-hero__dot"></span>
                @endif
                <span class="nd-hero__readtime">{{ $news->read_time }} min read</span>
            </div>

            <h1 class="nd-hero__title">{{ $news->title }}</h1>

            @if($news->excerpt)
                <p class="nd-hero__excerpt">{{ $news->excerpt }}</p>
            @endif

            <div class="nd-hero__byline">
                <div class="nr-byline">
                    @if($author?->photo)
                        <a href="{{ $profileUrl ?? '#' }}" class="nd-author-photo-wrap">
                            <img src="{{ asset('storage/' . $author->photo) }}" alt="{{ $authorName }}" class="nr-byline__photo">
                        </a>
                    @else
                        <a href="{{ $profileUrl ?? '#' }}" class="nr-byline__initial">{{ $initials }}</a>
                    @endif
                    <div>
                        <a href="{{ $profileUrl ?? '#' }}" class="nr-byline__name">{{ $authorName }}</a>
                        @if($author?->current_job_title)
                            <div class="nr-byline__role">{{ $author->current_job_title }}</div>
                        @endif
                    </div>
                </div>
                <div class="nd-hero__datebox">
                    <div class="nd-hero__date">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $news->published_at->format('d F Y') }}
                    </div>
                </div>
            </div>

        </div>
    </header>

    {{-- ── LAYOUT: ARTICLE + SIDEBAR ────────────────────────────────── --}}
    <div class="nd-layout">

        <article class="nd-article">
            <div class="nd-article__body">
                {!! $news->body !!}
            </div>

            {{-- Author card --}}
            @if($author)
            <div class="nd-author-card">
                <div class="nd-author-card__avatar">
                    @if($author->photo)
                        <a href="{{ $profileUrl ?? '#' }}">
                            <img src="{{ asset('storage/' . $author->photo) }}" alt="{{ $authorName }}">
                        </a>
                    @else
                        <a href="{{ $profileUrl ?? '#' }}" class="nd-author-card__initial">{{ $initials }}</a>
                    @endif
                </div>
                <div class="nd-author-card__info">
                    <p class="nd-author-card__label">Written by</p>
                    <a href="{{ $profileUrl ?? '#' }}" class="nd-author-card__name">{{ $authorName }}</a>
                    @if($author->current_job_title)
                        <p class="nd-author-card__role">
                            {{ $author->current_job_title }}
                            @if($author->current_company) · {{ $author->current_company }}@endif
                        </p>
                    @endif
                    @if($profileUrl)
                        <a href="{{ $profileUrl }}" class="nd-author-card__link">View profile →</a>
                    @endif
                </div>
            </div>
            @endif
        </article>

        {{-- Sidebar --}}
        <aside class="nd-sidebar">
            <div class="nd-sidebar__sticky">
                <div class="nd-sidebar__block">
                    <p class="nd-sidebar__label">Share</p>
                    <button class="nd-share-btn" id="ndCopyLink">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        Copy link
                    </button>
                </div>

                @if($news->category)
                <div class="nd-sidebar__block">
                    <p class="nd-sidebar__label">Category</p>
                    <a href="{{ route('news', ['category' => $news->category->slug]) }}" class="nd-sidebar__cat-link">
                        {{ $news->category->name }}
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
                @endif

                <div class="nd-sidebar__block">
                    <a href="{{ route('news') }}" class="nd-sidebar__back">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                        All news
                    </a>
                </div>
            </div>
        </aside>

    </div>

    {{-- ── RELATED ARTICLES ─────────────────────────────────────────── --}}
    @if($related->isNotEmpty())
    <section class="nd-related">
        <div class="nd-related__inner">
            <div class="nr-section-head" style="margin-bottom:32px">
                <span class="nr-section-head__rule"></span>
                <span class="nr-section-head__label">
                    More {{ $news->category ? 'in ' . $news->category->name : 'Articles' }}
                </span>
                <span class="nr-section-head__rule"></span>
            </div>

            <div class="nr-grid" style="margin-bottom:0">
                @foreach($related as $item)
                    @php $relAuthor = $item->author; @endphp
                    <article class="nr-card">
                        <a href="{{ route('news.show', $item) }}" class="nr-card__img-link">
                            <div class="nr-card__image">
                                @if($item->image)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}" loading="lazy">
                                @else
                                    <div class="nr-card__placeholder">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z"/></svg>
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div class="nr-card__body">
                            <div class="nr-card__eyebrow">
                                @if($item->category)
                                    <span class="nr-card__cat">{{ $item->category->name }}</span>
                                    <span class="nr-card__dot"></span>
                                @endif
                                <time class="nr-card__date">{{ $item->published_at->format('d M Y') }}</time>
                            </div>
                            <h3 class="nr-card__title">
                                <a href="{{ route('news.show', $item) }}">{{ $item->title }}</a>
                            </h3>
                            <div class="nr-card__footer">
                                <div class="nr-byline nr-byline--card">
                                    @if($relAuthor?->photo)
                                        <img class="nr-byline__photo nr-byline__photo--sm" src="{{ asset('storage/' . $relAuthor->photo) }}" alt="{{ $relAuthor->full_name }}">
                                    @else
                                        <span class="nr-byline__initial nr-byline__initial--sm">{{ strtoupper(substr($relAuthor?->full_name ?? 'A', 0, 1)) }}</span>
                                    @endif
                                    <span class="nr-byline__name nr-byline__name--dark">{{ $relAuthor?->full_name ?? 'ICCR Alumni' }}</span>
                                </div>
                                <a href="{{ route('news.show', $item) }}" class="nr-card__arrow">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

</div>{{-- /nr-root --}}

@push('scripts')
<script>
// Reading progress
(function () {
    const bar = document.getElementById('nrProgress');
    if (!bar) return;
    window.addEventListener('scroll', function () {
        const el  = document.documentElement;
        bar.style.width = Math.min(100, el.scrollTop / (el.scrollHeight - el.clientHeight) * 100) + '%';
    }, { passive: true });
})();

// Copy link
document.getElementById('ndCopyLink')?.addEventListener('click', function () {
    navigator.clipboard?.writeText(location.href).then(() => {
        const orig = this.innerHTML;
        this.textContent = 'Copied!';
        this.style.cssText += ';background:#e8640c;color:#fff;border-color:#e8640c';
        setTimeout(() => { this.innerHTML = orig; this.style.background = ''; this.style.color = ''; this.style.borderColor = ''; }, 2000);
    });
});
</script>
@endpush

@endsection