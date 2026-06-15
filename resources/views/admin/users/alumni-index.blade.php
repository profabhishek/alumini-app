@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Alumni Management')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Alumni Management</h1>
            <p class="admin-page-subtitle">
                {{ $alumni->total() }} alumni registered
                @if(request('search')) — searching "{{ request('search') }}" @endif
            </p>
        </div>
    </div>

    @foreach(['success','error','info'] as $msg)
        @if(session($msg))<div class="admin-alert admin-alert--{{ $msg }}">{{ session($msg) }}</div>@endif
    @endforeach

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.alumni.index') }}" class="admin-filters">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name, email, department, batch..."
               class="admin-input admin-search-input">

        <select name="status" class="admin-input admin-filter-select">
            <option value="">All Status</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
        </select>

        <select name="sort" class="admin-input admin-filter-select">
            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
            <option value="name"   {{ request('sort') === 'name'   ? 'selected' : '' }}>Name A–Z</option>
        </select>

        <button type="submit" class="admin-btn admin-btn--primary">Filter</button>

        @if(request('search') || request('status') || request('sort'))
            <a href="{{ route('admin.alumni.index') }}" class="admin-link-btn">Clear</a>
        @endif
    </form>

    @if($alumni->isEmpty())
        <div class="admin-empty-state">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <p>No alumni found.</p>
            <span>Try adjusting your filters.</span>
        </div>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Alumni</th>
                        <th>Email</th>
                        <th>Batch / Dept</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th class="admin-table__actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alumni as $member)
                    <tr>
                        <td>
                            <div class="admin-table__user">
                                @if($member->photo)
                                    <img src="{{ asset('storage/' . $member->photo) }}"
                                         class="admin-table__avatar admin-table__avatar--img"
                                         alt="{{ $member->full_name }}">
                                @else
                                    <div class="admin-table__avatar">{{ $member->initials }}</div>
                                @endif
                                <div>
                                    <div class="admin-table__name">{{ $member->full_name }}</div>
                                    <div class="admin-table__meta">{{ $member->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $member->email }}</td>
                        <td>
                            <div class="admin-table__name">{{ $member->batch_name }}</div>
                            <div class="admin-table__meta">{{ $member->department }}</div>
                        </td>
                        <td>{{ $member->created_at->format('d M Y') }}</td>
                        <td>
                            @if($member->is_approved)
                                <span class="admin-status-badge admin-status-badge--approved">Approved</span>
                            @else
                                <span class="admin-status-badge admin-status-badge--pending">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.alumni.show', $member) }}" class="admin-btn admin-btn--view">
                                    View
                                </a>
                                <a href="{{ route('admin.alumni.edit', $member) }}" class="admin-btn admin-btn--edit">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </a>
                                <form action="{{ route('admin.alumni.toggle-approval', $member) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="admin-btn {{ $member->is_approved ? 'admin-btn--warn' : 'admin-btn--approve' }}"
                                            onclick="return confirm('{{ $member->is_approved ? 'Suspend' : 'Approve' }} this account?')">
                                        {{ $member->is_approved ? 'Suspend' : 'Approve' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.alumni.destroy', $member) }}" method="POST"
                                      onsubmit="return confirm('Permanently delete {{ addslashes($member->full_name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn--reject">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $alumni->links() }}</div>
    @endif

</div>
@endsection