<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status Update</title>
    <style>
        body { margin:0; padding:0; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f4f6f9; }
        .wrapper { max-width:580px; margin:40px auto; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.09); }

        /* ── Header ── */
        .header { padding:32px 40px 28px; text-align:center; }
        .header-submitted   { background:linear-gradient(135deg,#1a3a5c,#2d6fa4); }
        .header-shortlisted { background:linear-gradient(135deg,#92400e,#d97706); }
        .header-hired       { background:linear-gradient(135deg,#065f46,#10b981); }
        .header-rejected    { background:linear-gradient(135deg,#374151,#6b7280); }

        .header-icon { font-size:40px; margin-bottom:12px; display:block; }
        .header h1 { color:#fff; font-size:22px; font-weight:700; margin:0 0 6px; }
        .header p  { color:rgba(255,255,255,.75); font-size:13px; margin:0; }

        /* ── Body ── */
        .body { padding:32px 40px; }
        .greeting { font-size:16px; font-weight:600; color:#1a202c; margin:0 0 12px; }
        .intro    { font-size:14px; color:#4a5568; line-height:1.7; margin:0 0 24px; }

        /* ── Job info card ── */
        .job-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; margin-bottom:24px; }
        .job-card-title { font-size:16px; font-weight:700; color:#1a202c; margin:0 0 4px; }
        .job-card-company { font-size:13px; color:#718096; margin:0 0 12px; }
        .job-meta { display:flex; flex-wrap:wrap; gap:8px; }
        .job-tag { display:inline-block; background:#e2e8f0; color:#475569; font-size:11.5px; font-weight:600; padding:3px 10px; border-radius:20px; }

        /* ── Status box ── */
        .status-box { border-radius:10px; padding:18px 20px; margin-bottom:24px; }
        .status-box-shortlisted { background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.3); }
        .status-box-hired       { background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.3); }
        .status-box-rejected    { background:rgba(100,116,139,.08); border:1px solid rgba(100,116,139,.25); }

        .status-label { font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:700; margin-bottom:6px; }
        .status-label-shortlisted { color:#b45309; }
        .status-label-hired       { color:#065f46; }
        .status-label-rejected    { color:#475569; }

        .status-value { font-size:15px; font-weight:600; color:#1a202c; }

        /* ── Reason ── */
        .reason-box { background:#fff5f5; border:1px solid #fed7d7; border-radius:10px; padding:16px 20px; margin-bottom:24px; }
        .reason-label { font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:700; color:#c53030; margin-bottom:6px; }
        .reason-text  { font-size:14px; color:#4a5568; line-height:1.65; margin:0; }

        /* ── Poster info ── */
        .poster-box { background:#f0f7ff; border:1px solid #bee3f8; border-radius:10px; padding:16px 20px; margin-bottom:24px; }
        .poster-label { font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:700; color:#2b6cb0; margin-bottom:8px; }
        .poster-name  { font-size:14px; font-weight:600; color:#1a202c; margin:0 0 2px; }
        .poster-email { font-size:13px; color:#4a5568; margin:0; }
        .poster-email a { color:#2d6fa4; text-decoration:none; }

        /* ── Next steps ── */
        .steps { margin-bottom:24px; }
        .steps-title { font-size:13px; font-weight:700; color:#1a202c; text-transform:uppercase; letter-spacing:.6px; margin-bottom:12px; }
        .step { display:flex; gap:12px; margin-bottom:10px; align-items:flex-start; }
        .step-num { width:22px; height:22px; border-radius:50%; background:#1a3a5c; color:#fff; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px; }
        .step-text { font-size:13.5px; color:#4a5568; line-height:1.55; }

        /* ── Warning ── */
        .warning { background:#fffbeb; border-left:4px solid #f6ad55; border-radius:4px; padding:12px 16px; font-size:13px; color:#744210; line-height:1.6; margin-bottom:24px; }

        /* ── Footer ── */
        .footer { background:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 40px; text-align:center; font-size:12px; color:#a0aec0; line-height:1.7; }
        .footer a { color:#2d6fa4; text-decoration:none; }

        @media (max-width:480px) {
            .body, .header { padding:24px 20px; }
            .footer { padding:16px 20px; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- ── Header ── --}}
    <div class="header header-{{ $application->status }}">
        <span class="header-icon">
            @if($application->status === 'shortlisted') ⭐
            @elseif($application->status === 'hired') 🎉
            @elseif($application->status === 'rejected') 📋
            @else 📬
            @endif
        </span>
        <h1>
            @if($application->status === 'shortlisted') You've Been Shortlisted!
            @elseif($application->status === 'hired') Congratulations — You're Hired!
            @elseif($application->status === 'rejected') Application Update
            @else Application Status Update
            @endif
        </h1>
        <p>ICCR Alumni Community Portal</p>
    </div>

    {{-- ── Body ── --}}
    <div class="body">

        <p class="greeting">Dear {{ $application->full_name }},</p>

        <p class="intro">
            @if($application->status === 'shortlisted')
                We're pleased to inform you that your application has been reviewed and you've been <strong>shortlisted</strong> for the position below. The job poster will be in touch with you soon regarding the next steps.
            @elseif($application->status === 'hired')
                Wonderful news! After careful consideration, you have been <strong>selected for this position</strong>. Please reach out to the job poster using the contact details below to proceed with onboarding.
            @elseif($application->status === 'rejected')
                Thank you for applying. After careful review of all applications, we regret to inform you that your application was <strong>not selected</strong> for this role. We encourage you to keep exploring other opportunities on the ICCR Alumni Portal.
            @else
                Your application status has been updated. Please see the details below.
            @endif
        </p>

        {{-- Job details card --}}
        <div class="job-card">
            <div class="job-card-title">{{ $application->job->title }}</div>
            <div class="job-card-company">{{ $application->job->company_name }}</div>
            <div class="job-meta">
                <span class="job-tag">{{ $application->job->job_type }}</span>
                <span class="job-tag">{{ $application->job->work_mode }}</span>
                @if($application->job->location)
                    <span class="job-tag">📍 {{ $application->job->location }}</span>
                @endif
                <span class="job-tag">Applied {{ $application->created_at->format('d M Y') }}</span>
            </div>
        </div>

        {{-- Status --}}
        <div class="status-box status-box-{{ $application->status }}">
            <div class="status-label status-label-{{ $application->status }}">Current Status</div>
            <div class="status-value">
                @if($application->status === 'shortlisted') ⭐ Shortlisted
                @elseif($application->status === 'hired') ✅ Hired
                @elseif($application->status === 'rejected') ✗ Not Selected
                @else {{ ucfirst($application->status) }}
                @endif
            </div>
        </div>

        {{-- Rejection reason (if any) --}}
        @if($application->status === 'rejected' && $application->rejection_reason)
            <div class="reason-box">
                <div class="reason-label">Feedback from the Job Poster</div>
                <p class="reason-text">{{ $application->rejection_reason }}</p>
            </div>
        @endif

        {{-- Job poster contact --}}
        <div class="poster-box">
            <div class="poster-label">Posted by</div>
            <div class="poster-name">{{ $posterName }}</div>
            <div class="poster-email">
                <a href="mailto:{{ $posterEmail }}">{{ $posterEmail }}</a>
            </div>
        </div>

        {{-- Next steps --}}
        @if($application->status === 'shortlisted')
            <div class="steps">
                <div class="steps-title">What happens next</div>
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-text">The job poster will contact you at <strong>{{ $application->email }}</strong> with further details.</div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-text">You may also reach out directly to the poster using the contact above.</div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-text">Keep an eye on your <strong>My Applications</strong> page on the portal for any further status changes.</div>
                </div>
            </div>
        @elseif($application->status === 'hired')
            <div class="steps">
                <div class="steps-title">Next steps</div>
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-text">Reach out to the job poster at <strong>{{ $posterEmail }}</strong> to confirm and begin onboarding.</div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-text">Prepare any documents or information the employer may need from you.</div>
                </div>
            </div>
        @elseif($application->status === 'rejected')
            <div class="warning">
                Don't be discouraged — new opportunities are posted regularly on the ICCR Alumni Portal. Browse the <strong>Jobs</strong> section to find your next role.
            </div>
        @endif

    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        &copy; {{ date('Y') }} Indian Council for Cultural Relations (ICCR) Alumni Portal.<br>
        This is an automated notification. Please do not reply directly to this email.<br>
        To manage your applications, visit the <a href="{{ url('/my-applications') }}">Alumni Portal</a>.
    </div>

</div>
</body>
</html>