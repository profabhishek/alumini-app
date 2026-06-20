(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // ── Toast ────────────────────────────────────────────────
    function showToast(message, type = "success") {
        document.querySelectorAll(".nlsub-toast").forEach((t) => t.remove());

        const toast = document.createElement("div");
        toast.className = `nlsub-toast nlsub-toast--${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add("show"));

        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // ── Add Subscriber modal ─────────────────────────────────
    const addBtn = document.getElementById("addSubscriberBtn");
    const addModal = document.getElementById("addSubscriberModal");
    const closeAddBtn = document.getElementById("closeAddModal");
    const cancelBtn = document.getElementById("cancelAddModal");
    const addForm = document.getElementById("addSubscriberForm");
    const emailInput = document.getElementById("newSubEmail");
    const emailError = document.getElementById("newSubEmailError");
    const submitBtn = document.getElementById("addSubscriberSubmit");
    const tableBody = document.getElementById("nlsubTableBody");

    function openAddModal() {
        addForm.reset();
        emailError.textContent = "";
        emailInput.classList.remove("invalid");
        addModal.hidden = false;
        emailInput.focus();
    }

    function closeAddModal() {
        addModal.hidden = true;
    }

    if (addBtn) addBtn.addEventListener("click", openAddModal);
    if (closeAddBtn) closeAddBtn.addEventListener("click", closeAddModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeAddModal);

    if (addModal) {
        addModal.addEventListener("click", function (e) {
            if (e.target === addModal) closeAddModal();
        });
    }

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && addModal && !addModal.hidden) closeAddModal();
    });

    if (addForm) {
        addForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            emailError.textContent = "";
            emailInput.classList.remove("invalid");
            submitBtn.disabled = true;

            try {
                const response = await fetch(
                    addForm.action || window.location.href,
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                        },
                        body: new FormData(addForm),
                    },
                );

                const data = await response.json();

                if (response.ok && data.success) {
                    showToast(data.message, "success");
                    closeAddModal();
                    // Reload to show the new subscriber + updated stats/pagination
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    const msg =
                        data.errors?.email?.[0] ||
                        data.message ||
                        "Something went wrong.";
                    emailError.textContent = msg;
                    emailInput.classList.add("invalid");
                }
            } catch (err) {
                showToast("Network error. Please try again.", "error");
            } finally {
                submitBtn.disabled = false;
            }
        });

        // The form has no action attribute in the markup — set it here
        // so we don't repeat the route() call in two places.
        addForm.action = window.NLSUB_STORE_URL || addForm.action;
    }

    // ── Toggle status / Delete (event delegation) ────────────
    if (tableBody) {
        tableBody.addEventListener("click", async function (e) {
            const toggleBtn = e.target.closest(".nlsub-toggle-btn");
            const deleteBtn = e.target.closest(".nlsub-delete-btn");

            if (toggleBtn) {
                const id = toggleBtn.dataset.id;
                const url = window.NLSUB_TOGGLE_URL_TEMPLATE.replace(
                    "__ID__",
                    id,
                );

                toggleBtn.disabled = true;

                try {
                    const response = await fetch(url, {
                        method: "PATCH",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                        },
                    });
                    const data = await response.json();

                    if (data.success) {
                        const row = toggleBtn.closest("tr");
                        const badge = row.querySelector("[data-status-badge]");

                        badge.textContent =
                            data.status === "active"
                                ? "Active"
                                : "Unsubscribed";
                        badge.className = `nlsub-badge nlsub-badge--${data.status}`;

                        toggleBtn.dataset.status = data.status;
                        toggleBtn.title =
                            data.status === "active"
                                ? "Unsubscribe"
                                : "Resubscribe";
                        toggleBtn.innerHTML =
                            data.status === "active"
                                ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>'
                                : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>';

                        showToast(data.message, "success");
                    } else {
                        showToast(
                            data.message || "Something went wrong.",
                            "error",
                        );
                    }
                } catch (err) {
                    showToast("Network error. Please try again.", "error");
                } finally {
                    toggleBtn.disabled = false;
                }
            }

            if (deleteBtn) {
                const id = deleteBtn.dataset.id;
                const email = deleteBtn.dataset.email;

                const confirmed = await window.adminConfirm(
                    `Remove ${email} from the subscriber list? This cannot be undone.`,
                    'Delete'
                );
                if (!confirmed) return;

                const url = window.NLSUB_DELETE_URL_TEMPLATE.replace(
                    "__ID__",
                    id,
                );

                deleteBtn.disabled = true;

                try {
                    const response = await fetch(url, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                        },
                    });
                    const data = await response.json();

                    if (data.success) {
                        const row = deleteBtn.closest("tr");
                        row.remove();
                        showToast(data.message, "success");

                        if (!tableBody.querySelector("tr")) {
                            tableBody.innerHTML =
                                '<tr><td colspan="5" class="nlsub-empty">No subscribers yet.</td></tr>';
                        }
                    } else {
                        showToast(
                            data.message || "Something went wrong.",
                            "error",
                        );
                    }
                } catch (err) {
                    showToast("Network error. Please try again.", "error");
                } finally {
                    deleteBtn.disabled = false;
                }
            }
        });
    }
})();
