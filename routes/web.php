<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Alumni\DashboardController;
use App\Http\Controllers\Community\EventController;
use App\Http\Controllers\Community\JobController;
use App\Http\Controllers\Community\Admin\AdminEventController;
use App\Http\Controllers\Community\Admin\EventCategoryController;
use App\Http\Controllers\Community\Admin\AdminJobController;
use App\Http\Controllers\Community\JobApplicationController;
use App\Http\Middleware\AlumniAuth;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Community\StoryController;
use App\Http\Controllers\Community\Admin\AdminStoryController;
use App\Http\Controllers\Community\AlumniDirectoryController;
use App\Http\Controllers\Community\AlumniProfileController;
use App\Http\Controllers\Community\ChatController;
use App\Http\Controllers\Admin\AlumniDataController;
use App\Http\Controllers\Community\PostController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\NoticeCategoryController;
use App\Http\Controllers\NewsPublicController;
use App\Http\Controllers\NoticePublicController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Community\NotificationController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\Admin\AdminNewsletterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Community\GroupController;
use App\Http\Controllers\ContactController;

/*
|==========================================================================
| PUBLIC WEBSITE
|==========================================================================
*/

Route::get('/', function () {
    return view('home.index', [
        'latestNews' => \App\Models\News::published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->take(3)
            ->get(),

        'stories' => \App\Models\Story::published()
            ->with('creator')
            ->latest()
            ->take(3)
            ->get(),

        'recentAlumni' => \App\Models\AlumniUser::where('role', 'alumni')
            ->where('is_approved', true)
            ->latest('created_at')
            ->take(8)
            ->get(),

        'galleryItems' => \App\Models\GalleryItem::published()
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->take(6)
            ->get(),
    ]);
})->name('home');

Route::get('/alumni', [AlumniDirectoryController::class, 'index'])->name('alumni');

Route::get('/news', [NewsPublicController::class, 'index'])->name('news');
Route::get('/news/{news:slug}', [NewsPublicController::class, 'show'])->name('news.show');

Route::get('/notice', [NoticePublicController::class, 'index'])->name('notice');
Route::get('/notice/{notice:slug}', [NoticePublicController::class, 'show'])->name('notice.show');

Route::get('/contact',  [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');


/*
|==========================================================================
| COMMUNITY PUBLIC PAGES (browseable without login)
|==========================================================================
*/

Route::get('/jobs', [JobController::class, 'index'])
    ->name('jobs.index');

Route::get('/events', [EventController::class, 'index'])
    ->name('events.index');

Route::get('/stories', [StoryController::class, 'index'])
    ->name('stories.index');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:5,1')
    ->name('newsletter.subscribe');
 
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

Route::get('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribeForm'])
    ->name('newsletter.unsubscribe.form');
 
Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribeByEmail'])
    ->middleware('throttle:5,1')
    ->name('newsletter.unsubscribe.email');


/*
|==========================================================================
| AUTHENTICATION
|==========================================================================
*/

Route::get('/signup', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/signup', [AuthController::class, 'register'])
    ->name('register.store');

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.authenticate');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::get('/refresh-captcha', function () {
    return response()->json([
        'captcha' => captcha_img('flat')
    ]);
})->name('refresh.captcha');

Route::get('/verify-otp', [AuthController::class, 'showOtpVerify'])
    ->name('otp.verify.show');

Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])
    ->name('otp.verify');

Route::post('/resend-otp', [AuthController::class, 'resendOtp'])
    ->name('otp.resend');

Route::get('/forgot-password', [PasswordController::class, 'showForgotForm'])
    ->name('password.forgot');

Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [PasswordController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [PasswordController::class, 'resetPassword'])
    ->name('password.update');


//  Newsletter Admin   

