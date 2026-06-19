@extends('layouts.app')

@section('title', 'Alumni Login')

@push ('styles')
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
                Welcome Back
            </h1>

            <p>
                Reconnect with the global ICCR alumni community,
                explore opportunities, participate in events,
                and stay connected with fellow alumni around the world.
            </p>

            <div class="stats">

                <div class="stat-card">
                    <h3>Global</h3>
                    <p>Alumni Network</p>
                </div>

                <div class="stat-card">
                    <h3>Events</h3>
                    <p>Conferences & Meetups</p>
                </div>

                <div class="stat-card">
                    <h3>Career</h3>
                    <p>Jobs & Opportunities</p>
                </div>

            </div>

        </div>

    </div>

    {{-- RIGHT PANEL --}}
    <div class="auth-right">

        <div class="form-container">

            <h2>Sign In</h2>

            <p class="subtitle">
                Access your alumni account
            </p>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login.authenticate') }}" method="POST">

                @csrf

                <div class="form-group">
                    <label>Email Address</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        required
                    >
                    @error('email')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Password</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                    <span class="toggle-password" id="togglePassword">
                        <i class="fa-regular fa-eye" id="eyeIcon"></i>
                    </span>
                    </div>

                    @error('password')
                        <small class="error">{{ $message }}</small>
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
                        <small class="error">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <button type="submit" class="login-btn">
                    Sign In
                </button>

            </form>

            <div class="signup-link">
                Don't have an account?
                <a href="{{ route('register') }}">
                    Join Community
                </a>
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
                .then(response => response.json())
                .then(data => {
                    document.getElementById('captcha-image').innerHTML =
                        data.captcha;
                });

        });

});
</script>
@endpush

@push('scripts')
<script>
const toggleBtn = document.getElementById("togglePassword");

if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
        const password = document.getElementById("password");
        const eyeIcon = document.getElementById("eyeIcon");

        if (password.type === "password") {
            password.type = "text";

            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        } else {
            password.type = "password";

            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        }
    });
}
</script>
@endpush


