@extends('layouts.community')

@section('hideRightSidebar', true)
@section('title', 'Pending Review — ' . $group->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/community/groups.css') }}">
<link rel="stylesheet" href="{{ asset('css/community/feed.css') }}?v=5">
@endpush

@section('content')
<div class="groups-page groups-page--medium">

    <a href="{{ route('groups.show', $group->slug) }}" class="groups-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to {{ $group->name }}
    </a>

    <div class="groups-hero">
        <div>
            <p class="groups-hero__eyebrow">Group Management</p>
            <h1 class="groups-hero__title">Pending Review</h1>
            <p class="groups-hero__sub">Approve or reject new posts and member edits before they appear in the group feed.</p>
        </div>
    </div>

    @php $totalPending = $pendingPosts->count() + $pendingEdits->count(); @endphp

    @if($totalPending === 0)
        <div class="pe-empty">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/>
            </svg>
            <p>All caught up — nothing pending review.</p>
        </div>
    @else

        {{-- ── New posts awaiting approval ─────────────────────────── --}}
        @if($pendingPosts->count() > 0)
            <div class="gm-section">
                <div class="gm-section__header">
                    <span class="gm-section__title">New Posts</span>
                    <span class="gm-section__count">{{ $pendingPosts->count() }}</span>
                </div>

                @foreach($pendingPosts as $post)
                    @php $author = $post->author; @endphp
                    <div class="pe-card" id="pe-card-{{ $post->id }}">
                        <div class="pe-card__author">
                            <div class="gm-avatar">
                                @if(!empty($author?->photo))
                                    <img loading="lazy" src="{{ asset('storage/' . $author->photo) }}" alt="{{ $author->full_name }}">
                                @else
                                    {{ $author?->initials ?? '?' }}
                                @endif
                            </div>
                            <div>
                                <div class="pe-card__name">{{ $author?->full_name ?? 'Unknown' }}</div>
                                <div class="pe-card__meta">Submitted {{ $post->created_at->diffForHumans() }}</div>
                            </div>
                        </div>

                        <div class="pe-new-post">
                            <div class="pe-diff__label" style="color:#1C2331;margin-bottom:8px">Post content</div>
                            <div class="pe-diff__body">{{ $post->body ?? '(no text — media only)' }}</div>
                            @if($post->media && $post->media->count() > 0)
                                <div class="pe-card__meta" style="margin-top:8px">
                                    + {{ $post->media->count() }} media file(s) attached
                                </div>
                            @endif
                        </div>

                        <div class="pe-card__actions">
                            <form method="POST" action="{{ route('posts.edit.reject', $post->id) }}"
                                  data-confirm-title="Reject this post?"
                                  data-confirm-body="The post will be permanently deleted."
                                  data-confirm-text="Reject & Delete"
                                  data-confirm-danger="true"
                                  class="pe-ajax-form"
                                  data-post-id="{{ $post->id }}"
                                  data-action-type="reject">
                                @csrf
                                <button type="submit" class="feed-btn">Reject</button>
                            </form>
                            <form method="POST" action="{{ route('posts.edit.approve', $post->id) }}"
                                  class="pe-ajax-form"
                                  data-post-id="{{ $post->id }}"
                                  data-action-type="approve">
                                @csrf
                                <button type="submit" class="feed-btn feed-btn--primary">Approve & Publish</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── Pending edits on published posts ────────────────────── --}}
        @if($pendingEdits->count() > 0)
            <div class="gm-section">
                <div class="gm-section__header">
                    <span class="gm-section__title">Pending Edits</span>
                    <span class="gm-section__count">{{ $pendingEdits->count() }}</span>
                </div>

                @foreach($pendingEdits as $post)
                    @php $author = $post->author; @endphp
                    <div class="pe-card" id="pe-card-{{ $post->id }}">
                        <div class="pe-card__author">
                            <div class="gm-avatar">
                                @if(!empty($author?->photo))
                                    <img loading="lazy" src="{{ asset('storage/' . $author->photo) }}" alt="{{ $author->full_name }}">
                                @else
                                    {{ $author?->initials ?? '?' }}
                                @endif
                            </div>
                            <div>
                                <div class="pe-card__name">{{ $author?->full_name ?? 'Unknown' }}</div>
                                <div class="pe-card__meta">Edit submitted {{ $post->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>

                        <div class="pe-diff">
                            <div class="pe-diff__col pe-diff__col--current">
                                <div class="pe-diff__label">Current</div>
                                <div class="pe-diff__body">{{ $post->body ?? '(no text)' }}</div>
                            </div>
                            <div class="pe-diff__arrow">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"/><path d="m13 6 6 6-6 6"/>
                                </svg>
                            </div>
                            <div class="pe-diff__col pe-diff__col--proposed">
                                <div class="pe-diff__label">Proposed</div>
                                <div class="pe-diff__body">{{ $post->pending_body }}</div>
                            </div>
                        </div>

                        <div class="pe-card__actions">
                            <form method="POST" action="{{ route('posts.edit.reject', $post->id) }}"
                                  data-confirm-title="Reject this edit?"
                                  data-confirm-body="The post will keep its current text and stay visible."
                                  data-confirm-text="Reject"
                                  data-confirm-danger="true"
                                  class="pe-ajax-form"
                                  data-post-id="{{ $post->id }}"
                                  data-action-type="reject">
                                @csrf
                                <button type="submit" class="feed-btn">Reject</button>
                            </form>
                            <form method="POST" action="{{ route('posts.edit.approve', $post->id) }}"
                                  class="pe-ajax-form"
                                  data-post-id="{{ $post->id }}"
                                  data-action-type="approve">
                                @csrf
                                <button type="submit" class="feed-btn feed-btn--primary">Approve Edit</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    @endif

</div>
@endsection

@push('scripts')
<script>
// AJAX handlers — approve animates card out, reject uses confirm modal then animates out
document.querySelectorAll('.pe-ajax-form[data-action-type="approve"]').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button');
        const postId = form.dataset.postId;
        const card   = document.getElementById('pe-card-' + postId);

        btn.disabled    = true;
        btn.textContent = 'Approving…';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            if (!res.ok) throw new Error('Failed');
            removeCard(card);
        } catch {
            btn.disabled    = false;
            btn.textContent = 'Approve';
        }
    });
});

