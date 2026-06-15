@extends('layouts.app')

@section('title', 'Check Your Email')

@section('content')
<section style="min-height: 60vh; display:flex; align-items:center; justify-content:center; padding: 60px 20px;">
    <div style="max-width: 480px; text-align:center;">
        <div style="font-size:48px; margin-bottom: 16px;">📬</div>
        <h1 style="font-size: 28px; font-weight: 800; color: #111; margin-bottom: 12px;">
            Check your inbox
        </h1>
        <p style="color:#666; font-size:15px; line-height:1.7;">
            If <strong>{{ $email }}</strong> is subscribed to our newsletter, we've sent a confirmation link to that address.
            Click the link in the email to confirm you'd like to unsubscribe.
        </p>
        <p style="color:#999; font-size:13px; line-height:1.7; margin-top:16px;">
            Didn't get an email? Check your spam folder, or make sure you entered the address you originally subscribed with.
        </p>
        <a href="{{ route('home') }}" style="display:inline-block; margin-top:24px; padding:12px 28px; border-radius:10px; background:#111; color:#fff; text-decoration:none; font-weight:700;">
            Back to Home
        </a>
    </div>
</section>
@endsection