@extends('layouts.community')

@section('hideRightSidebar', true)

@section('title', 'Messages')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/messages/chat.css') }}">
@endpush

@section('content')

    <div class="chat-shell">
        <aside class="sidebar">
            <h1 class="title">Messages</h1>
            <p class="subtitle">
                Search users, open conversations, create groups, and send files later.
                For now, this page is just the landing zone.
            </p>

            <form class="search" onsubmit="return false;">
                <input type="text" id="userSearch" placeholder="Search alumni by name or email">
                <button type="button" id="searchBtn">Search</button>
            </form>

            <div class="section-label">
                <span>Conversations</span>
                <span id="conversationCount">0</span>
            </div>

            <div id="conversationList">
                <div class="card empty-state">
                    No conversations loaded yet.
                </div>
            </div>

            <div class="section-label">
                <span>Search results</span>
                <span id="userCount">0</span>
            </div>

            <div id="userResults">
                <div class="card empty-state">
                    Type at least 2 characters to search.
                </div>
            </div>
        </aside>

        <main class="main">
            <div class="main-header">
                <div>
                    <div class="pill"><span class="dot"></span> Chat is ready</div>
                </div>
                <div class="pill">Session user: {{ session('alumni_name') ?? 'Guest' }}</div>
            </div>

            <div class="main-body">

                <div id="chatPlaceholder" class="hero">
                    <h2>Select a conversation</h2>
                    <p>
                        Choose a conversation from the left sidebar.
                    </p>
                </div>

                <div id="chatHeader" class="border-bottom p-3 fw-bold">
                    Conversation
                </div>

                <div id="messageList"
                    style="flex:1; overflow-y:auto; padding:15px;">
                </div>

                <div class="chat-input-area">

                    <div class="chat-input-row">

                        <input
                            type="text"
                            id="messageInput"
                            class="form-control"
                            placeholder="Type a message...">

                        <button
                            type="button"
                            id="sendMessageBtn"
                            class="btn btn-primary">

                            Send

                        </button>

                    </div>

                </div>

                    <div id="chatHeader" class="border-bottom p-3 fw-bold">
                        -
                    </div>

                    <div id="messageList"
                        style="height:500px; overflow-y:auto; padding:15px;">
                    </div>

                </div>

            </div>

        </main>
    </div>

@endsection

