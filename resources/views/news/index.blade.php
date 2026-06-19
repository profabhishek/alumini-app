@extends('layouts.app')
@section('title', 'News & Views — ICCR Alumni')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/news-redesign.css') }}">
@endpush

@section('content')

@php
    $showFeatured = $newsItems->currentPage() == 1 && $newsItems->isNotEmpty() && !request()->filled('category');
    $featured     = $showFeatured ? $newsItems->first() : null;
@endphp

<div class="nr-root">

    {{-- ── MASTHEAD ─────────────────────────────────────────────────── --}}
    <header class="nr-masthead">
        <div class="nr-masthead__inner">
            <div class="nr-masthead__eyebrow">
                <span class="nr-masthead__rule"></span>
                <span class="nr-masthead__label">ICCR Alumni Network</span>
                <span class="nr-masthead__rule"></span>
            </div>
            <h1 class="nr-masthead__title">News <em>&amp;</em> Views</h1>
            <p class="nr-masthead__sub">
                Stories, announcements and highlights from alumni communities across the world.
            </p>
        </div>
    </header>

    {{-- ── FILTER BAR ───────────────────────────────────────────────── --}}
    <div class="nr-filterbar">
        <div class="nr-filterbar__inner">
            <form class="nr-search" method="GET" action="{{ route('news') }}">
                @if(request()->filled('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <svg class="nr-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="nr-search__input" type="text" name="q"
                       value="{{ request('q') }}" placeholder="Search articles…">
                <button class="nr-search__btn" type="submit">Search</button>
            </form>

            @if($categories->isNotEmpty())
            <div class="nr-chips">
                <a href="{{ route('news') }}" class="nr-chip {{ !request('category') ? 'nr-chip--active' : '' }}">All</a>
                @foreach($categories as $cat)
                    <a href="{{ route('news', ['category' => $cat->slug]) }}"
                       class="nr-chip {{ request('category') === $cat->slug ? 'nr-chip--active' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="nr-body">

        {{-- ── FEATURED ARTICLE ─────────────────────────────────────── --}}
        @if($featured)
        <section class="nr-feature">
            <a href="{{ route('news.show', $featured) }}" class="nr-feature__link">
                <div class="nr-feature__image">
                    @if($featured->image)
                        <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}" loading="lazy">
                    @else
                        <div class="nr-feature__placeholder"></div>
                    @endif
                    <div class="nr-feature__image-scrim"></div>
                </div>

                <div class="nr-feature__content">
                    <div class="nr-feature__eyebrow">
                        <span class="nr-label nr-label--featured">Featured</span>
                        @if($featured->category)
                            <span class="nr-label nr-label--cat">{{ $featured->category->name }}</span>
                        @endif
                    </div>

                    <h2 class="nr-feature__title">{{ $featured->title }}</h2>

                    @if($featured->excerpt)
                        <p class="nr-feature__excerpt">{{ $featured->excerpt }}</p>
                    @endif

                    <div class="nr-feature__meta">
                        <div class="nr-byline">
                            @php $fAuthor = $featured->author; @endphp
                            @if($fAuthor?->photo)
                                <img class="nr-byline__photo" src="{{ asset('storage/' . $fAuthor->photo) }}" alt="{{ $fAuthor->full_name }}">
                            @else
                                <span class="nr-byline__initial">{{ strtoupper(substr($fAuthor?->full_name ?? 'A', 0, 1)) }}</span>
                            @endif
                            <div>
                                <div class="nr-byline__name">{{ $fAuthor?->full_name ?? 'ICCR Alumni' }}</div>
                                <div class="nr-byline__date">
                                    {{ $featured->published_at->format('d F Y') }}
                                    &middot; {{ $featured->read_time }} min read
                                </div>
                            </div>
                        </div>
                        <span class="nr-feature__cta">
                            Read article
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </div>
                </div>
            </a>
        </section>
        @endif

        {{-- ── SECTION HEADER ───────────────────────────────────────── --}}
        @if($newsItems->isNotEmpty())
        <div class="nr-section-head">
            <span class="nr-section-head__rule"></span>
            <span class="nr-section-head__label">
                {{ request()->filled('category') ? request('category') : 'Latest Articles' }}
                <span class="nr-section-head__count">{{ $newsItems->total() }}</span>
            </span>
            <span class="nr-section-head__rule"></span>
        </div>
        @endif

        {{-- ── ARTICLES GRID ────────────────────────────────────────── --}}
        @if($newsItems->isNotEmpty())
        <div class="nr-grid">
            @foreach($newsItems as $item)
                @continue($featured && $item->id === $featured->id)
                @php $author = $item->author; @endphp

                <article class="nr-card">
                    <a href="{{ route('news.show', $item) }}" class="nr-card__img-link">
                        <div class="nr-card__image">
                            @if($item->image)
                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" loading="lazy">
                            @else
                                <div class="nr-card__placeholder">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="nr-card__body">
                        <div class="nr-card__eyebrow">
                            @if($item->category)
                                <a href="{{ route('news', ['category' => $item->category->slug]) }}" class="nr-card__cat">
                                    {{ $item->category->name }}
                                </a>
                                <span class="nr-card__dot"></span>
                            @endif
                            <time class="nr-card__date">{{ $item->published_at->format('d M Y') }}</time>
                            <span class="nr-card__dot"></span>
                            <span class="nr-card__read">{{ $item->read_time }} min</span>
                        </div>

                        <h3 class="nr-card__title">
                            <a href="{{ route('news.show', $item) }}">{{ $item->title }}</a>
                        </h3>

                        @if($item->excerpt)
                            <p class="nr-card__excerpt">{{ $item->excerpt }}</p>
                        @endif

                        <div class="nr-card__footer">
                            <div class="nr-byline nr-byline--card">
                                @if($author?->photo)
                                    <a href="{{ $author->id ? url('/members/' . $author->id) : '#' }}">
                                        <img class="nr-byline__photo nr-byline__photo--sm" src="{{ asset('storage/' . $author->photo) }}" alt="{{ $author->full_name }}">
                                    </a>
                                @else
                                    <a href="{{ $author?->id ? url('/members/' . $author->id) : '#' }}" class="nr-byline__initial nr-byline__initial--sm">
                                        {{ strtoupper(substr($author?->full_name ?? 'A', 0, 1)) }}
                                    </a>
                                @endif
                                <a href="{{ $author?->id ? url('/members/' . $author->id) : '#' }}" class="nr-byline__name nr-byline__name--dark">
                                    {{ $author?->full_name ?? 'ICCR Alumni' }}
                                </a>
                            </div>
                            <a href="{{ route('news.show', $item) }}" class="nr-card__arrow" aria-label="Read {{ $item->title }}">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- ── PAGINATION ───────────────────────────────────────────── --}}
        @if($newsItems->hasPages())
        <nav class="nr-pagination" aria-label="News pages">
            @if($newsItems->onFirstPage())
                <span class="nr-page-btn nr-page-btn--disabled">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </span>
            @else
                <a class="nr-page-btn" href="{{ $newsItems->previousPageUrl() }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </a>
            @endif

            @foreach($newsItems->getUrlRange(max(1, $newsItems->currentPage()-2), min($newsItems->lastPage(), $newsItems->currentPage()+2)) as $page => $url)
                <a class="nr-page-btn {{ $page == $newsItems->currentPage() ? 'nr-page-btn--active' : '' }}" href="{{ $url }}">{{ $page }}</a>
            @endforeach

            @if($newsItems->hasMorePages())
                <a class="nr-page-btn" href="{{ $newsItems->nextPageUrl() }}">
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @else
                <span class="nr-page-btn nr-page-btn--disabled">
                    Next
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </span>
            @endif
        </nav>
        @endif

        @else
        <div class="nr-empty">
            <div class="nr-empty__icon">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>
            </div>
            <p class="nr-empty__title">No articles found</p>
            <p class="nr-empty__sub">Check back soon for updates from the community.</p>
        </div>
        @endif

    </div>{{-- /nr-body --}}
