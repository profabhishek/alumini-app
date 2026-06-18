@extends('layouts.app')

@section('title', 'Contact Us — ICCR Alumni')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@section('content')

<div class="ct-root">

    {{-- ── HERO ─────────────────────────────────────────── --}}
    <section class="ct-hero">
        <div class="ct-hero__inner">
            <p class="ct-hero__eyebrow">
                <span class="ct-hero__dot" aria-hidden="true"></span>
                ICCR Alumni Network
            </p>
            <h1 class="ct-hero__title">Get in Touch</h1>
            <p class="ct-hero__sub">
                Questions, suggestions, or need assistance?<br>
                We respond within 24–48 hours.
            </p>
        </div>
    </section>

    {{-- ── BODY ─────────────────────────────────────────── --}}
    <section class="ct-body">
        <div class="ct-body__inner">

            {{-- Left: info cards --}}
            <aside class="ct-info">

                <div class="ct-info__card">
                    <div class="ct-info__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <div>
                        <p class="ct-info__label">Email</p>
                        <a href="mailto:ardhasjha@ardhas.com" class="ct-info__value">ardhasjha@ardhas.com</a>
                    </div>
                </div>

                <div class="ct-info__card">
                    <div class="ct-info__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5 19.79 19.79 0 0 1 1.61 4.94 2 2 0 0 1 3.59 2.77h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.1a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 17.5z"/></svg>
                    </div>
                    <div>
                        <p class="ct-info__label">Phone</p>
                        <a href="tel:+919999832703" class="ct-info__value">+91 99998 32703</a>
                    </div>
                </div>

                <div class="ct-info__card">
                    <div class="ct-info__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <p class="ct-info__label">Address</p>
                        <p class="ct-info__value">ICCR Headquarters,<br>New Delhi, India</p>
                    </div>
                </div>

                <div class="ct-info__card">
                    <div class="ct-info__icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <p class="ct-info__label">Working Hours</p>
                        <p class="ct-info__value">Mon – Fri<br>9:00 AM – 6:00 PM IST</p>
                    </div>
                </div>

            </aside>

            {{-- Right: form --}}
            <div class="ct-form-wrap">

                {{-- Success banner --}}
                @if(session('success'))
                <div class="ct-alert ct-alert--success" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                {{-- Error banner --}}
                @if(session('error'))
                <div class="ct-alert ct-alert--error" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                <form class="ct-form"
                      action="{{ route('contact.send') }}"
                      method="POST"
                      novalidate>
                    @csrf

                    <div class="ct-form__head">
                        <h2 class="ct-form__title">Send a Message</h2>
                        <p class="ct-form__sub">We'll get back to you within 24–48 hours.</p>
                    </div>

                    <div class="ct-form__row">
                        <div class="ct-field {{ $errors->has('name') ? 'ct-field--error' : '' }}">
                            <label for="ct_name" class="ct-field__label">
                                Full Name <span aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="ct_name"
                                   name="name"
                                   class="ct-field__input"
                                   value="{{ old('name') }}"
                                   placeholder="Your full name"
                                   autocomplete="name"
                                   required>
                            @error('name')
                                <p class="ct-field__err">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ct-field {{ $errors->has('email') ? 'ct-field--error' : '' }}">
                            <label for="ct_email" class="ct-field__label">
                                Email Address <span aria-hidden="true">*</span>
                            </label>
                            <input type="email"
                                   id="ct_email"
                                   name="email"
                                   class="ct-field__input"
                                   value="{{ old('email') }}"
                                   placeholder="your@email.com"
                                   autocomplete="email"
                                   required>
                            @error('email')
                                <p class="ct-field__err">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="ct-field {{ $errors->has('subject') ? 'ct-field--error' : '' }}">
                        <label for="ct_subject" class="ct-field__label">
                            Subject <span aria-hidden="true">*</span>
                        </label>
                        <input type="text"
                               id="ct_subject"
                               name="subject"
                               class="ct-field__input"
                               value="{{ old('subject') }}"
                               placeholder="What is this about?"
                               required>
                        @error('subject')
                            <p class="ct-field__err">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="ct-field {{ $errors->has('message') ? 'ct-field--error' : '' }}">
                        <label for="ct_message" class="ct-field__label">
                            Message <span aria-hidden="true">*</span>
                        </label>
                        <textarea id="ct_message"
                                  name="message"
                                  class="ct-field__input ct-field__input--ta"
                                  rows="6"
                                  placeholder="Write your message here…"
                                  required>{{ old('message') }}</textarea>
                        @error('message')
                            <p class="ct-field__err">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="ct-submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Send Message
                    </button>

                </form>
            </div>

        </div>
    </section>

</div>

@endsection