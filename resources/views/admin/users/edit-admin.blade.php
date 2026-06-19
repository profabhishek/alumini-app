@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Edit Admin — ' . $user->full_name)
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Edit Admin</h1>
            <p class="admin-page-subtitle">Editing account for {{ $user->full_name }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="admin-link-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
            Back to Admins
        </a>
    </div>

    @foreach(['success','error'] as $msg)
        @if(session($msg))<div class="admin-alert admin-alert--{{ $msg }}">{{ session($msg) }}</div>@endif
    @endforeach

    <div class="admin-card">
        <form action="{{ route('admin.users.update-admin', $user) }}" method="POST" class="admin-form">
            @csrf @method('PUT')

            <div class="admin-form-grid">

                <div class="admin-form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                           required maxlength="255" class="admin-input @error('full_name') is-error @enderror">
                    @error('full_name')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           required maxlength="255" class="admin-input @error('email') is-error @enderror">
                    @error('email')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Role <span class="req">*</span></label>
                    <select name="role" class="admin-input @error('role') is-error @enderror"
                            {{ $user->id === (int) session('alumni_id') ? 'disabled' : '' }}>
                        <option value="admin"       {{ old('role', $user->role) === 'admin'       ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                    {{-- Preserve value when field is disabled --}}
                    @if($user->id === (int) session('alumni_id'))
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <span class="admin-form-hint">You cannot change your own role.</span>
                    @endif
                    @error('role')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div></div>

                <div class="admin-form-group">
                    <label>New Password <span class="admin-form-hint">(leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="Min. 8 characters"
                           class="admin-input @error('password') is-error @enderror">
                    @error('password')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="Re-enter new password"
                           class="admin-input">
                </div>

            </div>

            <div class="admin-form-actions">
                <a href="{{ route('admin.users.index') }}" class="admin-link-btn">Cancel</a>
                <button type="submit" class="admin-btn admin-btn--primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/></svg>
                    Save Changes
                </button>
            </div>

        </form>
    </div>

</div>
@endsection