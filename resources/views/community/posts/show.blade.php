@extends('layouts.community')
@section('hideRightSidebar', true)

@section('title', 'Post')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/home.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/community/feed.css') }}?v=5">
@endpush

@section('content')

<div class="feed-wrapper" id="postPageWrapper" style="max-width:680px;margin:0 auto;">

    <a href="{{ route('dashboard.home') }}" class="post-page-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
        Back to feed
    </a>

    <div class="feed-list" id="singlePostContainer" data-post='@json($post)'>
        {{-- Rendered client-side by post-page.js for full interactivity --}}
        <div class="feed-skeleton">
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
        </div>
    </div>

</div>

{{-- ============================================================
     LIGHTBOX (image/video viewer)
============================================================ --}}
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

@endsection

@push('styles')
<style>
.post-page-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 14px;
    transition: color 0.15s;
}
.post-page-back:hover { color: var(--iccr-saffron); }
</style>
@endpush

@push('scripts')
<script>
window.FeedConfig = {!! json_encode([
    'csrfToken'     => csrf_token(),
    'currentUserId' => (int) session('alumni_id'),
    'currentUserName' => session('alumni_name', 'Alumni'),
    'currentUserAvatar' => session('alumni_avatar') ? asset('storage/' . session('alumni_avatar')) : null,
    'currentUserInitials' => strtoupper(substr(session('alumni_name', 'A'), 0, 1)),
    'routes' => [
        'feed'           => route('posts.feed'),
        'store'          => route('posts.store'),
        'destroy'        => url('/posts/__ID__'),
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
<script src="{{ asset('js/community/feed-core.js') }}?v=5"></script>
<script src="{{ asset('js/community/post-page.js') }}?v=5"></script>
@endpush