Route::middleware('admin.auth')->group(function () {
    Route::get('/admin/newsletter/export', [AdminNewsletterController::class, 'export'])
        ->name('admin.newsletter.export');
 
    Route::get('/admin/newsletter', [AdminNewsletterController::class, 'index'])
        ->name('admin.newsletter.index');
 
    Route::post('/admin/newsletter', [AdminNewsletterController::class, 'store'])
        ->name('admin.newsletter.store');
 
    Route::patch('/admin/newsletter/{newsletterSubscriber}/toggle-status', [AdminNewsletterController::class, 'toggleStatus'])
        ->name('admin.newsletter.toggle-status');
 
    Route::delete('/admin/newsletter/{newsletterSubscriber}', [AdminNewsletterController::class, 'destroy'])
        ->name('admin.newsletter.destroy');
});


/*
|==========================================================================
| ADMIN — IMAGE GALLERY (admin.auth)
|==========================================================================
*/

Route::middleware('admin.auth')->group(function () {
    Route::get('/admin/gallery', [GalleryController::class, 'index'])->name('admin.gallery.index');
    Route::get('/admin/gallery/create', [GalleryController::class, 'create'])->name('admin.gallery.create');
    Route::post('/admin/gallery', [GalleryController::class, 'store'])->name('admin.gallery.store');
    Route::get('/admin/gallery/{galleryItem}/edit', [GalleryController::class, 'edit'])->name('admin.gallery.edit');
    Route::put('/admin/gallery/{galleryItem}', [GalleryController::class, 'update'])->name('admin.gallery.update');
    Route::delete('/admin/gallery/{galleryItem}', [GalleryController::class, 'destroy'])->name('admin.gallery.destroy');
    Route::patch('/admin/gallery/{galleryItem}/toggle-status', [GalleryController::class, 'toggleStatus'])->name('admin.gallery.toggle-status');
    Route::post('/admin/gallery/{galleryItem}/move-up', [GalleryController::class, 'moveUp'])->name('admin.gallery.move-up');
    Route::post('/admin/gallery/{galleryItem}/move-down', [GalleryController::class, 'moveDown'])->name('admin.gallery.move-down');
});


/*
|==========================================================================
| AUTHENTICATED ALUMNI AREA (alumni.auth)
|
| Any approved, logged-in alumni user. This is the largest group and
| covers: dashboard, events/jobs/stories self-service, profile,
| settings, chat, the social feed, and the public detail pages that
| require login (events.show, stories.show).
|==========================================================================
*/

