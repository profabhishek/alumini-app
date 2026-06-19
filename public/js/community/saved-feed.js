(() => {
    "use strict";

    const config = window.FeedConfig;
    const Core = window.FeedCore;
    if (!config || !Core) return;

    const $ = (sel, root = document) => root.querySelector(sel);

    const container = document.getElementById("savedFeedList");
    if (!container) return;

    const el = {
        list: container,
        skeleton: $("#savedFeedSkeleton"),
        empty: $("#savedFeedEmpty"),
        end: $("#savedFeedEnd"),
        loader: $("#savedFeedLoader"),
        lightbox: $("#feedLightbox"),
        lightboxContent: $("#lightboxContent"),
        lightboxClose: $("#lightboxClose"),
        lightboxPrev: $("#lightboxPrev"),
        lightboxNext: $("#lightboxNext"),
        repostModal: $("#shareModal"),
        repostModalClose: $("#shareModalClose"),
        repostCancelBtn: $("#shareCancelBtn"),
        repostConfirmBtn: $("#shareConfirmBtn"),
        repostCaption: $("#shareCaption"),
        repostPreviewWrap: $("#sharePreviewWrap"),
    };

    const state = {
        posts: new Map(),
        oldestId: null,
        hasMore: true,
        loading: false,
        initialized: false,
    };

    const controller = Core.createFeedController({
        container: el.list,
        posts: state.posts,
        controllerId: "saved-feed",
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
        // When "Save" is toggled off from this tab, remove the card from the list
        onPostRemovedFromSaved: (postId) => controller.removePostCard(postId),
        onPostRemoved: () => {
            if (!el.list.querySelector(".feed-card")) {
                el.empty.hidden = false;
            }
        },
        onPostPrepended: () => {
            // Reposting from the saved tab adds to the home feed, not this list.
        },
    });

    // ── Load saved posts ─────────────────────────────────────────────────

    async function loadSavedFeed() {
        if (state.loading || !state.hasMore) return;
        state.loading = true;
        el.loader.hidden = false;

        try {
            const params = new URLSearchParams({ limit: 10 });
            if (state.oldestId) params.set("before_id", state.oldestId);

            const data = await Core.api(
                `${config.routes.saved}?${params.toString()}`,
            );

            el.skeleton?.remove();

            const posts = data.posts || [];
            for (const post of posts) {
                state.posts.set(post.id, post);
                el.list.insertAdjacentHTML(
                    "beforeend",
                    Core.postCardMarkup(post),
                );
            }

            state.hasMore = Boolean(data.has_more);
            state.oldestId = data.oldest_id || state.oldestId;

            if (!state.hasMore) {
                el.end.hidden = false;
            }

            // Only show the empty state once we know for certain there are
            // zero posts in total (no more pages AND nothing rendered yet).
            if (state.posts.size === 0 && !state.hasMore) {
                el.empty.hidden = false;
            } else {
                el.empty.hidden = true;
            }
        } catch (err) {
            Core.toast(err.message, "error");
        } finally {
            state.loading = false;
            el.loader.hidden = true;
        }
    }

    function setupInfiniteScroll() {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) loadSavedFeed();
                }
            },
            { rootMargin: "400px" },
        );
        observer.observe(el.loader);
    }

    // ── Public init — called when the Saved tab is first opened ─────────

    window.initSavedFeed = function () {
        if (state.initialized) return;
        state.initialized = true;
        setupInfiniteScroll();
        loadSavedFeed();
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? "";
        const bc = window.FeedConfig?.routes?.batchCounts;
        if (Core.createLiveSync && bc) Core.createLiveSync(el.list, state.posts, csrf, bc);
    };
})();
