(function () {
    const config = window.EVENTS_AUTH || {
        loggedIn: false,
        loginUrl: "/login",
        alumniName: "",
        alumniEmail: "",
    };

    // ── Scroll reveal ──────────────────────────────────────────
    const revealEls = document.querySelectorAll(".reveal");
    if (revealEls.length) {
        const revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry, i) => {
                    if (entry.isIntersecting) {
                        setTimeout(
                            () => entry.target.classList.add("show"),
                            i * 80,
                        );
                        revealObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.07 },
        );

        revealEls.forEach((el) => revealObserver.observe(el));
    }

    // ── Modal elements ─────────────────────────────────────────
    const overlay = document.getElementById("registrationModal");
    if (!overlay) return; // no modal on this page

    const closeBtn = document.getElementById("regModalClose");
    const form = document.getElementById("regForm");
    const titleEl = document.getElementById("regModalTitle");
    const eventIdEl = document.getElementById("regEventId");
    const successBox = document.getElementById("regSuccess");
    const successMsg = document.getElementById("regSuccessMsg");
    const formError = document.getElementById("regFormError");
    const submitBtn = document.getElementById("regSubmitBtn");
    const btnText = document.getElementById("regBtnText");
    const btnSpinner = document.getElementById("regBtnSpinner");

    document.querySelectorAll('[data-register="true"]').forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            if (!config.loggedIn) {
                window.location.href = config.loginUrl;
                return;
            }

            const eventId = this.dataset.eventId;
            const eventTitle = this.dataset.eventTitle;

            titleEl.textContent = eventTitle;
            eventIdEl.value = eventId;

            form.reset();
            form.querySelector("#reg_full_name").value =
                config.alumniName || "";
            form.querySelector("#reg_email").value = config.alumniEmail || "";
            form.querySelector("#reg_no_of_people").value = 1;

            clearErrors();
            formError.style.display = "none";
            successBox.style.display = "none";
            form.style.display = "block";

            overlay.classList.add("open");
            document.body.style.overflow = "hidden";
        });
    });

    // ── Close modal ────────────────────────────────────────────
    window.closeRegModal = function () {
        overlay.classList.remove("open");
        document.body.style.overflow = "";
    };

    closeBtn.addEventListener("click", closeRegModal);

    overlay.addEventListener("click", function (e) {
        if (e.target === overlay) closeRegModal();
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeRegModal();
    });

    // ── Validation ─────────────────────────────────────────────
    function clearErrors() {
        document
            .querySelectorAll(".reg-error")
            .forEach((el) => (el.textContent = ""));
        document
            .querySelectorAll(".reg-field input, .reg-field textarea")
            .forEach((el) => el.classList.remove("invalid"));
    }

    function showError(fieldId, msg) {
        const errEl = document.getElementById("err_" + fieldId);
        const input = document.getElementById("reg_" + fieldId);
        if (errEl) errEl.textContent = msg;
        if (input) input.classList.add("invalid");
    }

    function validateForm() {
        clearErrors();
        let valid = true;

        const name = form.querySelector("#reg_full_name").value.trim();
        if (!name) {
            showError("full_name", "Full name is required.");
            valid = false;
        }

        const email = form.querySelector("#reg_email").value.trim();
        if (!email) {
            showError("email", "Email is required.");
            valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError("email", "Enter a valid email address.");
            valid = false;
        }

        const people = parseInt(form.querySelector("#reg_no_of_people").value);
        if (!people || people < 1) {
            showError("no_of_people", "At least 1 person required.");
            valid = false;
        } else if (people > 20) {
            showError("no_of_people", "Maximum 20 people allowed.");
            valid = false;
        }

        return valid;
    }

    // ── Submit ─────────────────────────────────────────────────
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        if (!validateForm()) return;

        submitBtn.disabled = true;
        btnText.style.display = "none";
        btnSpinner.style.display = "inline";
        formError.style.display = "none";

        const eventId = eventIdEl.value;
        const formData = new FormData(form);

        try {
            const response = await fetch(`/events/${eventId}/register`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                form.style.display = "none";
                successMsg.textContent = data.message;
                successBox.style.display = "block";

                const emailAddr = form.querySelector("#reg_email").value.trim();
                const emailNote = document.getElementById("regSuccessEmail");
                const emailAddrEl = document.getElementById(
                    "regSuccessEmailAddr",
                );
                if (emailNote && emailAddrEl && emailAddr) {
                    emailAddrEl.textContent = emailAddr;
                    emailNote.style.display = "flex";
                }

                updateSeatCount(eventId, data.new_count);
                swapToRegisteredBadge(eventId);
            } else {
                if (response.status === 409) {
                    closeRegModal();
                    swapToRegisteredBadge(eventId);
                } else {
                    formError.textContent =
                        data.message || "Something went wrong.";
                    formError.style.display = "block";
                }
            }
        } catch (err) {
            formError.textContent = "Network error. Please try again.";
            formError.style.display = "block";
        } finally {
            submitBtn.disabled = false;
            btnText.style.display = "inline";
            btnSpinner.style.display = "none";
        }
    });

    function swapToRegisteredBadge(eventId) {
        document
            .querySelectorAll(
                `[data-event-id="${eventId}"][data-register="true"]`,
            )
            .forEach((link) => {
                link.outerHTML = `
                <span class="event-btn event-btn--registered">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Already Registered
                </span>`;
            });
    }

    // ── Live seat count update on card ─────────────────────────
    function updateSeatCount(eventId, newCount) {
        document
            .querySelectorAll(`[data-event-id="${eventId}"]`)
            .forEach((link) => {
                const card = link.closest(".event-card, .event-detail-sidebar");
                if (!card) return;

                const seatsText = card.querySelector(".event-seats-text");
                const seatsFill = card.querySelector(".event-seats-fill");
                const totalEl = card.querySelector(".event-seats-track");

                if (!seatsText || !totalEl) return;

                const match = seatsText.textContent.match(/\/\s*(\d+)/);
                if (!match) return;

                const total = parseInt(match[1]);
                const pct = Math.min(100, Math.round((newCount / total) * 100));
                const seatsLeft = Math.max(0, total - newCount);

                seatsFill.style.width = pct + "%";
                seatsFill.classList.toggle("near", pct >= 80 && pct < 100);
                seatsFill.classList.toggle("full", pct >= 100);

                if (seatsLeft === 0) {
                    seatsText.innerHTML =
                        '<span class="tag-full">Fully Booked</span>';
                } else if (seatsLeft <= 10) {
                    seatsText.innerHTML = `<span class="tag-near">Only ${seatsLeft} seats left!</span>`;
                } else {
                    seatsText.innerHTML = `${newCount} / ${total} registered`;
                }
            });
    }
})();