Route::middleware('alumni.auth')->group(function () {

    /*
    |----------------------------------------------------------------
    | Dashboard
    |----------------------------------------------------------------
    */

    Route::get('/home', [DashboardController::class, 'index'])
        ->name('dashboard.home');

    /*
    |----------------------------------------------------------------
    | Job Applicants (for jobs the current user posted)
    |----------------------------------------------------------------
    */ 

    Route::get('/my-jobs/{job}/applicants', [JobApplicationController::class, 'applicants'])
        ->name('jobs.applicants');

    Route::patch('/my-jobs/{job}/applicants/{application}/status', [JobApplicationController::class, 'updateStatus'])
        ->name('jobs.applicants.status');

    /*
    |----------------------------------------------------------------
    | Event Creation
    |----------------------------------------------------------------
    */

    Route::get('/events/create', [EventController::class, 'create'])
        ->name('events.create');

    Route::post('/events/store', [EventController::class, 'store'])
        ->name('events.store');

    Route::get('/search', [SearchController::class, 'search'])->name('search');
    /*
    |----------------------------------------------------------------
    | My Events
    |----------------------------------------------------------------
    */

    Route::get('/my-events', [EventController::class, 'myEvents'])
        ->name('events.my');

    Route::get('/my-events/{event}/edit', [EventController::class, 'edit'])
        ->name('events.edit');

    Route::get('/my-events/{event}', [EventController::class, 'show'])
        ->name('events.my.show');

    Route::put('/my-events/{event}', [EventController::class, 'update'])
        ->name('events.update');

    Route::delete('/my-events/{event}', [EventController::class, 'destroy'])
        ->name('events.destroy');

    Route::post('/events/{id}/register', [EventController::class, 'register'])
        ->name('events.register');

    Route::get('/my-events/{event}/registrations', [EventController::class, 'registrations'])
        ->name('events.registrations');

    Route::post('/my-events/{eventId}/mark-seen', function ($eventId) {
        $seen = session('events_regs_seen', []);
        $seen[$eventId] = now()->toDateTimeString();
        session(['events_regs_seen' => $seen]);
        return response()->json(['ok' => true]);
    })->name('events.registrations.mark-seen');

    Route::post('/my-events/{eventId}/mark-seen', [EventController::class, 'markRegistrationsSeen'])
    ->name('events.registrations.mark-seen');

    /*
    |----------------------------------------------------------------
    | Job Creation
    |----------------------------------------------------------------
    */

    Route::get('/jobs/create', [JobController::class, 'create'])
        ->name('jobs.create');

    Route::post('/jobs/store', [JobController::class, 'store'])
        ->name('jobs.store');

    /*
    |----------------------------------------------------------------
    | My Jobs
    |----------------------------------------------------------------
    */

    Route::get('/my-jobs', [JobController::class, 'myJobs'])
        ->name('jobs.my');

    Route::get('/my-jobs/{job}/edit', [JobController::class, 'edit'])
        ->name('jobs.edit');

    Route::put('/my-jobs/{job}', [JobController::class, 'update'])
        ->name('jobs.update');

    Route::delete('/my-jobs/{job}', [JobController::class, 'destroy'])
        ->name('jobs.destroy');

    /*
    |----------------------------------------------------------------
    | Job Applications
    |----------------------------------------------------------------
    */

    Route::get('/jobs/{job}/apply', [JobApplicationController::class, 'create'])
        ->name('jobs.apply');

    Route::post('/jobs/{job}/apply', [JobApplicationController::class, 'store'])
        ->name('jobs.apply.store');

    Route::get('/my-applications', [JobApplicationController::class, 'myApplications'])
        ->name('jobs.my-applications');

    /*
    |----------------------------------------------------------------
    | Stories — self-service (create / my stories / edit / delete)
    |----------------------------------------------------------------
    */

    Route::get('/stories/create', [StoryController::class, 'create'])
        ->name('stories.create');

    Route::post('/stories', [StoryController::class, 'store'])
        ->name('stories.store');

    Route::get('/my-stories', [StoryController::class, 'myStories'])
        ->name('stories.my');

    Route::get('/my-stories/{story}/edit', [StoryController::class, 'edit'])
        ->name('stories.edit');

    Route::put('/my-stories/{story}', [StoryController::class, 'update'])
        ->name('stories.update');

    Route::delete('/my-stories/{story}', [StoryController::class, 'destroy'])
        ->name('stories.destroy');

    /*
    |----------------------------------------------------------------
    | Alumni Profile / Directory / Settings
    |----------------------------------------------------------------
    */

    Route::get('/members/{alumniUser}', [AlumniProfileController::class, 'show'])
        ->name('alumni.profile');

    Route::get('/profile', [\App\Http\Controllers\Community\ProfileController::class, 'index'])
        ->name('profile.index');

    Route::post('/profile/info', [\App\Http\Controllers\Community\ProfileController::class, 'updateInfo'])
        ->name('profile.update.info');

    Route::post('/profile/photo', [\App\Http\Controllers\Community\ProfileController::class, 'updatePhoto'])
        ->name('profile.update.photo');

    Route::post('/profile/password', [\App\Http\Controllers\Community\ProfileController::class, 'updatePassword'])
        ->name('profile.update.password');

    Route::get('/alumni-directory', [AlumniDirectoryController::class, 'index'])
        ->name('alumni.directory');

    Route::get('/settings', [\App\Http\Controllers\Community\SettingsController::class, 'index'])
        ->name('settings.index');

    Route::post('/settings/notifications', [\App\Http\Controllers\Community\SettingsController::class, 'updateNotifications'])
        ->name('settings.notifications');

    Route::post('/settings/preferences', [\App\Http\Controllers\Community\SettingsController::class, 'updatePreferences'])
        ->name('settings.preferences');

    Route::delete('/settings/sessions/{session}', [\App\Http\Controllers\Community\SettingsController::class, 'revokeSession'])
        ->name('settings.sessions.revoke');

    /*
    |----------------------------------------------------------------
    | Chat
    |----------------------------------------------------------------
    */

    Route::get('/chat', [ChatController::class, 'index'])
        ->name('chat.index');

    Route::get('/chat/conversations', [ChatController::class, 'conversations'])
        ->name('chat.conversations');

    Route::get('/chat/conversations/{id}/messages', [ChatController::class, 'messages'])
        ->name('chat.messages');

    Route::get('/chat/conversations/{id}/poll', [ChatController::class, 'poll'])
        ->name('chat.poll');

    Route::get('/chat/poll-conversations', [ChatController::class, 'pollConversations'])
        ->name('chat.poll-conversations');

    Route::post('/chat/conversations/{id}/messages', [ChatController::class, 'sendMessage'])
        ->name('chat.send');

    Route::delete('/chat/messages/{messageId}', [ChatController::class, 'deleteMessage'])
        ->name('chat.delete');

    Route::get('/chat/users/search', [ChatController::class, 'searchUsers'])
        ->name('chat.search-users');

    Route::post('/chat/direct', [ChatController::class, 'startDirect'])
        ->name('chat.direct');

    Route::post('/chat/groups', [ChatController::class, 'createGroup'])
        ->name('chat.groups.store');

    Route::get('/chat/groups/{id}/info', [ChatController::class, 'groupInfo'])
        ->name('chat.groups.info');

    Route::put('/chat/groups/{id}', [ChatController::class, 'updateGroup'])
        ->name('chat.groups.update');

    Route::post('/chat/groups/{id}/members', [ChatController::class, 'addMembers'])
        ->name('chat.groups.members.store');

    Route::delete('/chat/groups/{id}/members/{memberId}', [ChatController::class, 'removeMember'])
        ->name('chat.groups.members.destroy');

    Route::post('/chat/groups/{id}/promote/{memberId}', [ChatController::class, 'promoteAdmin'])
        ->name('chat.groups.members.promote');

    Route::post('/chat/groups/{id}/invite/regenerate', [ChatController::class, 'regenerateInvite'])
        ->name('chat.groups.invite.regenerate');

    Route::get('/chat/join/{token}', [ChatController::class, 'joinPage'])
        ->name('chat.join');

    Route::post('/chat/join/{token}', [ChatController::class, 'joinGroup'])
        ->name('chat.join.store');

    Route::get('/chat/groups/{id}/join-requests', [ChatController::class, 'joinRequests'])
        ->name('chat.groups.join-requests');

    Route::patch('/chat/groups/{id}/join-requests/{requestId}', [ChatController::class, 'handleJoinRequest'])
        ->name('chat.groups.join-requests.update');

    Route::get('/chat/users/online-status', [ChatController::class, 'onlineStatus'])
        ->name('chat.online-status');

    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount'])
        ->name('chat.unread-count');

    Route::get('/chat/conversations/{id}/tick-updates', [ChatController::class, 'tickUpdates'])
        ->name('chat.tick-updates');

    Route::post('/chat/mark-offline', [ChatController::class, 'markOffline'])
        ->name('chat.mark-offline');

    /*
    |----------------------------------------------------------------
    | Social Feed (Posts / Comments)
    |----------------------------------------------------------------
    */

    Route::get('/feed', [PostController::class, 'feed'])
        ->name('posts.feed');

    Route::post('/posts', [PostController::class, 'store'])
        ->name('posts.store');

    Route::delete('/posts/{id}', [PostController::class, 'destroy'])
        ->name('posts.destroy');

    Route::post('/posts/{id}/like', [PostController::class, 'toggleLike'])
        ->name('posts.like');

    Route::post('/posts/{id}/save', [PostController::class, 'toggleSave'])
        ->name('posts.save');

    Route::get('/profile/saved-posts', [PostController::class, 'savedPosts'])
        ->name('posts.saved');

    Route::get('/profile/my-posts', [PostController::class, 'myPosts'])
        ->name('posts.my');

    Route::post('/posts/{id}/share', [PostController::class, 'share'])
        ->name('posts.share');

    Route::get('/posts/{id}/comments', [PostController::class, 'comments'])
        ->name('posts.comments');

    Route::post('/posts/{id}/comments', [PostController::class, 'storeComment'])
        ->name('posts.comments.store');

    Route::delete('/posts/{postId}/comments/{commentId}', [PostController::class, 'destroyComment'])
        ->name('posts.comments.destroy');

    Route::post('/comments/{id}/like', [PostController::class, 'toggleCommentLike'])
        ->name('comments.like');

    Route::get('/posts/{post}', [PostController::class, 'show'])
        ->name('posts.show');

    Route::get('/events/{event:slug}', [EventController::class, 'show'])
        ->name('events.show');

    Route::get('/stories/{story:slug}', [StoryController::class, 'show'])
        ->name('stories.show');

    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])
        ->name('notifications.mark-read');

    Route::get('/notifications/personal', [\App\Http\Controllers\Community\NotificationController::class, 'personal'])
        ->name('notifications.personal');

    Route::post('/notifications/personal/mark-read', [\App\Http\Controllers\Community\NotificationController::class, 'markPersonalRead'])
        ->name('notifications.personal.mark-read');

    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Community\NotificationController::class, 'markOneRead'])
        ->where('id', '[0-9]+')
        ->name('notifications.mark-one-read');

    // ── Community Groups ────────────────────────────────────────────────
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group:slug}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{group:slug}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{group:slug}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::get('/groups/{group:slug}/feed', [PostController::class, 'feed'])->name('groups.feed');
    Route::post('/groups/{group:slug}/posts', [PostController::class, 'store'])->name('groups.posts.store');

    Route::get('/groups/{group:slug}/members', [GroupController::class, 'members'])->name('groups.members');
    Route::post('/groups/{group:slug}/members/{member}/approve', [GroupController::class, 'approveMember'])->name('groups.members.approve');
    Route::post('/groups/{group:slug}/members/{member}/reject', [GroupController::class, 'rejectMember'])->name('groups.members.reject');
    Route::patch('/groups/{group:slug}/members/{member}/role', [GroupController::class, 'updateMemberRole'])->name('groups.members.role');
    Route::delete('/groups/{group:slug}/members/{member}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::patch('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::post('/posts/{id}/edit/approve', [PostController::class, 'approveEdit'])->name('posts.edit.approve');
    Route::post('/posts/{id}/edit/reject', [PostController::class, 'rejectEdit'])->name('posts.edit.reject');
    Route::get('/groups/{group:slug}/pending-edits', [GroupController::class, 'pendingEdits'])->name('groups.pending-edits');
});


