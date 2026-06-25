@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Group Chat Invitations')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/messages/chat.css') }}">
<style>
.gi-page {
    max-width: 640px;
    margin: 32px auto;
    padding: 0 16px;
}

.gi-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 28px;
}

.gi-header__icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #fde9d6, #fbd0b0);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #E8640C;
    flex-shrink: 0;
}

.gi-header__title {
    font-size: 22px;
    font-weight: 800;
    color: #1C2331;
    margin: 0 0 2px;
}

.gi-header__sub {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
}

.gi-back {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    text-decoration: none;
    padding: 7px 14px;
    border-radius: 8px;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    transition: all .15s;
}
.gi-back:hover { background: #f3f4f6; color: #374151; }

/* ── Cards ─────────────────────────────── */
.gi-list { display: flex; flex-direction: column; gap: 12px; }

.gi-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 4px rgba(28,35,49,.06);
}

.gi-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fde9d6, #fbd0b0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 800;
    color: #E8640C;
    flex-shrink: 0;
    overflow: hidden;
}
.gi-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

.gi-info { flex: 1; min-width: 0; }

.gi-name {
    font-size: 15px;
    font-weight: 700;
    color: #1C2331;
    margin: 0 0 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.gi-meta {
    font-size: 12.5px;
    color: #6b7280;
    margin: 0;
}

.gi-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.gi-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all .15s;
    white-space: nowrap;
}

.gi-btn--accept {
    background: #E8640C;
    color: #fff;
    border-color: #E8640C;
}
.gi-btn--accept:hover:not(:disabled) { background: #d05a0b; }
.gi-btn--accept:disabled { opacity: .55; cursor: not-allowed; }

.gi-btn--decline {
    background: #f3f4f6;
    color: #6b7280;
    border-color: #e5e7eb;
}
.gi-btn--decline:hover { background: #e5e7eb; color: #374151; }

/* ── Empty ─────────────────────────────── */
.gi-empty {
    text-align: center;
    padding: 64px 24px;
    color: #9ca3af;
}
.gi-empty__icon { font-size: 48px; margin-bottom: 14px; }
.gi-empty__title { font-size: 17px; font-weight: 700; color: #374151; margin: 0 0 6px; }
.gi-empty__sub   { font-size: 13.5px; }

@media (max-width: 500px) {
    .gi-card { flex-wrap: wrap; }
    .gi-actions { width: 100%; }
    .gi-btn { flex: 1; justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="gi-page">

    <div class="gi-header">
        <div class="gi-header__icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
        </div>
        <div>
            <h1 class="gi-header__title">Group Chat Invitations</h1>
            <p class="gi-header__sub">Accept or decline pending invitations below</p>
        </div>
        <a href="{{ route('chat.index') }}" class="gi-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Chats
        </a>
    </div>

    @if(count($invitations) === 0)
        <div class="gi-empty">
            <div class="gi-empty__icon">💬</div>
            <p class="gi-empty__title">No pending invitations</p>
            <p class="gi-empty__sub">When someone invites you to a group chat, it will appear here.</p>
        </div>
    @else
        <div class="gi-list" id="invitationList">
            @foreach($invitations as $inv)
                <div class="gi-card" id="inv-{{ $inv['id'] }}">
                    <div class="gi-avatar">
                        @if($inv['group_avatar'])
                            <img loading="lazy" src="{{ $inv['group_avatar'] }}" alt="{{ $inv['group_name'] }}">
                        @else
                            {{ strtoupper(substr($inv['group_name'], 0, 1)) }}
                        @endif
                    </div>
                    <div class="gi-info">
                        <p class="gi-name">{{ $inv['group_name'] }}</p>
                        <p class="gi-meta">
                            Invited by <strong>{{ $inv['invited_by_name'] }}</strong> · {{ $inv['created_at'] }}
                        </p>
                    </div>
                    <div class="gi-actions">
                        <button class="gi-btn gi-btn--decline"
                            data-action="decline"
                            data-inv-id="{{ $inv['id'] }}"
                            data-card="inv-{{ $inv['id'] }}">
                            Decline
                        </button>
                        <button class="gi-btn gi-btn--accept"
                            data-action="accept"
                            data-token="{{ $inv['token'] }}"
                            data-card="inv-{{ $inv['id'] }}">
                            Accept
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const BASE = (window.APP_BASE_URL || '').replace(/\/$/, '');

    document.getElementById('invitationList')?.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;

        const action  = btn.dataset.action;
        const cardId  = btn.dataset.card;
        const card    = document.getElementById(cardId);
        const buttons = card?.querySelectorAll('button');

        // Disable both buttons while processing
        buttons?.forEach(b => b.disabled = true);

        try {
            if (action === 'accept') {
                const token = btn.dataset.token;
                const res = await fetch(`${BASE}/chat/join/${token}/accept`, {
                    method:  'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Failed to accept.');

                // Redirect to the group chat
                window.location.href = BASE + '/chat' + (data.conversation_id ? '?conversation=' + data.conversation_id : '');

            } else if (action === 'decline') {
                const invId = btn.dataset.invId;
                const res = await fetch(`${BASE}/chat/invitations/${invId}/decline`, {
                    method:  'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Failed to decline.');

                // Fade out the card
                if (card) {
                    card.style.transition = 'opacity .3s, transform .3s';
                    card.style.opacity    = '0';
                    card.style.transform  = 'translateX(20px)';
                    setTimeout(() => card.remove(), 310);
                }

                // Show empty state if no more cards
                setTimeout(() => {
                    const remaining = document.querySelectorAll('#invitationList .gi-card');
                    if (!remaining.length) location.reload();
                }, 400);
            }
        } catch (err) {
            alert(err.message);
            buttons?.forEach(b => b.disabled = false);
        }
    });
})();
</script>
@endpush
