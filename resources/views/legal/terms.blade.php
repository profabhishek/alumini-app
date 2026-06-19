@extends('layouts.app')

@section('title', 'Terms & Conditions — ICCR Alumni Network')

@push('styles')
<style>
.legal-hero {
    background: linear-gradient(135deg, #0d0d14 0%, #12121e 100%);
    padding: 72px 0 52px;
    text-align: center;
    border-bottom: 1px solid rgba(244,168,37,0.12);
}
.legal-hero .tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #f4a825;
    background: rgba(244,168,37,0.1);
    border: 1px solid rgba(244,168,37,0.25);
    border-radius: 50px;
    padding: 5px 16px;
    margin-bottom: 18px;
}
.legal-hero h1 {
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 12px;
}
.legal-hero p { color: rgba(255,255,255,0.45); font-size: 14px; margin: 0; }
.legal-body {
    max-width: 820px;
    margin: 60px auto 80px;
    padding: 0 24px;
}
.legal-body h2 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 40px 0 12px;
    padding-left: 14px;
    border-left: 3px solid #f4a825;
}
.legal-body h2:first-of-type { margin-top: 0; }
.legal-body p {
    font-size: 15px;
    line-height: 1.85;
    color: #444;
    margin: 0 0 16px;
}
.legal-body ul {
    margin: 0 0 16px 20px;
    padding: 0;
}
.legal-body ul li {
    font-size: 15px;
    line-height: 1.8;
    color: #444;
    margin-bottom: 6px;
}
.legal-updated {
    display: inline-block;
    font-size: 13px;
    color: rgba(255,255,255,0.4);
    margin-top: 8px;
}
.legal-contact-box {
    background: #f9f7f2;
    border: 1px solid #e8e2d4;
    border-radius: 14px;
    padding: 28px 32px;
    margin-top: 44px;
}
.legal-contact-box p { margin: 0; }
.legal-contact-box a { color: #c8860a; font-weight: 600; }
</style>
@endpush

@section('content')

<div class="legal-hero">
    <div class="tag">Legal</div>
    <h1>Terms &amp; Conditions</h1>
    <span class="legal-updated">Last updated: June 2026</span>
</div>

<div class="legal-body">

    <p>Welcome to the ICCR Alumni Network — the official platform of the Indian Council for Cultural Relations (ICCR), Government of India. By creating an account or using any part of this platform, you agree to the terms described below. These Terms &amp; Conditions exist to keep this space safe, respectful, and genuinely useful for our global community of former ICCR scholars. Please read them before you proceed.</p>

    <h2>1. Who May Join</h2>
    <p>Membership on this platform is open to individuals who have received a scholarship from the Indian Council for Cultural Relations (ICCR) or who were enrolled in an ICCR-affiliated academic programme. All registrations are reviewed and approved by the platform's authorised administrators. We reserve the right to decline or revoke membership if the information provided cannot be verified or if the applicant has previously violated these terms.</p>

    <h2>2. Your Account</h2>
    <p>You are responsible for the information you provide during registration. Please ensure that your name, batch year, institution, and contact details are accurate. You are also responsible for keeping your password secure. Do not share your login credentials with anyone. If you suspect unauthorised access to your account, please contact us immediately.</p>
    <p>Each person may hold only one account. Creating duplicate or fraudulent profiles is not permitted and will result in immediate removal.</p>

    <h2>3. How You May Use This Platform</h2>
    <p>The ICCR Alumni Network is built for meaningful connection among former scholars. You may use it to:</p>
    <ul>
        <li>Build and maintain your alumni profile.</li>
        <li>Connect with fellow ICCR alumni from your batch or country.</li>
        <li>Post and read alumni stories, news, and announcements.</li>
        <li>Explore and share job opportunities relevant to the community.</li>
        <li>Register for and participate in events organised by or for the alumni community.</li>
    </ul>
    <p>You may not use this platform to:</p>
    <ul>
        <li>Impersonate another person or misrepresent your identity or affiliation.</li>
        <li>Post spam, unsolicited advertisements, or commercial promotions unrelated to the alumni community.</li>
        <li>Harass, threaten, or discriminate against other alumni or community members.</li>
        <li>Share false, misleading, or defamatory content.</li>
        <li>Upload malicious files or attempt to compromise the security of the platform.</li>
        <li>Scrape, harvest, or systematically collect data about other alumni without permission.</li>
    </ul>

    <h2>4. Content You Post</h2>
    <p>When you share content on this platform — such as a story, a comment, or a job listing — you retain ownership of what you created. However, by posting it here, you grant the ICCR Alumni Network a non-exclusive licence to display, share, and promote that content within the platform and on our social channels, for the purpose of serving the alumni community.</p>
    <p>You are solely responsible for anything you post. Please do not share content that is private, confidential, or that belongs to someone else without their consent.</p>

    <h2>5. Moderation and Removal</h2>
    <p>Our administrators review flagged content and may remove anything that violates these terms or that is harmful to the community. If your content is removed, we will try to inform you, but we are not obligated to do so in cases of serious violations. Repeated violations may result in suspension or permanent removal from the platform.</p>

    <h2>6. Alumni Directory</h2>
    <p>The alumni directory is a community resource. The information visible there — name, batch year, country, department, and current role — is shared by alumni for the purpose of networking within this community. Using this directory to contact alumni for unsolicited commercial purposes, spamming, or any form of misuse is strictly prohibited.</p>

    <h2>7. Third-Party Links</h2>
    <p>Our platform may contain links to external websites, including ICCR's official website, employer job listings, and other resources. We do not control those websites and are not responsible for their content or practices. Visiting them is at your own discretion.</p>

    <h2>8. Intellectual Property</h2>
    <p>The design, logo, and structure of this platform are the property of the ICCR Alumni Network. You may not copy, reproduce, or repurpose them without written permission. The content posted by alumni remains theirs, as described in Section 4.</p>

    <h2>9. Limitation of Liability</h2>
    <p>This platform is provided in good faith and on a best-effort basis. We cannot guarantee uninterrupted availability or the accuracy of all content posted by alumni. We are not liable for any loss, damage, or inconvenience arising from your use of this platform, including content posted by other users.</p>

    <h2>10. Changes to These Terms</h2>
    <p>We may update these Terms &amp; Conditions as the platform evolves. Any significant changes will be communicated to registered alumni via email or a notice on the platform. Continuing to use the platform after such changes means you accept the updated terms.</p>

    <h2>11. Governing Law</h2>
    <p>These terms are governed by the laws of India. Any disputes arising from the use of this platform will be subject to the jurisdiction of courts in New Delhi, India.</p>

    <div class="legal-contact-box">
        <p>If you have questions about these Terms &amp; Conditions or want to report a concern, please reach us via our <a href="{{ route('contact') }}">Contact page</a> or at <a href="mailto:abhishekjha@ardhas.com">abhishekjha@ardhas.com</a>.</p>
    </div>

</div>

@endsection