/*
|==========================================================================
| ADMIN — USER & ALUMNI MANAGEMENT (admin.auth)
|==========================================================================
*/

Route::middleware('admin.auth')->group(function () {

    Route::get('/admin/users/pending', [AdminController::class, 'pendingUsers'])
        ->name('admin.users.pending');

    Route::patch('/admin/users/{user}/approve', [AdminController::class, 'approveUser'])
        ->name('admin.users.approve');

    Route::delete('/admin/users/{user}/reject', [AdminController::class, 'rejectUser'])
        ->name('admin.users.reject');

    // Alumni management (CRUD)
    Route::get('/admin/alumni', [AdminController::class, 'alumniIndex'])
        ->name('admin.alumni.index');
    Route::get('/admin/alumni/{user}', [AdminController::class, 'alumniShow'])
        ->name('admin.alumni.show');
    Route::get('/admin/alumni/{user}/edit', [AdminController::class, 'alumniEdit'])
        ->name('admin.alumni.edit');
    Route::put('/admin/alumni/{user}', [AdminController::class, 'alumniUpdate'])
        ->name('admin.alumni.update');
    Route::delete('/admin/alumni/{user}', [AdminController::class, 'alumniDestroy'])
        ->name('admin.alumni.destroy');
    Route::patch('/admin/alumni/{user}/toggle-approval', [AdminController::class, 'alumniToggleApproval'])
        ->name('admin.alumni.toggle-approval');

    // List all current admins / super admins
    Route::get('/admin/users', [AdminController::class, 'index'])
        ->name('admin.users.index');

    Route::delete('/admin/users/{user}/revoke', [AdminController::class, 'revokeAdmin'])
        ->name('admin.users.revoke');

    /*
    |----------------------------------------------------------------
    | Alumni Data Import / Export
    | SECURITY FIX: previously only required 'alumni.auth', meaning
    | ANY logged-in alumni could view/export/import/clear the entire
    | alumni dataset. Moved here under admin.auth.
    |----------------------------------------------------------------
    */

    Route::get('/admin/alumni-data/template', [AlumniDataController::class, 'template'])->name('admin.alumni-data.template');
    Route::get('/admin/alumni-data/export',   [AlumniDataController::class, 'export'])->name('admin.alumni-data.export');
    Route::get('/admin/alumni-data',          [AlumniDataController::class, 'index'])->name('admin.alumni-data.index');
    Route::post('/admin/alumni-data/import',  [AlumniDataController::class, 'import'])->name('admin.alumni-data.import');
    Route::delete('/admin/alumni-data',       [AlumniDataController::class, 'clearAll'])->name('admin.alumni-data.clear');
    Route::delete('/admin/alumni-data/{id}',  [AlumniDataController::class, 'destroy'])->name('admin.alumni-data.destroy');
});


