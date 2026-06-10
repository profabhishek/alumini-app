@extends('layouts.app')

@section('title', 'Verify Your Email')

@push('styles')
<style>
/* ── Page wrapper ──────────────────────────────────────────────────── */
.otp-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0f2044 0%, #1a3a5c 50%, #0f2044 100%);
    padding: 40px 16px;
}

/* ── Card ──────────────────────────────────────────────────────────── */
.otp-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 48px 44px;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
    text-align: center;
}

/* ── Icon ──────────────────────────────────────────────────────────── */
.otp-icon {
    width: 72px;
    height: 72px;
    background: linear-gradient(135deg, #e8f4fd, #cce6f9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.otp-icon i {
    font-size: 30px;
    color: #1a3a5c;
}

/* ── Heading ───────────────────────────────────────────────────────── */
.otp-card h2 {
    font-size: 26px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 10px;
}

.otp-card .subtitle {
    font-size: 14px;
    color: #718096;
    line-height: 1.6;
    margin: 0 0 8px;
}

.otp-card .email-highlight {
    font-weight: 600;
    color: #1a3a5c;
}

/* ── Alerts ────────────────────────────────────────────────────────── */
.alert {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 13.5px;
    margin-bottom: 20px;
    text-align: left;
}

.alert-info {
    background: #ebf8ff;
    color: #2b6cb0;
    border: 1px solid #bee3f8;
}

.alert-danger {
    background: #fff5f5;
    color: #c53030;
    border: 1px solid #fed7d7;
}

.alert-success {
    background: #f0fff4;
    color: #276749;
    border: 1px solid #9ae6b4;
}

/* ── OTP inputs ────────────────────────────────────────────────────── */
.otp-inputs {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 28px 0 6px;
}

.otp-inputs input {
    width: 52px;
    height: 58px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    color: #1a1a2e;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #f8fafc;
}

.otp-inputs input:focus {
    border-color: #2d6fa4;
    box-shadow: 0 0 0 3px rgba(45, 111, 164, 0.15);
    background: #fff;
}

.otp-inputs input.is-invalid {
    border-color: #e53e3e;
}

/* Hidden real input */
#otp-hidden {
    display: none;
}

.field-error {
    font-size: 12.5px;
    color: #e53e3e;
    margin-bottom: 16px;
    display: block;
}

/* ── Timer ─────────────────────────────────────────────────────────── */
.otp-timer {
    font-size: 13px;
    color: #718096;
    margin-bottom: 24px;
}

.otp-timer span {
    font-weight: 600;
    color: #1a3a5c;
}

/* ── Submit button ─────────────────────────────────────────────────── */
.verify-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #1a3a5c, #2d6fa4);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
    letter-spacing: 0.3px;
}

.verify-btn:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}

.verify-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* ── Resend ────────────────────────────────────────────────────────── */
.resend-section {
    margin-top: 20px;
    font-size: 13.5px;
    color: #718096;
}

.resend-section form {
    display: inline;
}

.resend-btn {
    background: none;
    border: none;
    color: #2d6fa4;
    font-weight: 600;
    cursor: pointer;
    font-size: 13.5px;
    padding: 0;
    text-decoration: underline;
}

.resend-btn:disabled {
    color: #a0aec0;
    cursor: not-allowed;
    text-decoration: none;
}

/* ── Back link ─────────────────────────────────────────────────────── */
.back-link {
    display: block;
    margin-top: 18px;
    font-size: 13px;
    color: #a0aec0;
    text-decoration: none;
}

.back-link:hover {
    color: #1a3a5c;
}

/* ── Responsive ────────────────────────────────────────────────────── */
@media (max-width: 480px) {
    .otp-card {
        padding: 36px 24px;
    }
    .otp-inputs input {
        width: 44px;
        height: 50px;
        font-size: 20px;
    }
}
</style>
@endpush

@section('content')
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<div class="otp-page">
    <div class="otp-card">

        {{-- Icon --}}
        <div class="otp-icon">
            <i class="fas fa-envelope-open-text"></i>
        </div>

        <h2>Verify Your Email</h2>

        <p class="subtitle">
            We've sent a 6-digit code to<br>
            <span class="email-highlight">{{ $email }}</span>
        </p>

        {{-- Flash messages --}}
        @if (session('info'))
            <div class="alert alert-info">
                <i class="fas fa-circle-info"></i>
                {{ session('info') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- OTP Form --}}
        <form action="{{ route('otp.verify') }}" method="POST" id="otp-form">
            @csrf

            {{-- Six individual digit boxes (UX), hidden real input --}}
            <div class="otp-inputs" id="otp-boxes">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" autocomplete="off">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" autocomplete="off">
            </div>

            {{-- Hidden input that carries the assembled OTP --}}
            <input type="hidden" name="otp" id="otp-hidden">

            @error('otp')
                <span class="field-error">
                    <i class="fas fa-triangle-exclamation"></i>
                    {{ $message }}
                </span>
            @enderror

            {{-- Countdown timer --}}
            <div class="otp-timer" id="timer-wrapper">
                Code expires in <span id="timer">10:00</span>
            </div>

            <button type="submit" class="verify-btn" id="verify-btn">
                Verify & Complete Registration
            </button>

        </form>

        {{-- Resend --}}
        <div class="resend-section">
            Didn't receive the code?
            <form action="{{ route('otp.resend') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="resend-btn" id="resend-btn" disabled>
                    Resend OTP
                </button>
            </form>
        </div>

        <a href="{{ route('register') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to registration
        </a>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── OTP box auto-advance & backspace ──────────────────────────── */
    const digits  = document.querySelectorAll('.otp-digit');
    const hidden  = document.getElementById('otp-hidden');
    const form    = document.getElementById('otp-form');
    const verifyBtn = document.getElementById('verify-btn');

    digits.forEach((box, idx) => {

        box.addEventListener('input', function () {
            // Keep only digits
            this.value = this.value.replace(/\D/g, '').slice(-1);

            if (this.value && idx < digits.length - 1) {
                digits[idx + 1].focus();
            }

            syncHidden();
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                digits[idx - 1].focus();
            }
        });

        // Handle paste on any box
        box.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);

            pasted.split('').forEach((ch, i) => {
                if (digits[i]) digits[i].value = ch;
            });

            const nextEmpty = [...digits].findIndex(d => !d.value);
            (nextEmpty !== -1 ? digits[nextEmpty] : digits[5]).focus();

            syncHidden();
        });
    });

    function syncHidden() {
        hidden.value = [...digits].map(d => d.value).join('');
    }

    // Mark invalid boxes if error present
    @if($errors->has('otp'))
        digits.forEach(d => d.classList.add('is-invalid'));
    @endif

    /* ── 10-minute countdown ───────────────────────────────────────── */
    const timerEl   = document.getElementById('timer');
    const resendBtn = document.getElementById('resend-btn');
    let totalSeconds = 10 * 60;   // 10 minutes
    let resendLock   = 60;        // resend enabled after 60 s

    const interval = setInterval(() => {

        totalSeconds--;
        resendLock  = Math.max(0, resendLock - 1);

        const m = Math.floor(totalSeconds / 60);
        const s = totalSeconds % 60;
        timerEl.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;

        // Enable resend after 60 seconds
        if (resendLock === 0) {
            resendBtn.disabled = false;
        }

        // OTP expired
        if (totalSeconds <= 0) {
            clearInterval(interval);
            timerEl.textContent = '00:00';
            verifyBtn.disabled  = true;
            verifyBtn.textContent = 'OTP Expired — Please Resend';
        }

    }, 1000);

});
</script>
@endpush

@endsection