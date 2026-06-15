<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0; padding:0; background:#f4f4f5; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding: 32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:14px; overflow:hidden;">
                    <tr>
                        <td style="background:#1C2331; padding: 24px 32px;">
                            <span style="color:#fff; font-size:18px; font-weight:bold;">ICCR Alumni Community</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <h2 style="margin:0 0 16px; color:#111; font-size:20px;">Confirm your unsubscribe request</h2>
                            <p style="margin:0 0 16px; color:#555; font-size:14.5px; line-height:1.7;">
                                We received a request to unsubscribe <strong>{{ $subscriber->email }}</strong> from the ICCR Alumni newsletter.
                            </p>
                            <p style="margin:0 0 24px; color:#555; font-size:14.5px; line-height:1.7;">
                                If this was you, click the button below to confirm. If you didn't request this, you can safely ignore this email — your subscription will stay active.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius:10px; background:#E8640C;">
                                        <a href="{{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}"
                                           style="display:inline-block; padding:13px 28px; color:#fff; font-weight:bold; font-size:14.5px; text-decoration:none;">
                                            Confirm Unsubscribe
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:24px 0 0; color:#999; font-size:12.5px; line-height:1.6;">
                                If the button doesn't work, copy and paste this link into your browser:<br>
                                {{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>