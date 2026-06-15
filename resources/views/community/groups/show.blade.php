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
        </div>

                <div>
            @if($group->description)

                <div class="group-detail-about-trigger">
                    <button type="button" class="about-btn" onclick="openAboutModal()">
                        About
                    </button>
                </div>

                <div id="aboutModal" class="custom-modal">
                    <div class="custom-modal-content">

                        <button class="custom-modal-close" onclick="closeAboutModal()">
                            &times;
                        </button>

                        <h3>About {{ $group->name }}</h3>

                        <div class="custom-modal-body">
                            {!! nl2br(e($group->description)) !!}
                        </div>

                    </div>
                </div>

            @endif
        </div>

        <div class="group-detail-bar__actions">
            @if($isApproved)
                @if(($isGroupMod || $isSiteAdmin) && $pendingCount > 0)
                    <span class="group-pending-count">
                        {{ $pendingCount }} pending request{{ $pendingCount === 1 ? '' : 's' }}
                    </span>
                @endif

                <form method="POST" action="{{ route('groups.leave', $group->slug) }}" onsubmit="return confirm('Leave this group?');">
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
        </div>


    </div>



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
                                    <img src="{{ asset('storage/' . session('alumni_avatar')) }}" alt="{{ session('alumni_name') }}">
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
                                    <img src="{{ asset('storage/' . session('alumni_avatar')) }}" alt="{{ session('alumni_name') }}">
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
        'like'           => url('/posts/__ID__/like'),
        'save'           => url('/posts/__ID__/save'),
        'share'          => url('/posts/__ID__/share'),
        'comments'       => url('/posts/__ID__/comments'),
        'commentDestroy' => url('/posts/__POST_ID__/comments/__ID__'),
        'commentLike'    => url('/comments/__ID__/like'),
        'postShow'       => url('/posts/__ID__'),
    ],
]) !!};
</script>
<script>
function openAboutModal() {
    document.getElementById('aboutModal').classList.add('active');
}

function closeAboutModal() {
    document.getElementById('aboutModal').classList.remove('active');
}

document.addEventListener('click', function(e) {
    const modal = document.getElementById('aboutModal');

    if (e.target === modal) {
        closeAboutModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAboutModal();
    }
});
</script>
<script src="{{ asset('js/community/feed-core.js') }}?v=4" defer></script>
<script src="{{ asset('js/community/feed.js') }}?v=4" defer></script>
@endpush
@endif