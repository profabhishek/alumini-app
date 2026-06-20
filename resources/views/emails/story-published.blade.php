<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Alumni Story</title>
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
            .cover-img { max-height:160px !important; }
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
                <div style="font-size:44px;line-height:1;margin-bottom:14px;">📖</div>
                <h1 style="color:#ffffff;margin:0 0 6px;font-size:24px;font-weight:800;letter-spacing:-0.3px;line-height:1.2;">New Alumni Story</h1>
                <p style="color:rgba(255,255,255,0.75);margin:0;font-size:13.5px;letter-spacing:0.5px;text-transform:uppercase;">ICCR Alumni Community</p>
            </td>
        </tr>

        <!-- ── BODY ── -->
        <tr>
            <td class="email-content" style="padding:36px 48px 20px;">

                <p style="color:#1f2937;font-size:16px;line-height:1.7;margin:0 0 8px;">Hi <strong>{{ $recipient->full_name }}</strong>,</p>
                <p style="color:#4b5563;font-size:15px;line-height:1.7;margin:0 0 28px;">A fellow alumnus just shared their story — take a moment to read and be inspired!</p>

                <!-- Story card -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="background:#f8faff;border:1.5px solid #dde5f5;border-radius:12px;overflow:hidden;">

                    @if($story->cover_image)
                    <!-- Cover image row -->
                    <tr>
                        <td style="padding:0;line-height:0;font-size:0;">
                            <img src="{{ asset('storage/' . $story->cover_image) }}"
                                 alt="{{ $story->title }}"
                                 class="cover-img"
                                 width="576"
                                 style="width:100%;max-height:200px;object-fit:cover;display:block;border-radius:10px 10px 0 0;">
                        </td>
                    </tr>
                    @endif

                    <!-- Story content row -->
                    <tr>
                        <td style="padding:24px 28px 26px;">

                            <!-- Category badge -->
                            @if($story->category)
                            <p style="margin:0 0 12px;">
                                <span style="display:inline-block;background:#e0edff;color:#1e4d96;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;letter-spacing:0.4px;text-transform:uppercase;">
                                    {{ $story->category }}
                                </span>
                            </p>
                            @endif

                            <!-- Story title -->
                            <p style="margin:0 0 8px;font-size:18px;font-weight:800;color:#1e3a5f;line-height:1.35;">{{ $story->title }}</p>

                            <!-- Author -->
                            @if($story->creator)
                            <p style="margin:0 0 16px;font-size:13px;color:#6b7280;">
                                <span style="color:#e8640c;font-weight:700;">✍</span>&nbsp;
                                By <strong style="color:#374151;">{{ $story->creator->full_name }}</strong>
                            </p>
                            @endif

                            <!-- Excerpt -->
                            @if($story->excerpt || $story->content)
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="border-top:1px solid #e2e8f0;padding-top:16px;">
                                        <p style="margin:0;font-size:14px;color:#4b5563;line-height:1.7;">
                                            @if($story->excerpt)
                                                {{ \Illuminate\Support\Str::limit($story->excerpt, 240) }}
                                            @else
                                                {{ \Illuminate\Support\Str::limit(strip_tags($story->content), 240) }}
                                            @endif
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
                            <a href="{{ route('stories.show', $story->slug) }}"
                               style="display:inline-block;background:linear-gradient(135deg,#e8640c,#f0832d);color:#ffffff;text-decoration:none;padding:15px 40px;border-radius:10px;font-size:15px;font-weight:700;letter-spacing:0.3px;box-shadow:0 4px 14px rgba(232,100,12,0.35);">
                                Read Full Story &rarr;
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
                    You're receiving this because you have <strong style="color:#6b7280;">Alumni Stories</strong> enabled in your notification settings.<br>
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
