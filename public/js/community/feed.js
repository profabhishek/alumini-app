/**
 * FEED.JS — Home feed (composer + infinite scroll feed)
 * Relies on feed-core.js (window.FeedCore) for rendering + interactions.
 */
(() => {
    "use strict";

    const config = window.FeedConfig;
    const Core = window.FeedCore;
    if (!config || !Core) return;

    const $ = (sel, root = document) => root.querySelector(sel);

    const el = {
        feedList: $("#feedList"),
        feedSkeleton: $("#feedSkeleton"),
        feedEnd: $("#feedEnd"),
        feedLoader: $("#feedLoader"),

        // Composer
        postTextarea: $("#postTextarea"),
        postBtn: $("#postBtn"),
        mediaPreview: $("#composerMediaPreview"),
        attachPhotoBtn: $("#attachPhotoBtn"),
        attachVideoBtn: $("#attachVideoBtn"),
        photoInput: $("#photoInput"),
        videoInput: $("#videoInput"),

        // Repost modal
        repostModal: $("#shareModal"),
        repostModalClose: $("#shareModalClose"),
        repostCancelBtn: $("#shareCancelBtn"),
        repostConfirmBtn: $("#shareConfirmBtn"),
        repostCaption: $("#shareCaption"),
        repostPreviewWrap: $("#sharePreviewWrap"),

        // Lightbox
        lightbox: $("#feedLightbox"),
        lightboxContent: $("#lightboxContent"),
        lightboxClose: $("#lightboxClose"),
        lightboxPrev: $("#lightboxPrev"),
        lightboxNext: $("#lightboxNext"),
    };

    if (!el.feedList) return;

    const state = {
        posts: new Map(),
        oldestId: null,
        hasMore: true,
        loading: false,
        composerFiles: [],
        composerMediaType: null,
    };

    // ── Controller (shared interactions: like/save/comment/repost/share/lightbox) ──

    const controller = Core.createFeedController({
        container: el.feedList,
        posts: state.posts,
        controllerId: "home",
        lightbox: {
            el: el.lightbox,
            content: el.lightboxContent,
            close: el.lightboxClose,
            prev: el.lightboxPrev,
            next: el.lightboxNext,
        },
        repostModal: {
            el: el.repostModal,
            close: el.repostModalClose,
            cancel: el.repostCancelBtn,
            confirm: el.repostConfirmBtn,
            caption: el.repostCaption,
            previewWrap: el.repostPreviewWrap,
        },
        onPostPrepended: (post) => prependPost(post),
        onPostRemoved: () => {},
    });

    // ── Composer ─────────────────────────────────────────────────────────

    function updatePostButtonState() {
        const hasText = el.postTextarea.value.trim().length > 0;
        const hasMedia = state.composerFiles.length > 0;
        el.postBtn.disabled = !(hasText || hasMedia);
    }

    function renderComposerPreview() {
        if (!state.composerFiles.length) {
            el.mediaPreview.hidden = true;
            el.mediaPreview.innerHTML = "";
            el.mediaPreview.classList.remove("is-video");
            el.attachPhotoBtn.disabled = false;
            el.attachVideoBtn.disabled = false;
            return;
        }

        el.mediaPreview.hidden = false;
        el.mediaPreview.classList.toggle(
            "is-video",
            state.composerMediaType === "video",
        );

        el.mediaPreview.innerHTML = state.composerFiles
            .map((file, i) => {
                const url = URL.createObjectURL(file);
                const inner =
                    state.composerMediaType === "video"
                        ? `<video src="${url}" muted></video>`
                        : `<img src="${url}" alt="">`;
                return `<div class="composer-media-item" data-index="${i}">
                ${inner}
                <button type="button" class="composer-media-remove" data-remove="${i}" aria-label="Remove">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>`;
            })
            .join("");

        el.attachPhotoBtn.disabled = state.composerMediaType === "video";
        el.attachVideoBtn.disabled = state.composerMediaType === "image";
    }

    function clearComposer() {
        el.postTextarea.value = "";
        Core.resizeTextarea(el.postTextarea);
        state.composerFiles = [];
        state.composerMediaType = null;
        renderComposerPreview();
        updatePostButtonState();
    }

    el.postTextarea.addEventListener("input", () => {
        Core.resizeTextarea(el.postTextarea);
        updatePostButtonState();
    });

    el.attachPhotoBtn.addEventListener("click", () => el.photoInput.click());
    el.attachVideoBtn.addEventListener("click", () => el.videoInput.click());

    el.photoInput.addEventListener("change", () => {
        const files = [...el.photoInput.files];
        if (!files.length) return;

        const remaining = 10 - state.composerFiles.length;
        const accepted = files.slice(0, remaining);

        for (const f of accepted) {
            if (f.size > 10 * 1024 * 1024) {
                Core.toast(
                    `"${f.name}" is over 10MB and was skipped.`,
                    "error",
                );
                continue;
            }
            state.composerFiles.push(f);
        }

        state.composerMediaType = "image";
        renderComposerPreview();
        updatePostButtonState();
        el.photoInput.value = "";
    });

    el.videoInput.addEventListener("change", () => {
        const file = el.videoInput.files[0];
        if (!file) return;

        if (file.size > 25 * 1024 * 1024) {
            Core.toast("Video must be under 25MB.", "error");
            el.videoInput.value = "";
            return;
        }

        state.composerFiles = [file];
        state.composerMediaType = "video";
        renderComposerPreview();
        updatePostButtonState();
        el.videoInput.value = "";
    });

    el.mediaPreview.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-remove]");
        if (!btn) return;
        const idx = Number(btn.dataset.remove);
        state.composerFiles.splice(idx, 1);
        if (!state.composerFiles.length) state.composerMediaType = null;
        renderComposerPreview();
        updatePostButtonState();
    });

    el.postBtn.addEventListener("click", async () => {
        if (el.postBtn.disabled) return;

        const body = el.postTextarea.value.trim();
        const formData = new FormData();
        if (body) formData.append("body", body);
        state.composerFiles.forEach((f) => formData.append("media[]", f));

        el.postBtn.disabled = true;
        el.postBtn.classList.add("is-loading");
        const originalLabel = el.postBtn.textContent;
        el.postBtn.innerHTML = '<span class="post-spinner"></span>Posting...';

        try {
            const data = await Core.api(config.routes.store, {
                method: "POST",
                body: formData,
            });
            clearComposer();
            prependPost(data.post);
            Core.toast("Posted!", "success");
        } catch (err) {
            Core.toast(err.message, "error");
        } finally {
            el.postBtn.classList.remove("is-loading");
            el.postBtn.textContent = originalLabel;
            updatePostButtonState();
        }
    });

    // ── Feed rendering ───────────────────────────────────────────────────

    function prependPost(post) {
        state.posts.set(post.id, post);
        el.feedList.insertAdjacentHTML("afterbegin", Core.postCardMarkup(post));
    }

    function appendPosts(postsArr) {
        for (const post of postsArr) {
            state.posts.set(post.id, post);
            el.feedList.insertAdjacentHTML(
                "beforeend",
                Core.postCardMarkup(post),
            );
        }
    }

    // ── Infinite scroll / feed loading ──────────────────────────────────

    async function loadFeed() {
        if (state.loading || !state.hasMore) return;
        state.loading = true;
        el.feedLoader.hidden = false;

        try {
            const params = new URLSearchParams({ limit: 10 });
            if (state.oldestId) params.set("before_id", state.oldestId);

            const data = await Core.api(
                `${config.routes.feed}?${params.toString()}`,
            );

            el.feedSkeleton?.remove();

            appendPosts(data.posts || []);
            state.hasMore = Boolean(data.has_more);
            state.oldestId = data.oldest_id || state.oldestId;

            if (!state.hasMore) {
                el.feedEnd.hidden = false;
            }
        } catch (err) {
            Core.toast(err.message, "error");
        } finally {
            state.loading = false;
            el.feedLoader.hidden = true;
        }
    }

    function setupInfiniteScroll() {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) loadFeed();
                }
            },
            { rootMargin: "400px" },
        );

        observer.observe(el.feedLoader);
    }

    // ── Boot ─────────────────────────────────────────────────────────────

    setupInfiniteScroll();
    loadFeed();
})();
