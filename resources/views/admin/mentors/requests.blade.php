@extends('layouts.community')
@section('title', 'Mentor Requests')
@section('hideRightSidebar', true)
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/community/mentors.css') }}">
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Mentor Requests</h1>
            <p class="admin-page-subtitle">Review and approve alumni applications to become mentors.</p>
        </div>
        <a href="{{ route('admin.mentor-categories.index') }}" class="mtr-btn mtr-btn--outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><tag/><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Manage Categories
        </a>
    </div>

    @foreach(['success','error','info'] as $k)
        @if(session($k))
            <div class="admin-alert admin-alert--{{ $k }}">{{ session($k) }}</div>
        @endif
    @endforeach

    {{-- Tabs --}}
    <div class="mentor-admin-tabs">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
        <a href="{{ route('admin.mentors.requests', ['status' => $key]) }}"
           class="mentor-admin-tab {{ $filter === $key ? 'active' : '' }}">
            {{ $label }}
            @if($counts[$key] > 0)
                <span class="tab-count">{{ $counts[$key] > 9 ? '9+' : $counts[$key] }}</span>
            @endif
        </a>
        @endforeach
    </div>

    @if($profiles->isEmpty())
        <div class="admin-empty-state">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            <p>No {{ $filter }} applications</p>
            <span>{{ $filter === 'pending' ? 'New applications will appear here.' : 'Nothing to show here.' }}</span>
        </div>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Expertise & Categories</th>
                        <th>Experience</th>
                        <th>Applied</th>
                        <th>Status</th>
                        <th class="admin-table__actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profiles as $profile)
                    @php $user = $profile->alumni; @endphp
                    <tr>
                        <td>
                            <div class="admin-table__user">
                                <div class="admin-table__avatar">
                                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="admin-table__name">{{ $user->full_name }}</div>
                                    <div style="font-size:12px;color:#718096;">{{ $user->email }}</div>
                                    @if($user->current_job_title)
                                    <div style="font-size:12px;color:#a0aec0;">{{ $user->current_job_title }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px;font-weight:600;color:#1c2331;margin-bottom:5px;">
                                {{ Str::limit($profile->expertise, 50) }}
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($profile->categories as $cat)
                                    <span class="mentor-cat-tag" style="background:{{ $cat->color }};font-size:10.5px;">{{ $cat->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td style="font-size:13px;">{{ $profile->experience_years }} yrs</td>
                        <td style="font-size:12.5px;color:#718096;">{{ $profile->applied_at->format('d M Y') }}</td>
                        <td>
                            <span class="status-badge status-badge--{{ $profile->status }}">{{ $profile->status_badge }}</span>
                            @if($profile->status === 'rejected' && $profile->rejection_reason)
                                <div style="font-size:11px;color:#718096;margin-top:3px;max-width:150px;">{{ Str::limit($profile->rejection_reason, 60) }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                <button class="mtr-btn mtr-btn--outline mtr-btn--sm"
                                        onclick="toggleBio({{ $profile->id }})">Bio</button>

                                @if($profile->status === 'pending' || $profile->status === 'rejected')
                                <form method="POST" action="{{ route('admin.mentors.approve', $profile) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="mtr-btn mtr-btn--primary mtr-btn--sm">Approve</button>
                                </form>
                                @endif

                                @if($profile->status === 'pending' || $profile->status === 'approved')
                                <button class="mtr-btn mtr-btn--outline mtr-btn--sm"
                                        style="color:#c53030;border-color:#fed7d7;"
                                        onclick="openRejectModal({{ $profile->id }}, '{{ e($user->full_name) }}')">Reject</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    {{-- Bio expandable row --}}
                    <tr id="bio-{{ $profile->id }}" style="display:none;">
                        <td colspan="6">
                            <div style="padding:12px 16px;background:#f7fafc;border-radius:8px;font-size:13px;color:#4a5568;line-height:1.65;">
                                <strong style="color:#1c2331;font-size:12px;display:block;margin-bottom:6px;">BIO</strong>
                                {{ $profile->bio }}
                                @if($profile->availability)
                                <div style="margin-top:8px;font-size:12px;"><strong>Availability:</strong> {{ $profile->availability }}</div>
                                @endif
                                <div style="margin-top:4px;font-size:12px;"><strong>Max Mentees:</strong> {{ $profile->max_mentees }}</div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:20px;">
            {{ $profiles->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Reject Modal --}}
<div class="mtr-modal-overlay" id="rejectModal">
    <div class="mtr-modal">
        <h3 class="mtr-modal__title">Reject Application</h3>
        <p class="mtr-modal__sub" id="rejectModalSub"></p>
        <form id="rejectForm" method="POST">
            @csrf @method('PATCH')
            <textarea name="rejection_reason" placeholder="Optional: explain why this application was rejected…" maxlength="500"></textarea>
            <div class="mtr-modal__actions">
                <button type="button" class="mtr-btn mtr-btn--outline" onclick="document.getElementById('rejectModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="mtr-btn mtr-btn--primary" style="background:#c53030;">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const BASE = (window.APP_BASE_URL || '').replace(/\/$/, '');

function openRejectModal(profileId, name) {
    document.getElementById('rejectModalSub').textContent = `Rejecting application from ${name}.`;
    document.getElementById('rejectForm').action = `${BASE}/admin/mentors/${profileId}/reject`;
    document.getElementById('rejectModal').classList.add('open');
}

function toggleBio(id) {
    const row = document.getElementById('bio-' + id);
    row.style.display = row.style.display === 'none' ? '' : 'none';
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush
@endsection
