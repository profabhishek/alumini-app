@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Messages')

@push('styles')
<style>
.comm-center {
    height: calc(100vh - var(--header-h, 60px));
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.wa-chat {
    flex: 1;
    height: 100% !important;
    min-height: 0 !important;
}
.comm-content-grid {
    padding: 0px;
}
</style>
    <link rel="stylesheet" href="{{ asset('css/community/messages/chat.css') }}">
@endpush

@section('content')
    <div
        class="wa-chat"
        id="chatApp"
        data-current-user-id="{{ (int) session('alumni_id') }}"
        data-current-user-name="{{ session('alumni_name', 'Alumni') }}"
    >
        <aside class="wa-sidebar" aria-label="Conversations">
            <header class="wa-sidebar__header">
                <div>
                    <p class="wa-eyebrow">Alumni community</p>
                    <h1 class="wa-title">Chats</h1>
                </div>

                <div class="wa-header-actions">
                    <button class="wa-icon-btn" id="newGroupBtn" type="button" title="Create group" aria-label="Create group">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M19 8v6M22 11h-6"/>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="wa-search-wrap">
                <svg class="wa-search-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-3.5-3.5"/>
                </svg>
                <input
                    class="wa-search"
                    id="chatSearch"
                    type="search"
                    placeholder="Search or start a new chat"
                    autocomplete="off"
                    aria-label="Search conversations or alumni"
                >
                <button class="wa-search-clear" id="clearSearchBtn" type="button" aria-label="Clear search" hidden>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="wa-list-status" id="listStatus" role="status" aria-live="polite"></div>

            <div class="wa-conversation-list" id="conversationList">
                <div class="wa-skeleton-list" aria-label="Loading conversations">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="wa-skeleton-row">
                            <span class="wa-skeleton wa-skeleton--avatar"></span>
                            <span class="wa-skeleton-copy">
                                <span class="wa-skeleton wa-skeleton--line"></span>
                                <span class="wa-skeleton wa-skeleton--line wa-skeleton--short"></span>
                            </span>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="wa-search-results" id="userResults" hidden></div>
        </aside>

        <main class="wa-panel" id="chatPanel">
            <section class="wa-welcome" id="chatWelcome">
                <div class="wa-welcome__art" aria-hidden="true">
                    <svg viewBox="0 0 120 120">
                        <path d="M24 31h72a12 12 0 0 1 12 12v40a12 12 0 0 1-12 12H57L32 108V95h-8a12 12 0 0 1-12-12V43a12 12 0 0 1 12-12Z"/>
                        <path d="M36 56h48M36 72h32"/>
                    </svg>
                </div>
                <h2>ICCR Alumni Messages</h2>
                <p>Select a conversation or search for an alumnus to start chatting.</p>
                <span class="wa-encryption-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="5" y="10" width="14" height="10" rx="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>
                    Private community messaging
                </span>
            </section>

            <section class="wa-active-chat" id="activeChat" hidden>
                <header class="wa-chat-header">
                    <button class="wa-icon-btn wa-back-btn" id="backToChatsBtn" type="button" aria-label="Back to chats">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>

                    <button class="wa-chat-identity" id="chatIdentityBtn" type="button">
                        <span class="wa-avatar" id="chatAvatar"></span>
                        <span class="wa-chat-identity__copy">
                            <strong id="chatName">Conversation</strong>
                            <span id="chatMeta">Click here for conversation info</span>
                        </span>
                    </button>

                    <div class="wa-header-actions">
                        <button class="wa-icon-btn" id="chatSearchBtn" type="button" aria-label="Search messages" title="Search messages">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"/>
                                <path d="m20 20-3.5-3.5"/>
                            </svg>
                        </button>
                        <button class="wa-icon-btn" id="chatMenuBtn" type="button" aria-label="Conversation menu" title="Conversation menu">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="5" r="1"/>
                                <circle cx="12" cy="12" r="1"/>
                                <circle cx="12" cy="19" r="1"/>
                            </svg>
                        </button>
                    </div>
                </header>

                <div class="wa-message-search" id="messageSearchBar" hidden>
                    <input id="messageSearchInput" type="search" placeholder="Search in conversation" aria-label="Search messages">
                    <span id="messageSearchCount"></span>
                    <button class="wa-icon-btn" id="closeMessageSearchBtn" type="button" aria-label="Close message search">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="wa-load-older" id="loadOlderWrap" hidden>
                    <button id="loadOlderBtn" type="button">Load earlier messages</button>
                </div>

                <div class="wa-messages" id="messageList" aria-live="polite" aria-label="Messages"></div>

                <div class="wa-reply-preview" id="replyPreview" hidden>
                    <div>
                        <strong id="replySender">Reply</strong>
                        <span id="replyText"></span>
                    </div>
                    <button class="wa-icon-btn" id="cancelReplyBtn" type="button" aria-label="Cancel reply">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="wa-upload-preview" id="uploadPreview" hidden>
                    <div class="wa-upload-preview__icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                            <path d="M14 2v6h6"/>
                        </svg>
                    </div>
                    <div class="wa-upload-preview__copy">
                        <strong id="uploadFileName"></strong>
                        <span id="uploadFileSize"></span>
                    </div>
                    <button class="wa-icon-btn" id="cancelUploadBtn" type="button" aria-label="Remove attachment">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <form class="wa-composer" id="messageForm">
                    <input id="fileInput" type="file" hidden>

                    <button class="wa-icon-btn wa-attach-btn" id="attachBtn" type="button" aria-label="Attach a file" title="Attach a file">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m21.4 11.6-8.9 8.9a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 1 1-2.8-2.8l8.5-8.5"/>
                        </svg>
                    </button>

                    <textarea
                        id="messageInput"
                        rows="1"
                        maxlength="5000"
                        placeholder="Type a message"
                        aria-label="Message"
                    ></textarea>

                    <button class="wa-send-btn" id="sendMessageBtn" type="submit" aria-label="Send message">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m22 2-7 20-4-9-9-4Z"/>
                            <path d="M22 2 11 13"/>
                        </svg>
                    </button>
                </form>
            </section>
        </main>

        <aside class="wa-info-panel" id="infoPanel" hidden aria-label="Conversation information">
            <header class="wa-info-panel__header">
                <button class="wa-icon-btn" id="closeInfoBtn" type="button" aria-label="Close conversation info">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
                <strong>Conversation info</strong>
            </header>
            <div class="wa-info-panel__body" id="infoPanelBody"></div>
        </aside>
    </div>

    <div class="wa-modal-backdrop" id="groupModal" hidden>
        <section class="wa-modal" role="dialog" aria-modal="true" aria-labelledby="groupModalTitle">
            <header class="wa-modal__header">
                <div>
                    <p class="wa-eyebrow">New conversation</p>
                    <h2 id="groupModalTitle">Create a group</h2>
                </div>
                <button class="wa-icon-btn" id="closeGroupModalBtn" type="button" aria-label="Close">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </header>

            <form id="groupForm">
                <label class="wa-field">
                    <span>Group name</span>
                    <input id="groupName" name="name" type="text" maxlength="100" required>
                </label>
                <label class="wa-field">
                    <span>Description <small>optional</small></span>
                    <textarea id="groupDescription" name="description" rows="3" maxlength="500"></textarea>
                </label>
                <label class="wa-field">
                    <span>Add members</span>
                    <input id="groupMemberSearch" type="search" placeholder="Search alumni">
                </label>
                <div class="wa-selected-members" id="selectedMembers"></div>
                <div class="wa-member-results" id="groupMemberResults">
                    <p>Search by name, email, or department.</p>
                </div>
                <div class="wa-form-error" id="groupFormError" role="alert"></div>
                <footer class="wa-modal__footer">
                    <button class="wa-secondary-btn" id="cancelGroupBtn" type="button">Cancel</button>
                    <button class="wa-primary-btn" id="createGroupBtn" type="submit">Create group</button>
                </footer>
            </form>
        </section>
    </div>

    <div class="wa-toast-region" id="toastRegion" aria-live="polite" aria-atomic="true"></div>
@endsection

@push('scripts')
    <script>
window.ChatConfig = {!! json_encode([
    'csrfToken' => csrf_token(),
    'currentUserId' => (int) session('alumni_id'),
    'routes' => [
        'conversations' => route('chat.conversations'),
        'pollConversations' => route('chat.poll-conversations'),
        'searchUsers' => route('chat.search-users'),
        'startDirect' => route('chat.direct'),
        'createGroup' => route('chat.groups.store'),
        'messages' => url('/chat/conversations/__ID__/messages'),
        'pollMessages' => url('/chat/conversations/__ID__/poll'),
        'deleteMessage' => url('/chat/messages/__ID__'),
        'groupInfo' => url('/chat/groups/__ID__/info'),
        'onlineStatus'      => route('chat.online-status'),
        'tickUpdates' => url('/chat/conversations/__ID__/tick-updates'),
        'markOffline' => route('chat.mark-offline'),
    ],
]) !!};
    </script>
    <script src="{{ asset('js/community/chat.js') }}" defer></script>
@endpush
