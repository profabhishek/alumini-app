/* public/js/community/confirm-modal.js
 *
 * Beautiful confirm modal — replaces every window.confirm() popup.
 * Fully self-contained: injects its own <style> so it works on any
 * page regardless of which CSS files are loaded.
 *
 * JS API:
 *   window.CommunityConfirm.show({ title, body, confirmText, cancelText, danger })
 *   → returns Promise<boolean>
 *
 * HTML form auto-wire — add data attributes to any <form>:
 *   data-confirm-title   (required)
 *   data-confirm-body    (optional)
 *   data-confirm-text    (optional, default "Confirm")
 *   data-confirm-danger  "true" → red confirm button
 */
(() => {
    "use strict";

    // ── Inject self-contained styles ─────────────────────────────────────
    const style = document.createElement("style");
    style.textContent = `
        .ccm-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(17,24,39,.55);
            backdrop-filter: blur(3px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity .15s ease;
        }
        .ccm-backdrop.ccm-visible { opacity: 1; }
        .ccm-box {
            background: #fff;
            border-radius: 16px;
            padding: 28px 24px 20px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 24px 64px rgba(0,0,0,.18);
            transform: translateY(12px) scale(.97);
            transition: transform .15s ease;
        }
        .ccm-backdrop.ccm-visible .ccm-box {
            transform: translateY(0) scale(1);
        }
        .ccm-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }
        .ccm-icon--danger { background: #fef2f2; color: #dc2626; }
        .ccm-icon--info   { background: #fff7f0; color: #E8640C; }
        .ccm-title {
            font-size: 16px;
            font-weight: 800;
            color: #1C2331;
            margin: 0 0 6px;
            line-height: 1.3;
        }
        .ccm-body {
            font-size: 13.5px;
            color: #6b7280;
            line-height: 1.65;
            margin: 0 0 22px;
        }
        .ccm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .ccm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 20px;
            font-size: 13.5px;
            font-weight: 700;
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            background: #fff;
            color: #374151;
            cursor: pointer;
            transition: background .12s, border-color .12s;
            font-family: inherit;
        }
        .ccm-btn:hover { background: #f3f4f6; }
        .ccm-btn--danger {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff;
        }
        .ccm-btn--danger:hover { background: #b91c1c; border-color: #b91c1c; }
        .ccm-btn--primary {
            background: #E8640C;
            border-color: #E8640C;
            color: #fff;
        }
        .ccm-btn--primary:hover { background: #d35a0a; border-color: #d35a0a; }
    `;
    document.head.appendChild(style);

    // ── Core modal function ──────────────────────────────────────────────
    function show({
        title,
        body = "",
        confirmText = "Confirm",
        cancelText = "Cancel",
        danger = false,
    }) {
        return new Promise((resolve) => {
            const backdrop = document.createElement("div");
            backdrop.className = "ccm-backdrop";

            const dangerIcon = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>`;
            const infoIcon = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;

            backdrop.innerHTML = `
                <div class="ccm-box" role="dialog" aria-modal="true">
                    <div class="ccm-icon ${danger ? "ccm-icon--danger" : "ccm-icon--info"}">
                        ${danger ? dangerIcon : infoIcon}
                    </div>
                    <h3 class="ccm-title">${title}</h3>
                    ${body ? `<p class="ccm-body">${body}</p>` : `<div style="margin-bottom:22px"></div>`}
                    <div class="ccm-actions">
                        <button type="button" class="ccm-btn" data-ccm="cancel">${cancelText}</button>
                        <button type="button" class="ccm-btn ${danger ? "ccm-btn--danger" : "ccm-btn--primary"}" data-ccm="confirm">${confirmText}</button>
                    </div>
                </div>`;

            document.body.appendChild(backdrop);
            requestAnimationFrame(() => backdrop.classList.add("ccm-visible"));

            function cleanup(result) {
                backdrop.classList.remove("ccm-visible");
                setTimeout(() => backdrop.remove(), 150);
                document.removeEventListener("keydown", onKey);
                resolve(result);
            }

            function onKey(e) {
                if (e.key === "Escape") cleanup(false);
                if (e.key === "Enter") cleanup(true);
            }

            backdrop
                .querySelector('[data-ccm="confirm"]')
                .addEventListener("click", () => cleanup(true));
            backdrop
                .querySelector('[data-ccm="cancel"]')
                .addEventListener("click", () => cleanup(false));
            backdrop.addEventListener("click", (e) => {
                if (e.target === backdrop) cleanup(false);
            });
            document.addEventListener("keydown", onKey);
        });
    }

    // ── Auto-wire HTML forms with data-confirm-title ─────────────────────
    document.addEventListener("submit", async (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.hasAttribute("data-confirm-title")) return;
        e.preventDefault();
        const ok = await show({
            title: form.dataset.confirmTitle,
            body: form.dataset.confirmBody || "",
            confirmText: form.dataset.confirmText || "Confirm",
            danger: form.dataset.confirmDanger === "true",
        });
        if (ok) form.submit();
    });

    // ── Export ───────────────────────────────────────────────────────────
    window.CommunityConfirm = { show };

    // Back-compat alias used by the old code
    window.GroupsConfirm = { showConfirmModal: show };
})();
