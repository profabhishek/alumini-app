<div id="registrationModal" class="ev-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="evModalTitle">
    <div class="ev-modal">

        {{-- Header --}}
        <div class="ev-modal__header">
            <div>
                <p class="ev-modal__header-label">Register for</p>
                <h2 class="ev-modal__header-title" id="evModalTitle">Event Name</h2>
            </div>
            <button class="ev-modal__close" id="regModalClose" aria-label="Close">&times;</button>
        </div>

        {{-- Success State --}}
        <div id="regSuccess" class="ev-modal__success" style="display:none;">
            <div class="ev-modal__success-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3>You're Registered!</h3>
            <p id="regSuccessMsg">You have successfully registered for this event.</p>
            <div class="ev-modal__success-email" id="regSuccessEmail" style="display:none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Confirmation sent to <strong id="regSuccessEmailAddr"></strong>
            </div>
            <button class="ev-modal__done" onclick="closeRegModal()">Done</button>
        </div>

        {{-- Form --}}
        <form id="regForm" novalidate>
            @csrf
            <input type="hidden" id="regEventId" name="event_id" value="">

            <div class="ev-modal__body">
                <div class="ev-modal__grid">
                    <div class="ev-field">
                        <label for="reg_full_name">Full Name <span>*</span></label>
                        <input type="text" id="reg_full_name" name="full_name"
                               value="{{ auth()->user()->name ?? '' }}"
                               placeholder="Your full name" required>
                        <span class="ev-field-error" id="err_full_name"></span>
                    </div>

                    <div class="ev-field">
                        <label for="reg_email">Email <span>*</span></label>
                        <input type="email" id="reg_email" name="email"
                               value="{{ auth()->user()->email ?? '' }}"
                               placeholder="your@email.com" required>
                        <span class="ev-field-error" id="err_email"></span>
                    </div>

                    <div class="ev-field">
                        <label for="reg_phone">Phone Number</label>
                        <input type="text" id="reg_phone" name="phone" placeholder="+91 98765 43210">
                    </div>

                    <div class="ev-field">
                        <label for="reg_country">Country</label>
                        <input type="text" id="reg_country" name="country" placeholder="India">
                    </div>

                    <div class="ev-field">
                        <label for="reg_batch_year">Alumni Batch / Year</label>
                        <input type="text" id="reg_batch_year" name="batch_year" placeholder="e.g. 2018–2020">
                    </div>

                    <div class="ev-field">
                        <label for="reg_no_of_people">No. of People <span>*</span></label>
                        <input type="number" id="reg_no_of_people" name="no_of_people" value="1" min="1" max="20" required>
                        <span class="ev-field-error" id="err_no_of_people"></span>
                    </div>
                </div>

                <div class="ev-field ev-field-full">
                    <label for="reg_message">Message / Special Requirements</label>
                    <textarea id="reg_message" name="message"
                              placeholder="Any special requirements or message for the organiser…"
                              rows="3"></textarea>
                </div>

                <div id="regFormError" class="ev-form-error" style="display:none;"></div>
            </div>

            <div class="ev-modal__footer">
                <button type="button" class="ev-modal__cancel" onclick="closeRegModal()">Cancel</button>
                <button type="submit" class="ev-modal__submit" id="regSubmitBtn">
                    <span id="regBtnText">Confirm Registration</span>
                    <span id="regBtnSpinner" style="display:none;">Submitting…</span>
                </button>
            </div>
        </form>

    </div>
</div>