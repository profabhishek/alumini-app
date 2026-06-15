@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', $user->full_name . ' — Alumni Detail')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">{{ $user->full_name }}</h1>
            <p class="admin-page-subtitle">Alumni profile detail view</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('admin.alumni.edit', $user) }}" class="admin-link-btn admin-link-btn--primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <a href="{{ route('admin.alumni.index') }}" class="admin-link-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                Back
            </a>
        </div>
    </div>

    @foreach(['success','error'] as $msg)
        @if(session($msg))<div class="admin-alert admin-alert--{{ $msg }}">{{ session($msg) }}</div>@endif
    @endforeach

    <div class="admin-detail-grid">

        {{-- Left: avatar + quick actions --}}
        <div class="admin-detail-sidebar">
            <div class="admin-card admin-card--center">
                @if($user->photo)
                    <img src="{{ asset('storage/' . $user->photo) }}" class="admin-detail-avatar" alt="{{ $user->full_name }}">
                @else
                    <div class="admin-detail-avatar-initials">{{ $user->initials }}</div>
                @endif
                <div class="admin-detail-name">{{ $user->full_name }}</div>
                <div class="admin-detail-email">{{ $user->email }}</div>

                <span class="admin-status-badge {{ $user->is_approved ? 'admin-status-badge--approved' : 'admin-status-badge--pending' }}" style="margin:8px 0;">
                    {{ $user->is_approved ? 'Approved' : 'Pending' }}
                </span>

                <div style="display:flex;flex-direction:column;gap:8px;width:100%;margin-top:12px;">
                    <form action="{{ route('admin.alumni.toggle-approval', $user) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="admin-btn {{ $user->is_approved ? 'admin-btn--warn' : 'admin-btn--approve' }}"
                                style="width:100%;justify-content:center;"
                                onclick="return confirm('{{ $user->is_approved ? 'Suspend' : 'Approve' }} this account?')">
                            {{ $user->is_approved ? 'Suspend Account' : 'Approve Account' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.alumni.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Permanently delete {{ addslashes($user->full_name) }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn--reject" style="width:100%;justify-content:center;">
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: all profile fields --}}
        <div>
            <div class="admin-card" style="margin-bottom:16px;">
                <div class="admin-detail-section-title">Personal Information</div>
                <dl class="admin-detail-list">
                    <div class="admin-detail-row"><dt>Full Name</dt><dd>{{ $user->full_name }}</dd></div>
                    <div class="admin-detail-row"><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                    <div class="admin-detail-row"><dt>Phone</dt><dd>{{ $user->phone }}</dd></div>
                    <div class="admin-detail-row"><dt>Gender</dt><dd>{{ $user->gender }}</dd></div>
                    <div class="admin-detail-row"><dt>Birth Date</dt><dd>{{ \Carbon\Carbon::parse($user->birth_date)->format('d M Y') }}</dd></div>
                    <div class="admin-detail-row"><dt>Country</dt><dd>{{ $user->country ?: '—' }}</dd></div>
                    <div class="admin-detail-row"><dt>Current City</dt><dd>{{ $user->current_city ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="admin-card" style="margin-bottom:16px;">
                <div class="admin-detail-section-title">Academic Information</div>
                <dl class="admin-detail-list">
                    <div class="admin-detail-row"><dt>Institute</dt><dd>{{ $user->institute }}</dd></div>
                    <div class="admin-detail-row"><dt>Department</dt><dd>{{ $user->department }}</dd></div>
                    <div class="admin-detail-row"><dt>Batch</dt><dd>{{ $user->batch_name }}</dd></div>
                    <div class="admin-detail-row"><dt>Passing Year</dt><dd>{{ $user->passing_year }}</dd></div>
                    <div class="admin-detail-row"><dt>Roll Number</dt><dd>{{ $user->roll_number }}</dd></div>
                    <div class="admin-detail-row">
                        <dt>Attachment</dt>
                        <dd>
                            @if($user->attachment && $user->attachment !== 'none')
                                <a href="{{ asset('storage/alumni-documents/' . $user->attachment) }}" target="_blank" class="admin-link">View PDF</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            @if($user->current_job_title || $user->current_company || $user->bio)
            <div class="admin-card">
                <div class="admin-detail-section-title">Professional</div>
                <dl class="admin-detail-list">
                    <div class="admin-detail-row"><dt>Job Title</dt><dd>{{ $user->current_job_title ?: '—' }}</dd></div>
                    <div class="admin-detail-row"><dt>Company</dt><dd>{{ $user->current_company ?: '—' }}</dd></div>
                    @if($user->bio)
                    <div class="admin-detail-row admin-detail-row--full">
                        <dt>Bio</dt><dd>{{ $user->bio }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection