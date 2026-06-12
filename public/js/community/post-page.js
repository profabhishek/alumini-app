(() => {
    "use strict";

    const config = window.FeedConfig;
    const Core = window.FeedCore;
    if (!config || !Core) return;

    const $ = (sel, root = document) => root.querySelector(sel);

    const el = {
        container: $("#singlePostContainer"),
        lightbox: $("#feedLightbox"),
        lightboxContent: $("#lightboxContent"),
        lightboxClose: $("#lightboxClose"),
        lightboxPrev: $("#lightboxPrev"),
        lightboxNext: $("#lightboxNext"),
    };

    if (!el.container) return;

    const state = {
        posts: new Map(),
    };

    // No repost modal on this page — repost from the home feed instead.
    const controller = Core.createFeedController({
        container: el.container,
        posts: state.posts,
        controllerId: "post-page",
        lightbox: {
            el: el.lightbox,
            content: el.lightboxContent,
            close: el.lightboxClose,
            prev: el.lightboxPrev,
            next: el.lightboxNext,
        },
        repostModal: {}, // not available on this page
        onPostRemoved: () => {
            // If the user deletes the post they're viewing, send them home
            window.location.href = config.routes.feed.replace("/feed", "/home");
        },
    });

    // ── Render the single post ──────────────────────────────────────────

    function render() {
        let post;
        try {
            post = JSON.parse(el.container.dataset.post);
        } catch {
            el.container.innerHTML =
                '<p class="comments-empty">This post could not be loaded.</p>';
            return;
        }

        state.posts.set(post.id, post);

        el.container.innerHTML = Core.postCardMarkup(post, {
            showMenu: true,
            openComments: true,
        });

        // Auto-load comments since the section starts open
        const card = el.container.querySelector(".feed-card");
        const section = card.querySelector(".comments-section");
        if (section && section.dataset.commentsState === "unloaded") {
            controller.loadComments(post.id, section, null);
        }
    }

    render();
})();
