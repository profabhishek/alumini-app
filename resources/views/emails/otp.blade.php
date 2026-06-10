<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        .wrapper {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #1a3a5c, #2d6fa4);
            padding: 32px 40px;
            text-align: center;
        }
        .header img {
            height: 48px;
            margin-bottom: 12px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .header p {
            color: rgba(255,255,255,0.75);
            font-size: 13px;
            margin: 6px 0 0;
        }
        .body {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 16px;
            color: #1a1a2e;
            margin: 0 0 14px;
            font-weight: 500;
        }
        .message {
            font-size: 14px;
            color: #4a5568;
            line-height: 1.7;
            margin: 0 0 28px;
        }
        .otp-box {
            background: #f0f7ff;
            border: 2px dashed #2d6fa4;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            margin-bottom: 28px;
        }
        .otp-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 10px;
        }
        .otp-code {
            font-size: 40px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #1a3a5c;
            font-family: 'Courier New', monospace;
        }
        .expiry-note {
            font-size: 12px;
            color: #e53e3e;
            margin-top: 10px;
            font-weight: 500;
        }
        .warning {
            background: #fffbeb;
            border-left: 4px solid #f6ad55;
            border-radius: 4px;
            padding: 12px 16px;
            font-size: 13px;
            color: #744210;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            line-height: 1.6;
        }
        .footer a {
            color: #2d6fa4;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <div class="header">
            <h1>ICCR Alumni Community Portal</h1>
            <p>Indian Council for Cultural Relations</p>
        </div>

        <div class="body">

            <p class="greeting">Hello, {{ $fullName }}!</p>

            <p class="message">
                Thank you for registering with the ICCR Alumni Community Portal.
                To complete your email verification, please use the OTP code below.
            </p>

            <div class="otp-box">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="expiry-note">⏱ This code expires in 10 minutes</div>
            </div>

            <div class="warning">
                <strong>Security Notice:</strong> Never share this code with anyone.
                ICCR staff will never ask you for this code.
                If you did not attempt to register, please ignore this email.
            </div>

            <p class="message" style="margin:0;">
                Once verified, your account will be submitted for administrator approval.
                You will receive a separate email once your account is activated.
            </p>

        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Indian Council for Cultural Relations (ICCR).
            All rights reserved.<br>
            This is an automated message — please do not reply to this email.
        </div>

    </div>
</body>
</html>