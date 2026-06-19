@extends('layouts.app')

@section('title', 'Privacy Policy — ICCR Alumni Network')

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
.legal-hero p {
    color: rgba(255,255,255,0.45);
    font-size: 14px;
    margin: 0;
}
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
    <h1>Privacy Policy</h1>
    <span class="legal-updated">Last updated: June 2026</span>
</div>

<div class="legal-body">

    <p>We understand that privacy matters — especially to a community built on trust, shared experiences, and cross-cultural bonds. This Privacy Policy explains how the ICCR Alumni Network collects, uses, and protects the personal information you share with us when you use our platform. Please read it carefully; it applies to all alumni, visitors, and users of this website.</p>

    <h2>1. Who We Are</h2>
    <p>The ICCR Alumni Network is the official online community platform of the Indian Council for Cultural Relations (ICCR), a Government of India body under the Ministry of External Affairs. This platform is developed and managed by ICCR's authorised team to serve former ICCR scholarship recipients across the world.</p>

    <h2>2. What Information We Collect</h2>
    <p>When you register or use our platform, we may collect the following:</p>
    <ul>
        <li><strong>Account information</strong> — your full name, email address, phone number (optional), nationality, batch year, department, and institution.</li>
        <li><strong>Profile information</strong> — your current position, a profile photo, and any bio or story you choose to share publicly.</li>
        <li><strong>Usage data</strong> — pages you visit, features you use, and how you interact with the community (jobs, events, stories, etc.).</li>
        <li><strong>Communications</strong> — messages you send through our contact form or newsletters you subscribe to.</li>
        <li><strong>Technical data</strong> — your IP address, browser type, and device information, collected automatically when you access the site.</li>
    </ul>
    <p>We do not collect any sensitive financial data. We do not ask for your passport number, bank details, or government identification.</p>

    <h2>3. How We Use Your Information</h2>
    <p>Your information is used to run, maintain, and improve the platform. Specifically, we use it to:</p>
    <ul>
        <li>Create and manage your alumni profile.</li>
        <li>Connect you with fellow alumni, events, and job opportunities.</li>
        <li>Send you updates, newsletters, and notices related to the alumni community (only if you opt in).</li>
        <li>Moderate content and ensure the platform remains a safe and respectful space.</li>
        <li>Understand how alumni use the platform so we can improve it over time.</li>
        <li>Comply with legal obligations when required.</li>
    </ul>
    <p>We do not sell your personal data to anyone. We do not use your information for advertising purposes.</p>

    <h2>4. Who We Share Your Information With</h2>
    <p>Your profile information — including your name, batch year, department, country, and current position — is visible to other registered and approved alumni on the platform. This is the nature of a community directory, and you consented to this when you joined.</p>
    <p>Beyond that, we share data only in the following limited circumstances:</p>
    <ul>
        <li><strong>Service providers</strong> — trusted third-party tools that help us operate the website (such as email delivery or cloud hosting). They process data on our behalf and are bound by confidentiality.</li>
        <li><strong>Legal requirements</strong> — if we are required by law, a court order, or a legitimate authority to disclose information, we will do so.</li>
    </ul>
    <p>We do not share your personal data with other alumni networks, sponsors, or third-party marketers.</p>

    <h2>5. Cookies</h2>
    <p>We use cookies to keep you logged in, remember your preferences, and understand how the site is being used. These are standard session cookies and do not track you across other websites. You can disable cookies in your browser settings, though some parts of the platform may not work properly without them.</p>

    <h2>6. Data Security</h2>
    <p>We take reasonable technical and organisational measures to protect your personal information. Passwords are hashed and never stored in plain text. Access to the admin panel is restricted to authorised personnel only. That said, no online platform can guarantee absolute security, and we encourage you to use a strong, unique password for your account.</p>

    <h2>7. How Long We Keep Your Data</h2>
    <p>We keep your account data for as long as your account remains active. If you request deletion of your account, we will remove your personal information within 30 days, except where we are required to retain it for legal or administrative reasons.</p>

    <h2>8. Your Rights</h2>
    <p>You have the right to access, correct, or delete the personal information we hold about you. You can update most of your profile information directly from your account settings. For requests related to data deletion or to raise a concern about how we handle your data, please contact us using the details below.</p>

    <h2>9. Children's Privacy</h2>
    <p>This platform is intended for adults who have completed or are completing higher education through ICCR scholarships. We do not knowingly collect personal information from anyone under the age of 18. If we become aware that a minor has registered, we will remove their account promptly.</p>

    <h2>10. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time as the platform grows or as legal requirements change. When we do, we will update the "Last updated" date at the top of this page and, where appropriate, notify registered alumni by email.</p>

    <div class="legal-contact-box">
        <p>If you have any questions about this Privacy Policy or how your data is handled, please reach out to us through our <a href="{{ route('contact') }}">Contact page</a> or write to us at <a href="mailto:abhishekjha@ardhas.com">abhishekjha@ardhas.com</a>. We are happy to help.</p>
    </div>

</div>

@endsection
