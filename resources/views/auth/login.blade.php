@extends('layouts.app')

@section('title', 'Alumni Login')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
@endpush

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
    .req { color: #e53e3e; margin-left: 2px; font-weight: 700; }
    .field-error-msg { display: block; color: #e53e3e; font-size: 12px; margin-top: 5px; }
</style>

<div class="auth-wrapper">

    {{-- LEFT PANEL --}}
    <div class="auth-left">
        <div class="overlay"></div>
        <div class="left-content">
            <span class="badge">ICCR Alumni Network</span>
            <h1>Welcome Back</h1>
            <p>Reconnect with the global ICCR alumni community, explore opportunities, participate in events, and stay connected with fellow alumni around the world.</p>
            <div class="stats">
                <div class="stat-card"><h3>Global</h3><p>Alumni Network</p></div>
                <div class="stat-card"><h3>Events</h3><p>Conferences &amp; Meetups</p></div>
                <div class="stat-card"><h3>Career</h3><p>Jobs &amp; Opportunities</p></div>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="auth-right">
        <div class="form-container">

            <h2>Sign In</h2>
            <p class="subtitle">Access your alumni account</p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            <form action="{{ route('login.authenticate') }}" method="POST"
                  novalidate autocomplete="off" id="loginForm">
                @csrf

                <div class="form-group">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter your email"
                           maxlength="255"
                           autocomplete="email"
                           required>
                    @error('email')
                        <span class="field-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password"
                               placeholder="Enter your password"
                               maxlength="128"
                               autocomplete="current-password"
                               required>
                        <span class="toggle-password" id="togglePassword" role="button" aria-label="Show password" style="cursor:pointer;">
                            <i class="fa-regular fa-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                    @error('password')
                        <span class="field-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <div class="options-row">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember Me
                    </label>
                    <a href="{{ route('password.forgot') }}" class="forgot-link">Forgot Password?</a>
                </div>

                <div class="form-group">
                    <label>Security Verification <span class="req">*</span></label>
                    <div class="captcha-container">
                        <span id="captcha-image">{!! captcha_img('flat') !!}</span>
                        <button type="button" id="reloadCaptcha" class="captcha-refresh" aria-label="Refresh CAPTCHA">
                            <i class="fas fa-arrows-rotate"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <input type="text" name="captcha"
                           placeholder="Enter CAPTCHA"
                           maxlength="20"
                           autocomplete="off"
                           required>
                    @error('captcha')
                        <span class="field-error-msg">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="login-btn" id="loginBtn">Sign In</button>

            </form>

            <div class="signup-link">
                Don't have an account? <a href="{{ route('register') }}">Join Community</a>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── CAPTCHA refresh ──────────────────────────────────────────────────
    document.getElementById('reloadCaptcha')?.addEventListener('click', function () {
        fetch("{{ route('refresh.captcha') }}")
            .then(r => r.json())
            .then(d => { document.getElementById('captcha-image').innerHTML = d.captcha; });
    });

    // ── Show/hide password ───────────────────────────────────────────────
    document.getElementById('togglePassword')?.addEventListener('click', function () {
        const pw   = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            pw.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // ── Prevent double-submit ────────────────────────────────────────────
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.textContent = 'Signing in…';
    });

});
</script>
@endpush
@endsection
