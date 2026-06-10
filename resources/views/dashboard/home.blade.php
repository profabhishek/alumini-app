@extends('layouts.community')

@section('title', 'Community Home')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community/home.css') }}">
@endpush

@section('content')

{{-- ============================================================
     CENTER FEED
============================================================ --}}
<div class="feed-wrapper">

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
                placeholder="What's on your mind?"
                rows="4"
                id="postTextarea"
            ></textarea>
        </div>
        <div class="composer-footer">
            <div class="composer-attachments">
                <span class="attachment-label">Add to your post:</span>
                <button class="attach-btn" title="Add Photo">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21,15 16,10 5,21"/>
                    </svg>
                </button>
                <button class="attach-btn" title="Add Video">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <polygon points="23,7 16,12 23,17"/>
                        <rect x="1" y="5" width="15" height="14" rx="2"/>
                    </svg>
                </button>
            </div>
            <button class="btn-post" id="postBtn">Post Now</button>
        </div>
    </div>

    {{-- FEED ITEMS --}}
    <div class="feed-list" id="feedList">

        {{-- FEED CARD: TEXT POST --}}
        <article class="feed-card card reveal">
            <div class="card-header">
                <div class="post-meta">
                    <div class="avatar avatar--sm">
                        <span class="avatar-initials">R</span>
                    </div>
                    <div class="post-info">
                        <span class="post-author">Rajesh Kumar Sharma</span>
                        <span class="post-time">2 hours ago &middot; <span class="post-badge">Batch 2018</span></span>
                    </div>
                </div>
                <button class="card-menu-btn" title="More options">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                    </svg>
                </button>
            </div>
            <div class="card-body">
                <p class="post-text">Thrilled to share that I've been selected as a visiting faculty at Jawaharlal Nehru University for the upcoming semester. ICCR's cultural exchange program truly opened doors I never imagined. Grateful to every mentor and batchmate who guided me along the way. 🎓</p>
            </div>
            <div class="card-actions">
                <button class="action-btn like-btn" data-liked="false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3z"/>
                        <path d="M7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/>
                    </svg>
                    <span class="action-count">24</span>
                </button>
                <button class="action-btn comment-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    <span class="action-count">8</span>
                </button>
                <button class="action-btn share-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    <span class="action-count">Share</span>
                </button>
                <button class="action-btn save-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/>
                    </svg>
                    <span class="action-count">Save</span>
                </button>
            </div>
        </article>

        {{-- FEED CARD: JOB POST --}}
        <article class="feed-card feed-card--job card reveal">
            <div class="card-header">
                <div class="post-meta">
                    <div class="avatar avatar--sm avatar--gold">
                        <span class="avatar-initials">IC</span>
                    </div>
                    <div class="post-info">
                        <span class="post-author">ICCR Official</span>
                        <span class="post-time">1 day ago &middot; <span class="post-badge post-badge--official">Official</span></span>
                    </div>
                </div>
                <span class="post-type-tag post-type-tag--job">Job</span>
            </div>
            <div class="card-body">
                <div class="job-card-inner">
                    <div class="job-logo">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                        </svg>
                    </div>
                    <div class="job-details">
                        <h3 class="job-title">Cultural Programme Officer</h3>
                        <p class="job-company">Indian Council for Cultural Relations</p>
                        <div class="job-meta-row">
                            <span class="job-meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                New Delhi
                            </span>
                            <span class="job-meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                Full-time
                            </span>
                            <span class="job-meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                ₹65,000/mo
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <button class="btn-apply">Apply Now</button>
                <div class="card-actions-right">
                    <button class="action-btn save-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                        <span class="action-count">Save</span>
                    </button>
                    <button class="action-btn share-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        <span class="action-count">Share</span>
                    </button>
                </div>
            </div>
        </article>

        {{-- FEED CARD: STORY POST --}}
        <article class="feed-card card reveal">
            <div class="card-header">
                <div class="post-meta">
                    <div class="avatar avatar--sm">
                        <span class="avatar-initials">P</span>
                    </div>
                    <div class="post-info">
                        <span class="post-author">Priya Venkataraman</span>
                        <span class="post-time">3 days ago &middot; <span class="post-badge">Batch 2015</span></span>
                    </div>
                </div>
                <span class="post-type-tag post-type-tag--story">Story</span>
            </div>
            <div class="card-body">
                <div class="story-image-wrap">
                    <div class="story-image-placeholder">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" opacity="0.3">
                            <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/>
                        </svg>
                    </div>
                </div>
                <h3 class="story-title">My Journey from ICCR Scholar to UN Cultural Envoy</h3>
                <p class="post-text story-excerpt">It was a cold December morning in Moscow when I first realised that the scholarship I had received wasn't just an academic opportunity — it was a bridge between civilisations. Seven years later, standing at the podium of the United Nations...</p>
                <button class="read-more-btn">Read More <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg></button>
            </div>
            <div class="card-actions">
                <button class="action-btn like-btn" data-liked="false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3z"/><path d="M7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/></svg>
                    <span class="action-count">87</span>
                </button>
                <button class="action-btn comment-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span class="action-count">31</span>
                </button>
                <button class="action-btn share-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    <span class="action-count">Share</span>
                </button>
                <button class="action-btn save-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                    <span class="action-count">Save</span>
                </button>
            </div>
        </article>

        {{-- FEED CARD: EVENT POST --}}
        <article class="feed-card feed-card--event card reveal">
            <div class="card-header">
                <div class="post-meta">
                    <div class="avatar avatar--sm avatar--gold">
                        <span class="avatar-initials">IC</span>
                    </div>
                    <div class="post-info">
                        <span class="post-author">ICCR Events</span>
                        <span class="post-time">5 days ago &middot; <span class="post-badge post-badge--official">Official</span></span>
                    </div>
                </div>
                <span class="post-type-tag post-type-tag--event">Event</span>
            </div>
            <div class="card-body">
                <div class="event-card-inner">
                    <div class="event-date-badge">
                        <span class="event-month">JUN</span>
                        <span class="event-day">20</span>
                    </div>
                    <div class="event-info">
                        <h3 class="event-title">India–Africa Cultural Dialogue Summit 2026</h3>
                        <div class="event-meta-row">
                            <span class="event-meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                                10:00 AM – 5:00 PM IST
                            </span>
                            <span class="event-meta-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                ICCR Headquarters, New Delhi
                            </span>
                        </div>
                        <p class="event-desc">A landmark dialogue bringing together alumni from across Africa and India to explore cultural diplomacy, shared history, and future collaborations.</p>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <button class="btn-register">Register Now</button>
                <div class="card-actions-right">
                    <button class="action-btn like-btn" data-liked="false">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3z"/><path d="M7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/></svg>
                        <span class="action-count">142</span>
                    </button>
                    <button class="action-btn share-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        <span class="action-count">Share</span>
                    </button>
                </div>
            </div>
        </article>

        {{-- FEED CARD: PHOTO POST --}}
        <article class="feed-card card reveal">
            <div class="card-header">
                <div class="post-meta">
                    <div class="avatar avatar--sm">
                        <span class="avatar-initials">A</span>
                    </div>
                    <div class="post-info">
                        <span class="post-author">Aditya Bhattacharya</span>
                        <span class="post-time">1 week ago &middot; <span class="post-badge">Batch 2020</span></span>
                    </div>
                </div>
                <button class="card-menu-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                </button>
            </div>
            <div class="card-body">
                <p class="post-text">Representing India at the International Cultural Exchange Fair in Berlin. What an incredible experience to showcase our heritage! 🇮🇳✨</p>
                <div class="photo-grid photo-grid--single">
                    <div class="photo-placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.25">
                            <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/>
                        </svg>
                        <span>Photo</span>
                    </div>
                </div>
            </div>
            <div class="card-actions">
                <button class="action-btn like-btn" data-liked="false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3z"/><path d="M7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/></svg>
                    <span class="action-count">56</span>
                </button>
                <button class="action-btn comment-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span class="action-count">14</span>
                </button>
                <button class="action-btn share-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    <span class="action-count">Share</span>
                </button>
                <button class="action-btn save-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                    <span class="action-count">Save</span>
                </button>
            </div>
        </article>

    </div>{{-- /feed-list --}}
</div>{{-- /feed-wrapper --}}

@endsection

@push('scripts')
    <script src="{{ asset('js/community/home.js') }}"></script>
@endpush