// Reject is handled automatically by confirm-modal.js data-confirm-* attributes
// After confirm the form submits normally (page reload), which is fine for reject.
// But for a smoother UX we intercept it too:
document.querySelectorAll('.pe-ajax-form[data-action-type="reject"]').forEach(form => {
    // The data-confirm-* listener from confirm-modal.js will preventDefault first.
    // We override form.submit() to do an AJAX call instead:
    const origSubmit = HTMLFormElement.prototype.submit;
    form.addEventListener('submit', async (e) => {
        // If this fires naturally (not from confirm modal) it means no confirm attr — skip
        if (!form.hasAttribute('data-confirm-title')) {
            e.preventDefault();
            doReject(form);
        }
        // If data-confirm-title is present, confirm-modal.js handles preventDefault,
        // then calls form.submit() directly (bypasses this listener) — so we need
        // to monkey-patch form.submit:
        form.submit = () => doReject(form);
    });
});

async function doReject(form) {
    const btn    = form.querySelector('button');
    const postId = form.dataset.postId;
    const card   = document.getElementById('pe-card-' + postId);

    btn.disabled    = true;
    btn.textContent = 'Rejecting…';

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        if (!res.ok) throw new Error('Failed');
        removeCard(card);
    } catch {
        btn.disabled    = false;
        btn.textContent = 'Reject';
    }
}

function removeCard(card) {
    if (!card) return;
    card.style.transition = 'opacity .25s, transform .25s';
    card.style.opacity    = '0';
    card.style.transform  = 'scale(.97)';
    setTimeout(() => {
        card.remove();
        // Update section count badges
        document.querySelectorAll('.gm-section').forEach(sec => {
            const count = sec.querySelectorAll('.pe-card').length;
            const badge = sec.querySelector('.gm-section__count');
            if (badge) badge.textContent = count;
            if (count === 0) sec.remove();
        });
        // Show empty state if nothing left
        if (!document.querySelector('.pe-card') && !document.querySelector('.pe-empty')) {
            document.querySelector('.groups-page').insertAdjacentHTML('beforeend',
                `<div class="pe-empty">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                    <p>All caught up — nothing pending review.</p>
                </div>`
            );
        }
    }, 260);
}
</script>
@endpush