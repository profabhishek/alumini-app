@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Group Invitations')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/groups.css') }}">
<style>
.inv-page { max-width: 680px; margin: 0 auto; padding: 24px 0; }
.inv-page__title { font-size: 22px; font-weight: 700; color: #1a3a5c; margin: 0 0 6px; }
.inv-page__sub   { font-size: 14px; color: #6b7280; margin: 0 0 28px; }
.inv-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.inv-card__cover {
    width: 56px; height: 56px;
    border-radius: 10px;
    background: linear-gradient(135deg,#1a3a5c,#2d6fa4);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; flex-shrink: 0;
}
.inv-card__cover img { width: 100%; height: 100%; object-fit: cover; }
.inv-card__body { flex: 1; min-width: 0; }
.inv-card__name { font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 3px; }
.inv-card__meta { font-size: 13px; color: #6b7280; margin: 0 0 10px; }
.inv-card__actions { display: flex; gap: 8px; flex-wrap: wrap; }
.inv-btn {
    padding: 7px 18px; border-radius: 7px; font-size: 13px;
    font-weight: 600; cursor: pointer; border: none; transition: background .15s;
}
.inv-btn--accept { background: #1a3a5c; color: #fff; }
.inv-btn--accept:hover { background: #142d47; }
.inv-btn--decline { background: #f3f4f6; color: #374151; }
.inv-btn--decline:hover { background: #e5e7eb; }
.inv-empty { text-align: center; padding: 60px 0; color: #9ca3af; }
.inv-empty__icon { font-size: 42px; margin-bottom: 10px; }
.inv-empty__title { font-size: 17px; font-weight: 600; color: #374151; margin-bottom: 6px; }
</style>
@endpush

@section('content')
<div class="inv-page">
    <h1 class="inv-page__title">Group Invitations</h1>
    <p class="inv-page__sub">Pending invitations to join community groups.</p>

    @if(session('success'))
        <div class="groups-flash groups-flash--success">{{ session('success') }}</div>
    @endif

    @forelse($invitations as $inv)
    <div class="inv-card" id="inv-{{ $inv->id }}">
        <div class="inv-card__cover">
            @if($inv->group->cover_image)
                <img loading="lazy" src="{{ $inv->group->cover_image }}" alt="{{ $inv->group->name }}">
            @else
                <svg width="28" height="28" fill="none" stroke="#fff" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
            @endif
        </div>
        <div class="inv-card__body">
            <p class="inv-card__name">{{ $inv->group->name }}</p>
            <p class="inv-card__meta">
                Invited by <strong>{{ $inv->invitedBy->full_name }}</strong>
                &middot; {{ $inv->created_at->diffForHumans() }}
            </p>
            <div class="inv-card__actions">
                <button class="inv-btn inv-btn--accept" onclick="respondInv({{ $inv->id }}, 'accept', '{{ $inv->group->slug }}')">
                    ✓ Accept
                </button>
                <button class="inv-btn inv-btn--decline" onclick="respondInv({{ $inv->id }}, 'decline', null)">
                    Decline
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="inv-empty">
        <div class="inv-empty__icon">📭</div>
        <p class="inv-empty__title">No pending invitations</p>
        <p>When someone invites you to a group, it will appear here.</p>
    </div>
    @endforelse
</div>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

function respondInv(id, action, groupSlug) {
    const card = document.getElementById('inv-' + id);
    const btns = card ? card.querySelectorAll('.inv-btn') : [];
    btns.forEach(b => b.disabled = true);

    fetch(`/groups/invitations/${id}/respond`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ action }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            if (card) {
                card.style.transition = 'opacity .3s';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 320);
            }
            if (action === 'accept' && data.redirect) {
                setTimeout(() => { window.location.href = data.redirect; }, 350);
            }
        }
    })
    .catch(() => { btns.forEach(b => b.disabled = false); });
}
</script>
@endpush

@endsection
