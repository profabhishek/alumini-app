@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Create Admin')

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Create Admin Account</h1>
            <p class="admin-page-subtitle">Grant a user admin or super admin access to the platform.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="admin-link-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 014-4h6a4 4 0 014 4v2"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
            View All Admins
        </a>
    </div>

    @if(session('success'))
        <div class="admin-alert admin-alert--success">{{ session('success') }}</div>
    @endif

    <div class="admin-card">
        <form action="{{ route('admin.users.store-admin') }}" method="POST" class="admin-form">
            @csrf

            <div class="admin-form-grid">

                <div class="admin-form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}"
                           placeholder="e.g. Priya Sharma" required maxlength="255"
                           class="admin-input @error('full_name') is-error @enderror">
                    @error('full_name')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="admin@example.com" required maxlength="255"
                           class="admin-input @error('email') is-error @enderror">
                    @error('email')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Role <span class="req">*</span></label>
                    <select name="role" class="admin-input @error('role') is-error @enderror">
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                    @error('role')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div></div>

                <div class="admin-form-group">
                    <label>Password <span class="req">*</span></label>
                    <input type="password" name="password"
                           placeholder="At least 8 characters" required minlength="8"
                           class="admin-input @error('password') is-error @enderror">
                    @error('password')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Confirm Password <span class="req">*</span></label>
                    <input type="password" name="password_confirmation"
                           placeholder="Re-enter password" required minlength="8"
                           class="admin-input">
                </div>

            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn--primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="17" y1="11" x2="23" y2="11"/><path d="M1 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/></svg>
                    Create Admin
                </button>
            </div>

        </form>
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=1">
@endpush