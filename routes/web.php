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

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('home.index'))->name('home');

Route::get('/alumni', [AlumniDirectoryController::class, 'index'])->name('alumni');

Route::get('/news', fn() => view('news.index'))
    ->name('news');

Route::get('/notice', fn() => view('notice.index'))
    ->name('notice');

Route::get('/contact', fn() => view('contact.index'))
    ->name('contact');


/*
|--------------------------------------------------------------------------
| Community Public Pages
|--------------------------------------------------------------------------
*/

Route::get('/jobs', [JobController::class, 'index'])
    ->name('jobs.index');

// Route::get('/stories', fn() => view('community.stories'))
//     ->name('community.stories');

Route::get('/events', [EventController::class, 'index'])
    ->name('events.index');

// All published stories (browseable without login)
Route::get('/stories', [StoryController::class, 'index'])
    ->name('stories.index');
 

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
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

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/home', [DashboardController::class, 'index'])
    ->name('dashboard.home');


/*
|--------------------------------------------------------------------------
| Authenticated Alumni Area
|--------------------------------------------------------------------------
*/

Route::middleware('alumni.auth')->group(function () {

    Route::get('/my-jobs/{job}/applicants', [JobApplicationController::class, 'applicants'])
        ->name('jobs.applicants');
    
    Route::patch('/my-jobs/{job}/applicants/{application}/status', [JobApplicationController::class, 'updateStatus'])
        ->name('jobs.applicants.status');

    /*
    |--------------------------------------------------------------------------
    | Event Creation
    |--------------------------------------------------------------------------
    */

    Route::get('/events/create', [EventController::class, 'create'])
        ->name('events.create');

    Route::post('/events/store', [EventController::class, 'store'])
        ->name('events.store');

    /*
    |--------------------------------------------------------------------------
    | My Events
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Job Creation
    |--------------------------------------------------------------------------
    */

    Route::get('/jobs/create', [JobController::class, 'create'])
        ->name('jobs.create');

    Route::post('/jobs/store', [JobController::class, 'store'])
        ->name('jobs.store');

    /*
    |--------------------------------------------------------------------------
    | My Jobs
    |--------------------------------------------------------------------------
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
    |--------------------------------------------------------------------------
    | Job Applications
    |--------------------------------------------------------------------------
    */

    Route::get('/jobs/{job}/apply', [JobApplicationController::class, 'create'])
        ->name('jobs.apply');

    Route::post('/jobs/{job}/apply', [JobApplicationController::class, 'store'])
        ->name('jobs.apply.store');

    Route::get('/my-applications', [JobApplicationController::class, 'myApplications'])
        ->name('jobs.my-applications');

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

    Route::put('/admin/stories/{story}', [AdminStoryController::class, 'update'])
        ->name('admin.stories.update');

    Route::get('/admin/stories/pending', [AdminStoryController::class, 'pending'])
    ->name('admin.stories.pending');
 
    Route::patch('/admin/stories/{story}/approve', [AdminStoryController::class, 'approve'])
        ->name('admin.stories.approve');
    
    Route::patch('/admin/stories/{story}/reject', [AdminStoryController::class, 'reject'])
        ->name('admin.stories.reject');
    
    Route::get('/admin/stories', [AdminStoryController::class, 'index'])
        ->name('admin.stories.index');
    
    Route::delete('/admin/stories/{story}', [AdminStoryController::class, 'destroy'])
        ->name('admin.stories.destroy');



    Route::get('/members/{alumniUser}', [AlumniProfileController::class, 'show'])
        ->name('alumni.profile');

    // ── Profile ───────────────────────────────────────────────────────────────
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
    |--------------------------------------------------------------------------
    | Chat
    |--------------------------------------------------------------------------
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
});



    Route::get('/stories/{story:slug}', [StoryController::class, 'show'])
        ->name('stories.show');
    
/*
|--------------------------------------------------------------------------
| Event Moderation (Admin / Moderator with approve_events)
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
| Event Categories (Admin / Moderator with manage_event_categories)
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
| Admin — All Events (admin.auth)
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
| Job Moderation (Admin / Moderator with approve_jobs)
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
| Admin — All Jobs (admin.auth)
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
| Public Detail Pages — MUST come last to avoid catching /create routes
|--------------------------------------------------------------------------
*/

Route::get('/events/{event}', [EventController::class, 'show'])
    ->name('events.show');

Route::get('/jobs/{job}', [JobController::class, 'show'])
    ->name('jobs.my-application');


/*
|--------------------------------------------------------------------------
| Development Only
|--------------------------------------------------------------------------
*/

Route::get('/nav', fn() => view('components.navbar'))
    ->name('navbar');