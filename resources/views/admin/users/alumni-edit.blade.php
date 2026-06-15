@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Edit Alumni — ' . $user->full_name)
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/admin.css') }}?v=2">
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Edit Alumni</h1>
            <p class="admin-page-subtitle">Editing profile for {{ $user->full_name }}</p>
        </div>
        <a href="{{ route('admin.alumni.show', $user) }}" class="admin-link-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
            Back
        </a>
    </div>

    @foreach(['success','error'] as $msg)
        @if(session($msg))<div class="admin-alert admin-alert--{{ $msg }}">{{ session($msg) }}</div>@endif
    @endforeach

    <div class="admin-card">
        <form action="{{ route('admin.alumni.update', $user) }}" method="POST" class="admin-form">
            @csrf @method('PUT')

            <div class="admin-form-section-title">Personal Information</div>
            <div class="admin-form-grid">

                <div class="admin-form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                           class="admin-input @error('full_name') is-error @enderror">
                    @error('full_name')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="admin-input @error('email') is-error @enderror">
                    @error('email')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Phone <span class="req">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="admin-input @error('phone') is-error @enderror">
                    @error('phone')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Gender <span class="req">*</span></label>
                    <select name="gender" class="admin-input @error('gender') is-error @enderror">
                        @foreach(['Male','Female','Other'] as $g)
                            <option value="{{ $g }}" {{ old('gender', $user->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                    @error('gender')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Birth Date <span class="req">*</span></label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date) }}"
                           class="admin-input @error('birth_date') is-error @enderror">
                    @error('birth_date')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}"
                           class="admin-input">
                </div>

                <div class="admin-form-group">
                    <label>Current City</label>
                    <input type="text" name="current_city" value="{{ old('current_city', $user->current_city) }}"
                           class="admin-input">
                </div>

            </div>

            <div class="admin-form-section-title" style="margin-top:24px;">Academic Information</div>
            <div class="admin-form-grid">

                <div class="admin-form-group">
                    <label>Institute <span class="req">*</span></label>
                    <input type="text" name="institute" value="{{ old('institute', $user->institute) }}"
                           class="admin-input @error('institute') is-error @enderror">
                    @error('institute')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Department <span class="req">*</span></label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}"
                           class="admin-input @error('department') is-error @enderror">
                    @error('department')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Batch <span class="req">*</span></label>
                    <input type="text" name="batch_name" value="{{ old('batch_name', $user->batch_name) }}"
                           class="admin-input @error('batch_name') is-error @enderror">
                    @error('batch_name')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Passing Year <span class="req">*</span></label>
                    <input type="number" name="passing_year" value="{{ old('passing_year', $user->passing_year) }}"
                           min="1970" max="{{ date('Y') + 5 }}"
                           class="admin-input @error('passing_year') is-error @enderror">
                    @error('passing_year')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label>Roll Number <span class="req">*</span></label>
                    <input type="text" name="roll_number" value="{{ old('roll_number', $user->roll_number) }}"
                           class="admin-input @error('roll_number') is-error @enderror">
                    @error('roll_number')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

            </div>

            <div class="admin-form-section-title" style="margin-top:24px;">Account & Security</div>
            <div class="admin-form-grid">

                <div class="admin-form-group">
                    <label>Approval Status</label>
                    <label class="admin-toggle-wrap">
                        <input type="hidden" name="is_approved" value="0">
                        <input type="checkbox" name="is_approved" value="1"
                               {{ old('is_approved', $user->is_approved) ? 'checked' : '' }}>
                        <span class="admin-toggle-label">Account Approved</span>
                    </label>
                    <span class="admin-form-hint">Unchecking this will suspend the alumni's login access.</span>
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
                <a href="{{ route('admin.alumni.show', $user) }}" class="admin-link-btn">Cancel</a>
                <button type="submit" class="admin-btn admin-btn--primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/></svg>
                    Save Changes
                </button>
            </div>

        </form>
    </div>

</div>
@endsection