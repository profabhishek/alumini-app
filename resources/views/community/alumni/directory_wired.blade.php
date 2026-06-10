@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Alumni Directory')

@push('styles')
<style>
/* ============================================================
   ALUMNI DIRECTORY — alumni-dir-* namespace
   ============================================================ */

.alumni-dir-page {
    padding: 32px 28px 60px;
    max-width: 1280px;
    margin: 0 auto;
}

/* ---------- Hero Bar ---------- */
.alumni-dir-hero {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.alumni-dir-hero__eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #E8640C;
    margin-bottom: 6px;
}

.alumni-dir-hero__title {
    font-size: 26px;
    font-weight: 700;
    color: #1C2331;
    line-height: 1.2;
    margin: 0 0 4px;
}

.alumni-dir-hero__sub {
    font-size: 13.5px;
    color: #6b7280;
    margin: 0;
}

.alumni-dir-stat {
    background: #fff;
    border: 1.5px solid #f0e8e0;
    border-radius: 14px;
    padding: 14px 24px;
    text-align: center;
    min-width: 130px;
    box-shadow: 0 2px 8px rgba(28,35,49,.06);
    flex-shrink: 0;
}

.alumni-dir-stat__num {
    font-size: 30px;
    font-weight: 800;
    color: #E8640C;
    line-height: 1;
    letter-spacing: -0.02em;
}

.alumni-dir-stat__label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9ca3af;
    margin-top: 4px;
}

/* ---------- Toolbar ---------- */
.alumni-dir-toolbar {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 28px;
    flex-wrap: wrap;
    box-shadow: 0 2px 8px rgba(28,35,49,.05);
    position: sticky;
    top: 16px;
    z-index: 40;
}

.alumni-dir-search {
    flex: 1 1 240px;
    position: relative;
}

.alumni-dir-search__icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
}

.alumni-dir-search__input {
    width: 100%;
    padding: 9px 14px 9px 38px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 14px;
    color: #1C2331;
    background: #f9fafb;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
    box-sizing: border-box;
}

.alumni-dir-search__input:focus {
    border-color: #E8640C;
    box-shadow: 0 0 0 3px rgba(232,100,12,.1);
    background: #fff;
}

.alumni-dir-select {
    flex: 0 1 180px;
    padding: 9px 32px 9px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13.5px;
    color: #374151;
    background: #f9fafb url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 10px center;
    appearance: none;
    -webkit-appearance: none;
    outline: none;
    cursor: pointer;
    transition: border-color .2s;
}

.alumni-dir-select:focus {
    border-color: #E8640C;
    box-shadow: 0 0 0 3px rgba(232,100,12,.1);
}

.alumni-dir-toolbar__btn {
    padding: 9px 20px;
    border-radius: 9px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all .18s;
    border: 1.5px solid transparent;
    white-space: nowrap;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.alumni-dir-toolbar__btn--primary {
    background: #E8640C;
    color: #fff;
    border-color: #E8640C;
}

.alumni-dir-toolbar__btn--primary:hover {
    background: #d05a0b;
}

.alumni-dir-toolbar__btn--ghost {
    background: transparent;
    color: #6b7280;
    border-color: #e5e7eb;
}

.alumni-dir-toolbar__btn--ghost:hover {
    border-color: #d1d5db;
    color: #374151;
    background: #f3f4f6;
}

/* ---------- Results Meta ---------- */
.alumni-dir-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 8px;
}

.alumni-dir-meta__count {
    font-size: 13.5px;
    color: #6b7280;
}

.alumni-dir-meta__count strong {
    color: #1C2331;
    font-weight: 700;
}

/* ---------- Grid ---------- */
.alumni-dir-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
}

/* ---------- Card ---------- */
.alumni-dir-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 16px;
    padding: 24px 20px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: border-color .2s, box-shadow .2s, transform .2s;
    position: relative;
    overflow: hidden;
}

.alumni-dir-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #E8640C 0%, #f5a623 100%);
    opacity: 0;
    transition: opacity .2s;
}