/*
|==========================================================================
| ADMIN — CREATE ADMIN (admin.auth + super_admin.auth)
|==========================================================================
*/

Route::middleware(['admin.auth', 'super_admin.auth'])->group(function () {

    Route::get('/admin/users/create-admin', [AdminController::class, 'createAdminForm'])
        ->name('admin.users.create-admin');

    Route::post('/admin/users/create-admin', [AdminController::class, 'storeAdmin'])
        ->name('admin.users.store-admin');

    Route::get('/admin/users/{user}/edit', [AdminController::class, 'editAdmin'])
        ->name('admin.users.edit-admin');

    Route::put('/admin/users/{user}', [AdminController::class, 'updateAdmin'])
        ->name('admin.users.update-admin');

    Route::delete('/admin/users/{user}/delete', [AdminController::class, 'destroyAdmin'])
        ->name('admin.users.destroy-admin');
});


/*
|==========================================================================
| STORY MODERATION (Admin / Moderator with approve_stories)
|
| SECURITY FIX: 'pending' / 'approve' / 'reject' were previously gated
| only by 'alumni.auth', meaning ANY logged-in alumni could approve or
| reject ANY pending story. Now mirrors the Event/Job moderation pattern:
| admins & super_admins always pass (AlumniUser::hasPermission()),
| moderators need permissions['approve_stories'] = true.
|==========================================================================
*/

