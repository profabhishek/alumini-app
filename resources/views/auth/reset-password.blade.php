@extends('layouts.app')

@section('title', 'Reset Password')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
    <style>
        /* ── Password strength meter ── */
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            margin-top: 8px;
            background: #e2e8f0;
            overflow: hidden;
            transition: all .3s ease;
        }

        .strength-bar-fill {
            height: 100%;
            width: 0;
            border-radius: 4px;
            transition: width .4s ease, background .4s ease;
        }

        .strength-label {
            font-size: 12px;
            margin-top: 4px;
            font-weight: 600;
            transition: color .3s ease;
        }

        .strength-weak   { color: #e53e3e; }
        .strength-fair   { color: #dd6b20; }
        .strength-good   { color: #d69e2e; }
        .strength-strong { color: #38a169; }

        .requirements {
            margin-top: 10px;
            padding: 12px 14px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            list-style: none;
        }

        .requirements li {
            font-size: 12.5px;
            color: #718096;
            padding: 2px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .requirements li.met {
            color: #38a169;
        }

        .requirements li .req-icon::before { content: '○'; }
        .requirements li.met .req-icon::before { content: '✓'; }
    </style>
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
                Create New Password
            </h1>

            <p>
                Choose a strong, unique password that you don't use on any other
                site. A good password is at least 8 characters and includes
                letters, numbers, and symbols.
            </p>

            <div class="stats">
                <div class="stat-card">
                    <h3>Secure</h3>
                    <p>Hashed with bcrypt</p>
                </div>

                <div class="stat-card">
                    <h3>Private</h3>
                    <p>One-time reset link</p>
                </div>

                <div class="stat-card">
                    <h3>Safe</h3>
                    <p>Sessions invalidated</p>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT PANEL --}}
    <div class="auth-right">

        <div class="form-container">

            <h2>New Password</h2>

            <p class="subtitle">
                Enter and confirm your new password below
            </p>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">

                @csrf

                {{-- Hidden fields carrying the token + email through the form --}}
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- New password --}}
                <div class="form-group">
                    <label>New Password</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter new password"
                            autocomplete="new-password"
                            required
                        >
                        <span class="toggle-password" id="togglePassword">
                            <i class="fa-regular fa-eye" id="eyeIcon"></i>
                        </span>
                    </div>

                    {{-- Strength meter --}}
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strengthFill"></div>
                    </div>
                    <p class="strength-label" id="strengthLabel"></p>

                    {{-- Requirements checklist --}}
                    <ul class="requirements" id="reqList">
                        <li id="req-length">
                            <span class="req-icon"></span> At least 8 characters
                        </li>
                        <li id="req-upper">
                            <span class="req-icon"></span> One uppercase letter
                        </li>
                        <li id="req-number">
                            <span class="req-icon"></span> One number
                        </li>
                        <li id="req-special">
                            <span class="req-icon"></span>
                            One special character (@$!%*?&#)
                        </li>
                    </ul>

                    @error('password')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Confirm password --}}
                <div class="form-group">
                    <label>Confirm New Password</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="passwordConfirm"
                            placeholder="Re-enter new password"
                            autocomplete="new-password"
                            required
                        >
                        <span class="toggle-password" id="toggleConfirm">
                            <i class="fa-regular fa-eye" id="eyeIconConfirm"></i>
                        </span>
                    </div>

                    <small id="matchMsg" style="display:none;font-size:12px;"></small>

                    @error('password_confirmation')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="login-btn" id="submitBtn">
                    Set New Password
                </button>

            </form>

            <div class="signup-link">
                Back to
                <a href="{{ route('login') }}">Sign In</a>
            </div>

        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle password visibility ────────────────────────────────────────
    function bindToggle(btnId, inputId, iconId) {
        const btn   = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);

        if (!btn) return;

        btn.addEventListener('click', function () {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    }

    bindToggle('togglePassword', 'password', 'eyeIcon');
    bindToggle('toggleConfirm',  'passwordConfirm', 'eyeIconConfirm');

    // ── Strength meter ────────────────────────────────────────────────────
    const passwordInput  = document.getElementById('password');
    const confirmInput   = document.getElementById('passwordConfirm');
    const fill           = document.getElementById('strengthFill');
    const label          = document.getElementById('strengthLabel');
    const matchMsg       = document.getElementById('matchMsg');

    const reqs = {
        length:  document.getElementById('req-length'),
        upper:   document.getElementById('req-upper'),
        number:  document.getElementById('req-number'),
        special: document.getElementById('req-special'),
    };

    function checkReq(el, met) {
        el.classList.toggle('met', met);
    }

    function getScore(val) {
        let score = 0;
        if (val.length >= 8)            score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[@$!%*?&#]/.test(val))    score++;
        return score;
    }

    const levels = [
        { label: '',       width: '0%',   color: '#e2e8f0', cls: '' },
        { label: 'Weak',   width: '25%',  color: '#e53e3e', cls: 'strength-weak' },
        { label: 'Fair',   width: '50%',  color: '#dd6b20', cls: 'strength-fair' },
        { label: 'Good',   width: '75%',  color: '#d69e2e', cls: 'strength-good' },
        { label: 'Strong', width: '100%', color: '#38a169', cls: 'strength-strong' },
    ];

    passwordInput.addEventListener('input', function () {
        const val   = this.value;
        const score = val.length === 0 ? 0 : Math.max(1, getScore(val));
        const level = levels[score];

        fill.style.width      = level.width;
        fill.style.background = level.color;

        label.textContent  = level.label;
        label.className    = 'strength-label ' + level.cls;

        // Update requirement checklist
        checkReq(reqs.length,  val.length >= 8);
        checkReq(reqs.upper,   /[A-Z]/.test(val));
        checkReq(reqs.number,  /[0-9]/.test(val));
        checkReq(reqs.special, /[@$!%*?&#]/.test(val));

        updateMatch();
    });

    // ── Match indicator ───────────────────────────────────────────────────
    function updateMatch() {
        const p = passwordInput.value;
        const c = confirmInput.value;

        if (!c) {
            matchMsg.style.display = 'none';
            return;
        }

        matchMsg.style.display = 'block';

        if (p === c) {
            matchMsg.textContent  = '✓ Passwords match';
            matchMsg.style.color  = '#38a169';
        } else {
            matchMsg.textContent  = '✗ Passwords do not match';
            matchMsg.style.color  = '#e53e3e';
        }
    }

    confirmInput.addEventListener('input', updateMatch);
});
</script>
@endpush