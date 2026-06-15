<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Confirmed</title>
</head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family:Segoe UI, Helvetica, Arial, sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7; padding:32px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:540px; background:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #e5e7eb;">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#1C2331; padding:28px 32px;">
                            <span style="font-size:11px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#E8640C;">
                                ICCR Alumni Community
                            </span>
                            <h1 style="margin:8px 0 0; font-size:20px; font-weight:800; color:#ffffff; letter-spacing:-0.01em;">
                                Registration Confirmed 🎉
                            </h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:28px 32px 8px;">
                            <p style="margin:0 0 12px; font-size:14.5px; line-height:1.6; color:#1C2331;">
                                Hi {{ $registration->full_name }},
                            </p>
                            <p style="margin:0 0 20px; font-size:14.5px; line-height:1.6; color:#374151;">
                                You're all set! Your registration for
                                <strong style="color:#1C2331;">{{ $event->title }}</strong>
                                has been confirmed. Here are your details:
                            </p>
                        </td>
                    </tr>

                    {{-- Event details card --}}
                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="background:#fafafa; border:1.5px solid #f0f0f0; border-radius:10px;">

                                <tr>
                                    <td style="padding:14px 18px; border-bottom:1px solid #f0f0f0;">
                                        <span style="display:block; font-size:10.5px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#9ca3af;">Event</span>
                                        <span style="display:block; font-size:14px; font-weight:600; color:#1C2331; margin-top:3px;">{{ $event->title }}</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 18px; border-bottom:1px solid #f0f0f0;">
                                        <span style="display:block; font-size:10.5px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#9ca3af;">Date &amp; Time</span>
                                        <span style="display:block; font-size:14px; font-weight:600; color:#1C2331; margin-top:3px;">
                                            {{ $event->start_date->format('D, d M Y') }}
                                            @if($event->end_date && $event->end_date->ne($event->start_date))
                                                – {{ $event->end_date->format('d M Y') }}
                                            @endif
                                            &nbsp;·&nbsp;
                                            {{ date('g:i A', strtotime($event->start_time)) }}
                                            @if($event->end_time)
                                                – {{ date('g:i A', strtotime($event->end_time)) }}
                                            @endif
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 18px; border-bottom:1px solid #f0f0f0;">
                                        <span style="display:block; font-size:10.5px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#9ca3af;">Location</span>
                                        <span style="display:block; font-size:14px; font-weight:600; color:#1C2331; margin-top:3px;">
                                            {{ $event->location ?: 'Online Event' }} ({{ $event->event_mode }})
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 18px; {{ $registration->message ? 'border-bottom:1px solid #f0f0f0;' : '' }}">
                                        <span style="display:block; font-size:10.5px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#9ca3af;">Attendees</span>
                                        <span style="display:block; font-size:14px; font-weight:600; color:#1C2331; margin-top:3px;">
                                            {{ $registration->no_of_people }} {{ $registration->no_of_people == 1 ? 'person' : 'people' }}
                                        </span>
                                    </td>
                                </tr>

                                @if($registration->message)
                                <tr>
                                    <td style="padding:14px 18px;">
                                        <span style="display:block; font-size:10.5px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#9ca3af;">Your Note</span>
                                        <span style="display:block; font-size:13.5px; color:#374151; margin-top:3px; line-height:1.5;">
                                            {{ $registration->message }}
                                        </span>
                                    </td>
                                </tr>
                                @endif

                            </table>
                        </td>
                    </tr>

                    {{-- CTA --}}
                    <tr>
                        <td style="padding:24px 32px;" align="center">
                            <a href="{{ route('events.show', $event->slug ?? $event->id) }}"
                               style="display:inline-block; background:#E8640C; color:#ffffff; text-decoration:none; font-size:13.5px; font-weight:700; padding:11px 28px; border-radius:9px;">
                                View Event Details
                            </a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:18px 32px 28px; border-top:1px solid #f0f0f0;">
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#9ca3af;">
                                This is an automated confirmation — please don't reply to this email.
                                If your plans change, you can manage your registration from your
                                ICCR Alumni Community account.
                            </p>
                        </td>
                    </tr>

                </table>

                <p style="margin:18px 0 0; font-size:11.5px; color:#9ca3af;">
                    &copy; {{ date('Y') }} Indian Council for Cultural Relations — Alumni Community
                </p>

            </td>
        </tr>
    </table>

</body>
</html>