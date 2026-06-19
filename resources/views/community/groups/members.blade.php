@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Manage Members — ' . $group->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/groups.css') }}">
@endpush

@section('content')
<div class="groups-page groups-page--medium">

    @if(session('success'))
        <div class="groups-flash groups-flash--success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="groups-flash groups-flash--info">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="groups-flash groups-flash--error">{{ session('error') }}</div>
    @endif

    <a href="{{ route('groups.show', $group->slug) }}" class="groups-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to {{ $group->name }}
    </a>

    <div class="groups-hero">
        <div>
            <p class="groups-hero__eyebrow">Group Management</p>
            <h1 class="groups-hero__title">Members</h1>
            <p class="groups-hero__sub">
                @if($canManageRoles)
                    Approve join requests, promote moderators, and manage member roles.
                @else
                    Approve join requests and manage member access.
                @endif
            </p>
        </div>
    </div>

    {{-- Pending requests --}}
    <div class="gm-section">
        <div class="gm-section__header">
            <span class="gm-section__title">Pending Requests</span>
            @if($pending->count() > 0)
                <span class="gm-section__count">{{ $pending->count() }}</span>
            @endif
        </div>

        @forelse($pending as $member)
            @php $alumni = $member->alumni; @endphp
            <div class="gm-row">
                <div class="gm-avatar">
                    @if(!empty($alumni?->photo))
                        <img loading="lazy" src="{{ asset('storage/' . $alumni->photo) }}" alt="{{ $alumni->full_name }}">
                    @else
                        {{ $alumni?->initials ?? '?' }}
                    @endif
                </div>
                <div class="gm-info">
                    <div class="gm-name">{{ $alumni?->full_name ?? 'Unknown' }}</div>
                    <div class="gm-meta">
                        Requested {{ $member->created_at->diffForHumans() }}
                        @if(!empty($alumni?->department))
                            &nbsp;·&nbsp; {{ $alumni->department }}
                        @endif
                    </div>
                </div>
                <div class="gm-actions">
                    <form method="POST" action="{{ route('groups.members.approve', [$group->slug, $member->id]) }}">
                        @csrf
                        <button type="submit" class="groups-btn groups-btn--primary groups-btn--sm">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('groups.members.reject', [$group->slug, $member->id]) }}"
                          data-confirm-title="Reject this request?"
                          data-confirm-body="{{ $alumni?->full_name }}'s request to join {{ $group->name }} will be declined."
                          data-confirm-text="Reject"
                          data-confirm-danger="true">
                        @csrf
                        <button type="submit" class="groups-btn groups-btn--danger groups-btn--sm">Reject</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="gm-empty">No pending requests right now.</div>
        @endforelse
    </div>

    {{-- Approved members --}}
    <div class="gm-section">
        <div class="gm-section__header">
            <span class="gm-section__title">Members</span>
            <span class="gm-section__count">{{ $approved->count() }}</span>
        </div>

        @foreach($approved as $member)
            @php $alumni = $member->alumni; @endphp
            <div class="gm-row">
                <div class="gm-avatar">
                    @if(!empty($alumni?->photo))
                        <img loading="lazy" src="{{ asset('storage/' . $alumni->photo) }}" alt="{{ $alumni->full_name }}">
                    @else
                        {{ $alumni?->initials ?? '?' }}
                    @endif
                </div>
                <div class="gm-info">
                    <div class="gm-name">
                        {{ $alumni?->full_name ?? 'Unknown' }}
                        @if((int) $member->alumni_id === $myId)
                            <span class="gm-you-tag">You</span>
                        @endif
                    </div>
                    <div class="gm-meta">
                        Joined {{ $member->joined_at?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div class="gm-actions">
                    @if($canManageRoles)
                        <form method="POST" action="{{ route('groups.members.role', [$group->slug, $member->id]) }}">
                            @csrf
                            @method('PATCH')
                            <select name="role" class="gm-role-select" onchange="this.form.submit()">
                                <option value="member" {{ $member->role === 'member' ? 'selected' : '' }}>Member</option>
                                <option value="moderator" {{ $member->role === 'moderator' ? 'selected' : '' }}>Moderator</option>
                                <option value="admin" {{ $member->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </form>

                        <form method="POST" action="{{ route('groups.members.remove', [$group->slug, $member->id]) }}"
                              data-confirm-title="Remove member?"
                              data-confirm-body="{{ $alumni?->full_name }} will be removed from {{ $group->name }} and would need to request to join again."
                              data-confirm-text="Remove"
                              data-confirm-danger="true">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="groups-btn groups-btn--danger groups-btn--sm">Remove</button>
                        </form>
                    @elseif($member->role === 'member')
                        <span class="group-role-badge group-role-badge--member">Member</span>
                        <form method="POST" action="{{ route('groups.members.remove', [$group->slug, $member->id]) }}"
                              data-confirm-title="Remove member?"
                              data-confirm-body="{{ $alumni?->full_name }} will be removed from {{ $group->name }} and would need to request to join again."
                              data-confirm-text="Remove"
                              data-confirm-danger="true">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="groups-btn groups-btn--danger groups-btn--sm">Remove</button>
                        </form>
                    @else
                        <span class="group-role-badge group-role-badge--{{ $member->role }}">{{ ucfirst($member->role) }}</span>
                        <span class="gm-locked">Managed by group admins</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/community/confirm-modal.js') }}"></script>
@endpush