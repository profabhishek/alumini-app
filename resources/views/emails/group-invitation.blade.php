<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Invitation</title>
    <style>
        body { margin:0; padding:0; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f4f6f9; }
        .wrapper { max-width:560px; margin:40px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.08); }
        .header { background:linear-gradient(135deg,#1a3a5c,#2d6fa4); padding:32px 40px; text-align:center; }
        .header h1 { color:#fff; margin:0; font-size:22px; }
        .body { padding:32px 40px; }
        .body p { color:#374151; font-size:15px; line-height:1.6; margin:0 0 16px; }
        .btn { display:inline-block; background:#e8640c; color:#fff; text-decoration:none; padding:12px 28px; border-radius:8px; font-weight:600; font-size:15px; margin:8px 0; }
        .footer { padding:20px 40px; text-align:center; font-size:12px; color:#9ca3af; border-top:1px solid #f3f4f6; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header"><h1>Group Invitation</h1></div>
    <div class="body">
        <p>Hi {{ $invitation->alumni->full_name }},</p>
        <p><strong>{{ $invitation->invitedBy->full_name }}</strong> has invited you to join the community group <strong>"{{ $invitation->group->name }}"</strong>.</p>
        @if($invitation->group->description)
            <p>{{ Str::limit($invitation->group->description, 200) }}</p>
        @endif
        <p>Click the button below to view your invitation and accept or decline:</p>
        <a href="{{ url('/groups/invitations') }}" class="btn">View Invitation</a>
        <p style="font-size:13px;color:#6b7280;">This invitation will remain pending until you respond.</p>
    </div>
    <div class="footer">ICCR Alumni Portal &mdash; You received this because someone invited you to a community group.</div>
</div>
</body>
</html>
