@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Browse Mentors')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/mentors.css') }}">
@endpush

@section('content')
<div class="mentor-page">

    {{-- Hero --}}
    <div class="mentor-hero">
        <div class="mentor-hero__text">
            <h1 class="mentor-hero__title">Find Your Mentor</h1>
            <p class="mentor-hero__sub">Connect with experienced ICCR alumni who can guide your journey — in arts, culture, career, research and beyond.</p>
            <div class="mentor-hero__actions">
                <a href="{{ route('mentors.apply') }}" class="mentor-hero__btn mentor-hero__btn--primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2a5 5 0 1 1 0 10A5 5 0 0 1 12 2z"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
                    Become a Mentor
                </a>
                <a href="{{ route('mentors.connections') }}" class="mentor-hero__btn mentor-hero__btn--ghost">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    My Connections
                </a>
            </div>
        </div>
        <div class="mentor-hero__visual">
            <div class="mentor-hero__stat">
                <span class="mentor-hero__stat-num">{{ $mentors->total() }}</span>
                <span class="mentor-hero__stat-label">Mentors</span>
            </div>
            <div class="mentor-hero__stat">
                <span class="mentor-hero__stat-num">{{ $categories->count() }}</span>
                <span class="mentor-hero__stat-label">Fields</span>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @foreach(['success','error','info'] as $k)
        @if(session($k))
            <div class="admin-alert admin-alert--{{ $k }}" style="margin-bottom:16px;">{{ session($k) }}</div>
        @endif
    @endforeach

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('mentors.index') }}" id="mentorFilterForm">
        <div class="mentor-filters">
            <div class="mentor-search">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, expertise…" oninput="debounceSubmit()">
            </div>
        </div>
        {{-- Category chips --}}
        <div class="mentor-cat-chips" style="margin-bottom:20px;">
            <a href="{{ route('mentors.index', request()->except('category')) }}"
               class="mentor-cat-chip {{ !request('category') ? 'active' : '' }}"
               style="{{ !request('category') ? 'background:#e8640c;border-color:#e8640c;color:#fff;' : '' }}">
                All
            </a>
            @foreach($categories as $cat)
                @php $isActive = request('category') === $cat->slug; @endphp
                <a href="{{ route('mentors.index', array_merge(request()->query(), ['category' => $cat->slug])) }}"
                   class="mentor-cat-chip {{ $isActive ? 'active' : '' }}"
                   style="{{ $isActive ? "background:{$cat->color};border-color:{$cat->color};color:#fff;" : '' }}">
                    <span class="chip-dot" style="background:{{ $cat->color }}"></span>
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </form>

    {{-- Grid --}}
    @if($mentors->isEmpty())
        <div class="mentor-empty">
            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            <h3>No mentors found</h3>
            <p>Try adjusting your search or browse all mentors.</p>
        </div>
    @else
        <div class="mentor-grid">
            @foreach($mentors as $mentor)
            @php
                $user = $mentor->alumni;
                $initials = collect(explode(' ', $user->full_name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                $connStatus = $myConnections[$mentor->id] ?? null;
                $accentColor = $mentor->categories->first()->color ?? '#e8640c';
            @endphp
            <div class="mentor-card">
                <div class="mentor-card__top" style="background:{{ $accentColor }};"></div>
                <div class="mentor-card__body">
                    <div class="mentor-card__head">
                        <div class="mentor-card__avatar" style="background:linear-gradient(135deg, {{ $accentColor }}cc, {{ $accentColor }});">
                            @if($user->photo)
                                <img src="{{ Storage::url($user->photo) }}" alt="{{ $user->full_name }}">
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <div class="mentor-card__info">
                            <p class="mentor-card__name">{{ $user->full_name }}</p>
                            <p class="mentor-card__title">
                                {{ $user->current_job_title ?: '' }}
                                @if($user->current_job_title && $user->current_company) · @endif
                                {{ $user->current_company ?: '' }}
                            </p>
                        </div>
                    </div>

                    @if($mentor->expertise)
                        <p class="mentor-card__expertise">{{ $mentor->expertise }}</p>
                    @endif

                    <p class="mentor-card__bio">{{ $mentor->bio }}</p>

                    <div class="mentor-card__cats">
                        @foreach($mentor->categories->take(3) as $cat)
                            <span class="mentor-cat-tag" style="background:{{ $cat->color }}">{{ $cat->name }}</span>
                        @endforeach
                    </div>

                    <div class="mentor-card__meta">
                        <span class="mentor-card__meta-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                            {{ $mentor->experience_years }}yr exp
                        </span>
                        @if($mentor->availability)
                        <span class="mentor-card__meta-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            {{ $mentor->availability }}
                        </span>
                        @endif
                        <span class="mentor-card__meta-item">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            {{ $mentor->accepted_connections_count }}/{{ $mentor->max_mentees }}
                        </span>
                    </div>
                </div>

                <div class="mentor-card__footer">
                    <a href="{{ route('mentors.show', $mentor) }}" class="mtr-btn mtr-btn--outline mtr-btn--sm">View Profile</a>

                    @if($user->id == session('alumni_id'))
                        <span class="status-badge status-badge--accepted">You</span>
                    @elseif($connStatus === 'accepted')
                        <span class="mtr-btn mtr-btn--success mtr-btn--sm">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
                            Connected
                        </span>
                    @elseif($connStatus === 'pending')
                        <span class="mtr-btn mtr-btn--pending mtr-btn--sm">Pending</span>
                    @elseif(!$mentor->hasCapacity())
                        <span class="mtr-btn mtr-btn--outline mtr-btn--sm" style="opacity:.5;cursor:default;">Full</span>
                    @else
                        <button class="mtr-btn mtr-btn--primary mtr-btn--sm"
                                onclick="openConnectModal({{ $mentor->id }}, '{{ e($user->full_name) }}')"
                                data-mentor-id="{{ $mentor->id }}">
                            Connect
                        </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="mentor-pagination">
            {{ $mentors->links() }}
        </div>
    @endif

</div>

{{-- Connect Modal --}}
<div class="mtr-modal-overlay connect-modal" id="connectModal">
    <div class="mtr-modal">
        <h3 class="mtr-modal__title">Connect with <span id="connectModalName"></span></h3>
        <p class="mtr-modal__sub">Introduce yourself — tell them what you're hoping to learn or achieve.</p>
        <textarea id="connectMessage" placeholder="Hi, I'm interested in connecting because…" maxlength="500"
                  style="width:100%;border-radius:9px;border:1.5px solid #e2e8f0;padding:10px 12px;font-size:13.5px;font-family:inherit;resize:vertical;min-height:90px;outline:none;margin-top:4px;box-sizing:border-box;"></textarea>
        <div id="connectError" style="display:none;color:#c53030;font-size:12.5px;margin-top:6px;"></div>
        <div class="mtr-modal__actions" style="margin-top:14px;">
            <button type="button" class="mtr-btn mtr-btn--outline" onclick="closeConnectModal()">Cancel</button>
            <button type="button" class="mtr-btn mtr-btn--primary" id="connectSubmitBtn" onclick="submitConnect()">Send Request</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const BASE = (window.APP_BASE_URL || '').replace(/\/$/, '');
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
let currentMentorId = null;

function openConnectModal(mentorId, name) {
    currentMentorId = mentorId;
    document.getElementById('connectModalName').textContent = name;
    document.getElementById('connectMessage').value = '';
    document.getElementById('connectError').style.display = 'none';
    document.getElementById('connectSubmitBtn').disabled = false;
    document.getElementById('connectSubmitBtn').textContent = 'Send Request';
    document.getElementById('connectModal').classList.add('open');
}

function closeConnectModal() {
    document.getElementById('connectModal').classList.remove('open');
    currentMentorId = null;
}

async function submitConnect() {
    if (!currentMentorId) return;
    const btn = document.getElementById('connectSubmitBtn');
    const errEl = document.getElementById('connectError');
    const message = document.getElementById('connectMessage').value.trim();

    btn.disabled = true;
    btn.textContent = 'Sending…';
    errEl.style.display = 'none';

    try {
        const res = await fetch(`${BASE}/mentors/${currentMentorId}/connect`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ message }),
            credentials: 'same-origin',
        });

        const data = await res.json();

        if (!res.ok) {
            errEl.textContent = data.error || 'Something went wrong. Please try again.';
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Send Request';
            return;
        }

        // Success — close modal, update the button to "Pending"
        closeConnectModal();
        const cardBtn = document.querySelector(`[data-mentor-id="${currentMentorId}"]`);
        if (cardBtn) {
            cardBtn.outerHTML = '<span class="mtr-btn mtr-btn--pending mtr-btn--sm">Pending</span>';
        }

        // Show a brief toast
        showToast('Connection request sent!');

    } catch (e) {
        errEl.textContent = 'Network error. Please try again.';
        errEl.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Send Request';
    }
}

function showToast(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#276749;color:#fff;padding:10px 22px;border-radius:10px;font-size:13.5px;font-weight:700;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.2);';
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

document.getElementById('connectModal').addEventListener('click', function(e) {
    if (e.target === this) closeConnectModal();
});

let searchTimer;
function debounceSubmit() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => document.getElementById('mentorFilterForm').submit(), 500);
}
</script>
@endpush
@endsection