.alumni-dir-card:hover {
    border-color: #f0c9a8;
    box-shadow: 0 8px 28px rgba(232,100,12,.12);
    transform: translateY(-2px);
}

.alumni-dir-card:hover::before {
    opacity: 1;
}

/* Avatar */
.alumni-dir-avatar {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fde9d6 0%, #fbd0b0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 800;
    color: #E8640C;
    margin-bottom: 14px;
    border: 2.5px solid #fbd0b0;
    flex-shrink: 0;
    letter-spacing: -0.01em;
}

.alumni-dir-card__name {
    font-size: 15px;
    font-weight: 700;
    color: #1C2331;
    margin: 0 0 4px;
    line-height: 1.3;
}

.alumni-dir-card__institute {
    font-size: 12.5px;
    color: #6b7280;
    margin: 0 0 3px;
    line-height: 1.4;
}

.alumni-dir-card__department {
    font-size: 12px;
    color: #9ca3af;
    margin: 0 0 12px;
}

/* Badges row */
.alumni-dir-card__badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 14px;
}

.alumni-dir-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 9px;
}

.alumni-dir-badge--year {
    background: #fff7f0;
    color: #E8640C;
    border: 1px solid #fbd0b0;
}

.alumni-dir-badge--batch {
    background: #f0f4ff;
    color: #4c6ef5;
    border: 1px solid #c5d0fa;
}

/* Actions */
.alumni-dir-card__actions {
    display: flex;
    gap: 8px;
    width: 100%;
    margin-top: auto;
}

.alumni-dir-card__btn {
    flex: 1;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all .18s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.alumni-dir-card__btn--view {
    background: #fff7f0;
    color: #E8640C;
    border-color: #fbd0b0;
}

.alumni-dir-card__btn--view:hover {
    background: #E8640C;
    color: #fff;
    border-color: #E8640C;
}

.alumni-dir-card__btn--dm {
    background: #f3f4f6;
    color: #9ca3af;
    border-color: #e5e7eb;
    cursor: not-allowed;
    opacity: .7;
}

/* ---------- Empty State ---------- */
.alumni-dir-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 64px 20px;
}

.alumni-dir-empty__icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: .4;
}

.alumni-dir-empty__title {
    font-size: 17px;
    font-weight: 700;
    color: #1C2331;
    margin: 0 0 6px;
}

.alumni-dir-empty__sub {
    font-size: 13.5px;
    color: #9ca3af;
    margin: 0;
}

/* ---------- Pagination ---------- */
.alumni-dir-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.alumni-dir-pagination nav { display: contents; }

.alumni-dir-pagination span[aria-current="page"] > span,
.alumni-dir-pagination a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: 9px;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: #374151;
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none;
    transition: all .18s;
}

.alumni-dir-pagination span[aria-current="page"] > span {
    background: #E8640C;
    border-color: #E8640C;
    color: #fff;
    font-weight: 700;
}

.alumni-dir-pagination a:hover {
    border-color: #E8640C;
    color: #E8640C;
}

.alumni-dir-pagination span[aria-disabled="true"] > span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: 9px;
    border: 1.5px solid #e5e7eb;
    background: #f9fafb;
    color: #d1d5db;
    font-size: 13.5px;
}

/* ---------- Responsive ---------- */
@media (max-width: 768px) {
    .alumni-dir-page { padding: 20px 16px 40px; }
    .alumni-dir-hero { flex-direction: column; align-items: flex-start; }
    .alumni-dir-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
    .alumni-dir-toolbar { top: 8px; }
    .alumni-dir-select { flex: 1 1 calc(50% - 6px); }
}
</style>
@endpush

