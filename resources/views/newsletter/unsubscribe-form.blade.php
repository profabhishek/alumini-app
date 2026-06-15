@extends('layouts.app')

@section('title', 'Unsubscribe')

@section('content')
<section style="min-height: 60vh; display:flex; align-items:center; justify-content:center; padding: 60px 20px;">
    <div style="max-width: 440px; width:100%; text-align:center;">
        <h1 style="font-size: 28px; font-weight: 800; color: #111; margin-bottom: 10px;">
            Unsubscribe
        </h1>
        <p style="color:#666; font-size:14.5px; line-height:1.7; margin-bottom: 24px;">
            Enter the email address you subscribed with, and we'll stop sending you newsletter updates.
        </p>

        <form method="POST" action="{{ route('newsletter.unsubscribe.email') }}" style="display:flex; flex-direction:column; gap:12px;">
            @csrf
            <input
                type="email"
                name="email"
                required
                placeholder="Enter your email address"
                value="{{ old('email') }}"
                style="height:50px; border:1.5px solid #ddd; border-radius:10px; padding:0 16px; font-size:15px; font-family:inherit;"
            />
            @error('email')
                <span style="color:#dc2626; font-size:13px; text-align:left;">{{ $message }}</span>
            @enderror
            <button type="submit" style="height:50px; border:none; border-radius:10px; background:#111; color:#fff; font-weight:700; font-size:15px; cursor:pointer;">
                Unsubscribe
            </button>
        </form>

        <a href="{{ route('home') }}" style="display:inline-block; margin-top:20px; font-size:13.5px; color:#999; text-decoration:none;">
            ← Back to Home
        </a>
    </div>
</section>
@endsection