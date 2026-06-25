@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'My Mentor Connections')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/mentors.css') }}">
@endpush

@section('content')
<div class="mentor-page">
    <div class="mentor-page-header">
        <div>
            <h1 class="mentor-page-title">My Connections</h1>
            <p class="mentor-page-subtitle">Manage your mentoring relationships.</p>
        </div>
        <a href="{{ route('mentors.index') }}" class="mtr-btn mtr-btn--outline">Browse Mentors</a>
    </div>

    @foreach(['success','error','info'] as $k)
        @if(session($k))
            <div class="admin-alert admin-alert--{{ $k }}" style="margin-bottom:16px;">{{ session($k) }}</div>
        @endif
    @endforeach

    <div class="connections-page">

        {{-- AS MENTEE: requests I've sent --}}
        <div>
            <h2 class="conn-section-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Mentors I've Connected With
                <span class="status-badge status-badge--pending" style="font-size:11px;padding:2px 8px;">{{ $asMentee->count() }}</span>
            </h2>

            @if($asMentee->isEmpty())
                <div class="mentor-empty" style="padding:36px 20px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    <h3>No connections yet</h3>
                    <p>Browse mentors and send a connection request to get started.</p>
                    <a href="{{ route('mentors.index') }}" class="mtr-btn mtr-btn--primary" style="margin-top:8px;">Browse Mentors</a>
                </div>
            @else
                <div class="conn-list">
                    @foreach($asMentee as $conn)
                    @php
                        $mUser = $conn->mentor->alumni;
                        $initials = collect(explode(' ', $mUser->full_name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                        $accentColor = $conn->mentor->categories->first()->color ?? '#e8640c';
                    @endphp
                    <div class="conn-card">
                        <a href="{{ route('mentors.show', $conn->mentor) }}" style="text-decoration:none;">
                            <div class="conn-card__avatar" style="background:{{ $accentColor }};">
                                @if($mUser->photo)
                                    <img src="{{ Storage::url($mUser->photo) }}" alt="{{ $mUser->full_name }}">
                                @else
                                    {{ $initials }}
                                @endif
                            </div>
                        </a>
                        <div class="conn-card__info">
                            <p class="conn-card__name">
                                <a href="{{ route('mentors.show', $conn->mentor) }}" style="color:inherit;text-decoration:none;">{{ $mUser->full_name }}</a>
                            </p>
                            <p class="conn-card__sub">
                                {{ $conn->mentor->expertise ?: '' }}
                                @if($conn->message)
                                    · "{{ Str::limit($conn->message, 60) }}"
                                @endif
                            </p>
                            @if($conn->mentor_note && $conn->status !== 'pending')
                                <p class="conn-card__sub" style="margin-top:3px;font-style:italic;">"{{ $conn->mentor_note }}"</p>
                            @endif
                        </div>
                        <div class="conn-card__actions">
                            <span class="status-badge status-badge--{{ $conn->status }}">{{ ucfirst($conn->status) }}</span>
                            @if($conn->status === 'accepted')
                            <button class="mtr-btn mtr-btn--primary mtr-btn--sm"
                                    onclick="startMentorChat({{ $mUser->id }}, this)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                                Message
                            </button>
                            <form method="POST" action="{{ route('mentors.connections.cancel', $conn) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="mtr-btn mtr-btn--outline mtr-btn--sm"
                                        onclick="return confirm('Remove this connection?')">Remove</button>
                            </form>
                            @elseif($conn->status === 'pending')
                            <form method="POST" action="{{ route('mentors.connections.cancel', $conn) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="mtr-btn mtr-btn--outline mtr-btn--sm">Withdraw</button>
                            </form>
                            @elseif($conn->status === 'declined')
                            <span style="font-size:12px;color:#a0aec0;">Request was declined</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- AS MENTOR: requests I've received --}}
        @if($myProfile)
        <div>
            <h2 class="conn-section-title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a5 5 0 1 1 0 10A5 5 0 0 1 12 2z"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                Mentee Requests Received
                <span class="status-badge status-badge--approved" style="font-size:11px;padding:2px 8px;">Mentor</span>
            </h2>

            <div style="margin-bottom:14px;padding:12px 16px;background:#f7fafc;border-radius:10px;font-size:13px;color:#4a5568;display:flex;gap:16px;flex-wrap:wrap;">
                <span>
                    <strong style="color:#1c2331;">{{ $myProfile->acceptedConnections->count() }}</strong>
                    / {{ $myProfile->max_mentees }} mentee slots used
                </span>
                @php $pendingCount = $myProfile->connections->where('status','pending')->count(); @endphp
                @if($pendingCount)
                <span style="color:#e8640c;font-weight:700;">{{ $pendingCount }} pending {{ Str::plural('request', $pendingCount) }}</span>
                @endif
                <a href="{{ route('mentors.apply') }}" style="color:#e8640c;font-weight:700;text-decoration:none;">Edit Profile →</a>
            </div>

            @if($myProfile->connections->isEmpty())
                <div class="mentor-empty" style="padding:36px 20px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <h3>No requests yet</h3>
                    <p>Mentees will appear here once they request to connect with you.</p>
                </div>
            @else
                <div class="conn-list">
                    @foreach($myProfile->connections->sortByDesc('created_at') as $conn)
                    @php
                        $menteeUser = $conn->mentee;
                        $mInitials = collect(explode(' ', $menteeUser->full_name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                    @endphp
                    <div class="conn-card" style="flex-wrap:wrap;">
                        <div class="conn-card__avatar" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                            @if($menteeUser->photo)
                                <img src="{{ Storage::url($menteeUser->photo) }}" alt="{{ $menteeUser->full_name }}">
                            @else
                                {{ $mInitials }}
                            @endif
                        </div>
                        <div class="conn-card__info">
                            <p class="conn-card__name">{{ $menteeUser->full_name }}</p>
                            <p class="conn-card__sub">
                                {{ $menteeUser->current_job_title ?: 'Alumni' }}
                                @if($conn->message)· "{{ Str::limit($conn->message, 80) }}"@endif
                            </p>
                            <p class="conn-card__sub" style="margin-top:2px;">
                                Requested {{ $conn->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="conn-card__actions" style="flex-wrap:wrap;">
                            <span class="status-badge status-badge--{{ $conn->status }}">{{ ucfirst($conn->status) }}</span>

                            @if($conn->status === 'pending')
                                <button class="mtr-btn mtr-btn--primary mtr-btn--sm"
                                        onclick="openRespondModal({{ $conn->id }}, 'accept', '{{ e($menteeUser->full_name) }}')">Accept</button>
                                <button class="mtr-btn mtr-btn--outline mtr-btn--sm"
                                        onclick="openRespondModal({{ $conn->id }}, 'decline', '{{ e($menteeUser->full_name) }}')">Decline</button>
                            @elseif($conn->status === 'accepted')
                                <button class="mtr-btn mtr-btn--primary mtr-btn--sm"
                                        onclick="startMentorChat({{ $menteeUser->id }}, this)">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                                    Message
                                </button>
                                <form method="POST" action="{{ route('mentors.connections.cancel', $conn) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="mtr-btn mtr-btn--outline mtr-btn--sm"
                                            onclick="return confirm('End this mentoring relationship?')">End</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @else
        {{-- Not yet a mentor --}}
        <div style="background:#fff;border:1.5px solid #edf2f7;border-radius:14px;padding:24px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
            <div style="flex:1;">
                <h3 style="font-size:15px;font-weight:800;color:#1c2331;margin:0 0 5px;">Want to become a mentor?</h3>
                <p style="font-size:13px;color:#718096;margin:0;">Share your expertise and guide fellow ICCR alumni. Submit an application and an admin will review it.</p>
            </div>
            <a href="{{ route('mentors.apply') }}" class="mtr-btn mtr-btn--primary">Apply Now</a>
        </div>
        @endif
    </div>
</div>

{{-- Respond Modal --}}
<div class="mtr-modal-overlay" id="respondModal">
    <div class="mtr-modal">
        <h3 class="mtr-modal__title" id="respondModalTitle">Respond to Request</h3>
        <p class="mtr-modal__sub" id="respondModalSub"></p>
        <form id="respondForm" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="action" id="respondAction">
            <textarea name="mentor_note" placeholder="Optional note to the mentee…" maxlength="300"></textarea>
            <div class="mtr-modal__actions">
                <button type="button" class="mtr-btn mtr-btn--outline" onclick="document.getElementById('respondModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="mtr-btn mtr-btn--primary" id="respondSubmitBtn">Submit</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const BASE = (window.APP_BASE_URL || '').replace(/\/$/, '');
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function startMentorChat(userId, btn) {
    btn.disabled = true;
    btn.textContent = 'Opening…';
    try {
        const res = await fetch(`${BASE}/chat/direct`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ user_id: userId }),
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Could not open chat.'); btn.disabled = false; btn.textContent = 'Message'; return; }
        window.location.href = `${BASE}/chat?conversation=${data.conversation.id}`;
    } catch(e) {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Message';
    }
}

function openRespondModal(connId, action, name) {
    const isAccept = action === 'accept';
    document.getElementById('respondModalTitle').textContent = (isAccept ? 'Accept' : 'Decline') + ' request from ' + name;
    document.getElementById('respondModalSub').textContent = isAccept
        ? 'Welcome ' + name + ' as your mentee. You can add an optional note.'
        : 'Let ' + name + ' know why you cannot take them on right now.';
    document.getElementById('respondAction').value = action;
    document.getElementById('respondSubmitBtn').textContent = isAccept ? 'Accept' : 'Decline';
    document.getElementById('respondForm').action = `${BASE}/mentors/connections/${connId}/respond`;
    document.getElementById('respondModal').classList.add('open');
}

document.getElementById('respondModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush
@endsection
