@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', $existing && $existing->status === 'approved' ? 'Edit Mentor Profile' : 'Become a Mentor')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/mentors.css') }}">
@endpush

@section('content')
<div class="mentor-page">
    <div class="mentor-page-header">
        <div>
            <h1 class="mentor-page-title">
                {{ $existing && $existing->status === 'approved' ? 'Edit Mentor Profile' : 'Become a Mentor' }}
            </h1>
            <p class="mentor-page-subtitle">Share your expertise with fellow ICCR alumni around the world.</p>
        </div>
        <a href="{{ route('mentors.index') }}" class="mtr-btn mtr-btn--outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15,18 9,12 15,6"/></svg>
            Browse Mentors
        </a>
    </div>

    {{-- Status banner if pending/rejected --}}
    @if($existing && $existing->status === 'pending')
        <div class="admin-alert admin-alert--info" style="margin-bottom:20px;">
            <strong>Application Under Review</strong> — Your mentor application was submitted on {{ $existing->applied_at->format('d M Y') }}. An admin will review it shortly. You cannot edit it while it's pending.
        </div>
    @elseif($existing && $existing->status === 'rejected')
        <div class="admin-alert admin-alert--error" style="margin-bottom:20px;">
            <strong>Application Rejected</strong>
            @if($existing->rejection_reason)
                — {{ $existing->rejection_reason }}
            @endif
            You may reapply by submitting the form below.
        </div>
    @endif

    @foreach(['success','error','info'] as $k)
        @if(session($k))
            <div class="admin-alert admin-alert--{{ $k }}" style="margin-bottom:16px;">{{ session($k) }}</div>
        @endif
    @endforeach

    @if(!($existing && $existing->status === 'pending'))
    <div class="mentor-apply-wrap">
        <div class="mentor-apply-card">

            {{-- Info box --}}
            <div style="background:#f7fafc;border-radius:10px;padding:14px 16px;margin-bottom:24px;font-size:13px;color:#4a5568;line-height:1.6;">
                <strong style="color:#1c2331;">How it works:</strong>
                Submit your application → Admin reviews & approves → Your profile goes live → Mentees can connect with you.
            </div>

            <form method="POST" action="{{ route('mentors.apply.store') }}">
                @csrf

                <div class="form-group">
                    <label for="expertise">Your Expertise <span style="color:#e74c3c">*</span></label>
                    <input type="text" id="expertise" name="expertise" maxlength="150"
                           placeholder="e.g. Kathak Dancer & Cultural Ambassador"
                           value="{{ old('expertise', $existing->expertise ?? '') }}" required>
                    <small style="color:#a0aec0;font-size:11.5px;margin-top:4px;display:block;">A short tagline that appears on your mentor card.</small>
                    @error('expertise') <small style="color:#e53e3e">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="bio">About You <span style="color:#e74c3c">*</span></label>
                    <textarea id="bio" name="bio" maxlength="1000" rows="5"
                              placeholder="Tell potential mentees about your background, experience, and what you can help them with…" required>{{ old('bio', $existing->bio ?? '') }}</textarea>
                    <small style="color:#a0aec0;font-size:11.5px;margin-top:4px;display:block;">Minimum 50 characters.</small>
                    @error('bio') <small style="color:#e53e3e">{{ $message }}</small> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group">
                        <label for="experience_years">Years of Experience <span style="color:#e74c3c">*</span></label>
                        <input type="number" id="experience_years" name="experience_years" min="0" max="50"
                               value="{{ old('experience_years', $existing->experience_years ?? 0) }}" required>
                        @error('experience_years') <small style="color:#e53e3e">{{ $message }}</small> @enderror
                    </div>
                    <div class="form-group">
                        <label for="max_mentees">Max Mentees <span style="color:#e74c3c">*</span></label>
                        <input type="number" id="max_mentees" name="max_mentees" min="1" max="20"
                               value="{{ old('max_mentees', $existing->max_mentees ?? 5) }}" required>
                        <small style="color:#a0aec0;font-size:11.5px;margin-top:4px;display:block;">How many mentees can you take at once?</small>
                        @error('max_mentees') <small style="color:#e53e3e">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="availability">Availability <span style="color:#e74c3c">*</span></label>
                    <input type="text" id="availability" name="availability" maxlength="100"
                           placeholder="e.g. Weekends, Tuesday evenings, Flexible"
                           value="{{ old('availability', $existing->availability ?? '') }}" required>
                    @error('availability') <small style="color:#e53e3e">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label>Areas of Expertise <span style="color:#e74c3c">*</span> <small style="font-weight:400;color:#a0aec0">(choose 1–5)</small></label>
                    <div class="mentor-cat-checkboxes">
                        @foreach($categories as $cat)
                        @php
                            $selectedCats = old('categories', $existing ? $existing->categories->pluck('id')->toArray() : []);
                        @endphp
                        <label class="mentor-cat-checkbox">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   {{ in_array($cat->id, $selectedCats) ? 'checked' : '' }}>
                            <span class="cat-dot" style="background:{{ $cat->color }}"></span>
                            {{ $cat->name }}
                        </label>
                        @endforeach
                    </div>
                    @error('categories') <small style="color:#e53e3e;margin-top:6px;display:block;">{{ $message }}</small> @enderror
                </div>

                <div style="margin-top:24px;display:flex;gap:10px;align-items:center;">
                    <button type="submit" class="mtr-btn mtr-btn--primary" style="padding:12px 28px;font-size:14px;">
                        {{ $existing && $existing->status === 'approved' ? 'Update Profile' : 'Submit Application' }}
                    </button>
                    <a href="{{ route('mentors.index') }}" class="mtr-btn mtr-btn--outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