Route::middleware([
    'alumni.auth',
    'alumni.permission:approve_stories',
])->group(function () {

    Route::get('/admin/stories/pending', [AdminStoryController::class, 'pending'])
        ->name('admin.stories.pending');

    Route::patch('/admin/stories/{story}/approve', [AdminStoryController::class, 'approve'])
        ->name('admin.stories.approve');

    Route::patch('/admin/stories/{story}/reject', [AdminStoryController::class, 'reject'])
        ->name('admin.stories.reject');
});


/*
|==========================================================================
| EVENT MODERATION (Admin / Moderator with approve_events)
|==========================================================================
*/

Route::middleware([
    'alumni.auth',
    'alumni.permission:approve_events',
])->group(function () {

    Route::get('/admin/events/pending', [AdminEventController::class, 'pending'])
        ->name('admin.events.pending');

    Route::patch('/admin/events/{event}/approve', [AdminEventController::class, 'approve'])
        ->name('admin.events.approve');

    Route::patch('/admin/events/{event}/reject', [AdminEventController::class, 'reject'])
        ->name('admin.events.reject');
});


/*
|==========================================================================
| EVENT CATEGORIES (Admin / Moderator with manage_event_categories)
|==========================================================================
*/

Route::middleware([
    'alumni.auth',
    'alumni.permission:manage_event_categories',
])->group(function () {

    Route::get('/admin/event-categories', [EventCategoryController::class, 'index'])
        ->name('admin.event-categories.index');

    Route::post('/admin/event-categories', [EventCategoryController::class, 'store'])
        ->name('admin.event-categories.store');

    Route::get('/admin/event-categories/{eventCategory}', [EventCategoryController::class, 'show'])
        ->name('admin.event-categories.show');

    Route::put('/admin/event-categories/{eventCategory}', [EventCategoryController::class, 'update'])
        ->name('admin.event-categories.update');

    Route::patch('/admin/event-categories/{eventCategory}/toggle', [EventCategoryController::class, 'toggle'])
        ->name('admin.event-categories.toggle');

    Route::delete('/admin/event-categories/{eventCategory}', [EventCategoryController::class, 'destroy'])
        ->name('admin.event-categories.destroy');
});


