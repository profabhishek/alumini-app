@extends('layouts.app')

@section('title', 'Alumni Stories')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/stories-redesign.css') }}">
@endpush

@section('content')

@php
    $storyThemes = [
        'linear-gradient(135deg, #1C2331 0%, #2d3a50 60%, #3d1a00 100%)',
        'linear-gradient(135deg, #0f1e2d 0%, #1C2331 50%, #1a0a2e 100%)',
        'linear-gradient(135deg, #0a1a0a 0%, #1C2331 60%, #1a1000 100%)',
    ];

    $showFeatured = !request()->filled('q') && !request()->filled('category') && $stories->currentPage() == 1;
    $featured = $showFeatured ? $stories->first() : null;
    $featExcerpt = $featured
        ? ($featured->excerpt ?: \App\Models\Story::makeExcerpt($featured->body, 260))
        : null;
@endphp

<div class="sr-root">

    {{-- ── MASTHEAD ─────────────────────────────────────────────────── --}}
    <header class="sr-masthead">
        <div class="sr-masthead__inner">
            <div class="sr-masthead__eyebrow">
                <span class="sr-masthead__rule"></span>
                <span class="sr-masthead__org">ICCR Alumni Network</span>
                <span class="sr-masthead__rule"></span>
            </div>
            <h1 class="sr-masthead__title">Alumni Stories</h1>
            <p class="sr-masthead__sub">
                Voices from across the world — journeys, milestones,<br>
                and the thread of culture that connects them.
            </p>
        </div>
    </header>

    {{-- ── SEARCH + FILTERS ────────────────────────────────────────── --}}
    <div class="sr-filters-bar">
        <div class="sr-filters-bar__inner">
            <form class="sr-search" method="GET" action="{{ route('stories.index') }}">
                @if(request()->filled('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <svg class="sr-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="sr-search__input" type="text" name="q"
                       value="{{ request('q') }}"
                       placeholder="Search stories, authors, categories…">
                <button class="sr-search__btn" type="submit">Search</button>
            </form>

            @if(isset($categories) && $categories->isNotEmpty())
            <div class="sr-chips">
                <a href="{{ route('stories.index', array_filter(['q' => request('q')])) }}"
                   class="sr-chip {{ !request('category') ? 'sr-chip--active' : '' }}">All</a>
                @foreach($categories as $cat)
                    <a href="{{ route('stories.index', array_filter(['q' => request('q'), 'category' => $cat])) }}"
                       class="sr-chip {{ request('category') === $cat ? 'sr-chip--active' : '' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="sr-body">

        {{-- ── FEATURED STORY ──────────────────────────────────────── --}}
        @if($featured)
        <section class="sr-feature">
            <a href="{{ route('stories.show', $featured) }}" class="sr-feature__link">
                <div class="sr-feature__image">
                    @if($featured->cover_image)
                        <img src="{{ asset('storage/' . $featured->cover_image) }}" alt="{{ $featured->title }}" loading="lazy">
                    @else
                        <div class="sr-feature__placeholder" style="background: {{ $storyThemes[0] }}">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="1.2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6Z"/></svg>
                        </div>
                    @endif
                    <div class="sr-feature__overlay"></div>
                </div>

                <div class="sr-feature__content">
                    <div class="sr-feature__eyebrow">
                        <span class="sr-label sr-label--featured">Featured Story</span>
                        @if($featured->category)
                            <span class="sr-label sr-label--cat">{{ $featured->category }}</span>
                        @endif
                    </div>

                    <h2 class="sr-feature__title">{{ $featured->title }}</h2>
                    <p class="sr-feature__excerpt">{{ $featExcerpt }}</p>

                    <div class="sr-feature__meta">
                        <div class="sr-author sr-author--light">
                            @php $fCreator = $featured->creator; @endphp
                            @if($fCreator?->photo)
                                <img loading="lazy" class="sr-author__photo" src="{{ asset('storage/' . $fCreator->photo) }}" alt="{{ $fCreator->full_name }}">
                            @else
                                <span class="sr-author__initial">{{ strtoupper(substr($fCreator?->full_name ?? 'A', 0, 1)) }}</span>
                            @endif
                            <div>
                                <div class="sr-author__name">{{ $fCreator?->full_name ?? 'ICCR Alumni' }}</div>
                                <div class="sr-author__date">{{ $featured->created_at->format('d F Y') }}</div>
                            </div>
                        </div>
                        <span class="sr-feature__cta">
                            Read Story
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </div>
                </div>
            </a>
        </section>
        @endif

        {{-- ── SECTION DIVIDER ─────────────────────────────────────── --}}
        @if($stories->isNotEmpty())
        <div class="sr-section-head">
            <span class="sr-section-head__rule"></span>
            <span class="sr-section-head__label">
                {{ request()->filled('q') || request()->filled('category') ? 'Results' : 'All Stories' }}
                <span class="sr-section-head__count">{{ $stories->total() }}</span>
            </span>
            <span class="sr-section-head__rule"></span>
        </div>
        @endif

        {{-- ── STORIES GRID ─────────────────────────────────────────── --}}
        @if($stories->isNotEmpty())
        <div class="sr-grid">
            @foreach($stories as $i => $story)
                @continue($featured && $story->id === $featured->id)
                @php
                    $creator   = $story->creator;
                    $excerpt   = $story->excerpt ?: \App\Models\Story::makeExcerpt($story->body, 130);
                    $bgTheme   = $storyThemes[$i % 3];
                    $readTime  = max(1, (int) ceil(str_word_count(strip_tags($story->body)) / 200));
                @endphp

                <article class="sr-card">
                    <a href="{{ route('stories.show', $story) }}" class="sr-card__image-link">
                        <div class="sr-card__image">
                            @if($story->cover_image)
                                <img src="{{ asset('storage/' . $story->cover_image) }}" alt="{{ $story->title }}" loading="lazy">
                            @else
                                <div class="sr-card__placeholder" style="background: {{ $bgTheme }}">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="sr-card__body">
                        <div class="sr-card__eyebrow">
                            @if($story->category)
                                <a href="{{ route('stories.index', ['category' => $story->category]) }}" class="sr-card__cat">{{ $story->category }}</a>
                                <span class="sr-card__eyebrow-dot"></span>
                            @endif
                            <time class="sr-card__date" datetime="{{ $story->created_at->toDateString() }}">
                                {{ $story->created_at->format('d M Y') }}
                            </time>
                            <span class="sr-card__eyebrow-dot"></span>
                            <span class="sr-card__read">{{ $readTime }} min read</span>
                        </div>

                        <h3 class="sr-card__title">
                            <a href="{{ route('stories.show', $story) }}">{{ $story->title }}</a>
                        </h3>

                        <p class="sr-card__excerpt">{{ $excerpt }}</p>

                        <div class="sr-card__footer">
                            <div class="sr-author">
                                @if($creator?->photo)
                                    <a href="{{ $creator?->id ? url('/members/' . $creator->id) : '#' }}">
                                        <img loading="lazy" class="sr-author__photo" src="{{ asset('storage/' . $creator->photo) }}" alt="{{ $creator->full_name }}">
                                    </a>
                                @else
                                    <a href="{{ $creator?->id ? url('/members/' . $creator->id) : '#' }}" class="sr-author__initial">
                                        {{ strtoupper(substr($creator?->full_name ?? 'A', 0, 1)) }}
                                    </a>
                                @endif
                                <div>
                                    <a href="{{ $creator?->id ? url('/members/' . $creator->id) : '#' }}" class="sr-author__name">
                                        {{ $creator?->full_name ?? 'ICCR Alumni' }}
                                    </a>
                                    @if($creator?->current_job_title || $creator?->passing_year)
                                        <div class="sr-author__role">
                                            {{ $creator?->current_job_title ?? ('Batch of ' . $creator?->passing_year) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('stories.show', $story) }}" class="sr-card__read-link" aria-label="Read {{ $story->title }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @else
        <div class="sr-empty">
            <div class="sr-empty__icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6Z"/></svg>
            </div>
            <p class="sr-empty__title">No stories found</p>
            <p class="sr-empty__sub">
                @if(request()->filled('q') || request()->filled('category'))
                    Try adjusting your search or browse all stories.
                @else
                    Stories from our alumni community will appear here once published.
                @endif
            </p>
            @if(request()->filled('q') || request()->filled('category'))
                <a href="{{ route('stories.index') }}" class="sr-btn sr-btn--outline">Clear filters</a>
            @endif
        </div>
        @endif

        {{-- ── PAGINATION ───────────────────────────────────────────── --}}
        @if($stories->hasPages())
        <nav class="sr-pagination" aria-label="Story pages">
            @if($stories->onFirstPage())
                <span class="sr-page-btn sr-page-btn--disabled">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </span>
            @else
                <a class="sr-page-btn" href="{{ $stories->previousPageUrl() }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </a>
            @endif

            @foreach($stories->getUrlRange(max(1, $stories->currentPage() - 2), min($stories->lastPage(), $stories->currentPage() + 2)) as $page => $url)
                <a class="sr-page-btn {{ $page == $stories->currentPage() ? 'sr-page-btn--active' : '' }}"
                   href="{{ $url }}">{{ $page }}</a>
            @endforeach

            @if($stories->hasMorePages())
                <a class="sr-page-btn" href="{{ $stories->nextPageUrl() }}">
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @else
                <span class="sr-page-btn sr-page-btn--disabled">
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </span>
            @endif
        </nav>
        @endif

        {{-- ── SUBMIT CTA ───────────────────────────────────────────── --}}
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

    </div>{{-- /sr-body --}}
</div>{{-- /sr-root --}}

@endsection

{{-- Append this @push('scripts') block to the bottom of community/stories.blade.php --}}

@push('scripts')
<script>
(function () {
    'use strict';

    const input = document.querySelector('.sr-search__input');
    const form  = document.querySelector('.sr-search');
    const body  = document.querySelector('.sr-body');
    if (!input || !body) return;

    const FEATURE_SEL    = '.sr-feature';
    const SEC_HEAD_SEL   = '.sr-section-head';
    const GRID_SEL       = '.sr-grid';
    const PAGINATION_SEL = '.sr-pagination';
    const EMPTY_SEL      = '.sr-empty';

    let debounceTimer = null;
    let currentQuery  = '';
    let controller    = null;

    // Spinner
    const spinner = document.createElement('div');
    spinner.style.cssText = [
        'position:fixed','top:0','left:0','right:0','bottom:0',
        'z-index:9998','display:none','align-items:center','justify-content:center',
        'pointer-events:none',
    ].join(';');
    spinner.innerHTML = `
        <div style="width:40px;height:40px;border:3px solid rgba(232,100,12,.2);
             border-top-color:#E8640C;border-radius:50%;
             animation:sr-spin .7s linear infinite"></div>
        <style>@keyframes sr-spin{to{transform:rotate(360deg)}}</style>`;
    document.body.appendChild(spinner);

    function showSpinner() { spinner.style.display = 'flex'; }
    function hideSpinner() { spinner.style.display = 'none'; }

    async function liveSearch(q) {
        if (q === currentQuery) return;
        currentQuery = q;

        controller?.abort();
        controller = new AbortController();
        showSpinner();

        try {
            const url = new URL(window.location.href);
            q ? url.searchParams.set('q', q) : url.searchParams.delete('q');
            history.replaceState(null, '', url.toString());

            const res = await fetch(url.toString(), {
                signal: controller.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!res.ok) throw new Error('Network error');

            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');

            [FEATURE_SEL, SEC_HEAD_SEL, GRID_SEL, PAGINATION_SEL, EMPTY_SEL]
                .forEach(sel => swapElement(sel, doc, body));

        } catch (err) {
            if (err.name !== 'AbortError') console.error('[live-search]', err);
        } finally {
            hideSpinner();
        }
    }

    function swapElement(selector, sourceDoc, targetRoot) {
        const incoming = sourceDoc.querySelector(selector);
        const existing = targetRoot.querySelector(selector);
        if (incoming && existing)       existing.replaceWith(incoming);
        else if (incoming && !existing) targetRoot.appendChild(incoming);
        else if (!incoming && existing) existing.remove();
    }

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => liveSearch(this.value.trim()), 320);
    });

    form?.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        liveSearch(input.value.trim());
    });

    // Category chips
    document.querySelectorAll('.sr-chip').forEach(chip => {
        chip.addEventListener('click', function (e) {
            e.preventDefault();
            const url = new URL(this.href, window.location.origin);
            const q   = input.value.trim();
            if (q) url.searchParams.set('q', q);
            history.replaceState(null, '', url.toString());
            currentQuery = '';
            liveSearch(q);
        });
    });

})();
</script>
@endpush