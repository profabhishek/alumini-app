@extends('layouts.app')

@section('title', 'Disclaimer — ICCR Alumni Network')

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
    <h1>Disclaimer</h1>
    <span class="legal-updated">Last updated: June 2026</span>
</div>

<div class="legal-body">

    <p>The ICCR Alumni Network is the official online platform of the Indian Council for Cultural Relations (ICCR), developed and managed by ICCR's authorised team. This disclaimer outlines the scope and limitations of the platform so that users can engage with it responsibly.</p>

    <h2>1. About This Platform</h2>
    <p>This website is an official initiative of the Indian Council for Cultural Relations (ICCR), a Government of India body under the Ministry of External Affairs. It is built to serve the global community of former ICCR scholarship recipients. While the platform is official, the content shared by individual alumni — including profiles, stories, job posts, and comments — represents their personal views and experiences, and not necessarily the position of ICCR or the Government of India.</p>

    <h2>2. Accuracy of Information</h2>
    <p>We make every reasonable effort to keep the information on this platform accurate and up to date. However, the content shared here — including alumni profiles, news articles, event details, job listings, and stories — is largely contributed by community members and may not always be verified or current.</p>
    <p>We do not warrant or guarantee the completeness, accuracy, or suitability of any information found on this platform. You should independently verify any information before relying on it, particularly for professional or career-related decisions.</p>

    <h2>3. Job Listings and Career Information</h2>
    <p>Job opportunities listed on this platform are shared by alumni and community members in good faith. The ICCR Alumni Network does not endorse, verify, or vouch for any employer, organisation, or position listed here. We strongly recommend that you conduct your own due diligence before applying for any role or engaging with any recruiter through this platform.</p>
    <p>We are not responsible for any employment outcomes, contractual arrangements, or disputes arising from job listings or professional connections made through this platform.</p>

    <h2>4. Alumni Stories and User-Generated Content</h2>
    <p>The stories, opinions, and experiences shared by alumni on this platform are personal accounts and reflections. They do not represent the views of the ICCR Alumni Network, ICCR, or the Government of India. We do not edit or fact-check user-submitted content before it is published, though we do review flagged content and may remove anything that violates our community standards.</p>

    <h2>5. External Links</h2>
    <p>This platform may contain links to external websites — including ICCR's official website, news sources, employer pages, and other resources. These links are provided for convenience only. We have no control over the content or availability of those external sites and are not responsible for their accuracy, privacy practices, or terms of use. Clicking an external link means you are leaving our platform.</p>

    <h2>6. No Professional Advice</h2>
    <p>Nothing on this platform constitutes legal, financial, medical, visa, immigration, or career advice. Any guidance or suggestions shared by alumni — whether in stories, discussions, or direct communications — are personal opinions, not professional recommendations. For matters requiring expert guidance, please consult a qualified professional.</p>

    <h2>7. Availability of the Platform</h2>
    <p>We do our best to keep this platform running smoothly, but we cannot guarantee uninterrupted or error-free access. We may occasionally take the site offline for maintenance, upgrades, or to address technical issues. We are not liable for any inconvenience, loss, or damage resulting from downtime or technical failures.</p>

    <h2>8. Changes to This Disclaimer</h2>
    <p>We may update this Disclaimer periodically to reflect changes in how the platform operates or in applicable laws. The most recent version will always be available on this page. If you continue to use the platform after updates are posted, you accept the revised disclaimer.</p>

    <div class="legal-contact-box">
        <p>If something on this platform is inaccurate, misleading, or concerns you, we genuinely want to know. Please get in touch through our <a href="{{ route('contact') }}">Contact page</a> or email us at <a href="mailto:abhishekjha@ardhas.com">abhishekjha@ardhas.com</a>.</p>
    </div>

</div>

@endsection
