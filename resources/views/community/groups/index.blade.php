@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Community Groups')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/groups.css') }}">
<style>
.group-card { position: relative; }
.group-card__notif-dot {
    position: absolute;
    top: 10px; right: 10px;
    background: #e8640c;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 999px;
    padding: 2px 7px;
    min-width: 20px;
    text-align: center;
    display: none;
    z-index: 10;
    box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.inv-banner {
    background: linear-gradient(90deg, #1a3a5c 0%, #2d6fa4 100%);
    color: #fff;
    padding: 14px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
}
.inv-banner a {
    margin-left: auto;
    background: #e8640c;
    color: #fff;
    padding: 7px 16px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    text-decoration: none;
}
.inv-banner a:hover { background: #c95b0b; }
</style>
@endpush

@section('content')
<div class="groups-page">

    @if(session('success'))
        <div class="groups-flash groups-flash--success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="groups-flash groups-flash--info">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="groups-flash groups-flash--error">{{ session('error') }}</div>
    @endif

    {{-- Pending invitations banner --}}
    <div id="inv-banner" class="inv-banner" style="display:none">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.64A2 2 0 012 .82h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
        </svg>
        <span id="inv-banner-text">You have pending group invitations</span>
        <a href="{{ route('groups.invitations') }}">View Invitations</a>
    </div>

    {{-- Hero --}}
    <div class="groups-hero">
        <div>
            <p class="groups-hero__eyebrow">ICCR Community</p>
            <h1 class="groups-hero__title">Community Groups</h1>
            <p class="groups-hero__sub">Join groups around shared interests, batches, or causes — or start your own.</p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <a href="{{ route('groups.invitations') }}" class="groups-btn groups-btn--ghost" style="position:relative">
                Invitations
                <span id="sb-badge-inv" style="display:none;position:absolute;top:-6px;right:-6px;background:#e8640c;color:#fff;font-size:10px;font-weight:700;border-radius:999px;padding:1px 5px;min-width:16px;text-align:center"></span>
            </a>
            <a href="{{ route('groups.create') }}" class="groups-btn groups-btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Create Group
            </a>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('groups.index') }}" class="groups-search">
        <svg class="groups-search__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <input
            type="text"
            name="search"
            value="{{ $search }}"
            placeholder="Search groups by name or description…"
            class="groups-search__input"
            autocomplete="off"
        >
        <button type="submit" class="groups-btn groups-btn--ghost">Search</button>
        @if($search)
            <a href="{{ route('groups.index') }}" class="groups-btn groups-btn--ghost">Clear</a>
        @endif
    </form>

    {{-- Grid --}}
    <div class="groups-grid">

        @forelse($groups as $group)
            @php $card = $group->toCardArray($myId); @endphp

            <a href="{{ route('groups.show', $group->slug) }}"
               class="group-card"
               data-group-id="{{ $group->id }}"
               @if($card['is_member']) data-member="1" @endif>

                {{-- Unread badge (filled by JS polling) --}}
                <span class="group-card__notif-dot" id="group-dot-{{ $group->id }}"></span>

                <div class="group-card__cover" @if($card['cover_image']) style="background-image:url('{{ $card['cover_image'] }}')" @endif>
                    @if(!$card['cover_image'])
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    @endif

                    @if($card['is_member'])
                        <span class="group-card__badge group-card__badge--member">
                            {{ $card['role'] === 'admin' ? 'Admin' : ($card['role'] === 'moderator' ? 'Moderator' : 'Member') }}
                        </span>
                    @elseif($card['is_pending'])
                        <span class="group-card__badge group-card__badge--pending">Pending</span>
                    @endif
                </div>

                <div class="group-card__body">
                    <h3 class="group-card__name">{{ $card['name'] }}</h3>

                    @if($card['description'])
                        <p class="group-card__desc">{{ \Illuminate\Support\Str::limit($card['description'], 90) }}</p>
                    @endif

                    <span class="group-card__members">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        {{ number_format($card['members_count']) }} {{ $card['members_count'] === 1 ? 'member' : 'members' }}
                    </span>
                </div>

            </a>
        @empty
            <div class="groups-empty">
                <div class="groups-empty__icon">👥</div>
                <p class="groups-empty__title">No groups yet</p>
                <p class="groups-empty__sub">
                    @if($search)
                        No groups match "<strong>{{ $search }}</strong>".
                    @else
                        Be the first to start a community group.
                    @endif
                </p>
            </div>
        @endforelse

    </div>

    {{-- Pagination --}}
    @if($groups->hasPages())
        <div class="groups-pagination">
            {{ $groups->links() }}
        </div>
    @endif

</div>

@push('scripts')
<script>
(function() {
    const URL_COUNTS = '{{ route('groups.unread-counts') }}';
    const URL_MARK   = (slug) => `/groups/${slug}/mark-read`;
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function applyGroupBadges(data) {
        const counts        = data.counts || {};
        const pendingCounts = data.pending_counts || {};
        const pending       = data.pending_invitations || 0;

        // Per-card dots — include pending posts/edits for admin/mod groups
        document.querySelectorAll('.group-card[data-member="1"]').forEach(card => {
            const gid  = card.dataset.groupId;
            const dot  = document.getElementById('group-dot-' + gid);
            const cnt  = (counts[gid] || 0) + (pendingCounts[gid] || 0);
            if (dot) {
                dot.textContent = cnt > 99 ? '99+' : cnt;
                dot.style.display = cnt > 0 ? 'inline-block' : 'none';
            }
        });

        // Invitation badge on button
        const invBadge = document.getElementById('sb-badge-inv');
        if (invBadge) {
            invBadge.textContent = pending > 99 ? '99+' : pending;
            invBadge.style.display = pending > 0 ? 'inline-block' : 'none';
        }

        // Banner
        const banner = document.getElementById('inv-banner');
        const bannerText = document.getElementById('inv-banner-text');
        if (banner && bannerText) {
            if (pending > 0) {
                bannerText.textContent = `You have ${pending} pending group invitation${pending > 1 ? 's' : ''}`;
                banner.style.display = 'flex';
            } else {
                banner.style.display = 'none';
            }
        }

        // Expose total for sidebar badge — same formula as the layout poller
        if (typeof window.updateGroupSidebarBadge === 'function') {
            window.updateGroupSidebarBadge((data.total || 0) + pending);
        }
    }

    function fetchGroupCounts() {
        fetch(URL_COUNTS, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(applyGroupBadges)
            .catch(() => {});
    }

    // Mark group read when user clicks on a group card
    document.querySelectorAll('.group-card[data-member="1"]').forEach(card => {
        card.addEventListener('click', function(e) {
            const href = card.getAttribute('href');
            // Extract slug from href (/groups/{slug})
            const slug = href ? href.replace('/groups/', '') : null;
            if (!slug) return;

            // Clear dot immediately
            const dot = document.getElementById('group-dot-' + card.dataset.groupId);
            if (dot) dot.style.display = 'none';

            fetch(`/groups/${slug}/mark-read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).catch(() => {});
        });
    });

    // Poll every 10s, pause on hidden tab
    let timer;
    function startPoll() { timer = setInterval(fetchGroupCounts, 10000); }
    function stopPoll()  { clearInterval(timer); }

    // Clear sidebar badge immediately on page load (server already stamped last_read_at)
    if (typeof window.updateGroupSidebarBadge === 'function') {
        window.updateGroupSidebarBadge(0);
    }

    fetchGroupCounts();
    startPoll();

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopPoll();
        else { fetchGroupCounts(); startPoll(); }
    });
})();
</script>
@endpush

@endsection
