(() => {
    "use strict";

    const config = window.FeedConfig;
    if (!config) return;

    // ── Utilities ────────────────────────────────────────────────────────

    function esc(v) {
        return String(v ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    function escAttr(v) {
        return esc(v).replaceAll("`", "&#096;");
    }

    function route(tpl, replacements) {
        let url = tpl;
        for (const [key, val] of Object.entries(replacements)) {
            url = url.replace(key, val);
        }
        return url;
    }

    function timeAgo(iso) {
        if (!iso) return "";
        const d = new Date(iso);
        const diff = (Date.now() - d.getTime()) / 1000;
        if (diff < 60) return "Just now";
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
        return d.toLocaleDateString([], {
            day: "numeric",
            month: "short",
            year: "numeric",
        });
    }

    function resizeTextarea(textarea) {
        textarea.style.height = "auto";
        textarea.style.height = `${Math.min(textarea.scrollHeight, 160)}px`;
    }

    async function api(url, opts = {}) {
        const headers = new Headers(opts.headers || {});
        headers.set("Accept", "application/json");
        headers.set("X-Requested-With", "XMLHttpRequest");
        if (
            !(opts.body instanceof FormData) &&
            opts.body &&
            !headers.has("Content-Type")
        ) {
            headers.set("Content-Type", "application/json");
        }
        if (opts.method && opts.method !== "GET") {
            headers.set("X-CSRF-TOKEN", config.csrfToken);
        }
        const res = await fetch(url, {
            credentials: "same-origin",
            ...opts,
            headers,
        });
        let payload = {};
        try {
            payload = await res.json();
        } catch {
            payload = {};
        }
        if (!res.ok) {
            const msg = payload.errors
                ? Object.values(payload.errors).flat().find(Boolean)
                : payload.error || payload.message || "Something went wrong.";
            throw new Error(msg);
        }
        return payload;
    }

    let toastRegion = null;
    function toast(msg, type = "info") {
        if (!toastRegion)
            toastRegion =
                document.getElementById("feedToastRegion") || document.body;
        const t = document.createElement("div");
        t.className = `feed-toast feed-toast--${type}`;
        t.textContent = msg;
        toastRegion.appendChild(t);
        requestAnimationFrame(() => t.classList.add("is-visible"));
        setTimeout(() => {
            t.classList.remove("is-visible");
            setTimeout(() => t.remove(), 200);
        }, 3200);
    }

    function avatarMarkup(entity, cls = "avatar avatar--sm") {
        const name = entity?.name || "?";
        const initials =
            entity?.initials || name.charAt(0).toUpperCase() || "?";
        if (entity?.avatar) {
            return `<div class="${cls}"><img src="${escAttr(entity.avatar)}" alt="${escAttr(name)}" loading="lazy"></div>`;
        }
        return `<div class="${cls}"><span class="avatar-initials">${esc(initials)}</span></div>`;
    }

    // ── Icons ────────────────────────────────────────────────────────────

    function likeIconSvg(filled) {
        return `<svg width="18" height="18" viewBox="0 0 24 24" fill="${filled ? "currentColor" : "none"}" stroke="currentColor" stroke-width="2">
            <path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3z"/>
            <path d="M7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/>
        </svg>`;
    }

    // Repost = circular arrows (re-share to your own feed)
    function repostIconSvg() {
        return `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 1l4 4-4 4"/>
            <path d="M3 11V9a4 4 0 014-4h14"/>
            <path d="M7 23l-4-4 4-4"/>
            <path d="M21 13v2a4 4 0 01-4 4H3"/>
        </svg>`;
    }

    // Share = paper plane (copy / share link)
    function sendIconSvg() {
        return `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"/>
            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>`;
    }

    function saveIconSvg(filled) {
        return `<svg width="18" height="18" viewBox="0 0 24 24" fill="${filled ? "currentColor" : "none"}" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>`;
    }

    // ── Markup renderers ─────────────────────────────────────────────────

    function mediaGridMarkup(media, postId) {
        if (!media || !media.length) return "";

        if (media[0].type === "video") {
            const m = media[0];
            return `<div class="video-wrap fv-player" data-fv-player>
                <video class="fv-video" playsinline muted loop data-src="${escAttr(m.url)}"></video>
                <div class="fv-overlay" data-fv-overlay>
                    <button class="fv-play-btn" data-fv-toggle-play type="button" aria-label="Play">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                </div>
                <div class="fv-controls">
                    <button class="fv-btn fv-btn--play" data-fv-toggle-play type="button" aria-label="Play/Pause">
                        <svg class="fv-icon-play" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        <svg class="fv-icon-pause" viewBox="0 0 24 24" fill="currentColor" hidden><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
                    </button>
                    <div class="fv-progress" data-fv-progress>
                        <div class="fv-progress-fill" data-fv-progress-fill></div>
                    </div>
                    <span class="fv-time" data-fv-time>0:00</span>
                    <button class="fv-btn fv-btn--mute" data-fv-toggle-mute type="button" aria-label="Mute/Unmute">
                        <svg class="fv-icon-mute-on" viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 12A4.5 4.5 0 0014 7.97v8.05a4.5 4.5 0 002.5-4.02zM19 12c0 .94-.2 1.82-.54 2.64l1.51 1.51A8.943 8.943 0 0021 12c0-4.28-3.05-7.85-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM3 9v6h4l5 5V4L7 9H3z"/></svg>
                        <svg class="fv-icon-mute-off" viewBox="0 0 24 24" fill="currentColor" hidden><path d="M3 9v6h4l5 5V4L7 9H3z"/><path d="M19 5L5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
                    </button>
                    <button class="fv-btn fv-btn--expand" data-fv-expand type="button" aria-label="Fullscreen">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/></svg>
                    </button>
                </div>
            </div>`;
        }

        const count = media.length;
        let gridClass = "photo-grid--1";
        if (count === 2) gridClass = "photo-grid--2";
        else if (count === 3) gridClass = "photo-grid--3";
        else if (count === 4) gridClass = "photo-grid--4";
        else if (count > 4) gridClass = "photo-grid--many";

        const visible = media.slice(0, 4);
        const extra = count - 4;

        const tiles = visible
            .map((m, i) => {
                const isLastVisible = i === 3 && extra > 0;
                return `<div class="photo-tile" data-media-index="${i}">
                <img src="${escAttr(m.url)}" alt="" loading="lazy">
                ${isLastVisible ? `<div class="photo-tile__more">+${extra}</div>` : ""}
            </div>`;
            })
            .join("");

        return `<div class="photo-grid ${gridClass}" data-post-id="${postId}">${tiles}</div>`;
    }

    function sharedPostMarkup(shared) {
        if (!shared) return "";
        const author = shared.author;
        return `<div class="feed-shared-inner" data-post-id="${shared.id}" data-navigable="true">
            <div class="card-header">
                <div class="post-meta">
                    ${avatarMarkup(author, "avatar avatar--sm")}
                    <div class="post-info">
                        <span class="post-author">${esc(author.name)}</span>
                        <span class="post-time">${esc(timeAgo(shared.created_at))}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                ${shared.body ? `<p class="post-text">${esc(shared.body).replaceAll("\n", "<br>")}</p>` : ""}
                ${mediaGridMarkup(shared.media, shared.id)}
            </div>
        </div>`;
    }

    /**
     * @param {object} post - post data (toFeedArray shape)
     * @param {object} opts
     *   - showMenu: boolean (default true)
     *   - extraMenuItems: string (html) inserted before standard items
     *   - openComments: boolean - render comments section expanded
     */
    function postCardMarkup(post, opts = {}) {
        const author = post.author;
        const isShare = Boolean(post.shared_post);
        const showMenu = opts.showMenu !== false;
        const openComments = Boolean(opts.openComments);

        const menuItems = post.is_mine
            ? `<button class="card-menu-item card-menu-item--danger" data-action="delete-post" data-post-id="${post.id}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6"/></svg>
                Delete post
            </button>`
            : `<button class="card-menu-item" data-action="report-post" data-post-id="${post.id}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86l-8.18 14.18A2 2 0 003.93 21h16.14a2 2 0 001.82-2.96L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Report post
            </button>`;

        const extraMenu = opts.extraMenuItems || "";

        return `<article class="feed-card card" data-post-id="${post.id}">
            ${
                isShare
                    ? `
                <div class="feed-share-header">
                    ${repostIconSvg()}
                    <span>${esc(author.name)} reposted</span>
                </div>
            `
                    : ""
            }
            <div class="card-header">
                <div class="post-meta" ${!isShare ? `data-post-id="${post.id}" data-navigable="true"` : ""}>
                    ${avatarMarkup(author, "avatar avatar--sm")}
                    <div class="post-info">
                        <span class="post-author">${esc(author.name)}</span>
                        <span class="post-time">${esc(timeAgo(post.created_at))}${author.job_title ? ` &middot; <span class="post-badge">${esc(author.job_title)}</span>` : ""}</span>
                    </div>
                </div>
                ${
                    showMenu
                        ? `
                <div class="card-menu-wrap">
                    <button class="card-menu-btn" data-action="toggle-menu" title="More options">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                    </button>
                    <div class="card-menu-dropdown" hidden>${extraMenu}${menuItems}</div>
                </div>`
                        : ""
                }
            </div>
            <div class="card-body" ${!isShare ? `data-post-id="${post.id}" data-navigable="body"` : ""}>
                ${post.body ? `<p class="post-text">${esc(post.body).replaceAll("\n", "<br>")}</p>` : ""}
                ${isShare ? sharedPostMarkup(post.shared_post) : mediaGridMarkup(post.media, post.id)}
            </div>

            <div class="card-reactions" ${post.likes_count === 0 && post.comments_count === 0 && post.shares_count === 0 ? "hidden" : ""}>
                <div class="card-reactions__likes" ${post.likes_count === 0 ? "hidden" : ""}>
                    <span class="like-icon-stack">${likeIconSvg(true)}</span>
                    <span class="likes-count-text">${post.likes_count ?? 0}</span>
                </div>
                <div class="card-reactions__right">
                    ${post.comments_count > 0 ? `<span data-action="toggle-comments">${post.comments_count} comment${post.comments_count !== 1 ? "s" : ""}</span>` : ""}
                    ${post.shares_count > 0 ? `<span>${post.shares_count} repost${post.shares_count !== 1 ? "s" : ""}</span>` : ""}
                </div>
            </div>

            <div class="card-actions">
                <button class="action-btn like-btn ${post.is_liked ? "is-active" : ""}" data-action="toggle-like" data-liked="${post.is_liked}">
                    ${likeIconSvg(post.is_liked)}
                    <span class="action-count">Like</span>
                </button>
                <button class="action-btn comment-btn" data-action="toggle-comments">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span class="action-count">Comment</span>
                </button>
                ${
                    !post.group_id
                        ? `
                <button class="action-btn repost-btn" data-action="open-repost" title="Repost to your feed">
                    ${repostIconSvg()}
                    <span class="action-count">Repost</span>
                </button>`
                        : ""
                }
                
                <button class="action-btn share-link-btn" data-action="share-link" title="Share link">
                    ${sendIconSvg()}
                    <span class="action-count">Share</span>
                </button>
                <button class="action-btn save-btn ${post.is_saved ? "is-active" : ""}" data-action="toggle-save">
                    ${saveIconSvg(post.is_saved)}
                    <span class="action-count">${post.is_saved ? "Saved" : "Save"}</span>
                </button>
            </div>

            <div class="comments-section" ${openComments ? "" : "hidden"} data-comments-state="unloaded">
                <div class="comment-composer">
                    ${avatarMarkup({ name: config.currentUserName, avatar: config.currentUserAvatar, initials: config.currentUserInitials }, "avatar avatar--sm")}
                    <div class="comment-input-wrap">
                        <textarea class="comment-input" rows="1" maxlength="2000" placeholder="Write a comment..." data-post-id="${post.id}"></textarea>
                        <button class="comment-send-btn" data-action="send-comment" data-post-id="${post.id}" disabled>
                            ${sendIconSvg()}
                        </button>
                    </div>
                </div>
                <div class="comment-list" data-post-id="${post.id}"></div>
            </div>
        </article>`;
    }

    function replyMarkup(reply, postId, parentId) {
        const author = reply.author;
        return `<div class="reply-item" data-comment-id="${reply.id}">
            ${avatarMarkup(author, "avatar avatar--sm")}
            <div class="comment-body-wrap">
                <div class="comment-bubble">
                    <span class="comment-author">${esc(author.name)}</span>
                    <span class="comment-text">${esc(reply.body).replaceAll("\n", "<br>")}</span>
                </div>
                <div class="comment-meta">
                    <span>${esc(timeAgo(reply.created_at))}</span>
                    <button data-action="toggle-comment-like" data-comment-id="${reply.id}" data-post-id="${postId}" class="${reply.is_liked ? "is-active" : ""}">
                        Like${reply.likes_count > 0 ? ` <span class="comment-like-count">(${reply.likes_count})</span>` : ""}
                    </button>
                    ${reply.is_mine ? `<button data-action="delete-comment" data-comment-id="${reply.id}" data-post-id="${postId}" data-parent-id="${parentId}">Delete</button>` : ""}
                </div>
            </div>
        </div>`;
    }

    function commentMarkup(comment, postId) {
        const author = comment.author;
        const repliesHtml = (comment.replies || [])
            .map((r) => replyMarkup(r, postId, comment.id))
            .join("");

        return `<div class="comment-item" data-comment-id="${comment.id}">
            ${avatarMarkup(author, "avatar avatar--sm")}
            <div class="comment-body-wrap">
                <div class="comment-bubble">
                    <span class="comment-author">${esc(author.name)}</span>
                    <span class="comment-text">${esc(comment.body).replaceAll("\n", "<br>")}</span>
                </div>
                <div class="comment-meta">
                    <span>${esc(timeAgo(comment.created_at))}</span>
                    <button data-action="toggle-comment-like" data-comment-id="${comment.id}" data-post-id="${postId}" class="${comment.is_liked ? "is-active" : ""}">
                        Like${comment.likes_count > 0 ? ` <span class="comment-like-count">(${comment.likes_count})</span>` : ""}
                    </button>
                    <button data-action="show-reply-box" data-comment-id="${comment.id}" data-post-id="${postId}">Reply</button>
                    ${comment.is_mine ? `<button data-action="delete-comment" data-comment-id="${comment.id}" data-post-id="${postId}">Delete</button>` : ""}
                </div>
                <div class="reply-composer" hidden data-reply-box-for="${comment.id}">
                    ${avatarMarkup({ name: config.currentUserName, avatar: config.currentUserAvatar, initials: config.currentUserInitials }, "avatar avatar--sm")}
                    <div class="comment-input-wrap">
                        <textarea class="comment-input" rows="1" maxlength="2000" placeholder="Write a reply..." data-post-id="${postId}" data-parent-id="${comment.id}"></textarea>
                        <button class="comment-send-btn" data-action="send-comment" data-post-id="${postId}" data-parent-id="${comment.id}" disabled>
                            ${sendIconSvg()}
                        </button>
                    </div>
                </div>
                <div class="reply-list" data-replies-for="${comment.id}" ${repliesHtml ? "" : "hidden"}>${repliesHtml}</div>
            </div>
        </div>`;
    }

    // ── Controller factory ──────────────────────────────────────────────

    /**
     * Creates an interaction controller bound to a container element.
     * The container receives delegated click/input/keydown listeners.
     *
     * @param {object} options
     *   - container: HTMLElement - the list container (e.g. #feedList)
     *   - posts: Map - shared state map of postId -> post object
     *   - onPostRemoved: (postId) => void - called after successful delete/unsave
     *   - onPostPrepended: (post) => void - called after repost succeeds (for the active feed)
     *   - lightbox: { el, content, close, prev, next } - lightbox elements (optional)
     *   - repostModal: { el, close, cancel, confirm, caption, previewWrap } - repost modal elements (optional)
     */
    function createFeedController(options) {
        const { container, posts } = options;
        const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

        const lb = options.lightbox || {};
        const rm = options.repostModal || {};

        const lbState = { media: [], index: 0 };
        const rmState = { targetId: null };

        // ── Like ─────────────────────────────────────────────────────

        async function handleToggleLike(btn, postId) {
            const wasLiked = btn.dataset.liked === "true";
            btn.dataset.liked = String(!wasLiked);
            btn.classList.toggle("is-active", !wasLiked);
            btn.classList.add("is-popping");
            setTimeout(() => btn.classList.remove("is-popping"), 350);

            const svg = btn.querySelector("svg");
            svg.setAttribute("fill", !wasLiked ? "currentColor" : "none");

            const card = btn.closest(".feed-card");
            const reactions = card.querySelector(".card-reactions");
            const likesWrap = card.querySelector(".card-reactions__likes");
            const countEl = card.querySelector(".likes-count-text");

            try {
                const data = await api(
                    route(config.routes.like, { __ID__: postId }),
                    { method: "POST" },
                );
                if (countEl) countEl.textContent = data.likes_count;
                if (likesWrap) likesWrap.hidden = data.likes_count === 0;
                if (data.likes_count > 0) reactions.hidden = false;

                const post = posts.get(postId);
                if (post) {
                    post.is_liked = data.is_liked;
                    post.likes_count = data.likes_count;
                }
            } catch (err) {
                btn.dataset.liked = String(wasLiked);
                btn.classList.toggle("is-active", wasLiked);
                svg.setAttribute("fill", wasLiked ? "currentColor" : "none");
                toast(err.message, "error");
            }
        }

        // ── Save ─────────────────────────────────────────────────────

        async function handleToggleSave(btn, postId) {
            const countSpan = btn.querySelector(".action-count");
            const svg = btn.querySelector("svg");
            const wasSaved = btn.classList.contains("is-active");

            btn.classList.toggle("is-active", !wasSaved);
            svg.setAttribute("fill", !wasSaved ? "currentColor" : "none");
            countSpan.textContent = !wasSaved ? "Saved" : "Save";

            try {
                const data = await api(
                    route(config.routes.save, { __ID__: postId }),
                    { method: "POST" },
                );

                const post = posts.get(postId);
                if (post) post.is_saved = data.is_saved;

                if (data.is_saved) {
                    toast("Saved to your profile.", "success");
                } else {
                    toast("Removed from saved.", "success");
                    if (options.onPostRemovedFromSaved)
                        options.onPostRemovedFromSaved(postId);
                }
            } catch (err) {
                btn.classList.toggle("is-active", wasSaved);
                svg.setAttribute("fill", wasSaved ? "currentColor" : "none");
                countSpan.textContent = wasSaved ? "Saved" : "Save";
                toast(err.message, "error");
            }
        }

        // ── Delete ───────────────────────────────────────────────────

        function removePostCard(postId) {
            posts.delete(postId);
            const card = container.querySelector(
                `[data-post-id="${postId}"].feed-card`,
            );
            if (card) {
                card.style.transition = "opacity 0.25s, transform 0.25s";
                card.style.opacity = "0";
                card.style.transform = "scale(0.97)";
                setTimeout(() => {
                    card.remove();
                    if (options.onPostRemoved) options.onPostRemoved(postId);
                }, 250);
            }
        }

        async function handleDeletePost(postId) {
            if (!confirm("Delete this post? This cannot be undone.")) return;
            try {
                await api(route(config.routes.destroy, { __ID__: postId }), {
                    method: "DELETE",
                });
                removePostCard(postId);
                toast("Post deleted.", "success");
            } catch (err) {
                toast(err.message, "error");
            }
        }

        // ── Comments ─────────────────────────────────────────────────

        async function toggleCommentsSection(card, postId) {
            const section = card.querySelector(".comments-section");
            const isHidden = section.hasAttribute("hidden");

            if (isHidden) {
                section.removeAttribute("hidden");
                if (section.dataset.commentsState === "unloaded") {
                    await loadComments(postId, section, null);
                }
                section.querySelector(".comment-input")?.focus();
            } else {
                section.setAttribute("hidden", "");
            }
        }

        async function loadComments(postId, section, beforeId) {
            const list = section.querySelector(
                `.comment-list[data-post-id="${postId}"]`,
            );

            if (!beforeId) {
                list.innerHTML =
                    '<div class="comments-loader"><span class="feed-spinner"></span></div>';
            } else {
                const existingBtn = section.querySelector(
                    ".comments-load-more",
                );
                if (existingBtn) existingBtn.textContent = "Loading...";
            }

            try {
                const params = new URLSearchParams({ limit: 10 });
                if (beforeId) params.set("before_id", beforeId);

                const url = route(config.routes.comments, { __ID__: postId });
                const data = await api(`${url}?${params.toString()}`);
                const html = (data.comments || [])
                    .map((c) => commentMarkup(c, postId))
                    .join("");

                if (!beforeId) {
                    list.innerHTML =
                        html ||
                        '<p class="comments-empty">No comments yet. Be the first to comment!</p>';
                } else {
                    section.querySelector(".comments-load-more")?.remove();
                    list.insertAdjacentHTML("beforeend", html);
                }

                if (data.has_more) {
                    list.insertAdjacentHTML(
                        "afterend",
                        `<button class="comments-load-more" data-action="load-more-comments" data-post-id="${postId}" data-before-id="${data.oldest_id}">View more comments</button>`,
                    );
                }

                section.dataset.commentsState = "loaded";
            } catch (err) {
                toast(err.message, "error");
            }
        }

        async function sendComment(textarea) {
            const body = textarea.value.trim();
            if (!body) return;

            const postId = textarea.dataset.postId;
            const parentId = textarea.dataset.parentId || null;
            const card = textarea.closest(".feed-card");
            const section = card.querySelector(".comments-section");
            const sendBtn = textarea.nextElementSibling;

            sendBtn.disabled = true;

            try {
                const url = route(config.routes.comments, { __ID__: postId });
                const data = await api(url, {
                    method: "POST",
                    body: JSON.stringify({ body, parent_id: parentId }),
                });

                textarea.value = "";
                resizeTextarea(textarea);

                if (parentId) {
                    const replyList = section.querySelector(
                        `.reply-list[data-replies-for="${parentId}"]`,
                    );
                    replyList.insertAdjacentHTML(
                        "beforeend",
                        replyMarkup(data.comment, postId, parentId),
                    );
                    replyList.hidden = false;
                    const replyBox = section.querySelector(
                        `[data-reply-box-for="${parentId}"]`,
                    );
                    if (replyBox) replyBox.hidden = true;
                } else {
                    const list = section.querySelector(
                        `.comment-list[data-post-id="${postId}"]`,
                    );
                    const emptyMsg = list.querySelector(".comments-empty");
                    if (emptyMsg) emptyMsg.remove();
                    list.insertAdjacentHTML(
                        "afterbegin",
                        commentMarkup(data.comment, postId),
                    );
                }

                updateCommentsCount(card, 1);
            } catch (err) {
                toast(err.message, "error");
            } finally {
                sendBtn.disabled = textarea.value.trim().length === 0;
            }
        }

        function updateCommentsCount(card, delta) {
            const post = posts.get(Number(card.dataset.postId));
            if (post)
                post.comments_count = Math.max(
                    0,
                    (post.comments_count || 0) + delta,
                );

            const reactions = card.querySelector(".card-reactions");
            let countSpan = reactions.querySelector(
                '[data-action="toggle-comments"]',
            );
            const newCount = post ? post.comments_count : null;
            if (newCount === null) return;

            if (newCount > 0) {
                reactions.hidden = false;
                if (countSpan) {
                    countSpan.textContent = `${newCount} comment${newCount !== 1 ? "s" : ""}`;
                } else {
                    reactions
                        .querySelector(".card-reactions__right")
                        .insertAdjacentHTML(
                            "afterbegin",
                            `<span data-action="toggle-comments">${newCount} comment${newCount !== 1 ? "s" : ""}</span>`,
                        );
                }
            } else if (countSpan) {
                countSpan.remove();
            }
        }

        async function handleCommentLike(btn) {
            const commentId = btn.dataset.commentId;
            const wasActive = btn.classList.contains("is-active");
            btn.classList.toggle("is-active", !wasActive);

            try {
                const data = await api(
                    route(config.routes.commentLike, { __ID__: commentId }),
                    { method: "POST" },
                );
                const countSpan = btn.querySelector(".comment-like-count");
                if (data.likes_count > 0) {
                    if (countSpan)
                        countSpan.textContent = `(${data.likes_count})`;
                    else
                        btn.insertAdjacentHTML(
                            "beforeend",
                            ` <span class="comment-like-count">(${data.likes_count})</span>`,
                        );
                } else if (countSpan) {
                    countSpan.remove();
                }
                btn.classList.toggle("is-active", data.is_liked);
            } catch (err) {
                btn.classList.toggle("is-active", wasActive);
                toast(err.message, "error");
            }
        }

        async function handleDeleteComment(btn) {
            if (!confirm("Delete this comment?")) return;

            const commentId = btn.dataset.commentId;
            const postId = btn.dataset.postId;
            const parentId = btn.dataset.parentId;
            const card = container.querySelector(
                `[data-post-id="${postId}"].feed-card`,
            );

            try {
                const url = route(config.routes.commentDestroy, {
                    __POST_ID__: postId,
                    __ID__: commentId,
                });
                await api(url, { method: "DELETE" });

                const item = card.querySelector(
                    `[data-comment-id="${commentId}"]`,
                );
                const isTopLevel = !parentId;
                let delta = -1;
                if (isTopLevel) {
                    const replies = item.querySelectorAll(".reply-item").length;
                    delta -= replies;
                }
                item?.remove();
                updateCommentsCount(card, delta);

                const list = card.querySelector(
                    `.comment-list[data-post-id="${postId}"]`,
                );
                if (list && !list.children.length) {
                    list.innerHTML =
                        '<p class="comments-empty">No comments yet. Be the first to comment!</p>';
                }

                toast("Comment deleted.", "success");
            } catch (err) {
                toast(err.message, "error");
            }
        }

        // ── Repost (Instagram-style: creates a new post pointing to original) ──

        function openRepostModal(postId) {
            if (!rm.el) {
                toast("Repost is unavailable here.", "error");
                return;
            }

            const post = posts.get(Number(postId));
            if (!post) return;

            rmState.targetId = postId;
            if (rm.caption) rm.caption.value = "";

            const previewPost = post.shared_post || post;
            if (rm.previewWrap)
                rm.previewWrap.innerHTML = sharedPostMarkup(previewPost);

            rm.el.hidden = false;
            document.body.style.overflow = "hidden";
            rm.el.dataset.activeController = options.controllerId || "default";
            setTimeout(() => rm.caption?.focus(), 50);
        }

        function closeRepostModal() {
            if (!rm.el) return;
            rm.el.hidden = true;
            document.body.style.overflow = "";
            rmState.targetId = null;
            delete rm.el.dataset.activeController;
        }

        async function confirmRepost() {
            if (
                rm.el.dataset.activeController !==
                (options.controllerId || "default")
            )
                return;
            if (!rmState.targetId) return;

            const caption = (rm.caption?.value || "").trim();
            rm.confirm.disabled = true;
            const originalLabel = rm.confirm.textContent;
            rm.confirm.textContent = "Reposting...";

            try {
                const url = route(config.routes.share, {
                    __ID__: rmState.targetId,
                });
                const data = await api(url, {
                    method: "POST",
                    body: JSON.stringify({ caption }),
                });

                // Bump the original post's repost count if visible in this container
                const originalId = data.post.shared_post?.id;
                if (originalId) {
                    bumpRepostCount(originalId);
                }

                closeRepostModal();
                toast("Reposted to your feed!", "success");

                if (options.onPostPrepended) options.onPostPrepended(data.post);
            } catch (err) {
                toast(err.message, "error");
            } finally {
                rm.confirm.disabled = false;
                rm.confirm.textContent = originalLabel;
            }
        }

        function bumpRepostCount(originalId) {
            const originalCard = container.querySelector(
                `[data-post-id="${originalId}"].feed-card`,
            );
            const post = posts.get(originalId);
            if (post) post.shares_count = (post.shares_count || 0) + 1;
            const newCount = post ? post.shares_count : 1;

            if (!originalCard) return;

            const reactions = originalCard.querySelector(".card-reactions");
            let shareSpan = [
                ...reactions.querySelectorAll(".card-reactions__right span"),
            ].find((s) => s.textContent.includes("repost"));

            if (shareSpan) {
                shareSpan.textContent = `${newCount} repost${newCount !== 1 ? "s" : ""}`;
            } else {
                reactions.hidden = false;
                reactions
                    .querySelector(".card-reactions__right")
                    .insertAdjacentHTML(
                        "beforeend",
                        `<span>${newCount} repost${newCount !== 1 ? "s" : ""}</span>`,
                    );
            }
        }

        if (rm.el) {
            rm.close?.addEventListener("click", () => {
                if (
                    rm.el.dataset.activeController ===
                    (options.controllerId || "default")
                )
                    closeRepostModal();
            });
            rm.cancel?.addEventListener("click", () => {
                if (
                    rm.el.dataset.activeController ===
                    (options.controllerId || "default")
                )
                    closeRepostModal();
            });
            rm.el.addEventListener("click", (e) => {
                if (
                    e.target === rm.el &&
                    rm.el.dataset.activeController ===
                        (options.controllerId || "default")
                )
                    closeRepostModal();
            });
            rm.confirm?.addEventListener("click", confirmRepost);
        }

        // ── Share link (copy / native share) ────────────────────────────

        function handleShareLink(postId) {
            const url = route(config.routes.postShow, { __ID__: postId });

            if (navigator.share) {
                navigator
                    .share({ title: "Check out this post", url })
                    .catch(() => {});
                return;
            }

            navigator.clipboard
                ?.writeText(url)
                .then(() => {
                    toast("Link copied to clipboard!", "success");
                })
                .catch(() => {
                    const input = document.createElement("input");
                    input.value = url;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand("copy");
                    input.remove();
                    toast("Link copied to clipboard!", "success");
                });
        }

        // ── Lightbox ─────────────────────────────────────────────────

        function openLightbox(media, index) {
            if (!lb.el) return;
            lbState.media = media;
            lbState.index = index;
            renderLightbox();
            lb.el.hidden = false;
            document.body.style.overflow = "hidden";
            lb.el.dataset.activeController = options.controllerId || "default";
        }

        function closeLightbox() {
            lb.el.hidden = true;
            document.body.style.overflow = "";
            lb.content.innerHTML = "";
            delete lb.el.dataset.activeController;
        }

        function renderLightbox() {
            const item = lbState.media[lbState.index];
            if (!item) return;

            lb.content.innerHTML =
                item.type === "video"
                    ? `<video src="${escAttr(item.url)}" controls autoplay></video>`
                    : `<img src="${escAttr(item.url)}" alt="">`;

            const multi = lbState.media.length > 1;
            if (lb.prev) lb.prev.hidden = !multi || lbState.index === 0;
            if (lb.next)
                lb.next.hidden =
                    !multi || lbState.index === lbState.media.length - 1;
        }

        if (lb.el) {
            lb.close?.addEventListener("click", () => {
                if (
                    lb.el.dataset.activeController ===
                    (options.controllerId || "default")
                )
                    closeLightbox();
            });
            lb.el.addEventListener("click", (e) => {
                if (
                    e.target === lb.el &&
                    lb.el.dataset.activeController ===
                        (options.controllerId || "default")
                )
                    closeLightbox();
            });
            lb.prev?.addEventListener("click", () => {
                if (
                    lb.el.dataset.activeController !==
                    (options.controllerId || "default")
                )
                    return;
                if (lbState.index > 0) {
                    lbState.index--;
                    renderLightbox();
                }
            });
            lb.next?.addEventListener("click", () => {
                if (
                    lb.el.dataset.activeController !==
                    (options.controllerId || "default")
                )
                    return;
                if (lbState.index < lbState.media.length - 1) {
                    lbState.index++;
                    renderLightbox();
                }
            });
            document.addEventListener("keydown", (e) => {
                if (
                    lb.el.hidden ||
                    lb.el.dataset.activeController !==
                        (options.controllerId || "default")
                )
                    return;
                if (e.key === "Escape") closeLightbox();
                if (e.key === "ArrowLeft") lb.prev?.click();
                if (e.key === "ArrowRight") lb.next?.click();
            });
        }

        // ── Navigate to post page ────────────────────────────────────

        function navigateToPost(postId) {
            window.location.href = route(config.routes.postShow, {
                __ID__: postId,
            });
        }

        // ── Event delegation ─────────────────────────────────────────

        container.addEventListener("click", (e) => {
            // Navigation (post-meta or body click, but not on interactive elements)
            const navEl = e.target.closest("[data-navigable]");
            if (
                navEl &&
                !e.target.closest(
                    "a, button, .photo-tile, .fv-player, .card-menu-wrap, .comments-section",
                )
            ) {
                navigateToPost(navEl.dataset.postId);
                return;
            }

            const card = e.target.closest(".feed-card");
            if (!card) return;
            const postId = Number(card.dataset.postId);

            const menuBtn = e.target.closest('[data-action="toggle-menu"]');
            if (menuBtn) {
                const dropdown = menuBtn.nextElementSibling;
                const isOpen = !dropdown.hidden;
                $$(".card-menu-dropdown", container).forEach(
                    (d) => (d.hidden = true),
                );
                dropdown.hidden = isOpen;
                return;
            }

            const delBtn = e.target.closest('[data-action="delete-post"]');
            if (delBtn) {
                handleDeletePost(delBtn.dataset.postId);
                return;
            }

            const reportBtn = e.target.closest('[data-action="report-post"]');
            if (reportBtn) {
                toast("Thanks — our team will review this post.", "info");
                $$(".card-menu-dropdown", container).forEach(
                    (d) => (d.hidden = true),
                );
                return;
            }

            const likeBtn = e.target.closest('[data-action="toggle-like"]');
            if (likeBtn) {
                handleToggleLike(likeBtn, postId);
                return;
            }

            const saveBtn = e.target.closest('[data-action="toggle-save"]');
            if (saveBtn) {
                handleToggleSave(saveBtn, postId);
                return;
            }

            const commentToggle = e.target.closest(
                '[data-action="toggle-comments"]',
            );
            if (commentToggle) {
                toggleCommentsSection(card, postId);
                return;
            }

            const repostBtn = e.target.closest('[data-action="open-repost"]');
            if (repostBtn) {
                openRepostModal(postId);
                return;
            }

            const shareLinkBtn = e.target.closest('[data-action="share-link"]');
            if (shareLinkBtn) {
                handleShareLink(postId);
                return;
            }

            const sendBtn = e.target.closest('[data-action="send-comment"]');
            if (sendBtn) {
                sendComment(sendBtn.previousElementSibling);
                return;
            }

            const replyBtn = e.target.closest('[data-action="show-reply-box"]');
            if (replyBtn) {
                const commentId = replyBtn.dataset.commentId;
                const box = card.querySelector(
                    `[data-reply-box-for="${commentId}"]`,
                );
                box.hidden = !box.hidden;
                if (!box.hidden) box.querySelector(".comment-input").focus();
                return;
            }

            const commentLikeBtn = e.target.closest(
                '[data-action="toggle-comment-like"]',
            );
            if (commentLikeBtn) {
                handleCommentLike(commentLikeBtn);
                return;
            }

            const deleteCommentBtn = e.target.closest(
                '[data-action="delete-comment"]',
            );
            if (deleteCommentBtn) {
                handleDeleteComment(deleteCommentBtn);
                return;
            }

            const loadMoreBtn = e.target.closest(
                '[data-action="load-more-comments"]',
            );
            if (loadMoreBtn) {
                const section = card.querySelector(".comments-section");
                loadComments(
                    loadMoreBtn.dataset.postId,
                    section,
                    loadMoreBtn.dataset.beforeId,
                );
                return;
            }

            // Custom video player clicks are handled by direct listeners
            // (attached in initVideoPlayer) with stopPropagation, so they
            // never reach this delegated handler. This is a safety no-op.
            if (e.target.closest("[data-fv-player]")) {
                return;
            }

            const photoTile = e.target.closest(".photo-tile");
            if (photoTile) {
                const grid = photoTile.closest(".photo-grid");
                const pid = Number(grid.dataset.postId);
                const post = posts.get(pid) || posts.get(postId);
                const media =
                    (post?.shared_post?.media?.length
                        ? post.shared_post.media
                        : post?.media) || [];
                const index = Number(photoTile.dataset.mediaIndex);
                openLightbox(media, index);
                return;
            }
        });

        container.addEventListener("input", (e) => {
            if (!e.target.classList.contains("comment-input")) return;
            resizeTextarea(e.target);
            const sendBtn = e.target.nextElementSibling;
            sendBtn.disabled = e.target.value.trim().length === 0;
        });

        container.addEventListener("keydown", (e) => {
            if (!e.target.classList.contains("comment-input")) return;
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                const sendBtn = e.target.nextElementSibling;
                if (!sendBtn.disabled) sendComment(e.target);
            }
        });

        document.addEventListener("click", (e) => {
            if (!e.target.closest(".card-menu-wrap")) {
                $$(".card-menu-dropdown", container).forEach(
                    (d) => (d.hidden = true),
                );
            }
        });

        // ── Custom video player ──────────────────────────────────────

        function formatTime(seconds) {
            if (!isFinite(seconds) || seconds < 0) return "0:00";
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${String(s).padStart(2, "0")}`;
        }

        function updateMuteIcon(player, muted) {
            const onIcon = player.querySelector(".fv-icon-mute-on");
            const offIcon = player.querySelector(".fv-icon-mute-off");
            if (onIcon)
                muted
                    ? onIcon.setAttribute("hidden", "")
                    : onIcon.removeAttribute("hidden");
            if (offIcon)
                !muted
                    ? offIcon.setAttribute("hidden", "")
                    : offIcon.removeAttribute("hidden");
        }

        function updatePlayIcon(player, playing) {
            const playIcon = player.querySelector(".fv-icon-play");
            const pauseIcon = player.querySelector(".fv-icon-pause");
            const overlay = player.querySelector("[data-fv-overlay]");
            if (playIcon)
                playing
                    ? playIcon.setAttribute("hidden", "")
                    : playIcon.removeAttribute("hidden");
            if (pauseIcon)
                !playing
                    ? pauseIcon.setAttribute("hidden", "")
                    : pauseIcon.removeAttribute("hidden");
            if (overlay) overlay.classList.toggle("is-hidden", playing);
            player.classList.toggle("is-playing", playing);
        }

        function toggleVideoPlay(player, video) {
            if (video.paused) {
                // Ensure the lazy-loaded src is set before playing
                if (!video.src && video.dataset.src) {
                    video.src = video.dataset.src;
                }
                // Pause any other playing videos in this container (single-playback)
                $$("[data-fv-player] .fv-video", container).forEach((v) => {
                    if (v !== video && !v.paused) {
                        v.pause();
                        updatePlayIcon(v.closest("[data-fv-player]"), false);
                    }
                });
                video.play().catch(() => {});
            } else {
                video.pause();
            }
        }

        function initVideoPlayer(player) {
            try {
                const video = player.querySelector(".fv-video");
                if (!video || player.dataset.fvInit) return;
                player.dataset.fvInit = "1";

                // Lazy-load the video source — avoids multiple identical videos
                // (e.g. a post and its repost embed) fetching the same large file
                // simultaneously, which the local dev server can't handle.
                function ensureSourceLoaded() {
                    if (video.src) return;
                    const src = video.dataset.src;
                    if (src) video.src = src;
                }

                const lazyObserver = new IntersectionObserver(
                    (entries) => {
                        for (const entry of entries) {
                            if (entry.isIntersecting) {
                                ensureSourceLoaded();
                                lazyObserver.disconnect();
                            }
                        }
                    },
                    { rootMargin: "200px" },
                );
                lazyObserver.observe(player);

                const progressFill = player.querySelector(
                    "[data-fv-progress-fill]",
                );
                const timeEl = player.querySelector("[data-fv-time]");
                const progressBar = player.querySelector("[data-fv-progress]");
                const playBtns = $$("[data-fv-toggle-play]", player);
                const muteBtn = player.querySelector("[data-fv-toggle-mute]");
                const expandBtn = player.querySelector("[data-fv-expand]");

                updateMuteIcon(player, video.muted);
                updatePlayIcon(player, false);

                video.addEventListener("play", () =>
                    updatePlayIcon(player, true),
                );
                video.addEventListener("playing", () =>
                    updatePlayIcon(player, true),
                );
                video.addEventListener("pause", () =>
                    updatePlayIcon(player, false),
                );
                video.addEventListener("ended", () =>
                    updatePlayIcon(player, false),
                );

                video.addEventListener("timeupdate", () => {
                    if (!video.duration) return;
                    const pct = (video.currentTime / video.duration) * 100;
                    if (progressFill) progressFill.style.width = `${pct}%`;
                    if (timeEl)
                        timeEl.textContent = formatTime(
                            video.duration - video.currentTime,
                        );
                    // Fallback sync in case play/pause events were missed
                    updatePlayIcon(player, !video.paused);
                });

                video.addEventListener("loadedmetadata", () => {
                    if (timeEl) timeEl.textContent = formatTime(video.duration);
                });

                video.addEventListener("error", () => {
                    console.error("[fv-player] video error:", video.error);
                });

                // Direct listeners (bypass delegation entirely for reliability)
                playBtns.forEach((btn) => {
                    btn.addEventListener("click", (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        toggleVideoPlay(player, video);
                    });
                });

                video.addEventListener("click", (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleVideoPlay(player, video);
                });

                muteBtn?.addEventListener("click", (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    video.muted = !video.muted;
                    updateMuteIcon(player, video.muted);
                });

                expandBtn?.addEventListener("click", (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (video.requestFullscreen) video.requestFullscreen();
                    else if (video.webkitEnterFullscreen)
                        video.webkitEnterFullscreen();
                });

                progressBar?.addEventListener("click", (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const rect = progressBar.getBoundingClientRect();
                    const ratio = Math.min(
                        1,
                        Math.max(0, (e.clientX - rect.left) / rect.width),
                    );
                    if (video.duration)
                        video.currentTime = ratio * video.duration;
                });
            } catch (err) {
                console.error("[fv-player] initVideoPlayer error:", err);
            }
        }

        function initAllVideoPlayers() {
            $$("[data-fv-player]", container).forEach(initVideoPlayer);
        }

        // Autoplay (muted) when a video scrolls into view, pause when it leaves —
        // Instagram/Facebook-style feed behavior.
        const videoObserver = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    const video = entry.target;
                    if (
                        entry.isIntersecting &&
                        entry.intersectionRatio >= 0.6
                    ) {
                        // Only force mute on the very first autoplay —
                        // respect the user's manual mute/unmute afterward.
                        if (!video.dataset.fvAutoplayed) {
                            video.muted = true;
                            const player = video.closest("[data-fv-player]");
                            updateMuteIcon(player, true);
                            video.dataset.fvAutoplayed = "1";
                        }

                        const tryPlay = () => {
                            if (!video.src && video.dataset.src) {
                                video.src = video.dataset.src;
                            }
                            video.play().catch(() => {});
                        };
                        if (video.readyState >= 2) {
                            tryPlay();
                        } else {
                            video.addEventListener("loadeddata", tryPlay, {
                                once: true,
                            });
                        }
                    } else {
                        video.pause();
                    }
                }
            },
            { threshold: [0, 0.6, 1] },
        );

        function setupVideoAutoplay() {
            $$("[data-fv-player] .fv-video", container).forEach((v) => {
                if (!v.dataset.fvObserved) {
                    v.dataset.fvObserved = "1";
                    videoObserver.observe(v);
                }
            });
        }

        // Re-scan for new players + re-bind autoplay observer whenever the
        // container's content changes (new posts appended/prepended).
        const mutationObserver = new MutationObserver(() => {
            initAllVideoPlayers();
            setupVideoAutoplay();
        });
        mutationObserver.observe(container, { childList: true, subtree: true });

        // Initial scan in case posts are already present
        initAllVideoPlayers();
        setupVideoAutoplay();

        return {
            removePostCard,
            loadComments,
        };
    }

    window.FeedCore = {
        esc,
        escAttr,
        route,
        timeAgo,
        resizeTextarea,
        api,
        toast,
        avatarMarkup,
        likeIconSvg,
        repostIconSvg,
        sendIconSvg,
        saveIconSvg,
        mediaGridMarkup,
        sharedPostMarkup,
        postCardMarkup,
        commentMarkup,
        replyMarkup,
        createFeedController,
    };
})();
