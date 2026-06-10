@extends('layouts.app')

@section('title', 'Contact Us')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@section('content')

<section class="contact-page">

    {{-- HERO --}}
    <section class="contact-hero">

        <div class="container">

            <span class="contact-badge">
                Get In Touch
            </span>

            <h1 class="contact-title">
                Contact Us
            </h1>

            <p class="contact-subtitle">
                Have a question, suggestion, or need assistance?
                We'd love to hear from you.
            </p>

        </div>

    </section>


    {{-- CONTACT SECTION --}}
    <section class="contact-section">

        <div class="container">

            <div class="contact-grid">

                {{-- LEFT --}}
                <div class="contact-info">

                    <div class="info-card">

                        <h3>
                            Contact Information
                        </h3>

                        <p>
                            Reach out to our team through any of the
                            channels below.
                        </p>

                        <div class="contact-item">

                            <div class="contact-icon">
                                ✉
                            </div>

                            <div>
                                <span>Email</span>
                                <strong>ardhasjha@ardhas.com</strong>
                            </div>

                        </div>

                        <div class="contact-item">

                            <div class="contact-icon">
                                ☎
                            </div>

                            <div>
                                <span>Phone</span>
                                <strong>+91 99998 32703</strong>
                            </div>

                        </div>

                        <div class="contact-item">

                            <div class="contact-icon">
                                📍
                            </div>

                            <div>
                                <span>Address</span>
                                <strong>
                                    ICCR Headquarters,
                                    New Delhi, India
                                </strong>
                            </div>

                        </div>

                        <div class="contact-item">

                            <div class="contact-icon">
                                🕒
                            </div>

                            <div>
                                <span>Working Hours</span>
                                <strong>
                                    Mon - Fri | 9:00 AM - 6:00 PM
                                </strong>
                            </div>

                        </div>

                    </div>

                </div>


                {{-- RIGHT --}}
                <div class="contact-form-card">

                    <h2>
                        Send Us A Message
                    </h2>

                    <form class="contact-form">

                        <div class="form-row">

                            <div class="form-group">
                                <label>Name</label>
                                <input
                                    type="text"
                                    placeholder="Your Name"
                                >
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input
                                    type="email"
                                    placeholder="Your Email"
                                >
                            </div>

                        </div>

                        <div class="form-group">
                            <label>Subject</label>
                            <input
                                type="text"
                                placeholder="Subject"
                            >
                        </div>

                        <div class="form-group">
                            <label>Message</label>
                            <textarea
                                rows="6"
                                placeholder="Write your message..."
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            class="contact-submit-btn"
                        >
                            Send Message
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </section>

</section>

@endsection
