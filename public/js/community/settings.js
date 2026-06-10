/**
 * settings.js — Alumni Community Settings Page
 * Tabs, appearance card toggling, visibility option toggling
 */

/* ── Tab switching ────────────────────────────────────────── */

function activateTab(name) {
    document.querySelectorAll(".settings-tab").forEach((btn) => {
        btn.classList.toggle("active", btn.dataset.tab === name);
    });
    document.querySelectorAll(".settings-tab-panel").forEach((panel) => {
        panel.classList.toggle("active", panel.id === `tab-${name}`);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    // Tab click
    document.querySelectorAll(".settings-tab").forEach((btn) => {
        btn.addEventListener("click", () => activateTab(btn.dataset.tab));
    });

    // ── Appearance cards ───────────────────────────────────────────────────
    document.querySelectorAll(".appearance-card").forEach((card) => {
        card.addEventListener("click", () => {
            document
                .querySelectorAll(".appearance-card")
                .forEach((c) => c.classList.remove("active"));
            card.classList.add("active");
            card.querySelector('input[type="radio"]').checked = true;
        });
    });

    // ── Visibility options ─────────────────────────────────────────────────
    document.querySelectorAll(".visibility-option").forEach((opt) => {
        opt.addEventListener("click", () => {
            document
                .querySelectorAll(".visibility-option")
                .forEach((o) => o.classList.remove("active"));
            opt.classList.add("active");
            opt.querySelector('input[type="radio"]').checked = true;
        });
    });
});
