@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Community Groups')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/groups.css') }}">
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

    {{-- Hero --}}
    <div class="groups-hero">
        <div>
            <p class="groups-hero__eyebrow">ICCR Community</p>
            <h1 class="groups-hero__title">Community Groups</h1>
            <p class="groups-hero__sub">Join groups around shared interests, batches, or causes — or start your own.</p>
        </div>
        <a href="{{ route('groups.create') }}" class="groups-btn groups-btn--primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create Group
        </a>
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

            <a href="{{ route('groups.show', $group->slug) }}" class="group-card">

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
@endsection