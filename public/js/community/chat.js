(() => {
    "use strict";

    const config = window.ChatConfig;
    const app = document.getElementById("chatApp");
    if (!config || !app) return;

    const $ = (sel, root = document) => root.querySelector(sel);

    // ── Element refs ──────────────────────────────────────────────────────
    const el = {
        conversationList: $("#conversationList"),
        userResults: $("#userResults"),
        listStatus: $("#listStatus"),
        search: $("#chatSearch"),
        clearSearch: $("#clearSearchBtn"),
        welcome: $("#chatWelcome"),
        activeChat: $("#activeChat"),
        chatPanel: $("#chatPanel"),
        chatAvatar: $("#chatAvatar"),
        chatName: $("#chatName"),
        chatMeta: $("#chatMeta"),
        chatIdentity: $("#chatIdentityBtn"),
        messageList: $("#messageList"),
        messageForm: $("#messageForm"),
        messageInput: $("#messageInput"),
        sendButton: $("#sendMessageBtn"),
        fileInput: $("#fileInput"),
        attachButton: $("#attachBtn"),
        uploadPreview: $("#uploadPreview"),
        uploadFileName: $("#uploadFileName"),
        uploadFileSize: $("#uploadFileSize"),
        cancelUpload: $("#cancelUploadBtn"),
        replyPreview: $("#replyPreview"),
        replySender: $("#replySender"),
        replyText: $("#replyText"),
        cancelReply: $("#cancelReplyBtn"),
        loadOlderWrap: $("#loadOlderWrap"),
        loadOlderButton: $("#loadOlderBtn"),
        backButton: $("#backToChatsBtn"),
        infoPanel: $("#infoPanel"),
        infoBody: $("#infoPanelBody"),
        closeInfo: $("#closeInfoBtn"),
        chatMenu: $("#chatMenuBtn"),
        chatSearchButton: $("#chatSearchBtn"),
        messageSearchBar: $("#messageSearchBar"),
        messageSearchInput: $("#messageSearchInput"),
        messageSearchCount: $("#messageSearchCount"),
        closeMessageSearch: $("#closeMessageSearchBtn"),
        groupModal: $("#groupModal"),
        newGroupButton: $("#newGroupBtn"),
        closeGroupModal: $("#closeGroupModalBtn"),
        cancelGroup: $("#cancelGroupBtn"),
        groupForm: $("#groupForm"),
        groupName: $("#groupName"),
        groupDescription: $("#groupDescription"),
        groupMemberSearch: $("#groupMemberSearch"),
        groupMemberResults: $("#groupMemberResults"),
        selectedMembers: $("#selectedMembers"),
        groupFormError: $("#groupFormError"),
        createGroupButton: $("#createGroupBtn"),
        toastRegion: $("#toastRegion"),
    };

    // ── State ─────────────────────────────────────────────────────────────
    const state = {
        conversations: [],
        activeConversation: null,
        messages: [],
        oldestMessageId: null,
        lastMessageId: 0,
        hasMoreMessages: false,
        replyTo: null,
        selectedFile: null,
        selectedMembers: new Map(),
        conversationPoll: null,
        messagePoll: null,
        onlineStatusPoll: null,
        searchTimer: null,
        memberSearchTimer: null,
        requestSerial: 0,
        onlineCache: new Map(),
        pendingTicks: new Map(),
        tickPoll: null,
    };

    // ── Utilities ─────────────────────────────────────────────────────────

    function route(tpl, id) {
        return tpl.replace("__ID__", String(id));
    }

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

    /**
     * Format an ISO timestamp in the USER'S LOCAL timezone.
     * This is the key function for local time display.
     *
     * @param {string} iso  - ISO 8601 string from server (UTC)
     * @param {'time'|'short'|'full'} mode
     */
    function localTime(iso, mode = "time") {
        if (!iso) return "";
        const d = new Date(iso);
        if (isNaN(d)) return "";

        const now = new Date();
        const today = new Date(
            now.getFullYear(),
            now.getMonth(),
            now.getDate(),
        );
        const msgDay = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        const isToday = msgDay.getTime() === today.getTime();
        const isYesterday = msgDay.getTime() === today.getTime() - 86400000;

        if (mode === "time") {
            // Just HH:MM in local timezone — e.g. "14:32"
            return d.toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
            });
        }

        if (mode === "short") {
            // For conversation list timestamps
            if (isToday)
                return d.toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit",
                });
            if (isYesterday) return "Yesterday";
            return d.toLocaleDateString([], { day: "numeric", month: "short" });
        }

        if (mode === "full") {
            // For last-seen strings: "Today at 14:32", "Yesterday at 09:15", "3 Jun at 11:00"
            const timeStr = d.toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
            });
            if (isToday) return `Last seen today at ${timeStr}`;
            if (isYesterday) return `Last seen yesterday at ${timeStr}`;
            const dateStr = d.toLocaleDateString([], {
                day: "numeric",
                month: "short",
            });
            return `Last seen ${dateStr} at ${timeStr}`;
        }

        return d.toLocaleString();
    }

    /**
     * Build last-seen string from cached online status.
     * Uses the raw ISO timestamp for local-timezone formatting.
     */
    function lastSeenText(userId) {
        const status = state.onlineCache.get(Number(userId));
        if (!status) return "";
        if (status.is_online) return "Online";
        if (!status.last_seen_at) return status.last_seen_human || "";
        // Prefer JS local-time formatting over server string
        return localTime(status.last_seen_at, "full");
    }

    function avatarMarkup(entity, cls = "wa-avatar") {
        const name = entity?.name || "?";
        const initials =
            entity?.initials || name.charAt(0).toUpperCase() || "?";
        const img = entity?.avatar
            ? `<img src="${escAttr(entity.avatar)}" alt="${escAttr(name)}" loading="lazy">`
            : esc(initials);
        return `<span class="${cls}">${img}</span>`;
    }

    function setAvatar(target, entity) {
        target.innerHTML = entity?.avatar
            ? `<img src="${escAttr(entity.avatar)}" alt="${escAttr(entity?.name || "")}" loading="lazy">`
            : esc(
                  entity?.initials ||
                      entity?.name?.charAt(0)?.toUpperCase() ||
                      "?",
              );
    }

    function debounce(fn, ms, key) {
        clearTimeout(state[key]);
        state[key] = setTimeout(fn, ms);
    }

    // ── API wrapper ───────────────────────────────────────────────────────

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

    // ── Toast ─────────────────────────────────────────────────────────────

    function toast(msg, type = "info") {
        const t = document.createElement("div");
        t.className = `wa-toast wa-toast--${type}`;
        t.textContent = msg;
        el.toastRegion.appendChild(t);
        requestAnimationFrame(() => t.classList.add("is-visible"));
        setTimeout(() => {
            t.classList.remove("is-visible");
            setTimeout(() => t.remove(), 200);
        }, 3200);
    }

    function setListStatus(msg = "", isError = false) {
        el.listStatus.textContent = msg;
        el.listStatus.classList.toggle("is-error", isError);
        el.listStatus.hidden = !msg;
    }

    function emptyState(title, copy) {
        return `<div class="wa-empty">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/>
            </svg>
            <strong>${esc(title)}</strong>
            <span>${esc(copy)}</span>
        </div>`;
    }

    // ── Online status ─────────────────────────────────────────────────────

    /**
     * Seed the cache from conversation data (no extra request needed on load).
     */
    function seedOnlineCache(conversations) {
        conversations.forEach((c) => {
            if (c.type === "direct" && c.other_user_id) {
                state.onlineCache.set(Number(c.other_user_id), {
                    is_online: c.other_is_online,
                    last_seen_at: c.other_last_seen_at,
                    last_seen_human: c.other_last_seen,
                });
            }
        });
    }

    /**
     * Poll online status for users currently visible:
     * - the other person in the active direct chat
     * - all members if active chat is a group (up to 50)
     */
    async function pollOnlineStatus() {
        if (!state.activeConversation) return;

        const ids = [];

        if (state.activeConversation.type === "direct") {
            if (state.activeConversation.other_user_id) {
                ids.push(state.activeConversation.other_user_id);
            }
        } else {
            // Group — collect IDs from current messages' senders
            state.messages.forEach((m) => {
                if (m.sender?.id && m.sender.id !== config.currentUserId) {
                    ids.push(m.sender.id);
                }
            });
        }

        const unique = [...new Set(ids)].slice(0, 50);
        if (!unique.length) return;

        try {
            const params = unique.map((id) => `ids[]=${id}`).join("&");
            const data = await api(`${config.routes.onlineStatus}?${params}`);

            (data.users || []).forEach((u) => {
                state.onlineCache.set(Number(u.id), {
                    is_online: u.is_online,
                    last_seen_at: u.last_seen_at,
                    last_seen_human: u.last_seen_human,
                });
            });

            // Update header if direct chat
            if (state.activeConversation.type === "direct") {
                updateChatMeta();
            }
        } catch {
            /* polling failure — silent */
        }
    }

    /**
     * Update the chat header meta line (Online / Last seen …)
     * Uses local-time formatting.
     */
    function updateChatMeta() {
        const conv = state.activeConversation;
        if (!conv) return;

        if (conv.type === "direct") {
            const status = conv.other_user_id
                ? state.onlineCache.get(Number(conv.other_user_id))
                : null;

            const isOnline = status?.is_online ?? conv.other_is_online ?? false;
            const seenText = status
                ? lastSeenText(conv.other_user_id)
                : conv.other_last_seen || "";

            // Update dot visibility
            const dot = document.getElementById("chatOnlineDot");
            if (dot) dot.hidden = !isOnline;

            el.chatMeta.textContent = isOnline ? "Online" : seenText;
        } else {
            el.chatMeta.textContent =
                `${conv.participant_count} participants` +
                (conv.is_admin ? " · you are an admin" : "");
        }
    }

    // ── Conversations ─────────────────────────────────────────────────────

    async function fetchConversations({ quiet = false } = {}) {
        try {
            if (!quiet) setListStatus("Loading chats…");
            const data = await api(config.routes.conversations);
            state.conversations = data.conversations || [];
            seedOnlineCache(state.conversations);
            renderConversations();
            setListStatus();

            // Refresh active conversation meta
            if (state.activeConversation) {
                const refreshed = state.conversations.find(
                    (c) => Number(c.id) === Number(state.activeConversation.id),
                );
                if (refreshed) {
                    const prevPending = state.activeConversation.pending_count || 0;
                    state.activeConversation = refreshed;
                    renderActiveHeader();
                    // If info panel is open and pending_count changed, auto-refresh it
                    if (!el.infoPanel.hidden && refreshed.pending_count !== prevPending) {
                        openInfoPanel();
                    }
                }
            }
        } catch (err) {
            if (!quiet) {
                el.conversationList.innerHTML = emptyState(
                    "Chats unavailable",
                    err.message,
                );
            }
            setListStatus(err.message, true);
        }
    }

    function renderConversations() {
        const query = el.search.value.trim().toLowerCase();
        const list =
            query.length < 2
                ? state.conversations
                : state.conversations.filter((c) =>
                      [c.name, c.latest_message?.preview]
                          .join(" ")
                          .toLowerCase()
                          .includes(query),
                  );

        if (!list.length) {
            el.conversationList.innerHTML = emptyState(
                query ? "No matching chats" : "No conversations yet",
                query
                    ? "Search alumni below to start a new chat."
                    : "Search for an alumnus to begin messaging.",
            );
            return;
        }

        el.conversationList.innerHTML = list
            .map((c) => {
                const isActive =
                    Number(state.activeConversation?.id) === Number(c.id);
                const preview =
                    c.latest_message?.preview ||
                    (c.type === "group"
                        ? `${c.participant_count} participants`
                        : "Start a conversation");
                const mine = c.latest_message?.is_mine ? "You: " : "";

                // Show online dot in sidebar for direct chats
                const status =
                    c.type === "direct" && c.other_user_id
                        ? state.onlineCache.get(Number(c.other_user_id))
                        : null;
                const isOnline =
                    status?.is_online ?? c.other_is_online ?? false;

                // Local time for last message
                const timeStr = c.latest_message?.time
                    ? c.updated_at
                        ? localTime(c.updated_at, "short")
                        : c.latest_message.time
                    : "";

                return `<button
                class="wa-conversation ${isActive ? "is-active" : ""}"
                type="button"
                data-conversation-id="${c.id}"
                aria-current="${isActive ? "true" : "false"}"
            >
                <span class="wa-avatar-wrap">
                    ${avatarMarkup(c)}
                    ${
                        c.type === "direct" && isOnline
                            ? `<span class="wa-online-dot wa-online-dot--sidebar" aria-hidden="true"></span>`
                            : ""
                    }
                </span>
                <span class="wa-conversation__body">
                    <span class="wa-conversation__topline">
                        <strong>${esc(c.name || "Conversation")}</strong>
                        <time>${esc(timeStr)}</time>
                    </span>
                    <span class="wa-conversation__bottomline">
                        <span class="wa-conversation__preview">
                            ${c.type === "group" ? '<span class="wa-group-mark">Group</span>' : ""}
                            ${esc(mine + preview)}
                        </span>
                        ${
                            c.unread_count > 0
                                ? `<span class="wa-unread">${Math.min(c.unread_count, 99)}</span>`
                                : ""
                        }
                        ${
                            c.type === "group" &&
                            c.is_admin &&
                            c.pending_count > 0
                                ? `<span class="wa-unread wa-pending-badge" title="${c.pending_count} pending join request${c.pending_count !== 1 ? "s" : ""}">${Math.min(c.pending_count, 9)}+</span>`
                                : ""
                        }
                    </span>
                </span>
            </button>`;
            })
            .join("");
    }

    // ── User search ───────────────────────────────────────────────────────

    async function searchUsers(q, target = "sidebar") {
        const container =
            target === "group" ? el.groupMemberResults : el.userResults;

        if (q.length < 2) {
            if (target === "sidebar") {
                el.userResults.hidden = true;
                el.conversationList.hidden = false;
            } else {
                container.innerHTML =
                    "<p>Type at least 2 characters to search.</p>";
            }
            return;
        }

        container.innerHTML =
            '<div class="wa-inline-loader">Searching alumni…</div>';
        if (target === "sidebar") {
            el.userResults.hidden = false;
            el.conversationList.hidden = true;
        }

        try {
            const data = await api(
                `${config.routes.searchUsers}?q=${encodeURIComponent(q)}`,
            );
            const users = data.users || [];

            if (!users.length) {
                container.innerHTML = emptyState(
                    "No alumni found",
                    "Try a different name, email, or department.",
                );
                return;
            }

            container.innerHTML = users
                .map(
                    (u) => `
                <button
                    class="${target === "group" ? "wa-member-result" : "wa-user-result"}"
                    type="button"
                    data-user-id="${u.id}"
                    data-user-name="${escAttr(u.name)}"
                    data-user-avatar="${escAttr(u.avatar || "")}"
                    data-user-initials="${escAttr(u.initials || "?")}"
                >
                    ${avatarMarkup(u, "wa-avatar wa-avatar--sm")}
                    <span>
                        <strong>${esc(u.name)}</strong>
                        <small>${esc(u.meta || u.email || "")}</small>
                    </span>
                    ${
                        target === "group"
                            ? `<span class="wa-member-add">${state.selectedMembers.has(Number(u.id)) ? "Added ✓" : "Add"}</span>`
                            : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>'
                    }
                </button>
            `,
                )
                .join("");
        } catch (err) {
            container.innerHTML = emptyState("Search failed", err.message);
        }
    }

    // ── Open conversation ─────────────────────────────────────────────────

    async function startDirect(userId) {
        try {
            const data = await api(config.routes.startDirect, {
                method: "POST",
                body: JSON.stringify({ user_id: Number(userId) }),
            });
            el.search.value = "";
            el.clearSearch.hidden = true;
            el.userResults.hidden = true;
            el.conversationList.hidden = false;
            await fetchConversations({ quiet: true });
            await openConversation(data.conversation.id);
        } catch (err) {
            toast(err.message, "error");
        }
    }

    async function openConversation(conversationId) {
        const conv = state.conversations.find(
            (c) => Number(c.id) === Number(conversationId),
        );
        if (!conv) return;

        const serial = ++state.requestSerial;
        state.activeConversation = conv;
        state.messages = [];
        state.oldestMessageId = null;
        state.lastMessageId = 0;
        state.hasMoreMessages = false;
        clearReply();
        clearUpload();

        renderConversations();
        renderActiveHeader();
        el.welcome.hidden = true;
        el.activeChat.hidden = false;
        app.classList.add("has-active-chat");
        closeInfoPanel();
        el.messageList.innerHTML =
            '<div class="wa-message-loader">Loading messages…</div>';

        try {
            const data = await api(route(config.routes.messages, conv.id));
            if (serial !== state.requestSerial) return;

            state.messages = data.messages || [];
            state.oldestMessageId = data.oldest_id || null;
            state.lastMessageId = state.messages.at(-1)?.id || 0;
            state.hasMoreMessages = Boolean(data.has_more);

            renderMessages();
            collectPendingTicks();
            scrollToBottomAfterImages();
            el.messageInput.focus({ preventScroll: true });
            startMessagePolling();
            startOnlinePolling();
            fetchConversations({ quiet: true });
        } catch (err) {
            el.messageList.innerHTML = emptyState(
                "Messages unavailable",
                err.message,
            );
        }
    }

    function renderActiveHeader() {
        const conv = state.activeConversation;
        if (!conv) return;

        setAvatar(el.chatAvatar, conv);
        el.chatName.textContent = conv.name || "Conversation";

        // Inject online dot into the header avatar area
        let dotEl = document.getElementById("chatOnlineDot");
        if (!dotEl) {
            dotEl = document.createElement("span");
            dotEl.id = "chatOnlineDot";
            dotEl.className = "wa-online-dot";
            dotEl.setAttribute("aria-label", "Online");
            el.chatAvatar.parentElement?.appendChild(dotEl);
        }

        const isOnline =
            conv.type === "direct"
                ? (state.onlineCache.get(Number(conv.other_user_id))
                      ?.is_online ??
                  conv.other_is_online ??
                  false)
                : false;
        dotEl.hidden = !isOnline;

        updateChatMeta();
    }

    // ── Messages ──────────────────────────────────────────────────────────

    async function loadOlderMessages() {
        if (
            !state.activeConversation ||
            !state.hasMoreMessages ||
            !state.oldestMessageId
        )
            return;

        el.loadOlderButton.disabled = true;
        el.loadOlderButton.textContent = "Loading…";
        const prevHeight = el.messageList.scrollHeight;

        try {
            const url = `${route(config.routes.messages, state.activeConversation.id)}?before_id=${state.oldestMessageId}`;
            const data = await api(url);
            const older = data.messages || [];
            state.messages = [...older, ...state.messages];
            state.oldestMessageId = data.oldest_id || state.oldestMessageId;
            state.hasMoreMessages = Boolean(data.has_more);
            renderMessages();
            collectPendingTicks();
            el.messageList.scrollTop = el.messageList.scrollHeight - prevHeight;
        } catch (err) {
            toast(err.message, "error");
        } finally {
            state.isSending = false;
            el.sendButton.disabled = false;
            el.messageInput.disabled = false;
            el.messageInput.focus();
        }
    }

    function messageContent(m) {
        if (m.deleted)
            return '<p class="wa-deleted-message">This message was deleted</p>';

        if (m.type === "image" && m.file_url) {
            return `<a class="wa-media" href="${escAttr(m.file_url)}" target="_blank" rel="noopener">
                <img src="${escAttr(m.file_url)}" alt="${escAttr(m.file_name || "Photo")}" loading="lazy">
            </a>`;
        }

        if (m.type === "video" && m.file_url) {
            return `<video class="wa-media wa-media--video" controls preload="metadata">
                <source src="${escAttr(m.file_url)}" type="${escAttr(m.file_mime || "video/mp4")}">
            </video>`;
        }

        if (["file", "pdf"].includes(m.type) && m.file_url) {
            return `<a class="wa-file" href="${escAttr(m.file_url)}" target="_blank" rel="noopener" download>
                <span class="wa-file__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                        <path d="M14 2v6h6"/>
                    </svg>
                </span>
                <span>
                    <strong>${esc(m.file_name || "Attachment")}</strong>
                    <small>${esc(m.file_size_human || m.type.toUpperCase())}</small>
                </span>
            </a>`;
        }

        return `<p class="wa-message-text">${esc(m.body || "").replaceAll("\n", "<br>")}</p>`;
    }

    function renderMessages() {
        el.loadOlderWrap.hidden = !state.hasMoreMessages;

        if (!state.messages.length) {
            el.messageList.innerHTML = emptyState(
                "No messages yet",
                "Send a message to start this conversation.",
            );
            return;
        }

        let prevDate = "";
        let html = "";

        for (const m of state.messages) {
            // Date separator — use local time
            const localDateLabel = m.created_at
                ? (() => {
                      const d = new Date(m.created_at);
                      const now = new Date();
                      const today = new Date(
                          now.getFullYear(),
                          now.getMonth(),
                          now.getDate(),
                      );
                      const msgDay = new Date(
                          d.getFullYear(),
                          d.getMonth(),
                          d.getDate(),
                      );
                      if (msgDay.getTime() === today.getTime()) return "Today";
                      if (msgDay.getTime() === today.getTime() - 86400000)
                          return "Yesterday";
                      return d.toLocaleDateString([], {
                          day: "numeric",
                          month: "long",
                          year: "numeric",
                      });
                  })()
                : m.date_label || "";

            if (localDateLabel !== prevDate) {
                prevDate = localDateLabel;
                html += `<div class="wa-date-separator"><span>${esc(localDateLabel)}</span></div>`;
            }

            if (m.type === "system") {
                html += `<div class="wa-system-message"><span>${esc(m.body || "")}</span></div>`;
                continue;
            }

            const showSender =
                state.activeConversation?.type === "group" && !m.is_mine;
            // Local time for message timestamp
            const timeDisplay = m.created_at
                ? localTime(m.created_at, "time")
                : m.time || "";

            html += `<article
                class="wa-message ${m.is_mine ? "is-mine" : "is-theirs"}"
                data-message-id="${m.id}"
                data-message-text="${escAttr(m.body || m.file_name || "")}"
            >
                <div class="wa-bubble">
                    ${showSender ? `<strong class="wa-message-sender">${esc(m.sender?.name || "")}</strong>` : ""}
                    ${
                        m.reply_to
                            ? `
                        <div class="wa-quoted-message">
                            <strong>${esc(m.reply_to.sender || "Message")}</strong>
                            <span>${esc(m.reply_to.body || "")}</span>
                        </div>`
                            : ""
                    }
                    ${messageContent(m)}
                    <div class="wa-message-meta">
                        <time datetime="${escAttr(m.created_at || "")}" title="${escAttr(m.created_at ? new Date(m.created_at).toLocaleString() : "")}">${esc(timeDisplay)}</time>
                        ${
                            m.is_mine
                                ? `<span class="wa-tick-btn" title="Message info">${tickMarkup(m.tick_state, m.id, m.delivered_at, m.read_at)}</span>`
                                : ""
                        }
                    </div>
                    <div class="wa-message-actions">
                        <button type="button" data-action="reply" aria-label="Reply" title="Reply">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 17-5-5 5-5"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                        </button>
                        ${
                            m.is_mine || state.activeConversation?.is_admin
                                ? `<button type="button" data-action="delete" aria-label="Delete" title="Delete">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/></svg>
                            </button>`
                                : ""
                        }
                    </div>
                </div>
            </article>`;
        }

        el.messageList.innerHTML = html;
        applyMessageFilter();
    }

    function scrollToBottom() {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                el.messageList.scrollTop = el.messageList.scrollHeight;
            });
        });
    }
    function scrollToBottomAfterImages() {
        // Scroll immediately
        scrollToBottom();

        // Then re-scroll after every image in the list loads
        el.messageList.querySelectorAll("img").forEach((img) => {
            if (!img.complete) {
                img.addEventListener("load", () => scrollToBottom(), {
                    once: true,
                });
                img.addEventListener("error", () => scrollToBottom(), {
                    once: true,
                });
            }
        });
    }
    // ── Reply / upload ────────────────────────────────────────────────────

    function setReply(m) {
        state.replyTo = m;
        el.replySender.textContent = m.is_mine
            ? "You"
            : m.sender?.name || "Reply";
        el.replyText.textContent = m.body || m.file_name || "Attachment";
        el.replyPreview.hidden = false;
        el.messageInput.focus();
    }

    function clearReply() {
        state.replyTo = null;
        el.replyPreview.hidden = true;
        el.replySender.textContent = "";
        el.replyText.textContent = "";
    }

    function formatBytes(bytes) {
        if (!bytes) return "";
        const units = ["B", "KB", "MB", "GB"];
        const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), 3);
        return `${(bytes / 1024 ** i).toFixed(i ? 1 : 0)} ${units[i]}`;
    }

    function setUpload(file) {
        state.selectedFile = file;
        el.uploadFileName.textContent = file.name;
        el.uploadFileSize.textContent = formatBytes(file.size);
        el.uploadPreview.hidden = false;
    }

    function clearUpload() {
        state.selectedFile = null;
        el.fileInput.value = "";
        el.uploadPreview.hidden = true;
    }

    // ── Send message ──────────────────────────────────────────────────────

    async function sendMessage(e) {
        e.preventDefault();
        if (!state.activeConversation) return;

        const body = el.messageInput.value.trim();
        if (!body && !state.selectedFile) return;

        el.sendButton.disabled = true;
        el.messageInput.disabled = true;
        state.isSending = true;

        try {
            let reqBody,
                headers = {};

            if (state.selectedFile) {
                reqBody = new FormData();
                reqBody.append("type", "file");
                reqBody.append("file", state.selectedFile);
                if (state.replyTo)
                    reqBody.append("reply_to_id", state.replyTo.id);
            } else {
                reqBody = JSON.stringify({
                    type: "text",
                    body,
                    reply_to_id: state.replyTo?.id || null,
                });
                headers = { "Content-Type": "application/json" };
            }

            const data = await api(
                route(config.routes.messages, state.activeConversation.id),
                {
                    method: "POST",
                    headers,
                    body: reqBody,
                },
            );

            state.messages.push(data.message);
            state.lastMessageId = Math.max(
                state.lastMessageId,
                data.message.id,
            );
            el.messageInput.value = "";
            resizeComposer();
            clearReply();
            clearUpload();
            renderMessages();
            collectPendingTicks();
            scrollToBottomAfterImages();
            pollTickUpdates();
            fetchConversations({ quiet: true });
        } catch (err) {
            toast(err.message, "error");
        } finally {
            el.sendButton.disabled = false;
            el.messageInput.disabled = false;
            el.messageInput.focus();
        }
    }

    async function deleteMessage(messageId) {
        if (
            !(await waConfirm({
                title: "Delete message",
                body: "This message will be deleted for everyone. This cannot be undone.",
                confirmText: "Delete",
                danger: true,
            }))
        )
            return;
        try {
            const data = await api(
                route(config.routes.deleteMessage, messageId),
                {
                    method: "DELETE",
                },
            );
            applyRemoteDeletions(data.message ? [data.message] : []);
            fetchConversations({ quiet: true });
            if (!data.already_deleted) {
                toast("Message deleted.", "success");
            }
        } catch (err) {
            toast(err.message, "error");
        }
    }

    // ── Polling ───────────────────────────────────────────────────────────

    async function pollMessages() {
        if (!state.activeConversation || document.hidden || state.isSending)
            return;
        try {
            const url = `${route(config.routes.pollMessages, state.activeConversation.id)}?after_id=${state.lastMessageId}`;
            const data = await api(url);
            const incoming = (data.messages || []).filter(
                (m) =>
                    !state.messages.some((x) => Number(x.id) === Number(m.id)),
            );

            applyRemoteDeletions(data.deleted || []);

            if (incoming.length) {
                const nearBottom =
                    el.messageList.scrollHeight -
                    el.messageList.scrollTop -
                    el.messageList.clientHeight;
                160;
                state.messages.push(...incoming);
                state.lastMessageId = Math.max(
                    state.lastMessageId,
                    ...incoming.map((m) => m.id),
                );
                renderMessages();
                collectPendingTicks();
                if (nearBottom) scrollToBottomAfterImages();
                fetchConversations({ quiet: true });
            }
        } catch {
            /* transient */
        }
    }

    function applyRemoteDeletions(deletedList) {
        if (!deletedList.length) return;

        deletedList.forEach((dm) => {
            const idx = state.messages.findIndex(
                (m) => Number(m.id) === Number(dm.id),
            );
            if (idx === -1) return;
            if (state.messages[idx].deleted) return; // already applied

            state.messages[idx] = { ...state.messages[idx], ...dm };

            const article = el.messageList.querySelector(
                `[data-message-id="${dm.id}"]`,
            );
            if (!article) return;

            const bubble = article.querySelector(".wa-bubble");
            if (!bubble) return;

            const label = dm.deleted_by_admin
                ? "This message was removed by admin"
                : "This message was deleted";

            const meta =
                bubble.querySelector(".wa-message-meta")?.outerHTML || "";
            bubble.innerHTML = `<p class="wa-deleted-message">${esc(label)}</p>${meta}`;
            bubble.parentElement
                ?.querySelector(".wa-message-actions")
                ?.remove();
        });
    }

    function startMessagePolling() {
        clearInterval(state.messagePoll);
        state.messagePoll = setInterval(pollMessages, 3000);
    }

    function startOnlinePolling() {
        clearInterval(state.onlineStatusPoll);
        clearInterval(state.tickPoll);
        // Run both immediately on conversation open
        pollOnlineStatus();
        pollTickUpdates();
        // Then poll on intervals
        state.onlineStatusPoll = setInterval(pollOnlineStatus, 8000);
        state.tickPoll = setInterval(pollTickUpdates, 2000);
    }

    function stopPolling() {
        clearInterval(state.messagePoll);
        clearInterval(state.onlineStatusPoll);
        clearInterval(state.tickPoll);
    }

    // ── Composer ──────────────────────────────────────────────────────────

    function resizeComposer() {
        el.messageInput.style.height = "auto";
        el.messageInput.style.height = `${Math.min(el.messageInput.scrollHeight, 120)}px`;
    }

    // ── Info panel ────────────────────────────────────────────────────────

    async function openInfoPanel() {
        const conv = state.activeConversation;
        if (!conv) return;

        el.infoPanel.hidden = false;
        app.classList.add("has-info-panel");
        el.infoBody.innerHTML = '<div class="wa-inline-loader">Loading…</div>';

        if (conv.type !== "group") {
            const status = conv.other_user_id
                ? state.onlineCache.get(Number(conv.other_user_id))
                : null;
            const isOnline = status?.is_online ?? conv.other_is_online ?? false;
            const seenText = status
                ? lastSeenText(conv.other_user_id)
                : conv.other_last_seen || "";

            el.infoBody.innerHTML = `
                <div class="wa-info-profile">
                    <span class="wa-avatar-wrap">
                        ${avatarMarkup(conv, "wa-avatar wa-avatar--xl")}
                        ${isOnline ? '<span class="wa-online-dot wa-online-dot--lg" aria-label="Online"></span>' : ""}
                    </span>
                    <h2>${esc(conv.name)}</h2>
                    <p class="${isOnline ? "wa-status-online" : "wa-status-offline"}">
                        ${esc(isOnline ? "Online" : seenText)}
                    </p>
                </div>
                <div class="wa-info-card">
                    <strong>Privacy</strong>
                    <p>Messages are visible only to participants in this conversation.</p>
                </div>`;
            return;
        }

        try {
            const data = await api(route(config.routes.groupInfo, conv.id));
            const group = data.group;
            const isAdmin = group.is_admin;

            el.infoBody.innerHTML = `
                <div class="wa-info-profile">
                    ${avatarMarkup({ ...conv, avatar: group.avatar }, "wa-avatar wa-avatar--xl")}
                    <h2 id="infoPanelGroupName">${esc(group.name)}</h2>
                    <p>${esc(group.description || `${group.members.length} participants`)}</p>
                </div>
 
                ${
                    isAdmin
                        ? `
                <div class="wa-info-card">
                    <strong>Group Settings</strong>
                    <div class="wa-info-edit-form">
                        <input
                            type="text"
                            id="editGroupName"
                            class="wa-info-input"
                            value="${escAttr(group.name)}"
                            maxlength="100"
                            placeholder="Group name"
                        >
                        <textarea
                            id="editGroupDesc"
                            class="wa-info-input wa-info-textarea"
                            maxlength="500"
                            placeholder="Group description (optional)"
                            rows="2"
                        >${esc(group.description || "")}</textarea>
                        <button class="wa-info-btn wa-info-btn--primary" data-action="save-group-settings">
                            Save Changes
                        </button>
                    </div>
                </div>`
                        : ""
                }
 
                ${
                    group.invite_url
                        ? `
                <div class="wa-info-card">
                    <strong>Invite Link</strong>
                    <div class="wa-info-link-row">
                        <span class="wa-info-link-text">${escAttr(group.invite_url)}</span>
                        <button class="wa-info-btn wa-info-btn--sm" type="button" data-copy="${escAttr(group.invite_url)}">
                            Copy
                        </button>
                        ${
                            isAdmin
                                ? `
                        <button class="wa-info-btn wa-info-btn--sm wa-info-btn--danger" type="button" data-action="regenerate-invite">
                            Reset
                        </button>`
                                : ""
                        }
                    </div>
                </div>`
                        : isAdmin
                          ? `
                <div class="wa-info-card">
                    <strong>Invite Link</strong>
                    <button class="wa-info-btn wa-info-btn--primary" data-action="regenerate-invite">
                        Generate Invite Link
                    </button>
                </div>`
                          : ""
                }
 
                ${
                    group.join_requests?.length
                        ? `
                <div class="wa-info-card">
                    <strong>${group.join_requests.length} Pending Request${group.join_requests.length > 1 ? "s" : ""}</strong>
                    ${group.join_requests
                        .map(
                            (r) => `
                        <div class="wa-pending-request">
                            ${avatarMarkup(r, "wa-avatar wa-avatar--sm")}
                            <span class="wa-pending-request__name">${esc(r.name)}</span>
                            <div class="wa-pending-request__actions">
                                <button class="wa-action-btn wa-action-btn--accept"
                                    data-request-id="${r.id}" data-action="accept" title="Accept">✓</button>
                                <button class="wa-action-btn wa-action-btn--reject"
                                    data-request-id="${r.id}" data-action="reject" title="Reject">✕</button>
                            </div>
                        </div>`,
                        )
                        .join("")}
                </div>`
                        : ""
                }
 
                <div class="wa-info-card">
                    <strong>${group.members.length} Participants</strong>
                    ${isAdmin ? `
                    <div class="wa-invite-search" style="position:relative;margin-bottom:10px;">
                        <input
                            type="text"
                            id="inviteSearchInput"
                            class="wa-info-input"
                            placeholder="Search people to invite…"
                            autocomplete="off"
                            style="padding-right:36px;"
                        >
                        <div id="inviteSearchResults" class="wa-invite-results" hidden></div>
                    </div>` : ""}
                    <div class="wa-member-list">
                        ${group.members
                            .map((m) => {
                                const cached = state.onlineCache.get(
                                    Number(m.id),
                                );
                                const online =
                                    cached?.is_online ?? m.is_online ?? false;
                                const seenStr = cached
                                    ? lastSeenText(m.id)
                                    : m.last_seen_human || "";
                                const canActOn = isAdmin && !m.is_me;

                                return `<div class="wa-info-member" data-member-id="${m.id}">
                                <span class="wa-avatar-wrap">
                                    ${avatarMarkup(m, "wa-avatar wa-avatar--sm")}
                                    ${online ? '<span class="wa-online-dot wa-online-dot--sm" aria-hidden="true"></span>' : ""}
                                </span>
                                <span>
                                    <strong>${esc(m.name)}${m.is_me ? " (You)" : ""}</strong>
                                    <small>${esc(m.role === "admin" ? "Group admin" : online ? "Online" : seenStr)}</small>
                                </span>
                                ${
                                    canActOn
                                        ? `
                                <div class="wa-member-actions">
                                    ${
                                        m.role !== "admin"
                                            ? `
                                    <button class="wa-member-action-btn" title="Make admin"
                                        data-action="promote-member" data-member-id="${m.id}" data-member-name="${escAttr(m.name)}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    </button>`
                                            : ""
                                    }
                                    <button class="wa-member-action-btn wa-member-action-btn--danger" title="Remove from group"
                                        data-action="remove-member" data-member-id="${m.id}" data-member-name="${escAttr(m.name)}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                            <line x1="17" y1="8" x2="23" y2="14"/>
                                            <line x1="23" y1="8" x2="17" y2="14"/>
                                        </svg>
                                    </button>
                                </div>`
                                        : ""
                                }
                            </div>`;
                            })
                            .join("")}
                    </div>
                </div>
 
                <div class="wa-info-card wa-info-card--danger">
                    ${
                        !isAdmin
                            ? `
                    <button class="wa-info-btn wa-info-btn--danger-full" data-action="leave-group">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Leave Group
                    </button>`
                            : `
                    <button class="wa-info-btn wa-info-btn--danger-full" data-action="leave-group">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Leave Group
                    </button>`
                    }
                </div>
            `;

            // Wire up invite search (admin only)
            if (isAdmin) setupInviteSearch(conv.id);

        } catch (err) {
            el.infoBody.innerHTML = emptyState(
                "Information unavailable",
                err.message,
            );
        }
    }

    // ── Invite search in info panel ───────────────────────────────────────

    function setupInviteSearch(conversationId) {
        const input   = document.getElementById("inviteSearchInput");
        const results = document.getElementById("inviteSearchResults");
        if (!input || !results) return;

        let debounceTimer;
        let currentQuery = "";

        input.addEventListener("input", function () {
            const q = input.value.trim();
            currentQuery = q;
            clearTimeout(debounceTimer);

            if (q.length < 2) {
                results.hidden = true;
                results.innerHTML = "";
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const data = await api(`${config.routes.searchUsers}?q=${encodeURIComponent(q)}`);
                    if (currentQuery !== q) return; // stale

                    const users = data.users || [];
                    if (!users.length) {
                        results.hidden = false;
                        results.innerHTML = `<div class="wa-invite-results__empty">No users found</div>`;
                        return;
                    }

                    results.hidden = false;
                    results.innerHTML = users.map(u => `
                        <div class="wa-invite-result-item" data-user-id="${u.id}" data-user-name="${escAttr(u.name)}">
                            <span class="wa-avatar wa-avatar--xs" aria-hidden="true">
                                ${u.avatar
                                    ? `<img src="${escAttr(u.avatar)}" alt="" loading="lazy">`
                                    : `<span>${escAttr(u.initials || u.name[0])}</span>`}
                            </span>
                            <span class="wa-invite-result-info">
                                <strong>${esc(u.name)}</strong>
                                ${u.meta ? `<small>${esc(u.meta)}</small>` : ""}
                            </span>
                            <button class="wa-info-btn wa-info-btn--sm wa-invite-btn"
                                data-action="invite-user"
                                data-user-id="${u.id}"
                                data-user-name="${escAttr(u.name)}">
                                Invite
                            </button>
                        </div>`).join("");
                } catch {
                    // silent
                }
            }, 280);
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function hideInviteResults(e) {
            if (!e.target.closest(".wa-invite-search")) {
                results.hidden = true;
                results.innerHTML = "";
                // Remove listener once panel is closed / search is gone
                if (!document.getElementById("inviteSearchInput")) {
                    document.removeEventListener("click", hideInviteResults);
                }
            }
        });
    }

    async function inviteUser(userId, userName, conversationId) {
        try {
            await api(
                route(config.routes.groupInfo, conversationId)
                    .replace("/info", "/invite-user"),
                {
                    method: "POST",
                    body: JSON.stringify({ user_id: userId }),
                },
            );
            toast(`Invitation sent to ${userName}.`, "success");

            // Clear search
            const input   = document.getElementById("inviteSearchInput");
            const results = document.getElementById("inviteSearchResults");
            if (input)   input.value = "";
            if (results) { results.hidden = true; results.innerHTML = ""; }

        } catch (err) {
            toast(err.message, "error");
        }
    }

    async function saveGroupSettings() {
        const conv = state.activeConversation;
        if (!conv) return;
        const name = document.getElementById("editGroupName")?.value.trim();
        const desc = document.getElementById("editGroupDesc")?.value.trim();
        if (!name) {
            toast("Group name cannot be empty.", "error");
            return;
        }

        try {
            await api(
                route(config.routes.groupInfo, conv.id).replace("/info", ""),
                {
                    method: "PUT",
                    body: JSON.stringify({ name, description: desc || null }),
                },
            );
            // Update sidebar + header
            state.activeConversation.name = name;
            el.chatName.textContent = name;
            fetchConversations({ quiet: true });
            pollTickUpdates();
            toast("Group updated.", "success");
            openInfoPanel(); // refresh panel
        } catch (err) {
            toast(err.message, "error");
        }
    }

    async function leaveGroup() {
        const conv = state.activeConversation;
        if (!conv) return;
        if (
            !(await waConfirm({
                title: "Leave group",
                body: `You'll no longer receive messages from "${conv.name}".`,
                confirmText: "Leave",
                danger: true,
            }))
        )
            return;

        try {
            await api(
                route(config.routes.groupInfo, conv.id).replace(
                    "/info",
                    `/members/${config.currentUserId}`,
                ),
                { method: "DELETE" },
            );
            toast("You left the group.", "success");
            closeInfoPanel();
            // Remove from conversations list and close chat
            state.conversations = state.conversations.filter(
                (c) => Number(c.id) !== Number(conv.id),
            );
            state.activeConversation = null;
            renderConversations();
            el.activeChat.hidden = true;
            el.welcome.hidden = false;
            app.classList.remove("has-active-chat");
            stopPolling();
        } catch (err) {
            toast(err.message, "error");
        }
    }

    async function removeMember(memberId, memberName) {
        const conv = state.activeConversation;
        if (!conv) return;
        if (
            !(await waConfirm({
                title: "Remove member",
                body: `${memberName} will be removed from this group.`,
                confirmText: "Remove",
                danger: true,
            }))
        )
            return;

        try {
            await api(
                route(config.routes.groupInfo, conv.id).replace(
                    "/info",
                    `/members/${memberId}`,
                ),
                { method: "DELETE" },
            );
            toast(`${memberName} removed.`, "success");
            openInfoPanel(); // refresh
        } catch (err) {
            toast(err.message, "error");
        }
    }

    async function promoteToAdmin(memberId, memberName) {
        const conv = state.activeConversation;
        if (!conv) return;
        if (
            !(await waConfirm({
                title: "Make admin",
                body: `${memberName} will become a group admin and can add or remove members.`,
                confirmText: "Make admin",
            }))
        )
            return;

        try {
            await api(
                route(config.routes.groupInfo, conv.id).replace(
                    "/info",
                    `/promote/${memberId}`,
                ),
                { method: "POST" },
            );
            toast(`${memberName} is now an admin.`, "success");
            openInfoPanel(); // refresh
        } catch (err) {
            toast(err.message, "error");
        }
    }

    async function regenerateInvite() {
        const conv = state.activeConversation;
        if (!conv) return;
        if (
            !(await waConfirm({
                title: "Reset invite link",
                body: "The current invite link will stop working. A new one will be generated.",
                confirmText: "Reset",
                danger: true,
            }))
        )
            return;

        try {
            const data = await api(
                route(config.routes.groupInfo, conv.id).replace(
                    "/info",
                    "/invite/regenerate",
                ),
                { method: "POST" },
            );
            toast("New invite link generated.", "success");
            // Update the active conversation invite url
            if (state.activeConversation) {
                state.activeConversation.invite_url = data.invite_url;
            }
            openInfoPanel(); // refresh to show new link
        } catch (err) {
            toast(err.message, "error");
        }
    }

    // ── Custom confirm modal ──────────────────────────────────────────────────
    function waConfirm({
        title,
        body,
        confirmText = "Confirm",
        danger = false,
    }) {
        return new Promise((resolve) => {
            const backdrop = document.createElement("div");
            backdrop.className = "wa-confirm-backdrop";
            backdrop.innerHTML = `
                <div class="wa-confirm-box">
                    <p class="wa-confirm-box__title">${esc(title)}</p>
                    <p class="wa-confirm-box__body">${esc(body)}</p>
                    <div class="wa-confirm-box__actions">
                        <button class="wa-confirm-box__btn wa-confirm-box__btn--cancel" id="waCancelBtn">Cancel</button>
                        <button class="wa-confirm-box__btn ${danger ? "wa-confirm-box__btn--danger" : "wa-confirm-box__btn--primary"}" id="waConfirmBtn">
                            ${esc(confirmText)}
                        </button>
                    </div>
                </div>`;
            document.body.appendChild(backdrop);
            const cleanup = (result) => {
                backdrop.remove();
                resolve(result);
            };
            backdrop
                .querySelector("#waConfirmBtn")
                .addEventListener("click", () => cleanup(true));
            backdrop
                .querySelector("#waCancelBtn")
                .addEventListener("click", () => cleanup(false));
            backdrop.addEventListener("click", (e) => {
                if (e.target === backdrop) cleanup(false);
            });
            document.addEventListener("keydown", function handler(e) {
                if (e.key === "Escape") {
                    cleanup(false);
                    document.removeEventListener("keydown", handler);
                }
                if (e.key === "Enter") {
                    cleanup(true);
                    document.removeEventListener("keydown", handler);
                }
            });
        });
    }

    function closeInfoPanel() {
        el.infoPanel.hidden = true;
        app.classList.remove("has-info-panel");
    }

    // ── Message search ────────────────────────────────────────────────────

    function toggleMessageSearch(open) {
        el.messageSearchBar.hidden = !open;
        if (open) el.messageSearchInput.focus();
        else {
            el.messageSearchInput.value = "";
            applyMessageFilter();
        }
    }

    function applyMessageFilter() {
        const q = el.messageSearchInput.value.trim().toLowerCase();
        const rows = el.messageList.querySelectorAll(".wa-message");
        let count = 0;
        rows.forEach((row) => {
            const match =
                !q || (row.dataset.messageText || "").toLowerCase().includes(q);
            row.classList.toggle("is-search-hidden", !match);
            row.classList.toggle("is-search-match", Boolean(q && match));
            if (q && match) count++;
        });
        el.messageSearchCount.textContent = q ? `${count} found` : "";
    }

    // ── Group modal ───────────────────────────────────────────────────────

    function openGroupModal() {
        state.selectedMembers.clear();
        el.groupForm.reset();
        el.groupFormError.textContent = "";
        renderSelectedMembers();
        el.groupMemberResults.innerHTML =
            "<p>Search by name, email, or department.</p>";
        el.groupModal.hidden = false;
        document.body.classList.add("wa-modal-open");
        setTimeout(() => el.groupName.focus(), 0);
    }

    function closeGroupModal() {
        el.groupModal.hidden = true;
        document.body.classList.remove("wa-modal-open");
    }

    function renderSelectedMembers() {
        const members = [...state.selectedMembers.values()];
        el.selectedMembers.hidden = !members.length;
        el.selectedMembers.innerHTML = members
            .map(
                (m) => `
            <button type="button" data-remove-member="${m.id}">
                ${avatarMarkup(m, "wa-avatar wa-avatar--xs")}
                <span>${esc(m.name)}</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
            </button>`,
            )
            .join("");
    }

    async function createGroup(e) {
        e.preventDefault();
        const name = el.groupName.value.trim();
        if (!name) return;

        el.createGroupButton.disabled = true;
        el.groupFormError.textContent = "";

        try {
            const data = await api(config.routes.createGroup, {
                method: "POST",
                body: JSON.stringify({
                    name,
                    description: el.groupDescription.value.trim() || null,
                    member_ids: [...state.selectedMembers.keys()],
                }),
            });
            closeGroupModal();
            await fetchConversations({ quiet: true });
            await openConversation(data.conversation.id);
            toast("Group created.", "success");
        } catch (err) {
            el.groupFormError.textContent = err.message;
        } finally {
            el.createGroupButton.disabled = false;
        }
    }

    // ── Join request actions (from info panel) ────────────────────────────

    async function handleJoinRequestAction(requestId, action) {
        if (!state.activeConversation) return;
        try {
            await api(
                route(
                    config.routes.groupInfo,
                    state.activeConversation.id,
                ).replace("/info", `/join-requests/${requestId}`),
                { method: "PATCH", body: JSON.stringify({ action }) },
            );
            toast(
                action === "accept" ? "Member added." : "Request rejected.",
                "success",
            );
            openInfoPanel(); // refresh
        } catch (err) {
            toast(err.message, "error");
        }
    }

    function tickMarkup(tickState, messageId, deliveredAt, readAt) {
        const state = tickState || "sent";

        // Build tooltip text
        let tooltip = "Sent";
        if (state === "delivered" && deliveredAt) {
            tooltip =
                "Delivered " +
                localTime(deliveredAt, "full").replace("Last seen ", "");
        }
        if (state === "read") {
            tooltip = "Read";
        }

        if (state === "sent") {
            // Single grey tick
            return `<svg class="wa-tick wa-tick--sent" viewBox="0 0 16 11"
                data-message-id="${messageId}" title="${esc(tooltip)}"
                aria-label="Sent">
                <path d="m1 6 4 4L15 1" stroke="currentColor" stroke-width="1.8"
                    fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>`;
        }

        if (state === "delivered") {
            // Double grey tick
            return `<svg class="wa-tick wa-tick--delivered" viewBox="0 0 22 11"
                data-message-id="${messageId}" title="${esc(tooltip)}"
                aria-label="Delivered">
                <path d="m1 6 4 4L15 1" stroke="currentColor" stroke-width="1.8"
                    fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="m7 6 4 4L21 1" stroke="currentColor" stroke-width="1.8"
                    fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>`;
        }

        // read — double blue tick
        return `<svg class="wa-tick wa-tick--read" viewBox="0 0 22 11"
            data-message-id="${messageId}" title="${esc(tooltip)}"
            aria-label="Read">
            <path d="m1 6 4 4L15 1" stroke="currentColor" stroke-width="1.8"
                fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="m7 6 4 4L21 1" stroke="currentColor" stroke-width="1.8"
                fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`;
    }

    /**
     * After rendering messages, collect all own messages that are
     * not yet 'read' into pendingTicks so we poll for updates.
     */
    function collectPendingTicks() {
        state.pendingTicks.clear();
        state.messages.forEach((m) => {
            if (m.is_mine && m.tick_state !== "read") {
                state.pendingTicks.set(m.id, m.tick_state);
            }
        });
    }

    /**
     * Poll tick updates for own unread messages.
     * Updates tick SVGs in-place without re-rendering the whole message list.
     */
    async function pollTickUpdates() {
        if (!state.activeConversation || state.pendingTicks.size === 0) return;

        const ids = [...state.pendingTicks.keys()];

        try {
            const params = ids.map((id) => `message_ids[]=${id}`).join("&");
            const data = await api(
                `${route(config.routes.tickUpdates, state.activeConversation.id)}?${params}`,
            );

            (data.ticks || []).forEach((tick) => {
                const current = state.pendingTicks.get(tick.id);
                if (!current || current === tick.tick_state) return;

                // Update state
                const msg = state.messages.find(
                    (m) => Number(m.id) === Number(tick.id),
                );
                if (msg) {
                    msg.tick_state = tick.tick_state;
                    msg.delivered_at = tick.delivered_at;
                    msg.read_at = tick.read_at;
                }

                // Update the SVG in-place
                const article = el.messageList.querySelector(
                    `[data-message-id="${tick.id}"]`,
                );
                if (article) {
                    const oldTick = article.querySelector(".wa-tick");
                    if (oldTick) {
                        const newTick = document.createElement("div");
                        newTick.innerHTML = tickMarkup(
                            tick.tick_state,
                            tick.id,
                            tick.delivered_at,
                            null,
                        );
                        oldTick.replaceWith(newTick.firstElementChild);
                    }
                }

                // If now read, remove from pending
                if (tick.tick_state === "read") {
                    state.pendingTicks.delete(tick.id);
                } else {
                    state.pendingTicks.set(tick.id, tick.tick_state);
                }
            });
        } catch {
            /* silent */
        }
    }

    function showTickPopup(tick, m) {
        document.querySelector(".wa-tick-popup")?.remove();

        const sentTime = m.created_at
            ? new Date(m.created_at).toLocaleString([], {
                  day: "2-digit",
                  month: "short",
                  hour: "2-digit",
                  minute: "2-digit",
              })
            : "—";

        const deliveredTime = m.delivered_at
            ? new Date(m.delivered_at).toLocaleString([], {
                  day: "2-digit",
                  month: "short",
                  hour: "2-digit",
                  minute: "2-digit",
              })
            : "Not yet";

        // For read time — find from conversation read data
        const readText = m.read_at
            ? new Date(m.read_at).toLocaleString([], {
                  day: "2-digit",
                  month: "short",
                  hour: "2-digit",
                  minute: "2-digit",
              })
            : m.tick_state === "read"
              ? "Seen"
              : "Not yet";

        const popup = document.createElement("div");
        popup.className = "wa-tick-popup";
        popup.innerHTML = `
            <p class="wa-tick-popup__title">Message Info</p>
            <div class="wa-tick-popup__row">
                <span>
                    <svg class="wa-tick wa-tick--sent" viewBox="0 0 16 11" width="14" height="10">
                        <path d="m1 6 4 4L15 1" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Sent
                </span>
                <span>${esc(sentTime)}</span>
            </div>
            <div class="wa-tick-popup__row">
                <span>
                    <svg class="wa-tick wa-tick--delivered" viewBox="0 0 22 11" width="18" height="10">
                        <path d="m1 6 4 4L15 1" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m7 6 4 4L21 1" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Delivered
                </span>
                <span>${esc(deliveredTime)}</span>
            </div>
            <div class="wa-tick-popup__row">
                <span>
                    <svg class="wa-tick wa-tick--read" viewBox="0 0 22 11" width="18" height="10">
                        <path d="m1 6 4 4L15 1" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m7 6 4 4L21 1" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Read
                </span>
                <span>${esc(readText)}</span>
            </div>`;

        const bubble = tick.closest(".wa-bubble");
        bubble.appendChild(popup);

        // Smart positioning — flip above if too close to bottom
        const bubbleRect = bubble.getBoundingClientRect();
        const panelRect = el.messageList.getBoundingClientRect();
        if (bubbleRect.bottom + 120 > panelRect.bottom) {
            popup.style.bottom = "calc(100% + 6px)";
            popup.style.top = "auto";
        } else {
            popup.style.top = "calc(100% + 6px)";
            popup.style.bottom = "auto";
        }

        setTimeout(() => {
            document.addEventListener(
                "click",
                () => {
                    document.querySelector(".wa-tick-popup")?.remove();
                },
                { once: true },
            );
        }, 0);
    }
    // ── Event listeners ───────────────────────────────────────────────────

    el.conversationList.addEventListener("click", (e) => {
        const item = e.target.closest("[data-conversation-id]");
        if (item) openConversation(item.dataset.conversationId);
    });

    el.userResults.addEventListener("click", (e) => {
        const item = e.target.closest("[data-user-id]");
        if (item) startDirect(item.dataset.userId);
    });

    el.search.addEventListener("input", () => {
        const q = el.search.value.trim();
        el.clearSearch.hidden = !q;
        renderConversations();
        debounce(() => searchUsers(q), 280, "searchTimer");
    });

    el.clearSearch.addEventListener("click", () => {
        el.search.value = "";
        el.clearSearch.hidden = true;
        el.userResults.hidden = true;
        el.conversationList.hidden = false;
        renderConversations();
        el.search.focus();
    });

    el.messageForm.addEventListener("submit", sendMessage);
    el.messageInput.addEventListener("input", resizeComposer);
    el.messageInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            el.messageForm.requestSubmit();
        }
    });

    el.attachButton.addEventListener("click", () => el.fileInput.click());
    el.fileInput.addEventListener("change", () => {
        const f = el.fileInput.files?.[0];
        if (f) setUpload(f);
    });
    el.cancelUpload.addEventListener("click", clearUpload);
    el.cancelReply.addEventListener("click", clearReply);
    el.loadOlderButton.addEventListener("click", loadOlderMessages);
    el.backButton.addEventListener("click", () => {
        app.classList.remove("has-active-chat");
        stopPolling();
    });

    el.messageList.addEventListener("click", (e) => {
        // Tick popup
        const tick =
            e.target.closest(".wa-tick") || e.target.closest(".wa-tick-btn");
        if (tick) {
            e.stopPropagation();
            const messageId =
                tick.closest("[data-message-id]")?.dataset.messageId;
            const m = state.messages.find(
                (m) => Number(m.id) === Number(messageId),
            );
            if (m) showTickPopup(tick, m);
            return;
        }

        // Message actions (reply / delete)
        const action = e.target.closest("[data-action]");
        if (!action) return;
        const article = action.closest("[data-message-id]");
        const m = state.messages.find(
            (x) => Number(x.id) === Number(article?.dataset.messageId),
        );
        if (!m) return;
        if (action.dataset.action === "reply") setReply(m);
        if (action.dataset.action === "delete") deleteMessage(m.id);
    });

    // Mobile: show actions on tap, hide on tap elsewhere
    if ("ontouchstart" in window) {
        el.messageList.addEventListener(
            "touchstart",
            (e) => {
                const bubble = e.target.closest(".wa-bubble");
                // Remove from all other bubbles first
                el.messageList
                    .querySelectorAll(".wa-bubble.is-touched")
                    .forEach((b) => {
                        if (b !== bubble) b.classList.remove("is-touched");
                    });
                if (bubble) bubble.classList.toggle("is-touched");
            },
            { passive: true },
        );

        document.addEventListener(
            "touchstart",
            (e) => {
                if (!e.target.closest(".wa-bubble")) {
                    el.messageList
                        .querySelectorAll(".wa-bubble.is-touched")
                        .forEach((b) => {
                            b.classList.remove("is-touched");
                        });
                }
            },
            { passive: true },
        );
    }
    el.chatIdentity.addEventListener("click", openInfoPanel);
    el.chatMenu.addEventListener("click", openInfoPanel);
    el.closeInfo.addEventListener("click", closeInfoPanel);

    el.infoBody.addEventListener("click", async (e) => {
        // Copy invite link
        const copyBtn = e.target.closest("[data-copy]");
        if (copyBtn) {
            try {
                await navigator.clipboard.writeText(copyBtn.dataset.copy);
                toast("Invite link copied.", "success");
            } catch {
                toast("Could not copy link.", "error");
            }
            return;
        }

        // Join request accept/reject
        const reqBtn = e.target.closest("[data-request-id]");
        if (reqBtn) {
            handleJoinRequestAction(
                reqBtn.dataset.requestId,
                reqBtn.dataset.action,
            );
            return;
        }

        // Info panel action buttons
        const actionBtn = e.target.closest("[data-action]");
        if (!actionBtn) return;

        switch (actionBtn.dataset.action) {
            case "save-group-settings":
                saveGroupSettings();
                break;
            case "leave-group":
                leaveGroup();
                break;
            case "remove-member":
                removeMember(
                    actionBtn.dataset.memberId,
                    actionBtn.dataset.memberName,
                );
                break;
            case "promote-member":
                promoteToAdmin(
                    actionBtn.dataset.memberId,
                    actionBtn.dataset.memberName,
                );
                break;
            case "regenerate-invite":
                regenerateInvite();
                break;
            case "invite-user":
                inviteUser(
                    actionBtn.dataset.userId,
                    actionBtn.dataset.userName,
                    state.activeConversation?.id,
                );
                break;
        }
    });

    el.chatSearchButton.addEventListener("click", () =>
        toggleMessageSearch(true),
    );
    el.closeMessageSearch.addEventListener("click", () =>
        toggleMessageSearch(false),
    );
    el.messageSearchInput.addEventListener("input", applyMessageFilter);

    el.newGroupButton.addEventListener("click", openGroupModal);
    el.closeGroupModal.addEventListener("click", closeGroupModal);
    el.cancelGroup.addEventListener("click", closeGroupModal);
    el.groupModal.addEventListener("click", (e) => {
        if (e.target === el.groupModal) closeGroupModal();
    });
    el.groupForm.addEventListener("submit", createGroup);

    el.groupMemberSearch.addEventListener("input", () => {
        const q = el.groupMemberSearch.value.trim();
        debounce(() => searchUsers(q, "group"), 280, "memberSearchTimer");
    });

    el.groupMemberResults.addEventListener("click", (e) => {
        const item = e.target.closest("[data-user-id]");
        if (!item) return;
        const id = Number(item.dataset.userId);
        if (state.selectedMembers.has(id)) state.selectedMembers.delete(id);
        else
            state.selectedMembers.set(id, {
                id,
                name: item.dataset.userName,
                avatar: item.dataset.userAvatar || null,
                initials: item.dataset.userInitials,
            });
        renderSelectedMembers();
        searchUsers(el.groupMemberSearch.value.trim(), "group");
    });

    el.selectedMembers.addEventListener("click", (e) => {
        const item = e.target.closest("[data-remove-member]");
        if (!item) return;
        state.selectedMembers.delete(Number(item.dataset.removeMember));
        renderSelectedMembers();
    });

    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) {
            fetchConversations({ quiet: true });
            pollMessages();
            pollOnlineStatus();
            pollTickUpdates();
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key !== "Escape") return;
        if (!el.groupModal.hidden) closeGroupModal();
        else if (!el.infoPanel.hidden) closeInfoPanel();
        else if (!el.messageSearchBar.hidden) toggleMessageSearch(false);
    });

    // ── Boot ──────────────────────────────────────────────────────────────
    fetchConversations().then(() => {
        if (config.openConversationId) {
            openConversation(config.openConversationId);

            const url = new URL(window.location.href);
            url.searchParams.delete("conversation");
            history.replaceState({}, "", url);
        }
    });
    state.conversationPoll = setInterval(
        () => fetchConversations({ quiet: true }),
        8000,
    );

    // ── Tab close / navigation away ───────────────────────────────────────────
    window.addEventListener("beforeunload", () => {
        if (navigator.sendBeacon) {
            // sendBeacon needs a Blob with Content-Type to send CSRF token
            const data = new Blob(
                [JSON.stringify({ _token: config.csrfToken })],
                { type: "application/json" },
            );
            navigator.sendBeacon(config.routes.markOffline, data);
        }
    });
})();
