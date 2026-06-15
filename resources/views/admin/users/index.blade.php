@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Manage Admins')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Manage Admins</h1>
            <p class="admin-page-subtitle">All accounts with admin or super admin access.</p>
        </div>
        @if(session('alumni_role') === 'super_admin')
            <a href="{{ route('admin.users.create-admin') }}" class="admin-link-btn admin-link-btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Admin
            </a>
        @endif
    </div>

    @foreach(['success','error','info'] as $msg)
        @if(session($msg))
            <div class="admin-alert admin-alert--{{ $msg }}">{{ session($msg) }}</div>
        @endif
    @endforeach

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th class="admin-table__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($admins as $admin)
                <tr>
                    <td>
                        <div class="admin-table__user">
                            <div class="admin-table__avatar">{{ $admin->initials }}</div>
                            <div>
                                <div class="admin-table__name">
                                    {{ $admin->full_name }}
                                    @if($admin->id === (int) session('alumni_id'))
                                        <span class="admin-badge-you">You</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $admin->email }}</td>
                    <td>
                        <span class="admin-role-badge admin-role-badge--{{ $admin->role }}">
                            {{ $admin->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                        </span>
                    </td>
                    <td>{{ $admin->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="admin-table__actions">
                            @if(session('alumni_role') === 'super_admin')
                                <a href="{{ route('admin.users.edit-admin', $admin) }}" class="admin-btn admin-btn--edit">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </a>

                                @if($admin->role !== 'super_admin' && $admin->id !== (int) session('alumni_id'))
                                    <form action="{{ route('admin.users.revoke', $admin) }}" method="POST"
                                          onsubmit="return confirm('Demote {{ addslashes($admin->full_name) }} back to alumni?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--warn">Demote</button>
                                    </form>
                                    <form action="{{ route('admin.users.destroy-admin', $admin) }}" method="POST"
                                          onsubmit="return confirm('Permanently delete this admin account? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--reject">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="admin-table__meta">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection