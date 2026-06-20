<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Event Announcement</title>
    <!--[if mso]>
    <noscript>
        <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    </noscript>
    <![endif]-->
    <style>
        body, table, td, p, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
        table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
        img { -ms-interpolation-mode:bicubic; border:0; outline:none; text-decoration:none; }
        body { margin:0 !important; padding:0 !important; background-color:#f0f4f8; }
        @media only screen and (max-width:600px) {
            .email-wrapper { width:100% !important; }
            .email-content { padding:24px 20px !important; }
            .header-pad { padding:28px 20px !important; }
            .meta-label { width:90px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">

<!-- Outer wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;">
<tr>
<td align="center" style="padding:40px 16px;">

    <!-- Email card -->
    <table class="email-wrapper" width="580" cellpadding="0" cellspacing="0" border="0"
           style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.10);">

        <!-- ── HEADER ── -->
        <tr>
            <td class="header-pad" style="background:linear-gradient(135deg,#1e3a5f 0%,#2563a8 100%);padding:40px 48px;text-align:center;">
                <div style="font-size:44px;line-height:1;margin-bottom:14px;">📅</div>
                <h1 style="color:#ffffff;margin:0 0 6px;font-size:24px;font-weight:800;letter-spacing:-0.3px;line-height:1.2;">New Event Announced</h1>
                <p style="color:rgba(255,255,255,0.75);margin:0;font-size:13.5px;letter-spacing:0.5px;text-transform:uppercase;">ICCR Alumni Community</p>
            </td>
        </tr>

        <!-- ── BODY ── -->
        <tr>
            <td class="email-content" style="padding:36px 48px 20px;">

                <p style="color:#1f2937;font-size:16px;line-height:1.7;margin:0 0 8px;">Hi <strong>{{ $recipient->full_name }}</strong>,</p>
                <p style="color:#4b5563;font-size:15px;line-height:1.7;margin:0 0 28px;">A new event has been published on the alumni portal — we'd love to see you there!</p>

                <!-- Event card -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="background:#f8faff;border:1.5px solid #dde5f5;border-radius:12px;overflow:hidden;">
                    <tr>
                        <!-- Accent bar -->
                        <td width="5" style="background:linear-gradient(180deg,#2563a8,#e8640c);border-radius:12px 0 0 12px;">&nbsp;</td>
                        <td style="padding:22px 24px 24px;">

                            <!-- Title -->
                            <p style="margin:0 0 16px;font-size:18px;font-weight:800;color:#1e3a5f;line-height:1.3;">{{ $event->title }}</p>

                            <!-- Meta rows using nested table -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">

                                @if($event->start_date)
                                <tr>
                                    <td class="meta-label" width="100" valign="top"
                                        style="padding:5px 0;font-size:12.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">
                                        📆&nbsp; Date
                                    </td>
                                    <td style="padding:5px 0;font-size:14px;color:#1f2937;line-height:1.5;">
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('D, M j, Y') }}
                                        @if($event->end_date && $event->end_date !== $event->start_date)
                                            &nbsp;&rarr;&nbsp;{{ \Carbon\Carbon::parse($event->end_date)->format('D, M j, Y') }}
                                        @endif
                                    </td>
                                </tr>
                                @endif

                                @if($event->location)
                                <tr>
                                    <td class="meta-label" width="100" valign="top"
                                        style="padding:5px 0;font-size:12.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">
                                        📍&nbsp; Location
                                    </td>
                                    <td style="padding:5px 0;font-size:14px;color:#1f2937;">{{ $event->location }}</td>
                                </tr>
                                @endif

                                @if($event->event_mode)
                                <tr>
                                    <td class="meta-label" width="100" valign="top"
                                        style="padding:5px 0;font-size:12.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">
                                        🖥&nbsp; Mode
                                    </td>
                                    <td style="padding:5px 0;">
                                        <span style="display:inline-block;background:#e0edff;color:#1e4d96;border-radius:20px;padding:3px 12px;font-size:12.5px;font-weight:700;">
                                            {{ $event->event_mode }}
                                        </span>
                                    </td>
                                </tr>
                                @endif

                                @if($event->category)
                                <tr>
                                    <td class="meta-label" width="100" valign="top"
                                        style="padding:5px 0;font-size:12.5px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">
                                        🏷&nbsp; Category
                                    </td>
                                    <td style="padding:5px 0;">
                                        <span style="display:inline-block;background:#fff3eb;color:#c0440a;border-radius:20px;padding:3px 12px;font-size:12.5px;font-weight:700;">
                                            {{ $event->category }}
                                        </span>
                                    </td>
                                </tr>
                                @endif

                            </table>

                            @if($event->description)
                            <!-- Excerpt -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-top:16px;border-top:1px solid #e2e8f0;margin-top:16px;">
                                        <p style="margin:0;font-size:13.5px;color:#6b7280;line-height:1.65;">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($event->description), 220) }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                        </td>
                    </tr>
                </table>

                <!-- CTA -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:32px;">
                    <tr>
                        <td align="center">
                            <a href="{{ route('events.show', $event->slug) }}"
                               style="display:inline-block;background:linear-gradient(135deg,#e8640c,#f0832d);color:#ffffff;text-decoration:none;padding:15px 40px;border-radius:10px;font-size:15px;font-weight:700;letter-spacing:0.3px;box-shadow:0 4px 14px rgba(232,100,12,0.35);">
                                View Event Details &rarr;
                            </a>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        <!-- ── DIVIDER ── -->
        <tr>
            <td style="padding:0 48px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr><td style="border-top:1px solid #f1f5f9;padding-top:0;">&nbsp;</td></tr>
                </table>
            </td>
        </tr>

        <!-- ── FOOTER ── -->
        <tr>
            <td style="padding:20px 48px 36px;text-align:center;">
                <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.7;">
                    You're receiving this because you have <strong style="color:#6b7280;">Event Announcements</strong> enabled in your notification settings.<br>
                    <a href="{{ url('/settings') }}" style="color:#2563a8;text-decoration:none;font-weight:600;">Manage notification preferences</a>
                    &nbsp;&bull;&nbsp;
                    <span style="color:#c4cad5;">ICCR Alumni Portal</span>
                </p>
            </td>
        </tr>

    </table>
    <!-- /Email card -->

</td>
</tr>
</table>
<!-- /Outer wrapper -->

</body>
</html>
