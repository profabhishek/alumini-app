@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', $group->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/groups.css') }}">
@if($isApproved)
<link rel="stylesheet" href="{{ asset('css/community/home.css') }}">
<link rel="stylesheet" href="{{ asset('css/community/feed.css') }}?v=4">
@endif
@endpush

@section('content')
<div class="groups-page">

    @if(session('success'))
        <div class="groups-flash groups-flash--success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="groups-flash groups-flash--info">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="groups-flash groups-flash--error">{{ session('error') }}</div>
    @endif

    <a href="{{ route('groups.index') }}" class="groups-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Groups
    </a>

    {{-- Hero --}}
    <div class="group-detail-hero" @if($group->cover_image) style="background-image:url('{{ asset('storage/' . $group->cover_image) }}')" @endif>
        <div class="group-detail-hero__overlay"></div>
        <div class="group-detail-hero__content">
            <h1 class="group-detail-hero__name">{{ $group->name }}</h1>
            <p class="group-detail-hero__meta">
                {{ number_format($group->membersCount()) }} {{ $group->membersCount() === 1 ? 'member' : 'members' }}
                &nbsp;·&nbsp; Created by {{ $group->creator->full_name ?? 'Unknown' }}
            </p>
        </div>
    </div>

    {{-- Status / actions bar --}}
    <div class="group-detail-bar">
        <div class="group-detail-bar__info">
            @if($groupRole)
                <span class="group-role-badge group-role-badge--{{ $groupRole }}">
                    {{ ucfirst($groupRole) }}
                </span>
            @endif
            @if($isPending)
                <span class="group-role-badge group-role-badge--pending">Request Pending</span>
            @endif
            @if($isSiteAdmin && !$groupRole && !$isPending)
                <span class="group-role-badge">Site Admin View</span>
            @endif

            @if($group->description)
                <button type="button" class="groups-btn groups-btn--ghost group-about-btn"
                        onclick="document.getElementById('groupAboutModal').classList.add('is-visible')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    About
                </button>
            @endif
        </div>

        <div class="group-detail-bar__actions">
            @if($isGroupMod || $isSiteAdmin)
                <a href="{{ route('groups.members', $group->slug) }}" class="groups-btn groups-btn--ghost group-manage-btn">
                    Manage Members
                    @if($pendingCount > 0)
                        <span class="notif-bubble">{{ $pendingCount > 99 ? '99+' : $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('groups.pending-edits', $group->slug) }}" class="groups-btn groups-btn--ghost group-manage-btn">
                    Pending Approvals
                    @if(!empty($pendingEditsCount) && $pendingEditsCount > 0)
                        <span class="notif-bubble">{{ $pendingEditsCount > 99 ? '99+' : $pendingEditsCount }}</span>
                    @endif
                </a>
                <button type="button" class="groups-btn groups-btn--ghost" id="inviteLinkBtn"
                        onclick="generateInviteLink('{{ $group->slug }}')">
                    🔗 Invite Link
                </button>
                <button type="button" class="groups-btn groups-btn--primary" id="inviteMemberBtn"
                        onclick="openInviteMemberModal()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    Invite Member
                </button>
            @endif

            @if($isApproved)
                <form method="POST" action="{{ route('groups.leave', $group->slug) }}"
                      data-confirm-title="Leave this group?"
                      data-confirm-body="You'll lose access to this group's posts and discussions until an admin or moderator approves you again."
                      data-confirm-text="Leave Group"
                      data-confirm-danger="true">
                    @csrf
                    <button type="submit" class="groups-btn groups-btn--ghost">Leave Group</button>
                </form>
            @elseif($isPending)
                <span class="groups-btn groups-btn--ghost" style="opacity:.6; cursor:default;">Request Pending</span>
            @else
                <form method="POST" action="{{ route('groups.join', $group->slug) }}">
                    @csrf
                    <button type="submit" class="groups-btn groups-btn--primary">Join Group</button>
                </form>
            @endif

            @if($isCreator)
                <form method="POST" action="{{ route('groups.destroy', $group->slug) }}"
                      data-confirm-title="Delete this group?"
                      data-confirm-body="This will permanently delete &ldquo;{{ $group->name }}&rdquo; along with all its posts, members, and invite links. This cannot be undone."
                      data-confirm-text="Delete Group"
                      data-confirm-danger="true">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="groups-btn groups-btn--danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                        </svg>
                        Delete Group
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- About modal --}}
    @if($group->description)
        <div class="gcm-backdrop gcm-static" id="groupAboutModal"
             onclick="if(event.target===this) this.classList.remove('is-visible')">
            <div class="gcm-box gcm-box--about">
                <div class="gcm-box__header">
                    <h3 class="gcm-title">About {{ $group->name }}</h3>
                    <button type="button" class="gcm-close" aria-label="Close"
                            onclick="document.getElementById('groupAboutModal').classList.remove('is-visible')">&times;</button>
                </div>
                <p class="gcm-about-text">{{ $group->description }}</p>
            </div>
        </div>
    @endif

    {{-- Feed area --}}
    <div class="group-detail-feed">
        @if($isApproved)

            <div class="feed-wrapper" id="feedWrapper">

                {{-- CREATE POST CARD --}}
                <div class="post-composer card">
                    <div class="composer-header">
                        <h2 class="composer-title">Create Post</h2>
                    </div>
                    <div class="composer-body">
                        <div class="composer-row">
                            <div class="avatar avatar--md">
                                @if(session('alumni_avatar'))
                                    <img loading="lazy" src="{{ asset('storage/' . session('alumni_avatar')) }}" alt="{{ session('alumni_name') }}">
                                @else
                                    <span class="avatar-initials">{{ strtoupper(substr(session('alumni_name', 'A'), 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="composer-name">{{ session('alumni_name', 'Alumni') }}</span>
                        </div>
                        <textarea
                            class="composer-textarea"
                            placeholder="Share something with {{ $group->name }}…"
                            rows="2"
                            id="postTextarea"
                            maxlength="5000"
                        ></textarea>

                        {{-- Media preview --}}
                        <div class="composer-media-preview" id="composerMediaPreview" hidden></div>
                    </div>
                    <div class="composer-footer">
                        <div class="composer-attachments">
                            <span class="attachment-label">Add to your post:</span>
                            <button class="attach-btn" type="button" id="attachPhotoBtn" title="Add Photos">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21,15 16,10 5,21"/>
                                </svg>
                            </button>
                            <button class="attach-btn" type="button" id="attachVideoBtn" title="Add Video">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <polygon points="23,7 16,12 23,17"/>
                                    <rect x="1" y="5" width="15" height="14" rx="2"/>
                                </svg>
                            </button>
                            <input type="file" id="photoInput" accept="image/jpeg,image/png,image/gif,image/webp" multiple hidden>
                            <input type="file" id="videoInput" accept="video/mp4,video/webm,video/quicktime" hidden>
                        </div>
                        <button class="btn-post" id="postBtn" type="button" disabled>Post Now</button>
                    </div>
                </div>

                {{-- FEED ITEMS --}}
                <div class="feed-list" id="feedList">
                    <div class="feed-skeleton" id="feedSkeleton">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="feed-skel-card card">
                                <div class="feed-skel-header">
                                    <span class="feed-skel feed-skel--avatar"></span>
                                    <span class="feed-skel-copy">
                                        <span class="feed-skel feed-skel--line" style="width:140px"></span>
                                        <span class="feed-skel feed-skel--line feed-skel--short" style="width:90px"></span>
                                    </span>
                                </div>
                                <span class="feed-skel feed-skel--line" style="width:100%;height:14px;margin-top:14px"></span>
                                <span class="feed-skel feed-skel--line" style="width:80%;height:14px;margin-top:8px"></span>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="feed-end" id="feedEnd" hidden>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l4-4 4 4M12 16V8"/></svg>
                    <p>You're all caught up</p>
                </div>

                <div class="feed-loader" id="feedLoader" hidden>
                    <span class="feed-spinner"></span>
                </div>

            </div>{{-- /feed-wrapper --}}

            {{-- REPOST MODAL --}}
            <div class="feed-modal-backdrop" id="shareModal" hidden>
                <div class="feed-modal">
                    <div class="feed-modal__header">
                        <h3>Repost</h3>
                        <button class="feed-modal__close" id="shareModalClose" type="button">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <div class="feed-modal__body">
                        <div class="composer-row">
                            <div class="avatar avatar--md">
                                @if(session('alumni_avatar'))
                                    <img loading="lazy" src="{{ asset('storage/' . session('alumni_avatar')) }}" alt="{{ session('alumni_name') }}">
                                @else
                                    <span class="avatar-initials">{{ strtoupper(substr(session('alumni_name', 'A'), 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="composer-name">{{ session('alumni_name', 'Alumni') }}</span>
                        </div>
                        <textarea class="composer-textarea" id="shareCaption" placeholder="Add your thoughts..." rows="2" maxlength="2000"></textarea>
                        <div class="share-preview-wrap" id="sharePreviewWrap"></div>
                    </div>
                    <div class="feed-modal__footer">
                        <button class="btn-secondary" id="shareCancelBtn" type="button">Cancel</button>
                        <button class="btn-post" id="shareConfirmBtn" type="button">Repost Now</button>
                    </div>
                </div>
            </div>

            {{-- LIGHTBOX --}}
            <div class="feed-lightbox" id="feedLightbox" hidden>
                <button class="feed-lightbox__close" id="lightboxClose" type="button">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <button class="feed-lightbox__nav feed-lightbox__nav--prev" id="lightboxPrev" type="button" hidden>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <div class="feed-lightbox__content" id="lightboxContent"></div>
                <button class="feed-lightbox__nav feed-lightbox__nav--next" id="lightboxNext" type="button" hidden>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>

            <div class="feed-toast-region" id="feedToastRegion"></div>

        @else
            <div class="group-feed-placeholder group-feed-placeholder--locked">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                <p>
                    @if($isPending)
                        Your request to join is pending — once approved, you'll see this group's posts here.
                    @else
                        This group's posts are private to members. Join to see what's being shared.
                    @endif
                </p>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.getElementById('groupAboutModal')?.classList.remove('is-visible');
    }
});

// ── Scroll to highlighted post from notification ─────────────────────────
(function() {
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight');
    if (!highlightId) return;

    // Remove param from URL without reload
    const clean = window.location.pathname;
    history.replaceState(null, '', clean);

    // Wait for feed JS to render posts, then scroll
    let attempts = 0;
    const tryScroll = setInterval(() => {
        const el = document.querySelector(`[data-post-id="${highlightId}"]`);
        if (el) {
            clearInterval(tryScroll);
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.style.outline = '3px solid #e8640c';
            el.style.borderRadius = '10px';
            setTimeout(() => el.style.outline = '', 2500);
        }
        if (++attempts > 30) clearInterval(tryScroll);
    }, 300);
})();

// ── Invite link generator ────────────────────────────────────────────────
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

function generateInviteLink(slug) {
    const btn = document.getElementById('inviteLinkBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Generating…'; }

    fetch(`/groups/${slug}/invite-link`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        credentials: 'same-origin',
    })
    .then(r => r.json())
    .then(data => {
        if (data.url) {
            showInviteLinkModal(data.url, data.expires_at);
        }
    })
    .catch(() => alert('Could not generate invite link.'))
    .finally(() => {
        if (btn) { btn.disabled = false; btn.textContent = '🔗 Invite Link'; }
    });
}

function showInviteLinkModal(url, expiresAt) {
    // Remove old if exists
    document.getElementById('inviteLinkModal')?.remove();

    const modal = document.createElement('div');
    modal.id = 'inviteLinkModal';
    modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;display:flex;align-items:center;justify-content:center;';
    modal.innerHTML = `
        <div style="background:#fff;border-radius:14px;padding:28px 30px;max-width:480px;width:90%;box-shadow:0 8px 40px rgba(0,0,0,.18);">
            <h3 style="margin:0 0 6px;font-size:17px;color:#1a3a5c;font-weight:700;">One-Time Invite Link</h3>
            <p style="margin:0 0 16px;font-size:13px;color:#6b7280;">This link expires in 7 days and can only be used once. Share it with the person you want to invite.</p>
            <div style="display:flex;gap:8px;align-items:center;">
                <input id="invLinkInput" type="text" readonly value="${url}"
                    style="flex:1;padding:9px 12px;border:1px solid #d1d5db;border-radius:7px;font-size:13px;color:#111827;background:#f9fafb;">
                <button onclick="copyInvLink()" style="padding:9px 16px;background:#1a3a5c;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;">
                    Copy
                </button>
            </div>
            <p style="margin:10px 0 0;font-size:12px;color:#9ca3af;">Expires: ${expiresAt}</p>
            <button onclick="document.getElementById('inviteLinkModal').remove()"
                style="margin-top:18px;width:100%;padding:9px;background:#f3f4f6;border:none;border-radius:7px;font-size:14px;cursor:pointer;color:#374151;">
                Close
            </button>
        </div>`;
    modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
    document.body.appendChild(modal);
}

function copyInvLink() {
    const inp = document.getElementById('invLinkInput');
    if (!inp) return;
    navigator.clipboard.writeText(inp.value).then(() => {
        const btn = inp.nextElementSibling;
        if (btn) { btn.textContent = 'Copied!'; setTimeout(() => btn.textContent = 'Copy', 2000); }
    }).catch(() => { inp.select(); document.execCommand('copy'); });
}

// ── Invite Member Modal ──────────────────────────────────────────────────
const INVITE_URL      = @json(route('groups.invite-user', $group->slug));
const GROUP_SEARCH_URL = @json(route('groups.search-users', $group->slug));

let inviteDebounce;

function openInviteMemberModal() {
    document.getElementById('inviteMemberModal')?.remove();

    const modal = document.createElement('div');
    modal.id = 'inviteMemberModal';
    modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;';
    modal.innerHTML = `
        <div style="background:#fff;border-radius:16px;padding:28px 26px 24px;max-width:460px;width:100%;box-shadow:0 12px 48px rgba(0,0,0,.2);position:relative;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
                <div style="width:38px;height:38px;background:linear-gradient(135deg,#fde9d6,#fbd0b0);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#E8640C;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                </div>
                <div>
                    <h3 style="margin:0 0 2px;font-size:16px;font-weight:700;color:#1C2331;">Invite a Member</h3>
                    <p style="margin:0;font-size:12.5px;color:#6b7280;">Search and send a direct invitation</p>
                </div>
                <button onclick="document.getElementById('inviteMemberModal').remove()"
                    style="margin-left:auto;background:none;border:none;cursor:pointer;color:#9ca3af;font-size:20px;line-height:1;padding:4px;">&times;</button>
            </div>

            <div style="position:relative;">
                <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#9ca3af;pointer-events:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input id="imSearchInput" type="text" placeholder="Search by name or email…" autocomplete="off"
                    style="width:100%;padding:9px 12px 9px 34px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13.5px;outline:none;box-sizing:border-box;transition:border-color .15s;"
                    onfocus="this.style.borderColor='#E8640C'" onblur="this.style.borderColor='#e5e7eb'">
                <div id="imSearchResults" style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1.5px solid #e5e7eb;border-radius:10px;box-shadow:0 6px 24px rgba(28,35,49,.12);z-index:10;max-height:220px;overflow-y:auto;"></div>
            </div>

            <p id="imStatusMsg" style="margin:12px 0 0;font-size:13px;min-height:18px;color:#16a34a;"></p>
        </div>`;

    modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
    document.body.appendChild(modal);

    const input   = document.getElementById('imSearchInput');
    const results = document.getElementById('imSearchResults');

    input.focus();
    input.addEventListener('input', () => {
        const q = input.value.trim();
        clearTimeout(inviteDebounce);
        results.style.display = 'none';
        results.innerHTML = '';

        if (q.length < 2) return;

        inviteDebounce = setTimeout(async () => {
            try {
                const res  = await fetch(`${GROUP_SEARCH_URL}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                const users = data.users || [];

                if (!users.length) {
                    results.innerHTML = '<div style="padding:12px 14px;font-size:13px;color:#9ca3af;text-align:center;">No users found</div>';
                    results.style.display = 'block';
                    return;
                }

                results.innerHTML = users.map(u => `
                    <div style="display:flex;align-items:center;gap:10px;padding:8px 12px;cursor:default;transition:background .12s;"
                         onmouseenter="this.style.background='#f9fafb'" onmouseleave="this.style.background=''">
                        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#fde9d6,#fbd0b0);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#E8640C;flex-shrink:0;overflow:hidden;">
                            ${u.avatar
                                ? `<img src="${u.avatar.replace(/"/g,'&quot;')}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
                                : (u.initials || u.name[0]).toUpperCase()}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13.5px;font-weight:600;color:#1C2331;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${u.name.replace(/</g,'&lt;')}</div>
                            ${u.meta ? `<div style="font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${u.meta.replace(/</g,'&lt;')}</div>` : ''}
                        </div>
                        <button onclick="sendGroupInvitation(${u.id}, '${u.name.replace(/'/g,"&#39;")}', this)"
                            style="flex-shrink:0;padding:6px 13px;background:#E8640C;color:#fff;border:none;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;transition:background .15s;"
                            onmouseenter="if(!this.disabled)this.style.background='#d05a0b'" onmouseleave="if(!this.disabled)this.style.background='#E8640C'">
                            Invite
                        </button>
                    </div>`).join('');
                results.style.display = 'block';

            } catch { /* silent */ }
        }, 280);
    });

    document.addEventListener('keydown', function closeOnEsc(e) {
        if (e.key === 'Escape') {
            document.getElementById('inviteMemberModal')?.remove();
            document.removeEventListener('keydown', closeOnEsc);
        }
    });
}

async function sendGroupInvitation(userId, userName, btn) {
    const statusMsg = document.getElementById('imStatusMsg');
    const results   = document.getElementById('imSearchResults');
    const input     = document.getElementById('imSearchInput');

    // Disable button immediately to prevent double-clicks
    if (btn) {
        btn.disabled = true;
        btn.textContent = '…';
        btn.style.background = '#9ca3af';
        btn.style.cursor = 'not-allowed';
    }
    if (statusMsg) { statusMsg.style.color = '#6b7280'; statusMsg.textContent = 'Sending invitation…'; }

    try {
        const res = await fetch(INVITE_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ alumni_id: userId }),
        });
        const data = await res.json();

        if (!res.ok) {
            const msg = data.errors
                ? Object.values(data.errors).flat()[0]
                : (data.error || data.message || 'Failed to send invitation.');
            throw new Error(msg);
        }

        if (statusMsg) { statusMsg.style.color = '#16a34a'; statusMsg.textContent = `✓ Invitation sent to ${userName}.`; }
        if (input)   { input.value = ''; }
        if (results) { results.style.display = 'none'; results.innerHTML = ''; }
        // Keep button disabled after success — user was removed from results
        if (btn) { btn.textContent = 'Sent ✓'; }

    } catch (err) {
        if (statusMsg) { statusMsg.style.color = '#b91c1c'; statusMsg.textContent = err.message; }
        // Re-enable button so admin can retry
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Invite';
            btn.style.background = '#E8640C';
            btn.style.cursor = 'pointer';
        }
    }
}
</script>
@endpush

@if($isApproved)
@push('scripts')
<script>
window.FeedConfig = {!! json_encode([
    'csrfToken'     => csrf_token(),
    'currentUserId' => (int) session('alumni_id'),
    'currentUserName' => session('alumni_name', 'Alumni'),
    'currentUserAvatar' => session('alumni_avatar') ? asset('storage/' . session('alumni_avatar')) : null,
    'currentUserInitials' => strtoupper(substr(session('alumni_name', 'A'), 0, 1)),
    'routes' => [
        'feed'           => route('groups.feed', $group->slug),
        'store'          => route('groups.posts.store', $group->slug),
        'destroy'        => url('/posts/__ID__'),
        'update'         => url('/posts/__ID__'),
        'editApprove'    => url('/posts/__ID__/edit/approve'),
        'editReject'     => url('/posts/__ID__/edit/reject'),
        'pendingEdits'   => route('groups.pending-edits', $group->slug),
        'like'           => url('/posts/__ID__/like'),
        'save'           => url('/posts/__ID__/save'),
        'share'          => url('/posts/__ID__/share'),
        'comments'       => url('/posts/__ID__/comments'),
        'commentDestroy' => url('/posts/__POST_ID__/comments/__ID__'),
        'commentLike'    => url('/comments/__ID__/like'),
        'postShow'       => url('/posts/__ID__'),
        'batchCounts'    => route('posts.batch-counts'),
    ],
]) !!};
</script>
<script src="{{ asset('js/community/feed-core.js') }}?v=5" defer></script>
<script src="{{ asset('js/community/feed.js') }}?v=5" defer></script>
@endpush
@endif