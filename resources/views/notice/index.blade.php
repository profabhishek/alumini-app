@extends('layouts.app')
@section('title', 'Notices & Announcements — ICCR Alumni')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,700&family=Inter:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,700&family=Inter:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,700&family=Inter:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap"></noscript>
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/public-content.css') }}?v=2">
@endpush

@section('content')

@php
    $heroNotice = $notices->isNotEmpty() && !request()->filled('category') ? $notices->first() : null;
    $gridNotices = $heroNotice ? $notices->slice(1) : $notices;
@endphp

<div class="nb-root">

    {{-- ── MASTHEAD ────────────────────────────────────────────── --}}
    <header class="nb-masthead nb-masthead--centered">
        <div class="nb-masthead__rule"></div>
        <div class="nb-masthead__inner nb-masthead__inner--centered">
            <div class="nb-masthead__eyebrow">
                <span class="nb-masthead__dot" aria-hidden="true"></span>
                <span class="nb-masthead__eyebrow-label">ICCR Alumni Network</span>
            </div>
            <h1 class="nb-masthead__title">
                Official <em>Notices</em>
            </h1>
            <p class="nb-masthead__sub">
                Summits, policy updates, fellowship announcements and official
                communications for ICCR alumni worldwide.
            </p>
        </div>
    </header>

    {{-- ── TOOLBAR ─────────────────────────────────────────────── --}}
    <div class="nb-toolbar">
        <div class="nb-toolbar__inner">
            <form class="nb-search" method="GET" action="{{ route('notice') }}">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <svg class="nb-search__icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="nb-search__input" type="text" name="search"
                       value="{{ request('search') }}" placeholder="Search notices…" autocomplete="off">
                <button class="nb-search__btn" type="submit">Search</button>
            </form>

            @if($categories->isNotEmpty())
            <div class="nb-pills">
                <a href="{{ route('notice') }}"
                   class="nb-pill {{ !request('category') ? 'is-active' : '' }}">All</a>
                @foreach($categories as $cat)
                    <a href="{{ route('notice', ['category' => $cat->slug]) }}"
                       class="nb-pill {{ request('category') === $cat->slug ? 'is-active' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── BODY ─────────────────────────────────────────────────── --}}
    <div class="nb-body">

        @if($notices->isEmpty())
            {{-- Empty state --}}
            <div class="nb-empty">
                <div class="nb-empty__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                </div>
                <p class="nb-empty__title">Nothing here yet</p>
                <p class="nb-empty__sub">
                    @if(request('search'))
                        No notices matched "<strong>{{ request('search') }}</strong>". Try a different term.
                    @else
                        Official announcements will appear here as they're published.
                    @endif
                </p>
                @if(request()->hasAny(['search','category']))
                    <a href="{{ route('notice') }}" class="nb-empty__cta">
                        Clear filters
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>

        @else

            {{-- ── HERO NOTICE ──────────────────────────────────── --}}
            @if($heroNotice)
            <a href="{{ route('notice.show', $heroNotice) }}" class="nb-hero-notice nb-reveal">
                <div class="nb-hero-notice__img">
                    @if($heroNotice->image)
                        <img src="{{ $heroNotice->image_url }}" alt="{{ $heroNotice->title }}" loading="lazy">
                    @else
                        <div class="nb-hero-notice__img-placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        </div>
                    @endif
                    <div class="nb-hero-notice__img-scrim"></div>

                    <div class="nb-stamp">
                        <span class="nb-stamp__month">{{ $heroNotice->published_at->format('M') }}</span>
                        <span class="nb-stamp__day">{{ $heroNotice->published_at->format('d') }}</span>
                        <span class="nb-stamp__year">{{ $heroNotice->published_at->format('Y') }}</span>
                    </div>

                    <span class="nb-hero-notice__badge">Latest</span>
                </div>

                <div class="nb-hero-notice__content">
                    @if($heroNotice->category)
                        <span class="nb-cat-tag">{{ $heroNotice->category->name }}</span>
                    @endif
                    <h2 class="nb-hero-notice__title">{{ $heroNotice->title }}</h2>
                    <p class="nb-hero-notice__excerpt">{{ $heroNotice->excerpt }}</p>
                    <div class="nb-hero-notice__meta">
                        <span class="nb-hero-notice__date-text">
                            Published {{ $heroNotice->published_at->format('l, d F Y') }}
                        </span>
                        <span class="nb-hero-notice__cta">
                            Read notice
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                        </span>
                    </div>
                </div>
            </a>
            @endif

            {{-- ── SECTION HEAD ─────────────────────────────────── --}}
            <div class="nb-section-head">
                <span class="nb-section-head__rule"></span>
                <span class="nb-section-head__label">
                    @if(request()->filled('search'))
                        Results for "{{ request('search') }}"
                    @elseif(request()->filled('category'))
                        {{ ucfirst(request('category')) }}
                    @else
                        All notices
                    @endif
                    <span class="nb-section-head__count">{{ $notices->total() }}</span>
                </span>
                <span class="nb-section-head__rule"></span>
            </div>

            {{-- ── GRID ─────────────────────────────────────────── --}}
            @if($gridNotices->isNotEmpty())
            <div class="nb-grid">
                @foreach($gridNotices as $i => $item)
                <a href="{{ route('notice.show', $item) }}"
                   class="nb-card nb-reveal nb-reveal--d{{ ($i % 3) + 1 }}">

                    <div class="nb-card__img-wrap">
                        @if($item->image)
                            <img class="nb-card__img" src="{{ $item->image_url }}" alt="{{ $item->title }}" loading="lazy">
                        @else
                            <div class="nb-card__placeholder">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                            </div>
                        @endif

                        @if($item->category)
                            <span class="nb-card__cat">{{ $item->category->name }}</span>
                        @endif

                        <div class="nb-card__stamp">
                            <span class="nb-card__stamp__month">{{ $item->published_at->format('M') }}</span>
                            <span class="nb-card__stamp__day">{{ $item->published_at->format('d') }}</span>
                        </div>
                    </div>

                    <div class="nb-card__body">
                        <div class="nb-card__eyebrow">
                            @if($item->category)
                                <span class="nb-card__eyebrow-cat">{{ $item->category->name }}</span>
                                <span class="nb-card__eyebrow-sep"></span>
                            @endif
                            <span class="nb-card__eyebrow-date">{{ $item->published_at->format('d M Y') }}</span>
                        </div>

                        <h3 class="nb-card__title">{{ $item->title }}</h3>
                        <p class="nb-card__excerpt">{{ $item->excerpt }}</p>

                        <div class="nb-card__footer">
                            <span class="nb-card__read">
                                Read notice
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                            <span style="font-family:var(--ff-mono);font-size:9px;letter-spacing:.1em;color:var(--nb-ash-lt);text-transform:uppercase;">
                                {{ $item->published_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

            {{-- ── PAGINATION ───────────────────────────────────── --}}
            @if($notices->hasPages())
            <div class="nb-pagination">
                {{ $notices->links() }}
            </div>
            @endif

        @endif
    </div>{{-- /nb-body --}}

</div>{{-- /nb-root --}}

@endsection

@push('scripts')
<script>
(function () {
    const els = document.querySelectorAll('.nb-reveal');
    if (!els.length) return;
    const obs = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('nb-reveal--show');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.06 });
    els.forEach(el => obs.observe(el));
})();
</script>
@endpush