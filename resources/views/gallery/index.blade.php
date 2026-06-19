@extends('layouts.app')

@section('title', 'Photo Gallery — ICCR Alumni Network')

@push('styles')
<style>
/* ── CSS Variables (inherits from global, but scoped fallbacks) ── */
:root {
    --gal-bg: #0d0d14;
    --gal-surface: #141420;
    --gal-border: rgba(255,255,255,0.08);
    --gal-gold: #f4a825;
    --gal-text: #e2e8f0;
    --gal-muted: rgba(255,255,255,0.45);
}

/* ── Page wrapper ── */
.gal-page {
    background: var(--gal-bg);
    min-height: 100vh;
    padding-bottom: 80px;
}

/* ── Hero header ── */
.gal-hero {
    position: relative;
    padding: 80px 0 60px;
    text-align: center;
    overflow: hidden;
}
.gal-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(244,168,37,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.gal-hero-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--gal-gold);
    background: rgba(244,168,37,0.1);
    border: 1px solid rgba(244,168,37,0.25);
    border-radius: 50px;
    padding: 5px 16px;
    margin-bottom: 20px;
}
.gal-hero h1 {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 14px;
    line-height: 1.15;
}
.gal-hero p {
    font-size: 1rem;
    color: var(--gal-muted);
    max-width: 520px;
    margin: 0 auto 32px;
    line-height: 1.7;
}
.gal-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--gal-surface);
    border: 1px solid var(--gal-border);
    border-radius: 50px;
    padding: 8px 20px;
    font-size: 13px;
    color: var(--gal-text);
}
.gal-count-badge strong { color: var(--gal-gold); }

/* ── Toolbar (filter / search) ── */
.gal-toolbar {
    max-width: 1200px;
    margin: 0 auto 36px;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.gal-search {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--gal-surface);
    border: 1px solid var(--gal-border);
    border-radius: 12px;
    padding: 10px 16px;
    flex: 1;
    max-width: 340px;
    transition: border-color 0.2s;
}
.gal-search:focus-within { border-color: rgba(244,168,37,0.4); }
.gal-search svg { color: var(--gal-muted); flex-shrink: 0; }
.gal-search input {
    background: none;
    border: none;
    outline: none;
    color: #fff;
    font-size: 14px;
    width: 100%;
}
.gal-search input::placeholder { color: var(--gal-muted); }
.gal-sort {
    display: flex;
    gap: 8px;
}
.gal-sort-btn {
    background: var(--gal-surface);
    border: 1px solid var(--gal-border);
    border-radius: 10px;
    color: var(--gal-muted);
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.gal-sort-btn.active,
.gal-sort-btn:hover {
    border-color: var(--gal-gold);
    color: var(--gal-gold);
    background: rgba(244,168,37,0.06);
}

/* ── Grid ── */
.gal-grid-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

/* Masonry-style using CSS columns */
.gal-masonry {
    column-count: 4;
    column-gap: 16px;
}
@media (max-width: 1100px) { .gal-masonry { column-count: 3; } }
@media (max-width: 700px)  { .gal-masonry { column-count: 2; } }
@media (max-width: 480px)  { .gal-masonry { column-count: 1; } }

/* ── Card ── */
.gal-card {
    break-inside: avoid;
    margin-bottom: 16px;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    background: var(--gal-surface);
    border: 1px solid var(--gal-border);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.gal-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
}
.gal-card img {
    width: 100%;
    display: block;
    transition: transform 0.5s ease;
}
.gal-card:hover img { transform: scale(1.04); }
.gal-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0) 55%);
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: flex-end;
    padding: 18px;
}
.gal-card:hover .gal-card-overlay { opacity: 1; }
.gal-card-title {
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    text-shadow: 0 1px 4px rgba(0,0,0,0.8);
}
.gal-card-zoom {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.25s ease;
}
.gal-card:hover .gal-card-zoom {
    opacity: 1;
    transform: scale(1);
}

/* ── Empty state ── */
.gal-empty {
    text-align: center;
    padding: 80px 20px;
    color: var(--gal-muted);
}
.gal-empty svg { margin: 0 auto 16px; display: block; opacity: 0.3; }
.gal-empty h3 { color: rgba(255,255,255,0.6); margin-bottom: 8px; }

/* ── Lightbox ── */
.gal-lightbox {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.95);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.gal-lightbox.open { display: flex; }
.gal-lightbox-inner {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
}
.gal-lightbox-inner img {
    max-width: 90vw;
    max-height: 85vh;
    border-radius: 16px;
    object-fit: contain;
    display: block;
    box-shadow: 0 30px 80px rgba(0,0,0,0.8);
}
.gal-lb-title {
    text-align: center;
    color: rgba(255,255,255,0.8);
    font-size: 14px;
    margin-top: 14px;
}
.gal-lb-close {
    position: absolute;
    top: -16px;
    right: -16px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}
.gal-lb-close:hover { background: rgba(244,168,37,0.3); }
.gal-lb-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}
.gal-lb-nav:hover { background: rgba(244,168,37,0.25); }
.gal-lb-prev { left: -60px; }
.gal-lb-next { right: -60px; }
@media (max-width: 600px) {
    .gal-lb-prev { left: 4px; top: auto; bottom: -54px; transform: none; }
    .gal-lb-next { right: 4px; top: auto; bottom: -54px; transform: none; }
}