@push('scripts')
<script>

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const searchInput = document.getElementById('userSearch');
        const searchBtn = document.getElementById('searchBtn');
        const userResults = document.getElementById('userResults');
        const userCount = document.getElementById('userCount');
        const conversationList = document.getElementById('conversationList');
        const conversationCount = document.getElementById('conversationCount');

        let activeConversationId = null;

        const messageList =
            document.getElementById('messageList');

        const messageInput =
            document.getElementById('messageInput');

        const sendMessageBtn =
            document.getElementById('sendMessageBtn');

        async function fetchConversations() {
            try {
                const response = await fetch("{{ route('chat.conversations') }}", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return;

                const data = await response.json();
                const conversations = data.conversations || [];
                conversationCount.textContent = conversations.length;

                if (!conversations.length) {
                    conversationList.innerHTML = `
                        <div class="card empty-state">No conversations found.</div>
                    `;
                    return;
                }

                conversationList.innerHTML = conversations.map(convo => {
                    const initials = convo.initials || '?';
                    const avatar = convo.avatar
                        ? `<img src="${convo.avatar}" alt="">`
                        : initials;

                    return `
                        <div class="card conversation open-conversation"
                            data-conversation-id="${convo.id}"
                            style="cursor:pointer;">
                            <div class="avatar">${avatar}</div>
                            <div class="meta">
                                <div class="name-row">
                                    <div class="name">${convo.name ?? 'Conversation'}</div>
                                    <div class="time">${convo.latest_message?.time ?? ''}</div>
                                </div>
                                <div class="preview">${convo.latest_message?.preview ?? 'No messages yet'}</div>
                            </div>
                            ${convo.unread_count > 0 ? `<div class="badge">${convo.unread_count}</div>` : ''}
                        </div>
                    `;
                }).join('');
                document.querySelectorAll('.open-conversation').forEach(card => {

                card.addEventListener('click', function () {

                const conversationId =
                    this.dataset.conversationId;

                const conversationName =
                    this.querySelector('.name').innerText;

                document.getElementById('chatHeader').innerText =
                    conversationName;

                openConversation(conversationId);

                });

            });
            } catch (error) {
                conversationList.innerHTML = `
                    <div class="card empty-state">Failed to load conversations.</div>
                `;
            }
        }

        async function openConversation(conversationId)
        {
            try {
                activeConversationId = conversationId;
                const response = await fetch(
                    `/chat/conversations/${conversationId}/messages`
                );

                const data = await response.json();

                console.log(data);

                document.getElementById('chatPlaceholder').style.display = 'none';

                document.getElementById('chatContainer').style.display = 'block';

                const messages = data.messages || [];

                messageList.innerHTML = messages.map(message => {

                    const mine = message.is_mine;

                    return `
                        <div style="
                            display:flex;
                            margin-bottom:10px;
                            justify-content:${mine ? 'flex-end' : 'flex-start'};
                        ">

                            <div style="
                                max-width:70%;
                                padding:10px 14px;
                                border-radius:12px;
                                background:${mine ? '#0d6efd' : '#f1f1f1'};
                                color:${mine ? '#fff' : '#000'};
                            ">

                                <div>
                                    ${message.body ?? ''}
                                </div>

                                <small style="opacity:.8;">
                                    ${message.time}
                                </small>

                            </div>

                        </div>
                    `;

                }).join('');

                messageList.scrollTop = messageList.scrollHeight;

            } catch (error) {

                console.error(error);

            }
        }

        async function sendMessage()
        {
            if (!activeConversationId) {
                alert('Select a conversation first.');
                return;
            }

            const body = messageInput.value.trim();

            if (!body) {
                return;
            }

            try {

                const response = await fetch(
                    `/chat/conversations/${activeConversationId}/messages`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            type: 'text',
                            body: body
                        })
                    }
                );

                const data = await response.json();

                console.log(data);

                messageInput.value = '';

                await openConversation(activeConversationId);

            } catch (error) {

                console.error(error);

            }
        }

        async function searchUsers() {
            const q = searchInput.value.trim();

            if (q.length < 2) {
                userResults.innerHTML = `<div class="card empty-state">Type at least 2 characters to search.</div>`;
                userCount.textContent = '0';
                return;
            }

            try {
                const response = await fetch(`{{ route('chat.search-users') }}?q=${encodeURIComponent(q)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                const users = data.users || [];
                userCount.textContent = users.length;

                if (!users.length) {
                    userResults.innerHTML = `<div class="card empty-state">No users found.</div>`;
                    return;
                }

                userResults.innerHTML = users.map(user => {
                    const avatar = user.avatar
                        ? `<img src="${user.avatar}" alt="">`
                        : `${user.initials || '?'}`;

                return `
                    <div class="card conversation start-chat"
                        data-user-id="${user.id}"
                        style="cursor:pointer;">

                        <div class="avatar">${avatar}</div>

                        <div class="meta">
                            <div class="name-row">
                                <div class="name">${user.name}</div>
                            </div>

                            <div class="preview">
                                ${user.meta || user.email || ''}
                            </div>
                        </div>

                    </div>
                `;
                }).join('');

                document.querySelectorAll('.start-chat').forEach(card => {

                card.addEventListener('click', async function () {

                    const userId = this.dataset.userId;

                    try {

                        const response = await fetch(
                            "{{ route('chat.direct') }}",
                            {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    user_id: userId
                                })
                            }
                        );

                        const data = await response.json();

                        console.log('Conversation created:', data);

                        await fetchConversations();

                    } catch (error) {

                        console.error(error);

                        alert('Failed to create conversation.');

                    }

                });

            });
            } catch (error) {
                userResults.innerHTML = `<div class="card empty-state">Search failed.</div>`;
            }
        }

        searchBtn.addEventListener('click', searchUsers);
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchUsers();
            }
        });

        sendMessageBtn?.addEventListener(
            'click',
            sendMessage
        );

        messageInput?.addEventListener(
            'keydown',
            function(e)
            {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            }
        );

        fetchConversations();
</script>
@endpush