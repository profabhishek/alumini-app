@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', $mentor->alumni->full_name . ' — Mentor Profile')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/mentors.css') }}">
@endpush

@section('content')
<div class="mentor-page">
    <div style="margin-bottom:16px;">
        <a href="{{ route('mentors.index') }}" style="display:inline-flex;align-items:center;gap:5px;font-size:13px;color:var(--text-muted,#718096);text-decoration:none;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15,18 9,12 15,6"/></svg>
            Back to Mentors
        </a>
    </div>

    @foreach(['success','error','info'] as $k)
        @if(session($k))
            <div class="admin-alert admin-alert--{{ $k }}" style="margin-bottom:16px;">{{ session($k) }}</div>
        @endif
    @endforeach

    @php
        $user = $mentor->alumni;
        $initials = collect(explode(' ', $user->full_name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
        $accentColor = $mentor->categories->first()->color ?? '#e8640c';
        $isSelf = $user->id == session('alumni_id');
        $canConnect = !$isSelf && !$connection && $mentor->hasCapacity();
    @endphp

    <div class="mentor-profile">
        {{-- Sidebar card --}}
        <div class="mentor-profile__card">
            <div class="mentor-profile__banner" style="background:linear-gradient(135deg, {{ $accentColor }}cc, #1c2331);"></div>
            <div class="mentor-profile__avatar-wrap">
                <div class="mentor-profile__avatar">
                    @if($user->photo)
                        <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->full_name }}">
                    @else
                        {{ $initials }}
                    @endif
                </div>
            </div>
            <div class="mentor-profile__info">
                <h2 class="mentor-profile__name">{{ $user->full_name }}</h2>
                <p class="mentor-profile__role">
                    {{ $user->current_job_title ?: '' }}
                    @if($user->current_job_title && ($user->current_company || $user->current_city)) · @endif
                    {{ $user->current_company ?: '' }}
                    @if($user->current_city), {{ $user->current_city }}@endif
                </p>

                <div class="mentor-profile__cats">
                    @foreach($mentor->categories as $cat)
                        <span class="mentor-cat-tag" style="background:{{ $cat->color }}">{{ $cat->name }}</span>
                    @endforeach
                </div>

                <div class="mentor-profile__stat-row">
                    <div class="mentor-profile__stat">
                        <div class="mentor-profile__stat-val">{{ $mentor->experience_years }}</div>
                        <div class="mentor-profile__stat-lbl">Yrs Exp</div>
                    </div>
                    <div class="mentor-profile__stat">
                        <div class="mentor-profile__stat-val">{{ $mentor->acceptedConnections->count() }}</div>
                        <div class="mentor-profile__stat-lbl">Mentees</div>
                    </div>
                    <div class="mentor-profile__stat">
                        <div class="mentor-profile__stat-val">{{ $mentor->max_mentees }}</div>
                        <div class="mentor-profile__stat-lbl">Max</div>
                    </div>
                </div>

                @if($mentor->availability)
                    <div style="margin-bottom:14px;">
                        <span class="avail-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            {{ $mentor->availability }}
                        </span>
                    </div>
                @endif

                @if($isSelf)
                    <a href="{{ route('mentors.apply') }}" class="mtr-btn mtr-btn--outline mentor-profile__connect-btn">
                        Edit My Profile
                    </a>
                @elseif($connection)
                    @if($connection->status === 'accepted')
                        <span class="mtr-btn mtr-btn--success mentor-profile__connect-btn" style="justify-content:center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
                            Connected
                        </span>
                        <button class="mtr-btn mtr-btn--primary mentor-profile__connect-btn" style="margin-top:7px;justify-content:center;"
                                onclick="startMentorChat({{ $user->id }}, this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            Send Message
                        </button>
                        <form method="POST" action="{{ route('mentors.connections.cancel', $connection) }}" style="margin-top:7px;">
                            @csrf @method('DELETE')
                            <button type="submit" class="mtr-btn mtr-btn--outline mentor-profile__connect-btn" style="font-size:12px;">Remove Connection</button>
                        </form>
                    @elseif($connection->status === 'pending')
                        <span class="mtr-btn mtr-btn--pending mentor-profile__connect-btn" style="cursor:default;">
                            Request Pending…
                        </span>
                        <form method="POST" action="{{ route('mentors.connections.cancel', $connection) }}" style="margin-top:7px;">
                            @csrf @method('DELETE')
                            <button type="submit" class="mtr-btn mtr-btn--outline mentor-profile__connect-btn" style="font-size:12px;">Withdraw Request</button>
                        </form>
                    @else
                        <span class="status-badge status-badge--declined" style="margin-bottom:10px;display:inline-flex;">Request Declined</span>
                    @endif
                @elseif(!$mentor->hasCapacity())
                    <span class="mtr-btn mentor-profile__connect-btn" style="background:#edf2f7;color:#a0aec0;cursor:not-allowed;">
                        Mentor is Full
                    </span>
                @else
                    <button class="mtr-btn mtr-btn--primary mentor-profile__connect-btn"
                            onclick="document.getElementById('connectModal').classList.add('open')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Connect with {{ explode(' ', $user->full_name)[0] }}
                    </button>
                @endif

                {{-- Social links --}}
                @if($user->linkedin_url || $user->twitter_url || $user->website_url)
                <div style="display:flex;gap:8px;margin-top:12px;">
                    @if($user->linkedin_url)
                    <a href="{{ $user->linkedin_url }}" target="_blank" title="LinkedIn"
                       style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#f7fafc;color:#0077b5;text-decoration:none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                    </a>
                    @endif
                    @if($user->website_url)
                    <a href="{{ $user->website_url }}" target="_blank" title="Website"
                       style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#f7fafc;color:#4a5568;text-decoration:none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Main content --}}
        <div class="mentor-profile__main">
            {{-- About --}}
            <div class="mtr-section">
                <h3 class="mtr-section__title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    About
                </h3>
                <p class="mtr-bio">{{ $mentor->bio }}</p>
            </div>

            {{-- Expertise areas --}}
            <div class="mtr-section">
                <h3 class="mtr-section__title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
                    Areas of Expertise
                </h3>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($mentor->categories as $cat)
                    <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:10px;background:{{ $cat->color }}18;border:1.5px solid {{ $cat->color }}44;">
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $cat->color }};flex-shrink:0;"></span>
                        <div>
                            <div style="font-size:13.5px;font-weight:700;color:#1c2331;">{{ $cat->name }}</div>
                            @if($cat->description)
                            <div style="font-size:11.5px;color:#718096;">{{ $cat->description }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Background --}}
            @if($user->department || $user->institute || $user->batch_name)
            <div class="mtr-section">
                <h3 class="mtr-section__title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    Background
                </h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
                    @if($user->institute)
                    <div style="padding:12px;background:#f7fafc;border-radius:10px;">
                        <div style="font-size:11px;color:#a0aec0;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Institute</div>
                        <div style="font-size:13.5px;font-weight:700;color:#1c2331;">{{ $user->institute }}</div>
                    </div>
                    @endif
                    @if($user->department)
                    <div style="padding:12px;background:#f7fafc;border-radius:10px;">
                        <div style="font-size:11px;color:#a0aec0;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Department</div>
                        <div style="font-size:13.5px;font-weight:700;color:#1c2331;">{{ $user->department }}</div>
                    </div>
                    @endif
                    @if($user->batch_name)
                    <div style="padding:12px;background:#f7fafc;border-radius:10px;">
                        <div style="font-size:11px;color:#a0aec0;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Batch</div>
                        <div style="font-size:13.5px;font-weight:700;color:#1c2331;">{{ $user->batch_name }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Chat helper (available for accepted connections) --}}
