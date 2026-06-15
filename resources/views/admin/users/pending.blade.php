@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Pending Registrations')

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Pending Registration Requests</h1>
            <p class="admin-page-subtitle">Review and approve or reject new alumni sign-ups.</p>
        </div>
        <span class="admin-count-badge">{{ $pendingUsers->total() }} pending</span>
    </div>

    @if(session('success'))
        <div class="admin-alert admin-alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="admin-alert admin-alert--error">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="admin-alert admin-alert--info">{{ session('info') }}</div>
    @endif

    @if($pendingUsers->isEmpty())
        <div class="admin-empty-state">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <p>No pending requests.</p>
            <span>New alumni sign-ups awaiting approval will appear here.</span>
        </div>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Batch</th>
                        <th>Department</th>
                        <th>Passing Year</th>
                        <th>Registered</th>
                        <th>Attachment</th>
                        <th class="admin-table__actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingUsers as $user)
                        <tr>
                            <td>
                                <div class="admin-table__user">
                                    <div class="admin-table__avatar">{{ $user->initials }}</div>
                                    <div>
                                        <div class="admin-table__name">{{ $user->full_name }}</div>
                                        <div class="admin-table__meta">{{ $user->roll_number }} &middot; {{ $user->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->batch_name }}</td>
                            <td>{{ $user->department }}</td>
                            <td>{{ $user->passing_year }}</td>
                            <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                @if($user->attachment && $user->attachment !== 'none')
                                    <a href="{{ asset('storage/alumni-documents/' . $user->attachment) }}" target="_blank" class="admin-link">
                                        View PDF
                                    </a>
                                @else
                                    <span class="admin-table__meta">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <form action="{{ route('admin.users.approve', $user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="admin-btn admin-btn--approve">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.reject', $user) }}" method="POST"
                                          onsubmit="return confirm('Reject and permanently delete this registration? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn admin-btn--reject">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            {{ $pendingUsers->links() }}
        </div>
    @endif

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=1">
@endpush