/*
|==========================================================================
| ADMIN — ALL EVENTS (admin.auth)
|==========================================================================
*/

Route::middleware('admin.auth')->group(function () {

    Route::get('/admin/events', [AdminEventController::class, 'allEvents'])
        ->name('admin.events.index');

    Route::put('/admin/events/{event}', [AdminEventController::class, 'update'])
        ->name('admin.events.update');

    Route::delete('/admin/events/{event}', [AdminEventController::class, 'destroy'])
        ->name('admin.events.delete');
});


/*
|==========================================================================
| ADMIN — ALL STORIES (admin.auth)
|
| SECURITY FIX: 'index' / 'update' / 'destroy' were previously gated
| only by 'alumni.auth', meaning ANY logged-in alumni could view the
| full admin stories list, edit any story's category/status, or
| delete any story outright.
|==========================================================================
*/

Route::middleware('admin.auth')->group(function () {

    Route::get('/admin/stories', [AdminStoryController::class, 'index'])
        ->name('admin.stories.index');

    Route::put('/admin/stories/{story}', [AdminStoryController::class, 'update'])
        ->name('admin.stories.update');

    Route::delete('/admin/stories/{story}', [AdminStoryController::class, 'destroy'])
        ->name('admin.stories.destroy');
});


/*
|==========================================================================
| ADMIN — NEWS & NOTICES (admin.auth)
|==========================================================================
*/

