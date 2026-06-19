@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Join ' . $conversation->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/messages/chat.css') }}">
<style>
/* ── Join page shell ─────────────────────────────────────── */
.jg-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 120px);
    padding: 32px 16px;
}

/* ── Card ────────────────────────────────────────────────── */
.jg-card {
    width: min(480px, 100%);
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(28,35,49,.10);
    overflow: hidden;
}

/* Banner strip */
.jg-card__banner {
    height: 72px;
    background: linear-gradient(120deg, #1C2331 0%, #2d3a50 60%, #E8640C 100%);
}

.jg-card__body {
    padding: 0 32px 32px;
    text-align: center;
}

/* ── Avatar block ────────────────────────────────────────── */
.jg-avatar-wrap {
    position: relative;
    display: inline-flex;
    margin-top: -36px;
    margin-bottom: 14px;
}

.jg-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    border: 4px solid #fff;
    background: linear-gradient(135deg, #fde9d6, #fbd0b0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 800;
    color: #E8640C;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(28,35,49,.14);
}

.jg-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* ── Text ────────────────────────────────────────────────── */
.jg-eyebrow {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #E8640C;
    margin: 0 0 6px;
}

.jg-name {
    font-size: 22px;
    font-weight: 800;
    color: #1C2331;
    margin: 0 0 6px;
    line-height: 1.2;
    letter-spacing: -0.01em;
}

.jg-description {
    font-size: 13.5px;
    color: #6b7280;
    margin: 0 0 16px;
    line-height: 1.5;
}

/* ── Meta pills ──────────────────────────────────────────── */
.jg-meta {
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}

.jg-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.jg-pill svg {
    width: 13px;
    height: 13px;
    flex-shrink: 0;
}

/* ── Divider ─────────────────────────────────────────────── */
.jg-divider {
    height: 1px;
    background: #f3f4f6;
    margin: 0 -32px 24px;
}

/* ── State banners ───────────────────────────────────────── */
.jg-state {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    text-align: left;
    margin-bottom: 20px;
}

.jg-state__icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.jg-state__icon svg {
    width: 18px;
    height: 18px;
}

.jg-state--member {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}
.jg-state--member .jg-state__icon {
    background: #dcfce7;
    color: #16a34a;
}
.jg-state--member .jg-state__title { color: #15803d; }

.jg-state--pending {
    background: #fffbeb;
    border: 1px solid #fde68a;
}
.jg-state--pending .jg-state__icon {
    background: #fef3c7;
    color: #d97706;
}
.jg-state--pending .jg-state__title { color: #b45309; }

.jg-state__copy { flex: 1; }
.jg-state__title {
    font-size: 13.5px;
    font-weight: 700;
    margin: 0 0 2px;
}
.jg-state__sub {
    font-size: 12.5px;
    color: #6b7280;
    margin: 0;
}

/* ── Actions ─────────────────────────────────────────────── */
.jg-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.jg-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 22px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid transparent;
    text-decoration: none;
    transition: all .18s;
    white-space: nowrap;
}

.jg-btn--primary {
    background: #E8640C;
    color: #fff;
    border-color: #E8640C;
}
.jg-btn--primary:hover:not(:disabled) {
    background: #d05a0b;
}
.jg-btn--primary:disabled {
    opacity: .55;
    cursor: not-allowed;
}

.jg-btn--ghost {
    background: #f3f4f6;
    color: #374151;
    border-color: #e5e7eb;
}
.jg-btn--ghost:hover {
    background: #e5e7eb;
}

/* ── Error ───────────────────────────────────────────────── */
.jg-error {
    min-height: 18px;
    margin-top: 12px;
    font-size: 13px;
    color: #b91c1c;
    font-weight: 500;
}

/* ── Members preview ─────────────────────────────────────── */
.jg-members-preview {
    display: flex;
    justify-content: center;
    margin-bottom: 6px;
}

.jg-members-preview .jg-avatar-stack {
    display: flex;
}

.jg-avatar-mini {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #fff;
    background: linear-gradient(135deg, #fde9d6, #fbd0b0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    color: #E8640C;
    margin-left: -8px;
    overflow: hidden;
}

.jg-avatar-mini:first-child {
    margin-left: 0;
}

/* ── Spinner ─────────────────────────────────────────────── */
@keyframes jg-spin {
    to { transform: rotate(360deg); }
}
.jg-spinner {
    display: inline-block;
    width: 15px;
    height: 15px;
    border: 2px solid rgba(255,255,255,.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: jg-spin .7s linear infinite;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 480px) {
    .jg-card__body { padding: 0 20px 24px; }
    .jg-divider    { margin: 0 -20px 20px; }
    .jg-actions    { flex-direction: column; }
    .jg-btn        { justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="jg-page">
    <div class="jg-card">

        {{-- Coloured banner --}}
        <div class="jg-card__banner"></div>

        <div class="jg-card__body">

            {{-- Group avatar --}}
            <div class="jg-avatar-wrap">
                <div class="jg-avatar">
                    @if($conversation->avatar)
                        <img loading="lazy" src="{{ asset('storage/' . $conversation->avatar) }}"
                             alt="{{ $conversation->name }}">
                    @else
                        {{ strtoupper(substr($conversation->name ?? 'G', 0, 1)) }}
                    @endif
                </div>
            </div>

            {{-- Eyebrow --}}
            <p class="jg-eyebrow">Group Invitation</p>

            {{-- Name --}}
            <h1 class="jg-name">{{ $conversation->name }}</h1>

            {{-- Description --}}
            @if($conversation->description)
                <p class="jg-description">{{ $conversation->description }}</p>
            @endif

            {{-- Meta pills --}}
            @php
                $memberCount = $conversation->participants()->count();
            @endphp
            <div class="jg-meta">
                <span class="jg-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                    {{ number_format($memberCount) }}
                    {{ Str::plural('member', $memberCount) }}
                </span>

                <span class="jg-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                    Community Group
                </span>

                @if($conversation->created_at)
                    <span class="jg-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Created {{ $conversation->created_at->format('M Y') }}
                    </span>
                @endif
            </div>

            <div class="jg-divider"></div>

                        {{-- ── State: link disabled ───────────────────────────── --}}
            @if($linkDisabled)
                <div class="jg-state" style="background:#fef2f2;border:1px solid #fecaca;">
                    <div class="jg-state__icon" style="background:#fee2e2;color:#b91c1c;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                        </svg>
                    </div>
                    <div class="jg-state__copy">
                        <p class="jg-state__title" style="color:#b91c1c;">Invite link disabled</p>
                        <p class="jg-state__sub">This invite link has been disabled by the group admin. Ask the admin to share a new link.</p>
                    </div>
                </div>
                <div class="jg-actions">
                    <a href="{{ route('chat.index') }}" class="jg-btn jg-btn--ghost">Back to Chats</a>
                </div>

            {{-- ── State: already a member ─────────────────────────── --}}
            @if($isMember)
                <div class="jg-state jg-state--member">
                    <div class="jg-state__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <div class="jg-state__copy">
                        <p class="jg-state__title">You're already a member</p>
                        <p class="jg-state__sub">You have access to this group's messages.</p>
                    </div>
                </div>

                <div class="jg-actions">
                    <a href="{{ route('chat.index') }}?conversation={{ $conversation->id }}"
                       class="jg-btn jg-btn--primary">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                        Open Group
                    </a>
                </div>

            {{-- ── State: pending request ───────────────────────────── --}}
            @elseif($hasPending)
                <div class="jg-state jg-state--pending">
                    <div class="jg-state__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="jg-state__copy">
                        <p class="jg-state__title">Request pending</p>
                        <p class="jg-state__sub">
                            Your join request is awaiting admin approval.
                            You'll have access once an admin accepts it.
                        </p>
                    </div>
                </div>

                <div class="jg-actions">
                    <a href="{{ route('chat.index') }}" class="jg-btn jg-btn--ghost">
                        Back to Chats
                    </a>
                </div>

            {{-- ── State: can join ─────────────────────────────────── --}}
            @else
                <p style="font-size:13.5px;color:#6b7280;margin:0 0 20px;line-height:1.6;">
                    You've been invited to join this group.
                    Your request will be reviewed by a group admin before you're added.
                </p>

                <div class="jg-actions">
                    <a href="{{ route('chat.index') }}" class="jg-btn jg-btn--ghost">
                        Cancel
                    </a>
                    <button class="jg-btn jg-btn--primary"
                            id="joinGroupBtn"
                            type="button">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        Request to Join
                    </button>
                </div>

                <p class="jg-error" id="joinGroupError" role="alert"></p>
            @endif

        </div>
    </div>
</div>
@endsection

@if(!$isMember && !$hasPending && !$linkDisabled)
@push('scripts')
<script>
(function () {
    const btn   = document.getElementById('joinGroupBtn');
    const error = document.getElementById('joinGroupError');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        btn.disabled   = true;
        error.textContent = '';
        btn.innerHTML  = '<span class="jg-spinner"></span> Sending…';

        try {
            const res  = await fetch(@json(route('chat.join.store', ['token' => $token])), {
                method:  'POST',
                headers: {
                    'Accept':           'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     @json(csrf_token()),
                },
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || data.message || 'Unable to send the request.');
            }

            // Success — update UI in place
            btn.closest('.jg-actions').innerHTML = '';

            const stateDiv = document.createElement('div');
            stateDiv.className = 'jg-state jg-state--pending';
            stateDiv.innerHTML = `
                <div class="jg-state__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="jg-state__copy">
                    <p class="jg-state__title">Request sent!</p>
                    <p class="jg-state__sub">
                        Waiting for admin approval. You'll be added once accepted.
                    </p>
                </div>`;

            // Insert before the (now-empty) actions div
            btn.closest('.jg-card__body').insertBefore(
                stateDiv,
                document.querySelector('.jg-actions')
            );

            // Show back button
            document.querySelector('.jg-actions').innerHTML =
                `<a href="{{ route('chat.index') }}" class="jg-btn jg-btn--ghost">Back to Chats</a>`;

        } catch (err) {
            error.textContent = err.message;
            btn.disabled      = false;
            btn.innerHTML     = `
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="20" y1="8" x2="20" y2="14"/>
                    <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
                Request to Join`;
        }
    });
})();
</script>
@endpush
@endif