@extends('layouts.community')

@section('hideRightSidebar', true)

@section('title', $alumniUser->full_name . ' — Alumni Profile')

@push('styles')
<style>

    /* ── Page shell ──────────────────────────────────────────── */
    .ap-page {
        max-width: 860px;
        margin: 0 auto;
        padding: 32px 24px 64px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* ── Back link ───────────────────────────────────────────── */
    .ap-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-decoration: none;
        transition: color .18s;
    }
    .ap-back:hover { color: #E8640C; }

    /* ── Hero card ───────────────────────────────────────────── */
    .ap-hero {
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(28,35,49,.06);
    }

    .ap-hero__banner {
        height: 120px;
        background: linear-gradient(120deg, #1C2331 0%, #2d3a50 50%, #E8640C 100%);
        position: relative;
    }

    /* Subtle texture overlay on banner */
    .ap-hero__banner::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,.06) 0%, transparent 60%),
                        radial-gradient(circle at 80% 20%, rgba(232,100,12,.2) 0%, transparent 50%);
    }

    .ap-hero__body {
        padding: 0 28px 28px;
        position: relative;
    }

    /* ── Avatar ──────────────────────────────────────────────── */
    .ap-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        border: 4px solid #fff;
        background: linear-gradient(135deg, #fde9d6, #fbd0b0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        font-weight: 800;
        color: #E8640C;
        letter-spacing: -0.02em;
        position: relative;
        margin-top: -45px;
        box-shadow: 0 4px 14px rgba(28,35,49,.14);
        overflow: hidden;
        flex-shrink: 0;
    }
    .ap-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    /* ── Hero top row: avatar + actions ─────────────────────── */
    .ap-hero__top {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .ap-hero__actions {
        display: flex;
        gap: 8px;
        padding-top: 10px;
    }

    /* ── Name / meta block ───────────────────────────────────── */
    .ap-hero__name {
        font-size: 22px;
        font-weight: 800;
        color: #1C2331;
        line-height: 1.2;
        margin: 0 0 4px;
        letter-spacing: -0.01em;
    }

    .ap-hero__sub {
        font-size: 13.5px;
        color: #6b7280;
        margin: 0 0 12px;
        line-height: 1.5;
    }

    /* Pill tags row */
    .ap-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .ap-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 11px;
        line-height: 1.4;
    }

    .ap-pill--year {
        background: #fff7f0;
        color: #E8640C;
        border: 1px solid #fbd0b0;
    }

    .ap-pill--dept {
        background: #f0f4ff;
        color: #4361ee;
        border: 1px solid #c5d0fa;
    }

    .ap-pill--batch {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    .ap-pill--gender {
        background: #faf5ff;
        color: #7c3aed;
        border: 1px solid #e9d5ff;
    }

    /* ── Section card ────────────────────────────────────────── */
    .ap-card {
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(28,35,49,.05);
    }

    .ap-card__header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 22px;
        border-bottom: 1px solid #f3f4f6;
        background: #fafafa;
    }

    .ap-card__icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #fff7f0;
        border: 1px solid #fbd0b0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #E8640C;
        flex-shrink: 0;
    }

    .ap-card__title {
        font-size: 13px;
        font-weight: 700;
        color: #1C2331;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .ap-card__body {
        padding: 20px 22px;
    }

    /* ── Detail grid ─────────────────────────────────────────── */
    .ap-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px 28px;
    }

    .ap-field {}

    .ap-field__label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: 4px;
    }

    .ap-field__value {
        font-size: 14px;
        font-weight: 500;
        color: #1C2331;
        line-height: 1.5;
    }

    .ap-field__value--muted {
        color: #9ca3af;
        font-style: italic;
    }

    /* ── Action buttons ──────────────────────────────────────── */
    .ap-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid transparent;
        text-decoration: none;
        transition: all .18s;
        white-space: nowrap;
    }

    .ap-btn--primary {
        background: #E8640C;
        color: #fff;
        border-color: #E8640C;
    }
    .ap-btn--primary:hover {
        background: #d05a0b;
        color: #fff;
    }

    .ap-btn--ghost {
        background: #f3f4f6;
        color: #374151;
        border-color: #e5e7eb;
    }
    .ap-btn--ghost:hover {
        background: #e5e7eb;
        color: #1C2331;
    }

    .ap-btn--dm {
        background: #f9fafb;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
        opacity: .7;
    }

    /* ── Own-profile banner ──────────────────────────────────── */
    .ap-own-banner {
        background: linear-gradient(90deg, #fff7f0, #fff);
        border: 1.5px solid #fbd0b0;
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13.5px;
        color: #92400e;
    }

    .ap-own-banner__icon {
        color: #E8640C;
        flex-shrink: 0;
    }

    .ap-own-banner a {
        color: #E8640C;
        font-weight: 700;
        text-decoration: none;
    }
    .ap-own-banner a:hover { text-decoration: underline; }

    /* ── Empty field placeholder ─────────────────────────────── */
    .ap-empty-field {
        color: #d1d5db;
        font-size: 13px;
        font-style: italic;
    }

    /* ── Responsive ──────────────────────────────────────────── */
    @media (max-width: 640px) {
        .ap-page { padding: 20px 14px 48px; }
        .ap-grid { grid-template-columns: 1fr; gap: 14px; }
        .ap-hero__body { padding: 0 18px 22px; }
        .ap-hero__name { font-size: 19px; }
        .ap-hero__top { flex-direction: column; align-items: flex-start; }
        .ap-hero__actions { padding-top: 0; }
    }

    .ap-privacy-note {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: normal;
    text-transform: none;
    color: #E8640C;
    margin-left: 4px;
}
</style>
@endpush

@section('content')
<div class="ap-page">

    {{-- Back link --}}
    <a href="{{ route('alumni.directory') }}" class="ap-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Directory
    </a>

    {{-- Own-profile notice --}}
    @if($isOwnProfile)
        <div class="ap-own-banner">
            <svg class="ap-own-banner__icon" width="18" height="18" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>This is your public profile. <a href="/profile">Update your information</a> to keep it accurate.</span>
        </div>
    @endif

    {{-- ── Hero card ─────────────────────────────────────────────────── --}}
    <div class="ap-hero">
        <div class="ap-hero__banner"></div>
        <div class="ap-hero__body">

            <div class="ap-hero__top">

                {{-- Avatar --}}
                <div class="ap-avatar">
                    @if(!empty($alumniUser->photo))
                        <img src="{{ asset('storage/' . $alumniUser->photo) }}"
                             alt="{{ $alumniUser->full_name }}"
                             loading="lazy">
                    @else
                        {{ $alumniUser->initials }}
                    @endif
                </div>

                {{-- Action buttons --}}
                <div class="ap-hero__actions">
                    @unless($isOwnProfile)
                        <button class="ap-btn ap-btn--primary" onclick="startDirectChat({{ $alumniUser->id }}, this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                            </svg>
                            Message
                        </button>
                    @endunless
                </div>

            </div>

            {{-- Name & subtitle --}}
            <h1 class="ap-hero__name">{{ $alumniUser->full_name }}</h1>

            <p class="ap-hero__sub">
                @if(!empty($alumniUser->institute))
                    {{ $alumniUser->institute }}
                    @if(!empty($alumniUser->department)) &nbsp;·&nbsp; {{ $alumniUser->department }} @endif
                @elseif(!empty($alumniUser->department))
                    {{ $alumniUser->department }}
                @else
                    ICCR Alumni
                @endif
            </p>

            {{-- Pill badges --}}
            <div class="ap-pills">
                @if(!empty($alumniUser->passing_year))
                    <span class="ap-pill ap-pill--year">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Class of {{ $alumniUser->passing_year }}
                    </span>
                @endif

                @if(!empty($alumniUser->batch_name))
                    <span class="ap-pill ap-pill--batch">
                        {{ $alumniUser->batch_name }}
                    </span>
                @endif

                @if(!empty($alumniUser->department))
                    <span class="ap-pill ap-pill--dept">
                        {{ $alumniUser->department }}
                    </span>
                @endif

                @if(!empty($alumniUser->gender))
                    <span class="ap-pill ap-pill--gender">
                        {{ ucfirst($alumniUser->gender) }}
                    </span>
                @endif
            </div>

        </div>
    </div>

    {{-- ── Academic Details ──────────────────────────────────────────── --}}
    <div class="ap-card">
        <div class="ap-card__header">
            <div class="ap-card__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <span class="ap-card__title">Academic Details</span>
        </div>
        <div class="ap-card__body">
            <div class="ap-grid">

                <div class="ap-field">
                    <p class="ap-field__label">Institute</p>
                    <p class="ap-field__value">
                        @if(!empty($alumniUser->institute))
                            {{ $alumniUser->institute }}
                        @else
                            <span class="ap-empty-field">Not provided</span>
                        @endif
                    </p>
                </div>

                <div class="ap-field">
                    <p class="ap-field__label">Department</p>
                    <p class="ap-field__value">
                        @if(!empty($alumniUser->department))
                            {{ $alumniUser->department }}
                        @else
                            <span class="ap-empty-field">Not provided</span>
                        @endif
                    </p>
                </div>

                <div class="ap-field">
                    <p class="ap-field__label">Passing Year</p>
                    <p class="ap-field__value">
                        @if(!empty($alumniUser->passing_year))
                            {{ $alumniUser->passing_year }}
                        @else
                            <span class="ap-empty-field">Not provided</span>
                        @endif
                    </p>
                </div>

                <div class="ap-field">
                    <p class="ap-field__label">Roll Number</p>
                    <p class="ap-field__value">
                        @if(!empty($alumniUser->roll_number))
                            {{ $alumniUser->roll_number }}
                        @else
                            <span class="ap-empty-field">Not provided</span>
                        @endif
                    </p>
                </div>

                <div class="ap-field">
                    <p class="ap-field__label">Batch</p>
                    <p class="ap-field__value">
                        @if(!empty($alumniUser->batch_name))
                            {{ $alumniUser->batch_name }}
                        @else
                            <span class="ap-empty-field">Not provided</span>
                        @endif
                    </p>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Personal Details ──────────────────────────────────────────── --}}
    <div class="ap-card">
        <div class="ap-card__header">
            <div class="ap-card__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <span class="ap-card__title">Personal Details</span>
        </div>
        <div class="ap-card__body">
            <div class="ap-grid">

                <div class="ap-field">
                    <p class="ap-field__label">Gender</p>
                    <p class="ap-field__value">
                        @if(!empty($alumniUser->gender))
                            {{ ucfirst($alumniUser->gender) }}
                        @else
                            <span class="ap-empty-field">Not provided</span>
                        @endif
                    </p>
                </div>

                <div class="ap-field">
                    <p class="ap-field__label">Date of Birth</p>
                    <p class="ap-field__value">
                        @if(!empty($alumniUser->birth_date))
                            {{ \Carbon\Carbon::parse($alumniUser->birth_date)->format('d M Y') }}
                        @else
                            <span class="ap-empty-field">Not provided</span>
                        @endif
                    </p>
                </div>

                @php
                    $canSeePrivate = $isOwnProfile || in_array(session('alumni_role'), ['admin', 'super_admin']);
                    $showEmail = $canSeePrivate || ! $alumniUser->hide_email;
                    $showPhone = $canSeePrivate || ! $alumniUser->hide_phone;
                @endphp

                @if($showEmail)
                    <div class="ap-field">
                        <p class="ap-field__label">
                            Email
                            @if($isOwnProfile && $alumniUser->hide_email)
                                <span class="ap-privacy-note">— hidden from other alumni</span>
                            @endif
                        </p>
                        <p class="ap-field__value">
                            <a href="mailto:{{ $alumniUser->email }}"
                               style="color:#E8640C; text-decoration:none; font-weight:500;">
                                {{ $alumniUser->email }}
                            </a>
                        </p>
                    </div>
                @endif

                @if($showPhone)
                    <div class="ap-field">
                        <p class="ap-field__label">
                            Phone
                            @if($isOwnProfile && $alumniUser->hide_phone)
                                <span class="ap-privacy-note">— hidden from other alumni</span>
                            @endif
                        </p>
                        <p class="ap-field__value">
                            @if(!empty($alumniUser->phone))
                                {{ $alumniUser->phone }}
                            @else
                                <span class="ap-empty-field">Not provided</span>
                            @endif
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ── Member Since ───────────────────────────────────────────────── --}}
    <div class="ap-card">
        <div class="ap-card__header">
            <div class="ap-card__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12,6 12,12 16,14"/>
                </svg>
            </div>
            <span class="ap-card__title">Community</span>
        </div>
        <div class="ap-card__body">
            <div class="ap-grid">
                <div class="ap-field">
                    <p class="ap-field__label">Member Since</p>
                    <p class="ap-field__value">
                        {{ $alumniUser->created_at->format('d M Y') }}
                    </p>
                </div>
                <div class="ap-field">
                    <p class="ap-field__label">Community Role</p>
                    <p class="ap-field__value" style="text-transform:capitalize;">
                        {{ str_replace('_', ' ', $alumniUser->role) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/community/start-chat.js') }}"></script>
@endpush