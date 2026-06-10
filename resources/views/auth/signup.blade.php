@extends('layouts.app')

@section('title', 'Join Community')

@push ('styles')
    <link rel="stylesheet" href="{{ asset('css/signup.css') }}" />
@endpush


@section('content')
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<section class="signup-page">
<div class="signup-container">

    {{-- Left Content --}}
    <div class="signup-showcase">

        <div class="showcase-badge">
            ICCR Alumni Network
        </div>

        <h1>
            Connect with Alumni Across the Globe
        </h1>

        <p>
            Join a professional network of ICCR alumni, discover opportunities,
            participate in events, and build meaningful global connections.
        </p>

        <div class="stats-grid">

            <div class="stat-card">
                <h3>120+</h3>
                <span>Countries</span>
            </div>

            <div class="stat-card">
                <h3>25K+</h3>
                <span>Alumni</span>
            </div>

            <div class="stat-card">
                <h3>500+</h3>
                <span>Events</span>
            </div>

            <div class="stat-card">
                <h3>1000+</h3>
                <span>Success Stories</span>
            </div>

        </div>

    </div>

    {{-- Right Form --}}
    <div class="signup-card">

        <div class="form-header">

            <h2>Join Community</h2>

            <p>
                Create your alumni account and become part of the ICCR network.
            </p>

        </div>

        @if ($errors->any())

            <div class="alert alert-danger">

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif
        <form action="{{ route('register.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input
                        type="text"
                        name="full_name"
                        value="{{ old('full_name') }}"
                        placeholder="Enter full name">
                </div>

                <div class="form-group">
                    <label>Batch Name *</label>
                    <select name="batch_name">
                        <option value="">Select Batch</option>

                        <option value="2018"
                            {{ old('batch_name') == '2018' ? 'selected' : '' }}>
                            2018
                        </option>

                        <option value="2022"
                            {{ old('batch_name') == '2022' ? 'selected' : '' }}>
                            2022
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Phone Number *</label>
                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="Enter phone number">
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter email">
                </div>

                <div class="form-group">
                    <label>Department *</label>

                    <select name="department">
                        <option value="">Select Department</option>

                        <option value="Fire Department"
                            {{ old('department') == 'Fire Department' ? 'selected' : '' }}>
                            Fire Department
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Passing Year *</label>

                    <select name="passing_year">
                        <option value="">Select Passing Year</option>

                        @for($year = date('Y'); $year >= 1980; $year--)
                            <option
                                value="{{ $year }}"
                                {{ old('passing_year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="form-group">
                    <label>ID / Roll Number *</label>

                    <input
                        type="text"
                        name="roll_number"
                        value="{{ old('roll_number') }}"
                        placeholder="Your ID/Roll Number">
                </div>

                <div class="form-group">
                    <label>Attachment (PDF) *</label>

                    <label class="custom-file">

                        <input
                            type="file"
                            name="attachment"
                            accept=".pdf">

                        <span class="file-btn">
                            Upload PDF
                        </span>

                        <span class="file-name">
                            No file selected
                        </span>

                    </label>
                </div>

                <div class="form-group">
                    <label>Birth Date *</label>

                    <input
                        type="date"
                        name="birth_date"
                        value="{{ old('birth_date') }}">
                </div>

                <div class="form-group">
                    <label>Gender *</label>

                    <select name="gender">
                        <option value="">Select Gender</option>

                        <option value="Male"
                            {{ old('gender') == 'Male' ? 'selected' : '' }}>
                            Male
                        </option>

                        <option value="Female"
                            {{ old('gender') == 'Female' ? 'selected' : '' }}>
                            Female
                        </option>

                        <option value="Other"
                            {{ old('gender') == 'Other' ? 'selected' : '' }}>
                            Other
                        </option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Add Institute *</label>

                    <input
                        type="text"
                        name="institute"
                        value="{{ old('institute') }}"
                        placeholder="Enter institute name">
                </div>

            </div>


            <div class="form-grid password-grid">

                <div class="form-group">
                    <label>Password *</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Create password">
                </div>

                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Confirm password">
                </div>

            </div>

            <div class="captcha-section">

                <label>Verification *</label>

                <div class="captcha-wrapper">

                    <span id="captcha-image">
                        {!! captcha_img('flat') !!}
                    </span>

                    <button
                        type="button"
                        id="reloadCaptcha"
                        class="captcha-refresh"
                        aria-label="Refresh CAPTCHA">

                        <i class="fas fa-arrows-rotate"></i>

                    </button>

                </div>

                <input
                    type="text"
                    name="captcha"
                    placeholder="Enter CAPTCHA"
                    required>

                @error('captcha')
                    <small class="error">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            <div class="terms-check">
                <input
                    type="checkbox"
                    id="terms"
                    name="terms"
                    required
                >
                <label for="terms">
                    I agree to the Terms & Conditions and Community Guidelines.
                </label>

            </div>

            <button
                type="submit"
                class="signup-btn">

                Join Community

            </button>

        </form>


        <div class="login-link">

            Already a member?

            <a href="{{ route('login') }}">
                Sign In
            </a>

        </div>

    </div>

</div>

</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('reloadCaptcha')
        ?.addEventListener('click', function () {

            fetch("{{ route('refresh.captcha') }}")
                .then(response => response.json())
                .then(data => {

                    document.getElementById('captcha-image')
                        .innerHTML = data.captcha;

                });

        });

});
</script>
@endpush
@endsection