</div>

@endsection

{{-- Append this @push('scripts') block to the bottom of news/index.blade.php --}}

@push('scripts')
<script>
(function () {
    'use strict';

    const input    = document.querySelector('.nr-search__input');
    const form     = document.querySelector('.nr-search');
    const body     = document.querySelector('.nr-body');
    if (!input || !body) return;

    // Elements we'll swap out
    const FEATURE_SEL  = '.nr-feature';
    const SEC_HEAD_SEL = '.nr-section-head';
    const GRID_SEL     = '.nr-grid';
    const PAGINATION_SEL = '.nr-pagination';
    const EMPTY_SEL    = '.nr-empty';

    let debounceTimer = null;
    let currentQuery  = '';
    let controller    = null; // AbortController for in-flight requests

    // ── Spinner overlay ──────────────────────────────────────────────
    const spinner = document.createElement('div');
    spinner.id = 'nr-live-spinner';
    spinner.style.cssText = [
        'position:fixed','top:0','left:0','right:0','bottom:0',
        'z-index:9998','display:none','align-items:center','justify-content:center',
        'pointer-events:none',
    ].join(';');
    spinner.innerHTML = `
        <div style="width:40px;height:40px;border:3px solid rgba(232,100,12,.2);
             border-top-color:#E8640C;border-radius:50%;
             animation:nr-spin .7s linear infinite"></div>
        <style>@keyframes nr-spin{to{transform:rotate(360deg)}}</style>`;
    document.body.appendChild(spinner);

    function showSpinner() { spinner.style.display = 'flex'; }
    function hideSpinner() { spinner.style.display = 'none'; }

    // ── Fetch & swap ─────────────────────────────────────────────────
    async function liveSearch(q) {
        if (q === currentQuery) return;
        currentQuery = q;

        // Cancel previous in-flight request
        controller?.abort();
        controller = new AbortController();

        showSpinner();

        try {
            const url  = new URL(window.location.href);
            url.searchParams.set('q', q);
            if (!q) url.searchParams.delete('q');

            // Update browser URL without reload
            history.replaceState(null, '', url.toString());

            const res  = await fetch(url.toString(), {
                signal: controller.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!res.ok) throw new Error('Network error');

            const html   = await res.text();
            const parser = new DOMParser();
            const doc    = parser.parseFromString(html, 'text/html');

            // Swap each section independently so category chips & header stay
            swapElement(FEATURE_SEL,    doc, body);
            swapElement(SEC_HEAD_SEL,   doc, body);
            swapElement(GRID_SEL,       doc, body);
            swapElement(PAGINATION_SEL, doc, body);
            swapElement(EMPTY_SEL,      doc, body);

        } catch (err) {
            if (err.name === 'AbortError') return; // cancelled — ignore
            console.error('[live-search]', err);
        } finally {
            hideSpinner();
        }
    }

    function swapElement(selector, sourceDoc, targetRoot) {
        const incoming = sourceDoc.querySelector(selector);
        const existing = targetRoot.querySelector(selector);

        if (incoming && existing) {
            existing.replaceWith(incoming);
        } else if (incoming && !existing) {
            // Insert the incoming element in the right position
            const grid = targetRoot.querySelector(GRID_SEL);
            if (grid) grid.before(incoming);
            else targetRoot.appendChild(incoming);
        } else if (!incoming && existing) {
            existing.remove();
        }
        // if neither: nothing to do
    }

    // ── Input handler with debounce ──────────────────────────────────
    input.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => liveSearch(q), 320);
    });

    // Prevent full form submit (let live search handle it)
    form?.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        liveSearch(input.value.trim());
    });

    // ── Category chip clicks also go through live swap ───────────────
    document.querySelectorAll('.nr-chip').forEach(chip => {
        chip.addEventListener('click', function (e) {
            e.preventDefault();
            const url = new URL(this.href, window.location.origin);
            // Carry over current search query
            const q = input.value.trim();
            if (q) url.searchParams.set('q', q);
            history.replaceState(null, '', url.toString());
            currentQuery = ''; // force re-fetch
            liveSearch(q);
        });
    });

})();
</script>
@endpush