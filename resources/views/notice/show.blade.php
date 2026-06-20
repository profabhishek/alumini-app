@extends('layouts.app')
@section('title', $notice->title . ' — ICCR Alumni Notices')

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
    $author = $notice->author;
    $authorName     = $author?->full_name ?? 'ICCR Alumni Office';
    $authorInitials = $author?->initials ?? 'IC';
@endphp

<div class="nb-detail">

    {{-- Reading progress bar --}}
    <div id="nbProgress" style="position:fixed;top:0;left:0;height:3px;background:var(--nb-gold);width:0%;z-index:1000;transition:width .1s linear;border-radius:0 2px 2px 0;"></div>

    {{-- Back --}}
    <div class="nb-detail-wrap">
        <a href="{{ route('notice') }}" class="nb-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            All Notices
        </a>
    </div>

    {{-- ── HERO IMAGE ───────────────────────────────────────────── --}}
    <div class="nb-detail-wrap">
        <div class="nb-detail-hero nb-reveal">
            @if($notice->image)
                <img src="{{ $notice->image_url }}" alt="{{ $notice->title }}">
            @else
                <div class="nb-detail-hero__placeholder">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width=".8"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                </div>
            @endif
            <div class="nb-detail-hero__scrim"></div>

            {{-- Big date stamp --}}
            <div class="nb-stamp" style="bottom:28px;left:28px;">
                <span class="nb-stamp__month">{{ $notice->published_at->format('M') }}</span>
                <span class="nb-stamp__day" style="font-size:64px;padding:4px 20px 10px;">{{ $notice->published_at->format('d') }}</span>
                <span class="nb-stamp__year">{{ $notice->published_at->format('Y') }}</span>
            </div>

            {{-- Category badge top right --}}
            @if($notice->category)
                <span style="position:absolute;top:22px;right:22px;background:rgba(255,255,255,.95);color:var(--nb-navy);font-family:var(--ff-mono);font-size:9.5px;font-weight:500;letter-spacing:.16em;text-transform:uppercase;padding:5px 13px;border-radius:999px;box-shadow:0 2px 10px rgba(0,0,0,.15);">
                    {{ $notice->category->name }}
                </span>
            @endif
        </div>
    </div>

    {{-- ── DETAIL LAYOUT ───────────────────────────────────────── --}}
    <div class="nb-detail-layout">

        {{-- Main content --}}
        <main class="nb-detail-main nb-reveal">
            @if($notice->category)
                <div class="nb-detail-cat">{{ $notice->category->name }}</div>
            @endif

            <h1 class="nb-detail-title">{{ $notice->title }}</h1>

            <div class="nb-detail-byline">
                <div class="nb-detail-byline__avatar" style="overflow:hidden;padding:0;">
                    @if($author?->photo)
                        <img loading="lazy" src="{{ asset('storage/' . $author->photo) }}" alt="{{ $authorName }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">
                    @else
                        {{ $authorInitials }}
                    @endif
                </div>
                <div class="nb-detail-byline__text">
                    <div class="nb-detail-byline__label">Issued by</div>
                    <div class="nb-detail-byline__name">{{ $authorName }}</div>
                </div>
                <div class="nb-detail-date">
                    <div style="font-family:var(--ff-mono);font-size:9px;letter-spacing:.14em;text-transform:uppercase;color:var(--nb-ash);">Published</div>
                    <div style="font-size:13px;font-weight:600;color:var(--nb-ink);margin-top:1px;">{{ $notice->published_at->format('d F Y') }}</div>
                    <div style="font-family:var(--ff-mono);font-size:10px;color:var(--nb-ash-lt);">{{ $notice->published_at->format('g:i A') }}</div>
                </div>
            </div>

            <div class="nb-rich">
                {!! $notice->description !!}
            </div>
        </main>

        {{-- Sidebar --}}
        <aside class="nb-detail-sidebar nb-reveal nb-reveal--d1">
            <div class="nb-info-card">
                <div class="nb-info-card__head">
                    <div class="nb-info-card__head-label">Notice details</div>
                    <div class="nb-info-card__head-title">{{ Str::limit($notice->title, 40) }}</div>
                </div>
                <div class="nb-info-card__body">
                    <div class="nb-info-row">
                        <div class="nb-info-row__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <div>
                            <div class="nb-info-row__label">Date published</div>
                            <div class="nb-info-row__value">{{ $notice->published_at->format('d M Y') }}</div>
                        </div>
                    </div>
                    @if($notice->category)
                    <div class="nb-info-row">
                        <div class="nb-info-row__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </div>
                        <div>
                            <div class="nb-info-row__label">Category</div>
                            <div class="nb-info-row__value">{{ $notice->category->name }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="nb-info-row">
                        <div class="nb-info-row__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <div class="nb-info-row__label">Issued by</div>
                            <div class="nb-info-row__value">{{ $authorName }}</div>
                        </div>
                    </div>
                    <div class="nb-info-row">
                        <div class="nb-info-row__icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <div class="nb-info-row__label">Posted</div>
                            <div class="nb-info-row__value">{{ $notice->published_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
                <div class="nb-info-card__foot">
                    <button class="nb-share-btn" onclick="
                        if(navigator.share){
                            navigator.share({title:'{{ addslashes($notice->title) }}',url:window.location.href});
                        } else {
                            navigator.clipboard.writeText(window.location.href).then(()=>{
                                this.textContent='Link copied!';
                                setTimeout(()=>this.innerHTML='<svg width=\'14\' height=\'14\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><circle cx=\'18\' cy=\'5\' r=\'3\'/><circle cx=\'6\' cy=\'12\' r=\'3\'/><circle cx=\'18\' cy=\'19\' r=\'3\'/><line x1=\'8.59\' y1=\'13.51\' x2=\'15.42\' y2=\'17.49\'/><line x1=\'15.41\' y1=\'6.51\' x2=\'8.59\' y2=\'10.49\'/></svg> Share this notice',2000);
                            });
                        }
                    ">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        Share this notice
                    </button>
                    <a href="{{ route('notice') }}" class="nb-back-btn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Back to all notices
                    </a>
                </div>
            </div>
        </aside>

    </div>{{-- /nb-detail-layout --}}

    {{-- ── RELATED NOTICES ─────────────────────────────────────── --}}
    @if(isset($relatedNotices) && $relatedNotices->isNotEmpty())
    <section class="nb-related">
        <div class="nb-related__inner">
            <div class="nb-related__head">
                <h2 class="nb-related__title">More Notices</h2>
                <a href="{{ route('notice') }}" class="nb-related__link">
                    View all
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/></svg>
                </a>
            </div>
            <div class="nb-grid">
                @foreach($relatedNotices as $i => $rel)
                <a href="{{ route('notice.show', $rel) }}"
                   class="nb-card nb-reveal nb-reveal--d{{ $i + 1 }}">
                    <div class="nb-card__img-wrap">
                        @if($rel->image)
                            <img class="nb-card__img" src="{{ $rel->image_url }}" alt="{{ $rel->title }}" loading="lazy">
                        @else
                            <div class="nb-card__placeholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                            </div>
                        @endif
                        @if($rel->category)
                            <span class="nb-card__cat">{{ $rel->category->name }}</span>
                        @endif
                        <div class="nb-card__stamp">
                            <span class="nb-card__stamp__month">{{ $rel->published_at->format('M') }}</span>
                            <span class="nb-card__stamp__day">{{ $rel->published_at->format('d') }}</span>
                        </div>
                    </div>
                    <div class="nb-card__body">
                        <div class="nb-card__eyebrow">
                            @if($rel->category)
                                <span class="nb-card__eyebrow-cat">{{ $rel->category->name }}</span>
                                <span class="nb-card__eyebrow-sep"></span>
                            @endif
                            <span class="nb-card__eyebrow-date">{{ $rel->published_at->format('d M Y') }}</span>
                        </div>
                        <h3 class="nb-card__title">{{ $rel->title }}</h3>
                        <p class="nb-card__excerpt">{{ $rel->excerpt }}</p>
                        <div class="nb-card__footer">
                            <span class="nb-card__read">
                                Read
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

</div>{{-- /nb-detail --}}

@endsection

@push('scripts')
<script>
(function () {
    // Reading progress
    const bar = document.getElementById('nbProgress');
    if (bar) {
        window.addEventListener('scroll', function () {
            const el = document.documentElement;
            const pct = Math.min(100, (el.scrollTop / (el.scrollHeight - el.clientHeight)) * 100);
            bar.style.width = pct + '%';
        }, { passive: true });
    }
    // Reveal
    const els = document.querySelectorAll('.nb-reveal');
    if (!els.length) return;
    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('nb-reveal--show');
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.1 });
        els.forEach(el => obs.observe(el));
    } else {
        els.forEach(el => el.classList.add('nb-reveal--show'));
    }
})();
</script>
@endpush