/* ── No-results ── */
.gal-no-results {
    display: none;
    text-align: center;
    padding: 60px;
    color: var(--gal-muted);
    font-size: 15px;
}
</style>
@endpush

@section('content')
<div class="gal-page">

    {{-- ── HERO ── --}}
    <div class="gal-hero">
        <div class="gal-hero-tag">ICCR Alumni Network</div>
        <h1>Our Gallery</h1>
        <p>Moments captured across decades — events, reunions, cultural celebrations, and milestones from our global alumni community.</p>
        <div class="gal-count-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
            <strong>{{ $allPhotos->count() }}</strong> photos shared
        </div>
    </div>

    @if($allPhotos->isNotEmpty())

    {{-- ── TOOLBAR ── --}}
    <div class="gal-toolbar">
        <div class="gal-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="galSearch" placeholder="Search photos…" autocomplete="off">
        </div>
        <div class="gal-sort">
            <button class="gal-sort-btn active" data-sort="newest">Newest</button>
            <button class="gal-sort-btn" data-sort="oldest">Oldest</button>
        </div>
    </div>

    {{-- ── MASONRY GRID ── --}}
    <div class="gal-grid-wrap">
        <div class="gal-masonry" id="galGrid">
            @foreach($allPhotos as $i => $photo)
            <div class="gal-card"
                 data-title="{{ strtolower($photo->title ?? '') }}"
                 data-date="{{ $photo->created_at->timestamp }}"
                 data-index="{{ $i }}"
                 onclick="openLightbox({{ $i }})">
                <img src="{{ asset('storage/' . $photo->image) }}"
                     alt="{{ $photo->title ?? 'Gallery photo' }}"
                     loading="lazy">
                <div class="gal-card-overlay">
                    @if($photo->title)
                    <div class="gal-card-title">{{ $photo->title }}</div>
                    @endif
                </div>
                <div class="gal-card-zoom">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                </div>
            </div>
            @endforeach
        </div>
        <div class="gal-no-results" id="galNoResults">No photos match your search.</div>
    </div>

    {{-- ── LIGHTBOX ── --}}
    <div class="gal-lightbox" id="galLightbox" onclick="closeLightboxOnBg(event)">
        <div class="gal-lightbox-inner">
            <button class="gal-lb-close" onclick="closeLightbox()" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            <button class="gal-lb-nav gal-lb-prev" onclick="lbNav(-1)" aria-label="Previous">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <img src="" alt="" id="lbImg">
            <button class="gal-lb-nav gal-lb-next" onclick="lbNav(1)" aria-label="Next">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <div class="gal-lb-title" id="lbTitle"></div>
        </div>
    </div>

    @else
    <div class="gal-empty">
        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
        <h3>No Photos Yet</h3>
        <p>Check back soon — photos will appear here as they are added.</p>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
// ── Photo data for lightbox ──────────────────────────────────────────────────
const GAL_PHOTOS = [
    @foreach($allPhotos as $photo)
    { src: "{{ asset('storage/' . $photo->image) }}", title: "{{ addslashes($photo->title ?? '') }}" },
    @endforeach
];

let lbIndex = 0;

function openLightbox(idx) {
    lbIndex = idx;
    renderLb();
    document.getElementById('galLightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('galLightbox').classList.remove('open');
    document.body.style.overflow = '';
}

function closeLightboxOnBg(e) {
    if (e.target === document.getElementById('galLightbox')) closeLightbox();
}

function lbNav(dir) {
    lbIndex = (lbIndex + dir + GAL_PHOTOS.length) % GAL_PHOTOS.length;
    renderLb();
}

function renderLb() {
    const p = GAL_PHOTOS[lbIndex];
    document.getElementById('lbImg').src = p.src;
    document.getElementById('lbImg').alt = p.title;
    document.getElementById('lbTitle').textContent = p.title || '';
}

// ── Keyboard navigation ──────────────────────────────────────────────────────
document.addEventListener('keydown', function (e) {
    const lb = document.getElementById('galLightbox');
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')       closeLightbox();
    if (e.key === 'ArrowLeft')    lbNav(-1);
    if (e.key === 'ArrowRight')   lbNav(1);
});

// ── Search ───────────────────────────────────────────────────────────────────
document.getElementById('galSearch')?.addEventListener('input', function () {
    filterGrid();
});

// ── Sort ─────────────────────────────────────────────────────────────────────
document.querySelectorAll('.gal-sort-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.gal-sort-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        sortGrid(this.dataset.sort);
    });
});

function filterGrid() {
    const q = document.getElementById('galSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('#galGrid .gal-card');
    let visible = 0;
    cards.forEach(card => {
        const match = !q || card.dataset.title.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('galNoResults').style.display = visible === 0 ? 'block' : 'none';
}

function sortGrid(order) {
    const grid = document.getElementById('galGrid');
    const cards = Array.from(grid.querySelectorAll('.gal-card'));
    cards.sort((a, b) => {
        const da = parseInt(a.dataset.date);
        const db = parseInt(b.dataset.date);
        return order === 'newest' ? db - da : da - db;
    });
    cards.forEach(c => grid.appendChild(c));
}
</script>
@endpush
