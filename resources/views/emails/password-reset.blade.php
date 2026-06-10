<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Your Password</title>
    <style>
        /* ── Reset ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1a202c;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Wrapper ── */
        .email-wrapper {
            max-width: 620px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        /* ── Header ── */
        .email-header {
            background: linear-gradient(135deg, #1a3a5c 0%, #2d6a9f 100%);
            padding: 40px 48px 36px;
            text-align: center;
        }

        .email-header .logo-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 100px;
            padding: 6px 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #c8dff4;
            margin-bottom: 20px;
        }

        .email-header h1 {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
            margin-bottom: 8px;
        }

        .email-header p {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
        }

        /* ── Lock icon circle ── */
        .icon-circle {
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            border: 2px solid rgba(255,255,255,0.3);
        }

        /* ── Body ── */
        .email-body {
            padding: 44px 48px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 16px;
        }

        .message {
            font-size: 15px;
            line-height: 1.7;
            color: #4a5568;
            margin-bottom: 32px;
        }

        /* ── CTA Button ── */
        .cta-wrapper {
            text-align: center;
            margin-bottom: 36px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #1a3a5c 0%, #2d6a9f 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            padding: 16px 40px;
            border-radius: 10px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(45, 106, 159, 0.4);
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 32px 0;
        }

        /* ── Warning box ── */
        .warning-box {
            background: #fffbeb;
            border: 1px solid #f6d860;
            border-left: 4px solid #f6c000;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 28px;
        }

        .warning-box p {
            font-size: 13.5px;
            color: #744210;
            line-height: 1.6;
        }

        .warning-box strong {
            font-weight: 700;
        }

        /* ── Fallback URL ── */
        .fallback-section {
            margin-bottom: 28px;
        }

        .fallback-section p {
            font-size: 13px;
            color: #718096;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .fallback-url {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            word-break: break-all;
            font-size: 12px;
            color: #2d6a9f;
            font-family: 'Courier New', monospace;
        }

        /* ── Security note ── */
        .security-note {
            background: #f0f7ff;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 32px;
        }

        .security-note p {
            font-size: 13px;
            color: #2c5282;
            line-height: 1.6;
        }

        .security-note ul {
            margin-top: 8px;
            padding-left: 18px;
        }

        .security-note ul li {
            font-size: 13px;
            color: #2c5282;
            margin-bottom: 4px;
        }

        /* ── Footer ── */
        .email-footer {
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            padding: 28px 48px;
            text-align: center;
        }

        .email-footer p {
            font-size: 12.5px;
            color: #a0aec0;
            line-height: 1.7;
        }

        .email-footer strong {
            color: #718096;
            font-weight: 600;
        }

        /* ── Mobile ── */
        @media (max-width: 600px) {
            .email-header,
            .email-body,
            .email-footer { padding-left: 24px; padding-right: 24px; }
            .email-header h1 { font-size: 22px; }
            .cta-button { padding: 14px 28px; font-size: 14px; }
        }
    </style>
</head>
<body>

<div class="email-wrapper">

    {{-- ── Header ── --}}
    <div class="email-header">

        <div class="logo-badge">ICCR Alumni Network</div>

        {{-- Lock icon (inline SVG, no external deps) --}}
        <div class="icon-circle">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M17 11H7C5.9 11 5 11.9 5 13V20C5 21.1 5.9 22 7 22H17C18.1 22
                         19 21.1 19 20V13C19 11.9 18.1 11 17 11Z"
                      fill="rgba(255,255,255,0.9)"/>
                <path d="M12 2C9.24 2 7 4.24 7 7V11H9V7C9 5.34 10.34 4 12 4C13.66 4
                         15 5.34 15 7V11H17V7C17 4.24 14.76 2 12 2Z"
                      fill="rgba(255,255,255,0.9)"/>
                <circle cx="12" cy="16.5" r="1.5" fill="#1a3a5c"/>
            </svg>
        </div>

        <h1>Password Reset Request</h1>
        <p>We received a request to reset your alumni account password</p>

    </div>

    {{-- ── Body ── --}}
    <div class="email-body">

        <p class="greeting">Hello, {{ $fullName }}</p>

        <p class="message">
            Someone requested a password reset for the ICCR Alumni Network account
            associated with this email address. If this was you, click the button
            below to choose a new password. The link is valid for
            <strong>{{ $expiresInMinutes }} minutes</strong>.
        </p>

        <div class="cta-wrapper">
            <a href="{{ $resetUrl }}" class="cta-button">
                Reset My Password
            </a>
        </div>

        {{-- Warning --}}
        <div class="warning-box">
            <p>
                <strong>⚠️ Didn't request this?</strong><br>
                If you didn't ask to reset your password, you can safely ignore this
                email. Your password will remain unchanged and no action is required.
                Someone may have entered your email by mistake.
            </p>
        </div>

        <hr class="divider">

        {{-- Fallback URL --}}
        <div class="fallback-section">
            <p>
                If the button above doesn't work, copy and paste this link into
                your browser:
            </p>
            <div class="fallback-url">{{ $resetUrl }}</div>
        </div>

        {{-- Security tips --}}
        <div class="security-note">
            <p><strong>🔒 Security reminders:</strong></p>
            <ul>
                <li>This link expires in {{ $expiresInMinutes }} minutes.</li>
                <li>The link can only be used once.</li>
                <li>Never share this link with anyone.</li>
                <li>ICCR staff will never ask for your password.</li>
            </ul>
        </div>

    </div>

    {{-- ── Footer ── --}}
    <div class="email-footer">
        <p>
            This email was sent to <strong>{{ $resetUrl ? parse_url($resetUrl)['host'] ?? '' : '' }}</strong><br>
            from the <strong>ICCR Alumni Network</strong> automated system.<br><br>
            If you have questions, contact our support team.<br>
            © {{ date('Y') }} ICCR Alumni Network. All rights reserved.
        </p>
    </div>

</div>

</body>
</html>