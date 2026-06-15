(() => {
    "use strict";

    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]',
    )?.content;

    function api(url, opts = {}) {
        const headers = new Headers(opts.headers || {});
        headers.set("Accept", "application/json");
        headers.set("X-Requested-With", "XMLHttpRequest");
        if (
            opts.body &&
            !(opts.body instanceof FormData) &&
            !headers.has("Content-Type")
        ) {
            headers.set("Content-Type", "application/json");
        }
        if (opts.method && opts.method !== "GET") {
            headers.set("X-CSRF-TOKEN", csrfToken);
        }
        return fetch(url, {
            credentials: "same-origin",
            ...opts,
            headers,
        }).then(async (res) => {
            let data = {};
            try {
                data = await res.json();
            } catch {
                /* empty */
            }
            if (!res.ok) throw new Error(data.message || "Request failed.");
            return data;
        });
    }

    function toast(msg, type = "info") {
        let region = document.getElementById("feedToastRegion");
        if (!region) {
            region = document.createElement("div");
            region.id = "feedToastRegion";
            region.className = "feed-toast-region";
            document.body.appendChild(region);
        }
        const t = document.createElement("div");
        t.className = `feed-toast feed-toast--${type}`;
        t.textContent = msg;
        region.appendChild(t);
        requestAnimationFrame(() => t.classList.add("is-visible"));
        setTimeout(() => {
            t.classList.remove("is-visible");
            setTimeout(() => t.remove(), 200);
        }, 3200);
    }

    // ── Rich text editor (Quill) ─────────────────────────────────────────

    function initEditor(editorSelector, hiddenInputSelector) {
        const editorEl = document.querySelector(editorSelector);
        const hiddenInput = document.querySelector(hiddenInputSelector);
        if (!editorEl || !hiddenInput || typeof Quill === "undefined")
            return null;

        const quill = new Quill(editorEl, {
            theme: "snow",
            placeholder: "Write the content here...",
            modules: {
                toolbar: [
                    [{ header: [2, 3, false] }],
                    ["bold", "italic", "underline"],
                    [{ list: "ordered" }, { list: "bullet" }],
                    ["link", "blockquote"],
                    ["clean"],
                ],
            },
        });

        // Seed initial content (editing existing item)
        if (hiddenInput.value) {
            quill.clipboard.dangerouslyPasteHTML(hiddenInput.value);
        }

        quill.on("text-change", () => {
            hiddenInput.value = quill.root.innerHTML;
        });

        // Ensure value is synced before submit
        const form = hiddenInput.closest("form");
        form?.addEventListener("submit", () => {
            hiddenInput.value = quill.root.innerHTML;
        });

        return quill;
    }

    // ── Image upload dropzone with preview ───────────────────────────────

    function initImageUpload(
        dropzoneSelector,
        previewWrapSelector,
        inputSelector,
    ) {
        const dropzone = document.querySelector(dropzoneSelector);
        const previewWrap = document.querySelector(previewWrapSelector);
        const input = document.querySelector(inputSelector);
        if (!dropzone || !input) return;

        function showPreview(url) {
            if (!previewWrap) return;
            previewWrap.innerHTML = `
                <img src="${url}" alt="Preview">
                <button type="button" class="admin-image-remove-btn" data-action="remove-image" aria-label="Remove image">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>`;
            previewWrap.hidden = false;
            dropzone.hidden = true;
        }

        input.addEventListener("change", () => {
            const file = input.files[0];
            if (!file) return;

            if (file.size > 4 * 1024 * 1024) {
                toast("Image must be under 4MB.", "error");
                input.value = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => showPreview(e.target.result);
            reader.readAsDataURL(file);
        });

        ["dragover", "dragleave", "drop"].forEach((evt) => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.toggle("is-dragover", evt === "dragover");
            });
        });

        dropzone.addEventListener("drop", (e) => {
            const file = e.dataTransfer.files[0];
            if (file) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event("change"));
            }
        });

        previewWrap?.addEventListener("click", (e) => {
            if (e.target.closest('[data-action="remove-image"]')) {
                input.value = "";
                previewWrap.hidden = true;
                previewWrap.innerHTML = "";
                dropzone.hidden = false;

                // Mark existing image for removal on update forms
                const removeFlag = document.querySelector(
                    'input[name="remove_image"]',
                );
                if (removeFlag) removeFlag.value = "1";
            }
        });
    }

    // ── Category manager modal ───────────────────────────────────────────

    function initCategoryModal(config) {
        const {
            openBtnSelector,
            modalSelector,
            closeSelector,
            listUrl,
            storeUrl,
            updateUrlTpl,
            toggleUrlTpl,
            destroyUrlTpl,
            addInputSelector,
            addBtnSelector,
            listSelector,
            selectSelectors, // array of <select> elements to refresh with categories
        } = config;

        const openBtn = document.querySelector(openBtnSelector);
        const modal = document.querySelector(modalSelector);
        const closeBtn = document.querySelector(closeSelector);
        const addInput = document.querySelector(addInputSelector);
        const addBtn = document.querySelector(addBtnSelector);
        const listEl = document.querySelector(listSelector);

        if (!modal) return;

        function renderList(categories) {
            if (!categories.length) {
                listEl.innerHTML =
                    '<div class="admin-empty-cats">No categories yet. Add one above.</div>';
                return;
            }

            listEl.innerHTML = categories
                .map(
                    (cat) => `
                <div class="admin-cat-row ${cat.status ? "" : "is-inactive"}" data-id="${cat.id}">
                    <input type="text" value="${escapeHtml(cat.name)}" data-action="rename">
                    <span class="admin-cat-row__count">${cat.news_count ?? cat.notices_count ?? 0}</span>
                    <button type="button" class="admin-cat-row__btn" data-action="toggle" title="${cat.status ? "Deactivate" : "Activate"}">
                        ${
                            cat.status
                                ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
                                : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" /><line x1="1" y1="1" x2="23" y2="23"/></svg>'
                        }
                    </button>
                    <button type="button" class="admin-cat-row__btn admin-cat-row__btn--danger" data-action="delete" title="Delete">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                    </button>
                </div>
            `,
                )
                .join("");
        }

        function refreshSelects(categories) {
            const active = categories.filter((c) => c.status);
            selectSelectors.forEach((sel) => {
                document.querySelectorAll(sel).forEach((select) => {
                    const current = select.value;
                    const placeholder =
                        select.querySelector('option[value=""]');
                    select.innerHTML = "";
                    if (placeholder) select.appendChild(placeholder);
                    active.forEach((cat) => {
                        const opt = document.createElement("option");
                        opt.value = cat.id;
                        opt.textContent = cat.name;
                        if (String(cat.id) === current) opt.selected = true;
                        select.appendChild(opt);
                    });
                });
            });
        }

        function escapeHtml(s) {
            return String(s).replace(
                /[&<>"']/g,
                (c) =>
                    ({
                        "&": "&amp;",
                        "<": "&lt;",
                        ">": "&gt;",
                        '"': "&quot;",
                        "'": "&#039;",
                    })[c],
            );
        }

        let cachedCategories = [];

        async function load() {
            try {
                const data = await api(listUrl);
                cachedCategories = data.categories;
                renderList(cachedCategories);
                refreshSelects(cachedCategories);
            } catch (err) {
                toast(err.message, "error");
            }
        }

        function open() {
            modal.hidden = false;
            document.body.style.overflow = "hidden";
            load();
        }

        function close() {
            modal.hidden = true;
            document.body.style.overflow = "";
        }

        openBtn?.addEventListener("click", open);
        closeBtn?.addEventListener("click", close);
        modal.addEventListener("click", (e) => {
            if (e.target === modal) close();
        });

        addBtn?.addEventListener("click", async () => {
            const name = addInput.value.trim();
            if (!name) return;

            try {
                const data = await api(storeUrl, {
                    method: "POST",
                    body: JSON.stringify({ name }),
                });
                cachedCategories.push(data.category);
                renderList(cachedCategories);
                refreshSelects(cachedCategories);
                addInput.value = "";
                toast("Category added.", "success");
            } catch (err) {
                toast(err.message, "error");
            }
        });

        addInput?.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                addBtn.click();
            }
        });

        listEl?.addEventListener("click", async (e) => {
            const row = e.target.closest(".admin-cat-row");
            if (!row) return;
            const id = row.dataset.id;

            const toggleBtn = e.target.closest('[data-action="toggle"]');
            if (toggleBtn) {
                try {
                    const data = await api(toggleUrlTpl.replace("__ID__", id), {
                        method: "PATCH",
                    });
                    const idx = cachedCategories.findIndex((c) => c.id == id);
                    cachedCategories[idx] = data.category;
                    renderList(cachedCategories);
                    refreshSelects(cachedCategories);
                } catch (err) {
                    toast(err.message, "error");
                }
                return;
            }

            const deleteBtn = e.target.closest('[data-action="delete"]');
            if (deleteBtn) {
                if (
                    !confirm(
                        "Delete this category? Items using it will become uncategorized.",
                    )
                )
                    return;
                try {
                    await api(destroyUrlTpl.replace("__ID__", id), {
                        method: "DELETE",
                    });
                    cachedCategories = cachedCategories.filter(
                        (c) => c.id != id,
                    );
                    renderList(cachedCategories);
                    refreshSelects(cachedCategories);
                    toast("Category deleted.", "success");
                } catch (err) {
                    toast(err.message, "error");
                }
                return;
            }
        });

        listEl?.addEventListener(
            "blur",
            async (e) => {
                if (!e.target.matches('[data-action="rename"]')) return;
                const row = e.target.closest(".admin-cat-row");
                const id = row.dataset.id;
                const name = e.target.value.trim();
                const original = cachedCategories.find((c) => c.id == id);
                if (!name || name === original?.name) return;

                try {
                    const data = await api(updateUrlTpl.replace("__ID__", id), {
                        method: "PUT",
                        body: JSON.stringify({ name }),
                    });
                    const idx = cachedCategories.findIndex((c) => c.id == id);
                    cachedCategories[idx] = data.category;
                    refreshSelects(cachedCategories);
                    toast("Category renamed.", "success");
                } catch (err) {
                    toast(err.message, "error");
                    e.target.value = original?.name || "";
                }
            },
            true,
        );
    }

    // ── Inline publish/draft toggle (list view) ──────────────────────────

    function initStatusToggles(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        container.addEventListener("click", async (e) => {
            const btn = e.target.closest("[data-toggle-status-url]");
            if (!btn) return;

            e.preventDefault();
            const url = btn.dataset.toggleStatusUrl;

            try {
                await api(url, { method: "PATCH" });

                const isPublished = btn.classList.contains(
                    "admin-pub-badge--published",
                );
                btn.classList.toggle(
                    "admin-pub-badge--published",
                    !isPublished,
                );
                btn.classList.toggle("admin-pub-badge--draft", isPublished);
                btn.querySelector(".admin-pub-badge__label").textContent =
                    isPublished ? "Draft" : "Published";

                toast(
                    isPublished ? "Moved to draft." : "Published.",
                    "success",
                );
            } catch (err) {
                toast(err.message, "error");
            }
        });
    }

    window.AdminContent = {
        initEditor,
        initImageUpload,
        initCategoryModal,
        initStatusToggles,
        toast,
        api,
    };
})();