Route::middleware('admin.auth')->group(function () {

    // ── News ─────────────────────────────────────────────────────────
    Route::get('/admin/news', [NewsController::class, 'index'])->name('admin.news.index');
    Route::get('/admin/news/create', [NewsController::class, 'create'])->name('admin.news.create');
    Route::post('/admin/news', [NewsController::class, 'store'])->name('admin.news.store');
    Route::get('/admin/news/{news}/edit', [NewsController::class, 'edit'])->name('admin.news.edit');
    Route::put('/admin/news/{news}', [NewsController::class, 'update'])->name('admin.news.update');
    Route::delete('/admin/news/{news}', [NewsController::class, 'destroy'])->name('admin.news.destroy');
    Route::patch('/admin/news/{news}/toggle-status', [NewsController::class, 'toggleStatus'])->name('admin.news.toggle-status');

    // News categories (AJAX/JSON — used by the "Manage Categories" modal)
    Route::get('/admin/news-categories', [NewsCategoryController::class, 'index'])->name('admin.news-categories.index');
    Route::post('/admin/news-categories', [NewsCategoryController::class, 'store'])->name('admin.news-categories.store');
    Route::put('/admin/news-categories/{newsCategory}', [NewsCategoryController::class, 'update'])->name('admin.news-categories.update');
    Route::patch('/admin/news-categories/{newsCategory}/toggle', [NewsCategoryController::class, 'toggle'])->name('admin.news-categories.toggle');
    Route::delete('/admin/news-categories/{newsCategory}', [NewsCategoryController::class, 'destroy'])->name('admin.news-categories.destroy');

    // ── Notices ──────────────────────────────────────────────────────
    Route::get('/admin/notices', [NoticeController::class, 'index'])->name('admin.notices.index');
    Route::get('/admin/notices/create', [NoticeController::class, 'create'])->name('admin.notices.create');
    Route::post('/admin/notices', [NoticeController::class, 'store'])->name('admin.notices.store');
    Route::get('/admin/notices/{notice}/edit', [NoticeController::class, 'edit'])->name('admin.notices.edit');
    Route::put('/admin/notices/{notice}', [NoticeController::class, 'update'])->name('admin.notices.update');
    Route::delete('/admin/notices/{notice}', [NoticeController::class, 'destroy'])->name('admin.notices.destroy');
    Route::patch('/admin/notices/{notice}/toggle-status', [NoticeController::class, 'toggleStatus'])->name('admin.notices.toggle-status');

    // Notice categories (AJAX/JSON — used by the "Manage Categories" modal)
    Route::get('/admin/notice-categories', [NoticeCategoryController::class, 'index'])->name('admin.notice-categories.index');
    Route::post('/admin/notice-categories', [NoticeCategoryController::class, 'store'])->name('admin.notice-categories.store');
    Route::put('/admin/notice-categories/{noticeCategory}', [NoticeCategoryController::class, 'update'])->name('admin.notice-categories.update');
    Route::patch('/admin/notice-categories/{noticeCategory}/toggle', [NoticeCategoryController::class, 'toggle'])->name('admin.notice-categories.toggle');
    Route::delete('/admin/notice-categories/{noticeCategory}', [NoticeCategoryController::class, 'destroy'])->name('admin.notice-categories.destroy');
});


/*
|==========================================================================
| JOB MODERATION (Admin / Moderator with approve_jobs)
|==========================================================================
*/

Route::middleware([
    'alumni.auth',
    'alumni.permission:approve_jobs',
])->group(function () {

    Route::get('/admin/jobs/pending', [AdminJobController::class, 'pending'])
        ->name('admin.jobs.pending');

    Route::patch('/admin/jobs/{job}/approve', [AdminJobController::class, 'approve'])
        ->name('admin.jobs.approve');

    Route::patch('/admin/jobs/{job}/reject', [AdminJobController::class, 'reject'])
        ->name('admin.jobs.reject');
});


/*
|==========================================================================
| ADMIN — ALL JOBS (admin.auth)
|==========================================================================
*/

Route::middleware('admin.auth')->group(function () {

    Route::get('/admin/jobs', [AdminJobController::class, 'index'])
        ->name('admin.jobs.index');

    Route::put('/admin/jobs/{job}', [AdminJobController::class, 'update'])
        ->name('admin.jobs.update');

    Route::delete('/admin/jobs/{job}', [AdminJobController::class, 'destroy'])
        ->name('admin.jobs.destroy');
});


/*
|==========================================================================
| PUBLIC DETAIL PAGES — MUST come last to avoid catching /create routes
|==========================================================================
*/

Route::get('/jobs/{job:slug}', [JobController::class, 'show'])->name('jobs.show');

/*
|==========================================================================
| DEVELOPMENT ONLY
|==========================================================================
*/

Route::get('/nav', fn() => view('components.navbar'))
    ->name('navbar');