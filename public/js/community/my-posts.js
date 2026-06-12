(() => {
    "use strict";

    const config = window.FeedConfig;
    const Core = window.FeedCore;
    if (!config || !Core) return;

    const $ = (sel, root = document) => root.querySelector(sel);

    const container = document.getElementById("myPostsList");
    if (!container) return;

    const el = {
        list: container,
        skeleton: $("#myPostsSkeleton"),
        empty: $("#myPostsEmpty"),
        end: $("#myPostsEnd"),
        loader: $("#myPostsLoader"),
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
        controllerId: "my-posts",
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
        onPostRemoved: (postId) => {
            if (!el.list.querySelector(".feed-card")) {
                el.empty.hidden = false;
            }
        },
        onPostPrepended: (post) => {
            // A repost from this tab also belongs to "my posts" — show it at top
            state.posts.set(post.id, post);
            el.list.insertAdjacentHTML("afterbegin", Core.postCardMarkup(post));
            el.empty.hidden = true;
        },
    });

    // ── Load my posts ────────────────────────────────────────────────────

    async function loadMyPosts() {
        if (state.loading || !state.hasMore) return;
        state.loading = true;
        el.loader.hidden = false;

        try {
            const params = new URLSearchParams({ limit: 10 });
            if (state.oldestId) params.set("before_id", state.oldestId);

            const data = await Core.api(
                `${config.routes.myPosts}?${params.toString()}`,
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
                    if (entry.isIntersecting) loadMyPosts();
                }
            },
            { rootMargin: "400px" },
        );
        observer.observe(el.loader);
    }

    // ── Public init — called when the My Posts tab is first opened ─────

    window.initMyPosts = function () {
        if (state.initialized) return;
        state.initialized = true;
        setupInfiniteScroll();
        loadMyPosts();
    };
})();
