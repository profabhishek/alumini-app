@extends('layouts.app')

@section('title', $story->title . ' — ICCR Alumni Stories')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/stories-redesign.css') }}">
<link rel="stylesheet" href="{{ asset('css/story-detail.css') }}">
@endpush

@section('content')

@php
    $creator     = $story->creator;
    $authorName  = $creator?->full_name ?? 'ICCR Alumni';
    $initials    = collect(preg_split('/\s+/', trim($authorName)))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $isOwner     = session('alumni_id') === $story->created_by;
    $readTime    = max(1, (int) ceil(str_word_count(strip_tags($story->body)) / 200));
    $profileUrl  = $creator?->id ? url('/members/' . $creator->id) : null;
    $storyThemes = [
        'linear-gradient(135deg, #1C2331 0%, #2d3a50 60%, #3d1a00 100%)',
        'linear-gradient(135deg, #0f1e2d 0%, #1C2331 50%, #1a0a2e 100%)',
        'linear-gradient(135deg, #0a1a0a 0%, #1C2331 60%, #1a1000 100%)',
    ];
@endphp

<div class="sd-root">

    {{-- ── STATUS BANNER (owner preview) ──────────────────────────── --}}
    @if($story->status !== 'published' && $isOwner)
    <div class="sd-preview-banner">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        This story is <strong>{{ $story->status }}</strong> — only you can see it.
        @if($story->status === 'rejected' && $story->rejection_reason)
            <span class="sd-preview-banner__reason">Reason: {{ $story->rejection_reason }}</span>
        @elseif($story->status === 'pending')
            It will go live once an admin approves it.
        @endif
    </div>
    @endif

    {{-- ── HERO / HEADER ───────────────────────────────────────────── --}}
    <header class="sd-hero">

        {{-- Cover as full-bleed background --}}
        <div class="sd-hero__bg">
            @if($story->cover_image)
                <img src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}">
            @else
                <div class="sd-hero__bg-placeholder" style="background: {{ $storyThemes[$story->id % 3] }}"></div>
            @endif
            <div class="sd-hero__scrim"></div>
        </div>

        <div class="sd-hero__inner">

            {{-- Back link --}}
            <a href="{{ route('stories.index') }}" class="sd-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                All Stories
            </a>

            {{-- Category + read time --}}
            <div class="sd-hero__eyebrow">
                @if($story->category)
                    <a href="{{ route('stories.index', ['category' => $story->category]) }}" class="sd-cat">{{ $story->category }}</a>
                    <span class="sd-hero__eyebrow-dot"></span>
                @endif
                <span class="sd-hero__readtime">{{ $readTime }} min read</span>
            </div>

            {{-- Title --}}
            <h1 class="sd-hero__title">{{ $story->title }}</h1>

            @if($story->excerpt)
                <p class="sd-hero__excerpt">{{ $story->excerpt }}</p>
            @endif

            {{-- Author + date --}}
            <div class="sd-hero__byline">
                <div class="sd-byline-author">
                    @if($creator?->photo)
                        <a href="{{ $profileUrl ?? '#' }}" class="sd-byline-author__photo-wrap">
                            <img src="{{ asset('storage/' . $creator->photo) }}" alt="{{ $authorName }}" class="sd-byline-author__photo">
                        </a>
                    @else
                        <a href="{{ $profileUrl ?? '#' }}" class="sd-byline-author__initial">{{ $initials }}</a>
                    @endif
                    <div>
                        <a href="{{ $profileUrl ?? '#' }}" class="sd-byline-author__name">{{ $authorName }}</a>
                        @if($creator?->current_job_title)
                            <div class="sd-byline-author__role">{{ $creator->current_job_title }}</div>
                        @elseif($creator?->passing_year)
                            <div class="sd-byline-author__role">Batch of {{ $creator->passing_year }}</div>
                        @elseif($story->creator_role)
                            <div class="sd-byline-author__role">{{ ucfirst($story->creator_role) }}</div>
                        @endif
                    </div>
                </div>

                <div class="sd-byline-meta">
                    <div class="sd-byline-meta__date">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $story->created_at->format('d F Y') }}
                    </div>
                    @if($story->updated_at->gt($story->created_at->addHour()))
                        <div class="sd-byline-meta__updated">Updated {{ $story->updated_at->diffForHumans() }}</div>
                    @endif
                </div>
            </div>

        </div>
    </header>

    {{-- ── ARTICLE BODY ─────────────────────────────────────────────── --}}
    <div class="sd-layout">

        <article class="sd-article">

            {{-- Progress bar (scroll indicator) --}}
            <div class="sd-progress" id="sdProgress" role="progressbar" aria-label="Reading progress"></div>

            <div class="sd-article__body">
                {!! $story->body !!}
            </div>

            {{-- ── Author card at the bottom ──────────────────────── --}}
            @if($creator)
            <div class="sd-author-card">
                <div class="sd-author-card__avatar">
                    @if($creator->photo)
                        <a href="{{ $profileUrl ?? '#' }}">
                            <img src="{{ asset('storage/' . $creator->photo) }}" alt="{{ $authorName }}">
                        </a>
                    @else
                        <a href="{{ $profileUrl ?? '#' }}" class="sd-author-card__initial">{{ $initials }}</a>
                    @endif
                </div>
                <div class="sd-author-card__info">
                    <p class="sd-author-card__label">Written by</p>
                    <a href="{{ $profileUrl ?? '#' }}" class="sd-author-card__name">{{ $authorName }}</a>
                    @if($creator->current_job_title)
                        <p class="sd-author-card__role">{{ $creator->current_job_title }}
                            @if($creator->current_company) · {{ $creator->current_company }}@endif
                        </p>
                    @endif
                    @if($profileUrl)
                        <a href="{{ $profileUrl }}" class="sd-author-card__link">View profile →</a>
                    @endif
                </div>
            </div>
            @endif

        </article>

        {{-- ── Sticky sidebar (TOC / share) ───────────────────────── --}}
        <aside class="sd-sidebar">
            <div class="sd-sidebar__sticky">

                {{-- Share --}}
                <div class="sd-sidebar__block">
                    <p class="sd-sidebar__label">Share</p>
                    <div class="sd-share-btns">
                        <button class="sd-share-btn" id="sdCopyLink" title="Copy link">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            Copy link
                        </button>
                    </div>
                </div>

                {{-- Back to stories --}}
                <div class="sd-sidebar__block">
                    <a href="{{ route('stories.index') }}" class="sd-sidebar__back-link">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                        All stories
                    </a>
                </div>
            </div>
        </aside>

    </div>{{-- /sd-layout --}}

    {{-- ── RELATED STORIES ─────────────────────────────────────────── --}}
    @if($related->isNotEmpty())
    <section class="sd-related">
        <div class="sd-related__inner">

            <div class="sr-section-head" style="margin-bottom:32px">
                <span class="sr-section-head__rule"></span>
                <span class="sr-section-head__label">
                    More {{ $story->category ? 'in ' . $story->category : 'Stories' }}
                </span>
                <span class="sr-section-head__rule"></span>
            </div>

            <div class="sr-grid" style="margin-bottom:0">
                @foreach($related as $i => $rel)
                    @php
                        $relCreator  = $rel->creator;
                        $relExcerpt  = $rel->excerpt ?: \App\Models\Story::makeExcerpt($rel->body, 120);
                        $relReadTime = max(1, (int) ceil(str_word_count(strip_tags($rel->body)) / 200));
                    @endphp
                    <article class="sr-card">
                        <a href="{{ route('stories.show', $rel) }}" class="sr-card__image-link">
                            <div class="sr-card__image">
                                @if($rel->cover_image)
                                    <img src="{{ asset('storage/' . $rel->cover_image) }}" alt="{{ $rel->title }}" loading="lazy">
                                @else
                                    <div class="sr-card__placeholder" style="background: {{ $storyThemes[$i % 3] }}">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z"/></svg>
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div class="sr-card__body">
                            <div class="sr-card__eyebrow">
                                @if($rel->category)
                                    <span class="sr-card__cat">{{ $rel->category }}</span>
                                    <span class="sr-card__eyebrow-dot"></span>
                                @endif
                                <time class="sr-card__date">{{ $rel->created_at->format('d M Y') }}</time>
                                <span class="sr-card__eyebrow-dot"></span>
                                <span class="sr-card__read">{{ $relReadTime }} min</span>
                            </div>
                            <h3 class="sr-card__title">
                                <a href="{{ route('stories.show', $rel) }}">{{ $rel->title }}</a>
                            </h3>
                            <p class="sr-card__excerpt">{{ $relExcerpt }}</p>
                            <div class="sr-card__footer">
                                <div class="sr-author">
                                    @if($relCreator?->photo)
                                        <img class="sr-author__photo" src="{{ asset('storage/' . $relCreator->photo) }}" alt="{{ $relCreator->full_name }}" style="border:none">
                                    @else
                                        <span class="sr-author__initial" style="text-decoration:none">{{ strtoupper(substr($relCreator?->full_name ?? 'A', 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <div class="sr-author__name" style="color:var(--sr-ink)">{{ $relCreator?->full_name ?? 'ICCR Alumni' }}</div>
                                    </div>
                                </div>
                                <a href="{{ route('stories.show', $rel) }}" class="sr-card__read-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

        </div>
    </section>
    @endif

    {{-- ── SUBMIT CTA ───────────────────────────────────────────────── --}}
    <div style="max-width:1160px;margin:0 auto;padding:0 28px 80px">
        <div class="sr-cta">
            <div class="sr-cta__inner">
                <div class="sr-cta__text">
                    <p class="sr-cta__eyebrow">Your turn</p>
                    <h2 class="sr-cta__title">Share your journey with the network</h2>
                    <p class="sr-cta__sub">Every alumnus has a story worth telling. Inspire the next generation.</p>
                </div>
                <a href="{{ \Route::has('stories.create') ? route('stories.create') : '#' }}" class="sr-btn sr-btn--primary">
                    Submit your story
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>

</div>{{-- /sd-root --}}

@push('scripts')
<script>
// Reading progress bar
(function () {
    const bar = document.getElementById('sdProgress');
    if (!bar) return;
    function update() {
        const el  = document.documentElement;
        const pct = el.scrollTop / (el.scrollHeight - el.clientHeight) * 100;
        bar.style.width = Math.min(100, pct) + '%';
    }
    window.addEventListener('scroll', update, { passive: true });
    update();
})();

// Copy link
document.getElementById('sdCopyLink')?.addEventListener('click', function () {
    navigator.clipboard?.writeText(location.href).then(() => {
        const orig = this.innerHTML;
        this.textContent = 'Copied!';
        this.style.background = '#e8640c';
        this.style.color = '#fff';
        setTimeout(() => { this.innerHTML = orig; this.style.background = ''; this.style.color = ''; }, 2000);
    });
});
</script>
@endpush

@endsection