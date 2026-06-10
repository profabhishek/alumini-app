@extends('layouts.community')
@section('hideRightSidebar', true)

@section('title', 'Settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/settings.css') }}">
@endpush

@section('content')
<div class="settings-page">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="settings-alert settings-alert--success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,12 4,10"/><circle cx="12" cy="12" r="10"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="settings-alert settings-alert--error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="settings-header-card">
        <div class="settings-header-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
            </svg>
        </div>
        <div class="settings-header-text">
            <h1>Settings</h1>
            <p>Manage your notifications, appearance, and account preferences.</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="settings-tabs" id="settingsTabs">
        <button class="settings-tab active" data-tab="notifications">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
            Notifications
        </button>
        <button class="settings-tab" data-tab="preferences">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
            Preferences
        </button>
        <button class="settings-tab" data-tab="sessions">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            Active Sessions
        </button>
    </div>

    {{-- ── Tab: Notifications ─────────────────────────────────────────── --}}
    <div class="settings-tab-panel active" id="tab-notifications">
        <form action="{{ route('settings.notifications') }}" method="POST" class="settings-form">
            @csrf

            <div class="settings-form-section">
                <h2 class="settings-section-title">Email Notifications</h2>
                <p class="settings-section-desc">Choose which emails you'd like to receive from the alumni community.</p>

                <div class="settings-toggles">

                    <div class="settings-toggle-item">
                        <div class="settings-toggle-info">
                            <span class="settings-toggle-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Event Announcements
                            </span>
                            <span class="settings-toggle-desc">Get notified when new events are published or updated.</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox"
                                   name="email_notifications[events]"
                                   value="1"
                                   {{ ($user->email_notifications['events'] ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="settings-toggle-item">
                        <div class="settings-toggle-info">
                            <span class="settings-toggle-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                                Job Postings
                            </span>
                            <span class="settings-toggle-desc">Receive alerts when new jobs are posted by alumni.</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox"
                                   name="email_notifications[jobs]"
                                   value="1"
                                   {{ ($user->email_notifications['jobs'] ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="settings-toggle-item">
                        <div class="settings-toggle-info">
                            <span class="settings-toggle-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                                Alumni Stories
                            </span>
                            <span class="settings-toggle-desc">Be the first to read inspiring stories from fellow alumni.</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox"
                                   name="email_notifications[stories]"
                                   value="1"
                                   {{ ($user->email_notifications['stories'] ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                </div>
            </div>

            <div class="settings-form-actions">
                <button type="submit" class="btn-save">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                    Save Preferences
                </button>
            </div>

        </form>
    </div>

    {{-- ── Tab: Preferences ──────────────────────────────────────────────  --}}
    <div class="settings-tab-panel" id="tab-preferences">
        <form action="{{ route('settings.preferences') }}" method="POST" class="settings-form">
            @csrf

            <div class="settings-form-section">
                <h2 class="settings-section-title">Appearance</h2>
                <p class="settings-section-desc">Choose how the community portal looks for you.</p>

                <div class="appearance-cards" id="appearanceCards">

                    <label class="appearance-card {{ $user->appearance === 'light' || !$user->appearance ? 'active' : '' }}">
                        <input type="radio" name="appearance" value="light"
                               {{ $user->appearance === 'light' || !$user->appearance ? 'checked' : '' }} hidden>
                        <div class="appearance-preview appearance-preview--light">
                            <div class="ap-bar"></div>
                            <div class="ap-body">
                                <div class="ap-line ap-line--wide"></div>
                                <div class="ap-line ap-line--narrow"></div>
                            </div>
                        </div>
                        <span class="appearance-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                            Light
                        </span>
                    </label>

                    <label class="appearance-card {{ $user->appearance === 'dark' ? 'active' : '' }}">
                        <input type="radio" name="appearance" value="dark"
                               {{ $user->appearance === 'dark' ? 'checked' : '' }} hidden>
                        <div class="appearance-preview appearance-preview--dark">
                            <div class="ap-bar"></div>
                            <div class="ap-body">
                                <div class="ap-line ap-line--wide"></div>
                                <div class="ap-line ap-line--narrow"></div>
                            </div>
                        </div>
                        <span class="appearance-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                            Dark
                        </span>
                    </label>

                </div>
            </div>

            <div class="settings-form-section">
                <h2 class="settings-section-title">Profile Visibility</h2>
                <p class="settings-section-desc">Control who can find and view your alumni profile.</p>

                <div class="visibility-options">

                    <label class="visibility-option {{ $user->profile_visibility === 'public' || !$user->profile_visibility ? 'active' : '' }}">
                        <input type="radio" name="profile_visibility" value="public"
                               {{ $user->profile_visibility === 'public' || !$user->profile_visibility ? 'checked' : '' }} hidden>
                        <div class="visibility-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                        </div>
                        <div class="visibility-text">
                            <span class="visibility-title">Public</span>
                            <span class="visibility-desc">Anyone can view your profile, including non-members.</span>
                        </div>
                        <div class="visibility-check">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,12 4,10"/></svg>
                        </div>
                    </label>

                    <label class="visibility-option {{ $user->profile_visibility === 'alumni-only' ? 'active' : '' }}">
                        <input type="radio" name="profile_visibility" value="alumni-only"
                               {{ $user->profile_visibility === 'alumni-only' ? 'checked' : '' }} hidden>
                        <div class="visibility-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        </div>
                        <div class="visibility-text">
                            <span class="visibility-title">Alumni Only</span>
                            <span class="visibility-desc">Only verified alumni members can view your profile.</span>
                        </div>
                        <div class="visibility-check">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,12 4,10"/></svg>
                        </div>
                    </label>

                </div>
            </div>

            <div class="settings-form-actions">
                <button type="submit" class="btn-save">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                    Save Preferences
                </button>
            </div>

        </form>
    </div>

    {{-- ── Tab: Sessions ─────────────────────────────────────────────────  --}}
    <div class="settings-tab-panel" id="tab-sessions">

        <div class="settings-form-section">
            <h2 class="settings-section-title">Active Sessions</h2>
            <p class="settings-section-desc">These are the devices currently logged into your account. Revoke any session you don't recognise.</p>

            @if($sessions->isEmpty())
                <div class="sessions-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <p>No session records found.</p>
                </div>
            @else
                <div class="sessions-list">
                    @foreach($sessions as $sess)
                        <div class="session-item">
                            <div class="session-icon">
                                {{-- Mobile vs desktop icon based on device string --}}
                                @if(str_contains(strtolower($sess->device ?? ''), 'android') || str_contains(strtolower($sess->device ?? ''), 'ios'))
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                @else
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                @endif
                            </div>
                            <div class="session-info">
                                <span class="session-device">{{ $sess->device ?? 'Unknown device' }}</span>
                                <span class="session-meta">
                                    {{ $sess->ip_address ?? '—' }}
                                    @if($sess->location) · {{ $sess->location }} @endif
                                    · {{ $sess->last_active_at?->diffForHumans() ?? 'Unknown time' }}
                                </span>
                            </div>
                            <form action="{{ route('settings.sessions.revoke', $sess) }}"
                                  method="POST"
                                  onsubmit="return confirm('Revoke this session?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-revoke">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Revoke
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/community/settings.js') }}"></script>

@if(session('tab'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        activateTab('{{ session('tab') }}');
    });
</script>
@endif
@endpush