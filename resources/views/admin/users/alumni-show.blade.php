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

                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:center;margin:8px 0;">
                    <span class="admin-status-badge {{ $user->is_approved ? 'admin-status-badge--approved' : 'admin-status-badge--pending' }}">
                        {{ $user->is_approved ? 'Approved' : 'Pending' }}
                    </span>
                    @if($user->is_iccr_alumni)
                        <span class="admin-status-badge" style="background:#e0edff;color:#1a56db;border:1px solid #b8d4f8;">
                            ICCR Alumni
                        </span>
                    @endif
                </div>

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
                    <div class="admin-detail-row"><dt>Nationality</dt><dd>{{ $user->nationality ?: '—' }}</dd></div>
                    <div class="admin-detail-row"><dt>Phone</dt><dd>{{ $user->phone ?: '—' }}</dd></div>
                    <div class="admin-detail-row"><dt>Gender</dt><dd>{{ $user->gender ?: '—' }}</dd></div>
                    <div class="admin-detail-row"><dt>Birth Date</dt><dd>{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('d M Y') : '—' }}</dd></div>
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

            <div class="admin-card" style="margin-bottom:16px;">
                <div class="admin-detail-section-title">Professional</div>
                <dl class="admin-detail-list">
                    <div class="admin-detail-row"><dt>Job Title</dt><dd>{{ $user->current_job_title ?: ($user->current_position ?: '—') }}</dd></div>
                    <div class="admin-detail-row"><dt>Company</dt><dd>{{ $user->current_company ?: '—' }}</dd></div>
                    @if($user->linkedin_url || $user->facebook_url)
                    <div class="admin-detail-row">
                        <dt>Social</dt>
                        <dd>
                            @if($user->linkedin_url)
                                <a href="{{ $user->linkedin_url }}" target="_blank" class="admin-link" style="margin-right:8px;">LinkedIn</a>
                            @endif
                            @if($user->facebook_url)
                                <a href="{{ $user->facebook_url }}" target="_blank" class="admin-link">Facebook</a>
                            @endif
                        </dd>
                    </div>
                    @endif
                    @if($user->bio)
                    <div class="admin-detail-row admin-detail-row--full">
                        <dt>Bio</dt><dd>{{ $user->bio }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            @if($user->is_iccr_alumni)
            <div class="admin-card" style="border-left:3px solid #1a56db;">
                <div class="admin-detail-section-title" style="color:#1a56db;">ICCR Alumni Data Sync</div>
                @php $hasAlumniRecord = \App\Models\AlumniData::where('email', $user->email)->exists(); @endphp
                @if($hasAlumniRecord)
                    <p style="font-size:13px;color:var(--text-muted);margin:0;">
                        ✅ A matching record exists in alumni_data. Fields were {{ $user->is_approved ? 'auto-populated on approval' : 'auto-populated when approved' }}.
                    </p>
                @else
                    <p style="font-size:13px;color:var(--text-muted);margin:0;">
                        ⚠️ No matching record found in alumni_data for <strong>{{ $user->email }}</strong>. Fields will remain blank until a CSV import includes this email.
                    </p>
                @endif
            </div>
            @endif
        </div>

    </div>

</div>
@endsection