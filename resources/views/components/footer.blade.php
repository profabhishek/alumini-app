<footer>
    {{-- <div class="footer-newsletter">
        <div class="nl-inner">
            <div>
                <div class="nl-label">Stay Connected</div>
                <div class="nl-heading">Subscribe to Our Newsletter</div>
                <div class="nl-sub">
                    Get the latest alumni news, events & opportunities delivered
                    to your inbox.
                </div>
            </div>
            <div>
                <form class="nl-form" id="newsletterForm" novalidate>
                    @csrf

                    <div style="position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;" aria-hidden="true">
                        <label for="nl_website">Leave this field empty</label>
                        <input type="text" id="nl_website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <input
                        id="nl_email"
                        class="nl-input"
                        type="email"
                        name="email"
                        placeholder="Enter your email address"
                        required
                    />
                    <button class="nl-btn" id="nlSubmitBtn" type="submit">
                        <span id="nlBtnText">Subscribe</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                    </button>
                </form>
                <div id="nlMessage" class="nl-message" style="display:none;"></div>
                <div style="margin-top: 10px; font-size: 11.5px; color: black">
                    No spam. <a href="{{ route('newsletter.unsubscribe.form') }}" style="color: inherit; text-decoration: underline;">Unsubscribe anytime.</a>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="footer-main">
        <div class="footer-grid">
            <div>
                <a href="/" class="footer-brand-logo">
                    <div class="logo-mark">
                        <img
                            src="https://iccr.hialumni.com/storage/uploads/Setting/7881769241382.png"
                            alt="iccr"
                        />
                    </div>
                </a>
                <p class="brand-desc">ICCR Alumni refers to the community of former scholarship students of the Indian Council for Cultural Relations. These alumni remain connected through events, mentorship, and collaborations — fostering academic progress and professional networks worldwide.</p>
                <div class="social-row">
                    <a href="#" class="social-btn" aria-label="Facebook">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" /></svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="X / Twitter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.258 5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="LinkedIn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z" />
                            <rect x="2" y="9" width="4" height="12" />
                            <circle cx="4" cy="4" r="2" />
                        </svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="Instagram">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <div class="col-title">Useful Links</div>
                <ul class="col-links">
                    <li><a href="{{ route('notice') }}">Notice Board</a></li>
                    <li><a href="{{ route('events.index') }}">Events</a></li>
                    <li><a href="{{ route('stories.index') }}">Alumni Stories</a></li>
                    <li><a href="{{ route('news') }}">News</a></li>
                    <li><a href="{{ route('alumni') }}">Alumni Directory</a></li>
                    <li><a href="{{ route('jobs.index') }}">Job Opportunities</a></li>
                    <li><a href="{{ route('gallery') }}">Gallery</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                </ul>
            </div>

            <div>
                <div class="col-title">Policies</div>
                <ul class="col-links">
                    <li><a href="{{ route('privacy-policy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}">Terms &amp; Conditions</a></li>
                    <li><a href="{{ route('disclaimer') }}">Disclaimer</a></li>
                </ul>
            </div>

            <div>
                <div class="col-title">Contact Us</div>
                <div class="contact-items">
                    <div class="contact-item">
                        <div class="ci-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                        <div class="ci-text">
                            <span class="ci-label">Address</span>
                            <span class="ci-val">New Delhi, India</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="ci-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg>
                        </div>
                        <div class="ci-text">
                            <span class="ci-label">Email</span>
                            <span class="ci-val"><a href="mailto:abhishekjha@ardhas.com">abhishekjha@ardhas.com</a></span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="ci-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        </div>
                        <div class="ci-text">
                            <span class="ci-label">About ICCR</span>
                            <span class="ci-val"><a href="https://iccr.gov.in" target="_blank" rel="noopener">iccr.gov.in</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-inner">
            <div class="copy">
                © 2026 <span>Indian Council for Cultural Relations</span>. All
                rights reserved.
            </div>
            <div class="bottom-links">
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms</a>
                <a href="{{ route('disclaimer') }}">Disclaimer</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </div>
    </div>
</footer>

@push('scripts')
<script>
(function () {
    const form     = document.getElementById('newsletterForm');
    if (!form) return;

    const emailInput = document.getElementById('nl_email');
    const submitBtn  = document.getElementById('nlSubmitBtn');
    const btnText    = document.getElementById('nlBtnText');
    const msgBox     = document.getElementById('nlMessage');

    function showMessage(text, type) {
        msgBox.textContent = text;
        msgBox.className   = 'nl-message nl-message--' + type;
        msgBox.style.display = 'block';
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const email = emailInput.value.trim();

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showMessage('Please enter a valid email address.', 'error');
            return;
        }

        submitBtn.disabled = true;
        btnText.textContent = 'Subscribing...';

        try {
            const response = await fetch('{{ route('newsletter.subscribe') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });

            if (response.status === 429) {
                showMessage("You're submitting too quickly. Please wait a moment and try again.", 'error');
            } else {
                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    form.reset();
                } else {
                    const fieldError = data.errors?.email?.[0];
                    showMessage(fieldError || data.message || 'Something went wrong. Please try again.', 'error');
                }
            }
        } catch (err) {
            showMessage('Network error. Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            btnText.textContent = 'Subscribe';
        }
    });
})();
</script>
@endpush
