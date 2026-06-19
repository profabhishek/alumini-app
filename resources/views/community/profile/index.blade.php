@extends('layouts.community')
@section('hideRightSidebar', true)

@section('title', 'My Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/profile.css') }}">
<link rel="stylesheet" href="{{ asset('css/community/home.css') }}?v=5">
<link rel="stylesheet" href="{{ asset('css/community/feed.css') }}?v=5">
@endpush

@section('content')
<div class="profile-page">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="profile-alert profile-alert--success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,12 4,10"/><circle cx="12" cy="12" r="10"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="profile-alert profile-alert--error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Profile Header Card --}}
    <div class="profile-header-card">
        <div class="profile-cover"></div>
        <div class="profile-header-body">
            <div class="profile-avatar-wrap" id="avatarTrigger">
                @if($user->photo)
                    <img src="{{ asset('storage/' . $user->photo) }}"
                         alt="{{ $user->full_name }}"
                         class="profile-avatar-img"
                         id="currentAvatarImg">
                @else
                    <div class="profile-avatar-initials" id="currentAvatarImg">
                        {{ $user->initials }}
                    </div>
                @endif
                <div class="profile-avatar-overlay">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <span>Change Photo</span>
                </div>
            </div>

            <div class="profile-header-info">
                <h1 class="profile-header-name">{{ $user->full_name }}</h1>
                <p class="profile-header-meta">
                    @if($user->current_job_title && $user->current_company)
                        {{ $user->current_job_title }} at {{ $user->current_company }}
                    @elseif($user->department)
                        {{ $user->department }}
                    @endif
                </p>
                <div class="profile-header-tags">
                    @if($user->country)
                        <span class="profile-tag">🌍 {{ $user->country }}</span>
                    @endif
                    @if($user->passing_year)
                        <span class="profile-tag">🎓 Class of {{ $user->passing_year }}</span>
                    @endif
                    @if($user->institute)
                        <span class="profile-tag">🏛 {{ $user->institute }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="profile-tabs" id="profileTabs">
        <button class="profile-tab active" data-tab="info">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Personal Info
        </button>
        <button class="profile-tab" data-tab="posts">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
            My Posts
        </button>
        <button class="profile-tab" data-tab="saved">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
            Saved Posts
        </button>
        <button class="profile-tab" data-tab="social">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            Social & Privacy
        </button>
        <button class="profile-tab" data-tab="password">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Password
        </button>
    </div>

    {{-- Tab: Personal Info --}}
    <div class="profile-tab-panel active" id="tab-info">
        <form action="{{ route('profile.update.info') }}" method="POST" class="profile-form">
        @csrf

        {{-- preserve social tab fields --}}
        <input type="hidden" name="linkedin_url" value="{{ $user->linkedin_url }}">
        <input type="hidden" name="twitter_url" value="{{ $user->twitter_url }}">
        <input type="hidden" name="facebook_url" value="{{ $user->facebook_url }}">
        <input type="hidden" name="website_url" value="{{ $user->website_url }}">
        <input type="hidden" name="hide_email" value="{{ $user->hide_email ? '1' : '0' }}">
        <input type="hidden" name="hide_phone" value="{{ $user->hide_phone ? '1' : '0' }}">
            <div class="profile-form-section">
                <h2 class="profile-section-title">Basic Information</h2>
                <div class="profile-form-grid">

                    <div class="form-group">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="full_name"
                               value="{{ old('full_name', $user->full_name) }}"
                               class="form-input @error('full_name') is-error @enderror">
                        @error('full_name')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="{{ $user->email }}"
                               class="form-input form-input--disabled" disabled>
                        <span class="form-hint">Email cannot be changed.</span>
                    </div>

                    <div class="form-group">
                        <label>Phone Number <span class="req">*</span></label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $user->phone) }}"
                               class="form-input @error('phone') is-error @enderror">
                        @error('phone')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country"
                               value="{{ old('country', $user->country) }}"
                               class="form-input @error('country') is-error @enderror">
                        @error('country')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Current City</label>
                        <input type="text" name="current_city"
                               value="{{ old('current_city', $user->current_city) }}"
                               class="form-input">
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" value="{{ $user->department }}"
                               class="form-input form-input--disabled" disabled>
                        <span class="form-hint">Set during registration.</span>
                    </div>

                    <div class="form-group">
                        <label>Batch</label>
                        <input type="text" value="{{ $user->batch_name }}"
                               class="form-input form-input--disabled" disabled>
                    </div>

                    <div class="form-group">
                        <label>Passing Year</label>
                        <input type="text" value="{{ $user->passing_year }}"
                               class="form-input form-input--disabled" disabled>
                    </div>

                </div>
            </div>

            <div class="profile-form-section">
                <h2 class="profile-section-title">Professional Info</h2>
                <div class="profile-form-grid">

                    <div class="form-group">
                        <label>Job Title</label>
                        <input type="text" name="current_job_title"
                               value="{{ old('current_job_title', $user->current_job_title) }}"
                               placeholder="e.g. Software Engineer"
                               class="form-input">
                    </div>

                    <div class="form-group">
                        <label>Company / Organization</label>
                        <input type="text" name="current_company"
                               value="{{ old('current_company', $user->current_company) }}"
                               placeholder="e.g. Google"
                               class="form-input">
                    </div>

                    <div class="form-group form-group--full">
                        <label>Bio <span class="form-hint-inline">(max 1000 characters)</span></label>
                        <textarea name="bio" rows="5"
                                  maxlength="1000"
                                  placeholder="Tell other alumni a little about yourself..."
                                  class="form-input form-textarea @error('bio') is-error @enderror">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')<span class="form-error">{{ $message }}</span>@enderror
                        <span class="form-char-count"><span id="bioCount">{{ strlen($user->bio ?? '') }}</span>/1000</span>
                    </div>

                </div>
            </div>

            <div class="profile-form-actions">
                <button type="submit" class="btn-save">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                    Save Changes
                </button>
            </div>

        </form>
    </div>

    {{-- Tab: My Posts --}}
    <div class="profile-tab-panel" id="tab-posts">
        <div class="feed-list profile-feed-list" id="myPostsList">
            <div class="feed-skeleton" id="myPostsSkeleton">
                @for ($i = 0; $i < 2; $i++)
                    <div class="feed-skel-card card">
                        <div class="feed-skel-header">
                            <span class="feed-skel feed-skel--avatar"></span>
                            <span class="feed-skel-copy">
                                <span class="feed-skel feed-skel--line" style="width:140px"></span>
                                <span class="feed-skel feed-skel--line feed-skel--short" style="width:90px"></span>
                            </span>
                        </div>
                        <span class="feed-skel feed-skel--line" style="width:100%;height:14px;margin-top:14px"></span>
                        <span class="feed-skel feed-skel--line" style="width:80%;height:14px;margin-top:8px"></span>
                    </div>
                @endfor
            </div>
            <div class="profile-feed-empty" id="myPostsEmpty" hidden>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                <p>You haven't posted anything yet.</p>
                <span>Share an update, photo, or video on the home feed to see it here.</span>
            </div>
        </div>
        <div class="feed-end" id="myPostsEnd" hidden>
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l4-4 4 4M12 16V8"/></svg>
            <p>You're all caught up</p>
        </div>
        <div class="feed-loader" id="myPostsLoader" hidden>
            <span class="feed-spinner"></span>
        </div>
    </div>

    {{-- Tab: Saved Posts --}}
    <div class="profile-tab-panel" id="tab-saved">
        <div class="feed-list profile-feed-list" id="savedFeedList">
            <div class="feed-skeleton" id="savedFeedSkeleton">
                @for ($i = 0; $i < 2; $i++)
                    <div class="feed-skel-card card">
                        <div class="feed-skel-header">
                            <span class="feed-skel feed-skel--avatar"></span>
                            <span class="feed-skel-copy">
                                <span class="feed-skel feed-skel--line" style="width:140px"></span>
                                <span class="feed-skel feed-skel--line feed-skel--short" style="width:90px"></span>
                            </span>
                        </div>
                        <span class="feed-skel feed-skel--line" style="width:100%;height:14px;margin-top:14px"></span>
                        <span class="feed-skel feed-skel--line" style="width:80%;height:14px;margin-top:8px"></span>
                    </div>
                @endfor
            </div>
            <div class="profile-feed-empty" id="savedFeedEmpty" hidden>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                <p>No saved posts yet.</p>
                <span>Tap the Save icon on any post to keep it here for later.</span>
            </div>
        </div>
        <div class="feed-end" id="savedFeedEnd" hidden>
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l4-4 4 4M12 16V8"/></svg>
            <p>You're all caught up</p>
        </div>
        <div class="feed-loader" id="savedFeedLoader" hidden>
            <span class="feed-spinner"></span>
        </div>
    </div>

    {{-- Tab: Social & Privacy --}}
    <div class="profile-tab-panel" id="tab-social">
        <form action="{{ route('profile.update.info') }}" method="POST" class="profile-form">
            @csrf

        {{-- preserve info tab fields --}}
        <input type="hidden" name="full_name" value="{{ $user->full_name }}">
        <input type="hidden" name="phone" value="{{ $user->phone }}">
        <input type="hidden" name="bio" value="{{ $user->bio }}">
        <input type="hidden" name="current_job_title" value="{{ $user->current_job_title }}">
        <input type="hidden" name="current_company" value="{{ $user->current_company }}">
        <input type="hidden" name="current_city" value="{{ $user->current_city }}">
        <input type="hidden" name="country" value="{{ $user->country }}">

            <div class="profile-form-section">
                <h2 class="profile-section-title">Social Links</h2>
                <div class="profile-form-grid">

                    <div class="form-group">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                            LinkedIn URL
                        </label>
                        <input type="url" name="linkedin_url"
                               value="{{ old('linkedin_url', $user->linkedin_url) }}"
                               placeholder="https://linkedin.com/in/username"
                               class="form-input @error('linkedin_url') is-error @enderror">
                        @error('linkedin_url')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg>
                            Twitter / X URL
                        </label>
                        <input type="url" name="twitter_url"
                               value="{{ old('twitter_url', $user->twitter_url) }}"
                               placeholder="https://twitter.com/username"
                               class="form-input @error('twitter_url') is-error @enderror">
                        @error('twitter_url')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                            Facebook URL
                        </label>
                        <input type="url" name="facebook_url"
                               value="{{ old('facebook_url', $user->facebook_url) }}"
                               placeholder="https://facebook.com/username"
                               class="form-input @error('facebook_url') is-error @enderror">
                        @error('facebook_url')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                            Personal Website
                        </label>
                        <input type="url" name="website_url"
                               value="{{ old('website_url', $user->website_url) }}"
                               placeholder="https://yourwebsite.com"
                               class="form-input @error('website_url') is-error @enderror">
                        @error('website_url')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                </div>
            </div>

            <div class="profile-form-section">
                <h2 class="profile-section-title">Privacy Settings</h2>
                <p class="profile-section-desc">Control what other alumni can see on your profile.</p>

                <div class="privacy-toggles">

                    <div class="privacy-toggle-item">
                        <div class="privacy-toggle-info">
                            <span class="privacy-toggle-label">Hide Email Address</span>
                            <span class="privacy-toggle-desc">Other alumni won't see your email on your profile.</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="hide_email" value="1"
                                   {{ $user->hide_email ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="privacy-toggle-item">
                        <div class="privacy-toggle-info">
                            <span class="privacy-toggle-label">Hide Phone Number</span>
                            <span class="privacy-toggle-desc">Other alumni won't see your phone number on your profile.</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="hide_phone" value="1"
                                   {{ $user->hide_phone ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                </div>
            </div>

            <div class="profile-form-actions">
                <button type="submit" class="btn-save">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                    Save Changes
                </button>
            </div>

        </form>
    </div>

    {{-- Tab: Password --}}
    <div class="profile-tab-panel" id="tab-password">
        <form action="{{ route('profile.update.password') }}" method="POST" class="profile-form">
            @csrf

            <div class="profile-form-section">
                <h2 class="profile-section-title">Change Password</h2>
                <div class="profile-form-grid profile-form-grid--narrow">

                    <div class="form-group form-group--full">
                        <label>Current Password <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" name="current_password" id="currentPass"
                                   class="form-input @error('current_password') is-error @enderror">
                            <button type="button" class="toggle-pass" data-target="currentPass">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('current_password')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group form-group--full">
                        <label>New Password <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" name="password" id="newPass"
                                   class="form-input @error('password') is-error @enderror">
                            <button type="button" class="toggle-pass" data-target="newPass">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div class="password-strength" id="passStrength">
                            <div class="strength-bar">
                                <span class="strength-fill" id="strengthFill"></span>
                            </div>
                            <span class="strength-label" id="strengthLabel"></span>
                        </div>
                        @error('password')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group form-group--full">
                        <label>Confirm New Password <span class="req">*</span></label>
                        <div class="input-password-wrap">
                            <input type="password" name="password_confirmation" id="confirmPass"
                                   class="form-input">
                            <button type="button" class="toggle-pass" data-target="confirmPass">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <div class="profile-form-actions">
                <button type="submit" class="btn-save">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Update Password
                </button>
            </div>

        </form>
    </div>

</div>

{{-- Photo Crop Modal --}}
<div class="crop-modal" id="cropModal">
    <div class="crop-modal__backdrop" id="cropBackdrop"></div>
    <div class="crop-modal__box">
        <div class="crop-modal__header">
            <h3>Update Profile Photo</h3>
            <button class="crop-modal__close" id="cropClose">&times;</button>
        </div>
        <div class="crop-modal__body">
            <div class="crop-upload-zone" id="cropUploadZone">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <p>Click or drag a photo here</p>
                <span>JPG, PNG — max 5 MB</span>
                <input type="file" id="photoFileInput" accept="image/jpeg,image/png" hidden>
            </div>
            <div class="crop-canvas-wrap" id="cropCanvasWrap" style="display:none;">
                <img id="cropImage" src="" alt="Crop preview">
            </div>
        </div>
        <div class="crop-modal__footer" id="cropFooter" style="display:none;">
            <button class="btn-crop-cancel" id="cropCancel">Choose Different</button>
            <form action="{{ route('profile.update.photo') }}" method="POST" id="cropForm">
                @csrf
                <input type="hidden" name="cropped_photo" id="croppedPhotoInput">
                <button type="submit" class="btn-crop-save">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,12 4,10"/><circle cx="12" cy="12" r="10"/></svg>
                    Save Photo
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================
     REPOST MODAL (shared by My Posts and Saved Posts tabs)
============================================================ --}}
<div class="feed-modal-backdrop" id="shareModal" hidden>
    <div class="feed-modal">
        <div class="feed-modal__header">
            <h3>Repost</h3>
            <button class="feed-modal__close" id="shareModalClose" type="button">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="feed-modal__body">
            <div class="composer-row">
                <div class="avatar avatar--md">
                    @if(session('alumni_avatar'))
                        <img src="{{ asset('storage/' . session('alumni_avatar')) }}" alt="{{ session('alumni_name') }}">
                    @else
                        <span class="avatar-initials">{{ strtoupper(substr(session('alumni_name', 'A'), 0, 1)) }}</span>
                    @endif
                </div>
                <span class="composer-name">{{ session('alumni_name', 'Alumni') }}</span>
            </div>
            <textarea class="composer-textarea" id="shareCaption" placeholder="Add your thoughts..." rows="2" maxlength="2000"></textarea>
            <div class="share-preview-wrap" id="sharePreviewWrap"></div>
        </div>
        <div class="feed-modal__footer">
            <button class="btn-secondary" id="shareCancelBtn" type="button">Cancel</button>
            <button class="btn-post" id="shareConfirmBtn" type="button">Repost Now</button>
        </div>
    </div>
</div>

{{-- ============================================================
     LIGHTBOX (shared by My Posts and Saved Posts tabs)
============================================================ --}}
<div class="feed-lightbox" id="feedLightbox" hidden>
    <button class="feed-lightbox__close" id="lightboxClose" type="button">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <button class="feed-lightbox__nav feed-lightbox__nav--prev" id="lightboxPrev" type="button" hidden>
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
    </button>
    <div class="feed-lightbox__content" id="lightboxContent"></div>
    <button class="feed-lightbox__nav feed-lightbox__nav--next" id="lightboxNext" type="button" hidden>
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
    </button>
</div>

<div class="feed-toast-region" id="feedToastRegion"></div>

@endsection

@push('scripts')
{{-- Cropper.js from CDN --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
window.FeedConfig = {!! json_encode([
    'csrfToken'     => csrf_token(),
    'currentUserId' => (int) session('alumni_id'),
    'currentUserName' => session('alumni_name', 'Alumni'),
    'currentUserAvatar' => session('alumni_avatar') ? asset('storage/' . session('alumni_avatar')) : null,
    'currentUserInitials' => strtoupper(substr(session('alumni_name', 'A'), 0, 1)),
    'routes' => [
        'feed'           => route('posts.feed'),
        'store'          => route('posts.store'),
        'destroy'        => url('/posts/__ID__'),
        'like'           => url('/posts/__ID__/like'),
        'save'           => url('/posts/__ID__/save'),
        'share'          => url('/posts/__ID__/share'),
        'comments'       => url('/posts/__ID__/comments'),
        'commentDestroy' => url('/posts/__POST_ID__/comments/__ID__'),
        'commentLike'    => url('/comments/__ID__/like'),
        'postShow'       => url('/posts/__ID__'),
        'batchCounts'    => route('posts.batch-counts'),
        'saved'          => route('posts.saved'),
        'myPosts'        => route('posts.my'),
    ],
]) !!};
</script>
<script src="{{ asset('js/community/feed-core.js') }}?v=5"></script>
<script src="{{ asset('js/community/my-posts.js') }}?v=5"></script>
<script src="{{ asset('js/community/saved-feed.js') }}?v=5"></script>
<script src="{{ asset('js/community/profile.js') }}?v=5"></script>

@if(session('tab'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        activateTab('{{ session('tab') }}');
    });
</script>
@endif

@endpush