@push('scripts')
<script>
const BASE_SHOW = (window.APP_BASE_URL || '').replace(/\/$/, '');
const CSRF_SHOW = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function startMentorChat(userId, btn) {
    btn.disabled = true;
    btn.textContent = 'Opening…';
    try {
        const res = await fetch(`${BASE_SHOW}/chat/direct`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_SHOW },
            body: JSON.stringify({ user_id: userId }),
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Could not open chat.'); btn.disabled = false; btn.textContent = 'Send Message'; return; }
        window.location.href = `${BASE_SHOW}/chat?conversation=${data.conversation.id}`;
    } catch(e) {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Send Message';
    }
}
</script>
@endpush

{{-- Connect Modal --}}
@if($canConnect)
<div class="mtr-modal-overlay connect-modal" id="connectModal">
    <div class="mtr-modal">
        <h3 class="mtr-modal__title">Connect with {{ explode(' ', $user->full_name)[0] }}</h3>
        <p class="mtr-modal__sub">Introduce yourself and tell them what you're hoping to achieve together.</p>
        <textarea id="connectMsg" placeholder="Hi {{ explode(' ', $user->full_name)[0] }}, I'd love to connect because…" maxlength="500"
                  style="width:100%;border-radius:9px;border:1.5px solid #e2e8f0;padding:10px 12px;font-size:13.5px;font-family:inherit;resize:vertical;min-height:90px;outline:none;margin-top:4px;box-sizing:border-box;"></textarea>
        <div id="connectErr" style="display:none;color:#c53030;font-size:12.5px;margin-top:6px;"></div>
        <div class="mtr-modal__actions" style="margin-top:14px;">
            <button type="button" class="mtr-btn mtr-btn--outline" onclick="document.getElementById('connectModal').classList.remove('open')">Cancel</button>
            <button type="button" class="mtr-btn mtr-btn--primary" id="connectBtn" onclick="submitConnect()">Send Request</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const BASE   = (window.APP_BASE_URL || '').replace(/\/$/, '');
const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content || '';
const MENTOR_ID = {{ $mentor->id }};

async function submitConnect() {
    const btn    = document.getElementById('connectBtn');
    const errEl  = document.getElementById('connectErr');
    const message = document.getElementById('connectMsg').value.trim();

    btn.disabled = true;
    btn.textContent = 'Sending…';
    errEl.style.display = 'none';

    try {
        const res = await fetch(`${BASE}/mentors/${MENTOR_ID}/connect`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ message }),
            credentials: 'same-origin',
        });
        const data = await res.json();

        if (!res.ok) {
            errEl.textContent = data.error || 'Something went wrong.';
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Send Request';
            return;
        }

        // Replace the connect button in the sidebar with "Pending" state
        document.getElementById('connectModal').classList.remove('open');
        const connectBtnWrap = document.querySelector('.mentor-profile__connect-btn');
        if (connectBtnWrap) {
            connectBtnWrap.outerHTML = '<span class="mtr-btn mtr-btn--pending mentor-profile__connect-btn" style="cursor:default;justify-content:center;">Request Pending…</span>';
        }

        // Toast
        const t = document.createElement('div');
        t.textContent = 'Connection request sent!';
        t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#276749;color:#fff;padding:10px 22px;border-radius:10px;font-size:13.5px;font-weight:700;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.2);';
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);

    } catch(e) {
        errEl.textContent = 'Network error. Please try again.';
        errEl.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Send Request';
    }
}

document.getElementById('connectModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush
@endif
@endsection
