@extends('layouts.app')

@section('title', 'Forgot Password')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
@endpush

@section('content')

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<div class="auth-wrapper">

    {{-- LEFT PANEL --}}
    <div class="auth-left">

        <div class="overlay"></div>

        <div class="left-content">
            <span class="badge">
                ICCR Alumni Network
            </span>

            <h1>
                Forgot Your Password?
            </h1>

            <p>
                No worries — it happens to the best of us. Enter your registered
                email address and we'll send you a secure link to reset your
                password within minutes.
            </p>

            <div class="stats">

                <div class="stat-card">
                    <h3>Secure</h3>
                    <p>Encrypted Reset Link</p>
                </div>

                <div class="stat-card">
                    <h3>60 min</h3>
                    <p>Link Expiry Window</p>
                </div>

                <div class="stat-card">
                    <h3>One-Time</h3>
                    <p>Single Use Token</p>
                </div>

            </div>
        </div>

    </div>

    {{-- RIGHT PANEL --}}
    <div class="auth-right">

        <div class="form-container">

            <h2>Reset Password</h2>

            <p class="subtitle">
                Enter your email to receive a reset link
            </p>

            {{-- Flash messages --}}
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    <i class="fas fa-envelope-circle-check"
                       style="margin-right:6px;"></i>
                    {{ session('info') }}
                </div>
            @endif

            @if(!session('info'))

                {{-- Only show form if we haven't sent a link yet --}}
                <form action="{{ route('password.email') }}" method="POST">

                    @csrf

                    <div class="form-group">
                        <label>Email Address</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your registered email"
                            required
                            autofocus
                        >
                        @error('email')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- CAPTCHA --}}
                    <div class="form-group">
                        <label>Security Verification</label>

                        <div class="captcha-container">
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
                    </div>

                    <div class="form-group">
                        <input
                            type="text"
                            name="captcha"
                            placeholder="Enter CAPTCHA"
                            required>

                        @error('captcha')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit" class="login-btn">
                        Send Reset Link
                    </button>

                </form>

            @endif

            <div class="signup-link">
                Remembered your password?
                <a href="{{ route('login') }}">Back to Sign In</a>
            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('reloadCaptcha')
        ?.addEventListener('click', function () {
            fetch("{{ route('refresh.captcha') }}")
                .then(r => r.json())
                .then(data => {
                    document.getElementById('captcha-image').innerHTML =
                        data.captcha;
                });
        });
});
</script>
@endpush