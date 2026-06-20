@extends('layouts.community')
@section('hideRightSidebar', true)
@section('title', 'Create Event')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/events/create.css') }}">
@endpush

@section('content')

<div class="event-page">

    {{-- Header --}}
    <div class="event-page-header">
        <div>
            <span class="page-badge">ICCR Events</span>
            <h1>Create Event</h1>
            <p>
                Organize alumni reunions, conferences, cultural programs,
                workshops and networking events across the global ICCR community.
            </p>
        </div>

        <div class="header-actions">
            <button type="submit" form="eventForm" class="btn-primary">
                Publish Event
            </button>
        </div>
    </div>

    <div id="serverErrorBox" style="display:none;margin-bottom:1rem;padding:1rem 1.25rem;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;color:#991b1b;">
        <strong>Please fix the following errors:</strong>
        <ul id="serverErrorList" style="margin:.5rem 0 0 1.25rem;padding:0;"></ul>
    </div>

    <form id="eventForm" action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="event-layout">

            {{-- Left Content --}}
            <div class="event-main">

                {{-- Event Details --}}
                <div class="card event-card">
                    <div class="card-header">
                        <h3>Event Details</h3>
                        <p>Basic information about your event.</p>
                    </div>

                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="title">Event Title *</label>
                                <input
                                    type="text"
                                    id="title"
                                    name="title"
                                    placeholder="India–Africa Cultural Dialogue 2026"
                                    maxlength="255"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="category">Category *</label>
                                @if($categories->isEmpty())
                                    <div style="padding:10px 12px;border:1px solid #fecaca;background:#fff5f5;border-radius:8px;font-size:13px;color:#c53030;">
                                        No active categories found. Please ask an admin to add categories at
                                        <a href="{{ route('admin.event-categories.index') }}" style="color:#e8640c;font-weight:600;">Event Categories</a>.
                                    </div>
                                @else
                                <select id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <span style="font-size:12px;color:#c53030;margin-top:4px;display:block;">{{ $message }}</span>
                                @enderror
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="event_mode">Event Mode *</label>
                                <select id="event_mode" name="event_mode" required>
                                    <option value="">Select Event Mode</option>
                                    <option value="Physical">Physical</option>
                                    <option value="Online">Online</option>
                                    <option value="Hybrid">Hybrid</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="location">Venue / Location *</label>
                                <input
                                    type="text"
                                    id="location"
                                    name="location"
                                    placeholder="ICCR Headquarters, New Delhi"
                                >
                            </div>

                            <div class="form-group">
                                <label for="start_date">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" required>
                            </div>

                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date">
                            </div>

                            <div class="form-group">
                                <label for="start_time">Start Time *</label>
                                <input type="time" id="start_time" name="start_time" required>
                            </div>

                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="time" id="end_time" name="end_time">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="card event-card">
                    <div class="card-header">
                        <h3>Event Description</h3>
                        <p>Tell attendees what this event is about.</p>
                    </div>

                    <div class="card-body">
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="8"
                                placeholder="Write event details, agenda, speakers and key highlights..."
                                required
                            ></textarea>
                        </div>
                    </div>
                </div>

                {{-- Registration --}}
                <div class="card event-card">
                    <div class="card-header">
                        <h3>Registration & Tickets</h3>
                        <p>Manage participation settings.</p>
                    </div>

                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="event_type">Event Type</label>
                                <select id="event_type" name="event_type">
                                    <option value="Free">Free</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="total_seats">Total Seats</label>
                                <input type="number" id="total_seats" name="total_seats" placeholder="200" min="1">
                            </div>

                            <div class="form-group">
                                <label for="registration_deadline">Registration Deadline</label>
                                <input type="date" id="registration_deadline" name="registration_deadline">
                            </div>

                            {{-- Ticket price hidden: all events are free --}}

                            <div class="form-group">
                                <label for="registration_required">Registration Required</label>
                                <select id="registration_required" name="registration_required">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Media --}}
                <div class="card event-card">
                    <div class="card-header">
                        <h3>Event Banner</h3>
                        <p>Upload a cover image for your event.</p>
                    </div>

                    <div class="card-body">
                        <div class="upload-box">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>

                            <h4>Upload Event Banner</h4>
                            <p>PNG, JPG or WEBP up to 5MB</p>

                            <input type="file" id="banner_image" name="banner_image" accept="image/png,image/jpeg,image/webp">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Preview --}}
            <div class="event-sidebar">
                <div class="card preview-card">
                    <div class="preview-image" id="previewImage">
                        <span id="previewImageText">Event Banner Preview</span>
                    </div>

                    <div class="preview-body">
                        <span class="preview-tag" id="previewCategory">Conference</span>

                        <h3 id="previewTitle">
                            Event Title Preview
                        </h3>

                        <ul class="preview-meta">
                            <li id="previewDate">📅 Select Date</li>
                            <li id="previewTime">🕒 Select Time</li>
                            <li id="previewLocation">📍 Event Location</li>
                            <li id="previewMode">⚪ Physical</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ==========================
       Elements
    ========================== */

    const form = document.getElementById('eventForm');

    const titleInput = document.getElementById('title');
    const categoryInput = document.getElementById('category');
    const locationInput = document.getElementById('location');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const eventModeInput = document.getElementById('event_mode');
    const eventTypeInput = document.getElementById('event_type');
    const totalSeatsInput = document.getElementById('total_seats');
    const registrationDeadlineInput = document.getElementById('registration_deadline');
    const ticketPriceInput = document.getElementById('ticket_price');
    const bannerInput = document.getElementById('banner_image');

    const previewTitle = document.getElementById('previewTitle');
    const previewCategory = document.getElementById('previewCategory');
    const previewLocation = document.getElementById('previewLocation');
    const previewDate = document.getElementById('previewDate');
    const previewTime = document.getElementById('previewTime');
    const previewMode = document.getElementById('previewMode');
    const previewImage = document.getElementById('previewImage');
    const previewImageText = document.getElementById('previewImageText');

    if (!form) return;

    /* ==========================
       Helpers
    ========================== */

    const DATE_TIME_FORMAT = 'YYYY-MM-DDTHH:MM';

    function showError(message, field = null) {
        if (field && typeof field.setCustomValidity === 'function') {
            field.setCustomValidity(message);
            field.reportValidity();
            field.focus();
        } else {
            alert(message);
        }
    }

    function clearError(field) {
        if (field && typeof field.setCustomValidity === 'function') {
            field.setCustomValidity('');
        }
    }

    function clearAllErrors() {
        [
            titleInput,
            categoryInput,
            locationInput,
            startDateInput,
            endDateInput,
            startTimeInput,
            endTimeInput,
            eventTypeInput,
            totalSeatsInput,
            registrationDeadlineInput,
            ticketPriceInput,
            bannerInput
        ].forEach(clearError);
    }

    function parseDateTime(dateValue, timeValue, fallbackTime = '00:00') {
        if (!dateValue) return null;
        return new Date(`${dateValue}T${timeValue || fallbackTime}`);
    }

    function isValidDateObject(value) {
        return value instanceof Date && !Number.isNaN(value.getTime());
    }

    function updateDateConstraints() {
        if (startDateInput && endDateInput) {
            endDateInput.min = startDateInput.value || '';
        }

        if (startDateInput && registrationDeadlineInput) {
            if (!startDateInput.value) {
                registrationDeadlineInput.max = '';
                return;
            }

            const d = new Date(startDateInput.value);
            d.setDate(d.getDate() - 1);

            registrationDeadlineInput.max = d.toISOString().split('T')[0];
        }
    }

    function updateEndTimeConstraint() {
        if (!startDateInput || !endDateInput || !startTimeInput || !endTimeInput) return;

        const sameDay =
            startDateInput.value &&
            endDateInput.value &&
            startDateInput.value === endDateInput.value;

        if (sameDay) {
            endTimeInput.min = startTimeInput.value || '';
        } else {
            endTimeInput.min = '';
        }
    }

    function updatePreviewDate() {
        if (!previewDate) return;

        if (!startDateInput.value) {
            previewDate.textContent = '📅 Select Date';
            return;
        }

        const date = new Date(startDateInput.value);

        previewDate.textContent =
            '📅 ' +
            date.toLocaleDateString('en-IN', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
    }

    function updatePreviewTime() {
        if (!previewTime) return;

        const start = startTimeInput?.value || 'Select Time';
        previewTime.textContent = '🕒 ' + start;
    }

    function validateFile(file) {
        if (!file) return true;

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        const maxSizeMB = 5;
        const maxBytes = maxSizeMB * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            showError(
                'Only JPG, PNG, and WEBP images are allowed.',
                bannerInput
            );
            return false;
        }

        if (file.size > maxBytes) {
            showError(
                'Banner image must be 5MB or smaller.',
                bannerInput
            );
            return false;
        }

        clearError(bannerInput);
        return true;
    }

    function validateForm() {
        clearAllErrors();

        const title = titleInput?.value.trim() || '';
        const category = categoryInput?.value || '';
        const location = locationInput?.value.trim() || '';
        const startDate = startDateInput?.value || '';
        const endDate = endDateInput?.value || '';
        const startTime = startTimeInput?.value || '';
        const endTime = endTimeInput?.value || '';
        const eventMode = eventModeInput?.value || '';
        const eventType = eventTypeInput?.value || 'Free';
        const deadline = registrationDeadlineInput?.value || '';
        const ticketPrice = ticketPriceInput?.value || '';
        const seats = totalSeatsInput?.value || '';
        const bannerFile = bannerInput?.files?.[0] || null;

        if (!title) {
            showError('Event title is required.', titleInput);
            return false;
        }

        if (!category) {
            showError('Please select a category.', categoryInput);
            return false;
        }

        if (!eventMode) {
            showError('Please select an event mode.', eventModeInput);
            return false;
        }

        if (!location) {
            showError('Venue / location is required.', locationInput);
            return false;
        }

        if (!startDate) {
            showError('Start date is required.', startDateInput);
            return false;
        }

        if (!startTime) {
            showError('Start time is required.', startTimeInput);
            return false;
        }

        if ((endDate && !endTime) || (!endDate && endTime)) {
            showError(
                'End date and end time must be provided together.',
                endDate || endTimeInput
            );
            return false;
        }

        const startDateTime = parseDateTime(startDate, startTime);
        if (!isValidDateObject(startDateTime)) {
            showError('Start date and time are invalid.', startDateInput);
            return false;
        }

        let endDateTime = null;

        if (endDate && endTime) {
            endDateTime = parseDateTime(endDate, endTime);
            if (!isValidDateObject(endDateTime)) {
                showError('End date and time are invalid.', endDateInput);
                return false;
            }

            if (endDateTime <= startDateTime) {
                showError(
                    'Event end date and time must be after start date and time.',
                    endDateInput
                );
                return false;
            }
        }

        if (deadline) {
            const deadlineDate = new Date(deadline + 'T00:00:00');
            const startDateOnly = new Date(startDate + 'T00:00:00');

            if (!isValidDateObject(deadlineDate)) {
                showError(
                    'Registration deadline is invalid.',
                    registrationDeadlineInput
                );
                return false;
            }

            if (deadlineDate > startDateOnly) {
                showError(
                    'Registration deadline must be on or before the event start date.',
                    registrationDeadlineInput
                );
                return false;
            }
        }

        if (seats && Number(seats) <= 0) {
            showError(
                'Total seats must be greater than zero.',
                totalSeatsInput
            );
            return false;
        }

        if (bannerFile && !validateFile(bannerFile)) {
            return false;
        }

        return true;
    }

    function validateEndDateLive() {
        clearError(endDateInput);
        clearError(endTimeInput);

        if (!startDateInput?.value || !endDateInput?.value || !startTimeInput?.value || !endTimeInput?.value) {
            updateEndTimeConstraint();
            return;
        }

        const startDateTime = parseDateTime(startDateInput.value, startTimeInput.value);
        const endDateTime = parseDateTime(endDateInput.value, endTimeInput.value);

        if (isValidDateObject(startDateTime) && isValidDateObject(endDateTime)) {
            if (endDateTime <= startDateTime) {
                showError(
                    'End date and time must be after the start date and time.',
                    endDateInput
                );
                return false;
            }
        }

        return true;
    }

    /* ==========================
       Live Preview
    ========================== */

    titleInput?.addEventListener('input', () => {
        previewTitle.textContent = titleInput.value.trim() || 'Event Title Preview';
        clearError(titleInput);
    });

    categoryInput?.addEventListener('change', () => {
        previewCategory.textContent = categoryInput.value || 'Category';
        clearError(categoryInput);
    });

    locationInput?.addEventListener('input', () => {
        previewLocation.textContent =
            '📍 ' + (locationInput.value.trim() || 'Event Location');
        clearError(locationInput);
    });

    startDateInput?.addEventListener('change', () => {
        updateDateConstraints();
        updateEndTimeConstraint();
        updatePreviewDate();
        clearError(startDateInput);

        if (endDateInput?.value && endDateInput.value < startDateInput.value) {
            endDateInput.value = '';
            endTimeInput.value = '';
            previewTime.textContent = '🕒 Select Time';
        }

        if (registrationDeadlineInput?.value && registrationDeadlineInput.value >= startDateInput.value) {
            registrationDeadlineInput.value = '';
        }

        clearError(endDateInput);
        clearError(registrationDeadlineInput);
    });

    endDateInput?.addEventListener('change', () => {
        updateEndTimeConstraint();
        clearError(endDateInput);

        if (startDateInput?.value && endDateInput.value < startDateInput.value) {
            showError(
                'End date cannot be before start date.',
                endDateInput
            );
            return;
        }

        validateEndDateLive();
    });

    startTimeInput?.addEventListener('change', () => {
        updateEndTimeConstraint();
        updatePreviewTime();
        clearError(startTimeInput);
        validateEndDateLive();
    });

    endTimeInput?.addEventListener('change', () => {
        clearError(endTimeInput);
        validateEndDateLive();
    });

    eventModeInput?.addEventListener('change', () => {
        previewMode.textContent = '⚪ ' + eventModeInput.value;
        clearError(eventModeInput);
    });

    eventTypeInput?.addEventListener('change', () => {
        clearError(eventTypeInput);
    });

    totalSeatsInput?.addEventListener('input', () => {
        clearError(totalSeatsInput);
    });

    registrationDeadlineInput?.addEventListener('change', () => {
        clearError(registrationDeadlineInput);

        if (startDateInput?.value && registrationDeadlineInput.value) {
            const dl = new Date(registrationDeadlineInput.value + 'T00:00:00');
            const sd = new Date(startDateInput.value + 'T00:00:00');
            if (dl > sd) {
                showError(
                    'Registration deadline must be on or before the event start date.',
                    registrationDeadlineInput
                );
            }
        }
    });

    ticketPriceInput?.addEventListener('input', () => {
        clearError(ticketPriceInput);
    });

    bannerInput?.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        if (!validateFile(file)) {
            bannerInput.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            previewImage.style.backgroundImage = `url(${event.target.result})`;
            previewImage.style.backgroundSize = 'cover';
            previewImage.style.backgroundPosition = 'center';
            previewImageText.style.display = 'none';
            clearError(bannerInput);
        };

        reader.readAsDataURL(file);
    });

    /* ==========================
       Initial Constraints
    ========================== */

    updateDateConstraints();
    updateEndTimeConstraint();
    updatePreviewDate();
    updatePreviewTime();

    /* ==========================
       Submit — AJAX (keep form data on error)
    ========================== */

    const submitBtn   = document.querySelector('[form="eventForm"]');
    const errorBox    = document.getElementById('serverErrorBox');
    const errorList   = document.getElementById('serverErrorList');

    function showServerErrors(errors) {
        errorList.innerHTML = '';
        // errors is either an object {field:[msgs]} or an array of strings
        const msgs = Array.isArray(errors)
            ? errors
            : Object.values(errors).flat();
        msgs.forEach(msg => {
            const li = document.createElement('li');
            li.textContent = msg;
            errorList.appendChild(li);
        });
        errorBox.style.display = '';
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function hideServerErrors() {
        errorBox.style.display = 'none';
        errorList.innerHTML = '';
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        hideServerErrors();

        if (!validateForm()) return;

        const formData = new FormData(form);

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving…';
        }

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        || '{{ csrf_token() }}',
                },
                body: formData,
            });

            if (res.ok || res.status === 201) {
                // Success — redirect to my events
                const data = await res.json().catch(() => ({}));
                window.location.href = data.redirect || '{{ route('events.my') }}';
                return;
            }

            if (res.status === 422) {
                const data = await res.json();
                showServerErrors(data.errors || data.message || 'Validation failed.');
                return;
            }

            // Other server error
            showServerErrors(['An unexpected error occurred. Please try again.']);
        } catch (err) {
            showServerErrors(['Network error. Please check your connection and try again.']);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Publish Event';
            }
        }
    });
});
</script>
@endpush