@section('content')
<div class="alumni-dir-page">

    {{-- Hero --}}
    <div class="alumni-dir-hero">
        <div>
            <p class="alumni-dir-hero__eyebrow">ICCR Community</p>
            <h1 class="alumni-dir-hero__title">Alumni Directory</h1>
            <p class="alumni-dir-hero__sub">Discover and connect with fellow ICCR alumni across batches and disciplines.</p>
        </div>
        <div class="alumni-dir-stat">
            <div class="alumni-dir-stat__num">{{ number_format($totalAlumni) }}</div>
            <div class="alumni-dir-stat__label">Total Alumni</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <form method="GET" action="{{ route('alumni.directory') }}" id="alumni-dir-form">
        <div class="alumni-dir-toolbar">

            <div class="alumni-dir-search">
                <svg class="alumni-dir-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    class="alumni-dir-search__input"
                    placeholder="Search by name, institute, department, batch…"
                    value="{{ $search }}"
                    id="alumni-dir-search-input"
                    autocomplete="off"
                >
            </div>

            <select name="department" class="alumni-dir-select"
                onchange="document.getElementById('alumni-dir-form').submit()">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>
                        {{ $dept }}
                    </option>
                @endforeach
            </select>

            <select name="passing_year" class="alumni-dir-select"
                onchange="document.getElementById('alumni-dir-form').submit()">
                <option value="">All Years</option>
                @foreach ($passingYears as $yr)
                    <option value="{{ $yr }}" {{ $passingYear == $yr ? 'selected' : '' }}>
                        {{ $yr }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="alumni-dir-toolbar__btn alumni-dir-toolbar__btn--primary">
                Search
            </button>

            @if ($search || $department || $passingYear)
                <a href="{{ route('alumni.directory') }}"
                   class="alumni-dir-toolbar__btn alumni-dir-toolbar__btn--ghost">
                    Clear
                </a>
            @endif

        </div>
    </form>

    {{-- Results meta --}}
    <div class="alumni-dir-meta">
        <p class="alumni-dir-meta__count">
            Showing <strong>{{ $alumni->firstItem() ?? 0 }}–{{ $alumni->lastItem() ?? 0 }}</strong>
            of <strong>{{ $alumni->total() }}</strong> alumni
            @if ($search || $department || $passingYear)
                &nbsp;matching your filters
            @endif
        </p>
    </div>

    {{-- Grid --}}
    <div class="alumni-dir-grid">

        @forelse ($alumni as $member)

            <div class="alumni-dir-card">

                {{-- Avatar --}}
                <div class="alumni-dir-avatar">
                    {{ $member->initials }}
                </div>

                {{-- Name --}}
                <p class="alumni-dir-card__name">{{ $member->full_name }}</p>

                {{-- Institute --}}
                @if (!empty($member->institute))
                    <p class="alumni-dir-card__institute">{{ $member->institute }}</p>
                @endif

                {{-- Department --}}
                @if (!empty($member->department))
                    <p class="alumni-dir-card__department">{{ $member->department }}</p>
                @endif

                {{-- Badges --}}
                <div class="alumni-dir-card__badges">
                    @if (!empty($member->passing_year))
                        <span class="alumni-dir-badge alumni-dir-badge--year">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            {{ $member->passing_year }}
                        </span>
                    @endif
                    @if (!empty($member->batch_name))
                        <span class="alumni-dir-badge alumni-dir-badge--batch">
                            {{ $member->batch_name }}
                        </span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="alumni-dir-card__actions">
                    <a href="{{ route('alumni.profile', $member) }}" class="alumni-dir-card__btn alumni-dir-card__btn--view">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        View
                    </a>
                    <button class="alumni-dir-card__btn alumni-dir-card__btn--dm"
                        title="Messaging coming soon" disabled>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                        Message
                    </button>
                </div>

            </div>

        @empty

            <div class="alumni-dir-empty">
                <div class="alumni-dir-empty__icon">🎓</div>
                <p class="alumni-dir-empty__title">No alumni found</p>
                <p class="alumni-dir-empty__sub">
                    @if ($search || $department || $passingYear)
                        Try adjusting your search or clearing the filters.
                    @else
                        No approved alumni profiles yet.
                    @endif
                </p>
            </div>

        @endforelse

    </div>

    {{-- Pagination --}}
    @if ($alumni->hasPages())
        <div class="alumni-dir-pagination">
            {{ $alumni->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    const input  = document.getElementById('alumni-dir-search-input');
    const form   = document.getElementById('alumni-dir-form');
    let timer    = null;

    if (input && form) {
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 520);
        });
    }
})();
</script>
@endpush