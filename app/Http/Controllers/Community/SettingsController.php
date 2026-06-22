<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniSession;
use App\Models\AlumniUser;
use App\Models\UserBlock;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    // ── Show settings page ────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user     = AlumniUser::findOrFail(session('alumni_id'));
        $sessions = AlumniSession::where('alumni_user_id', $user->id)
                        ->orderByDesc('last_active_at')
                        ->get();

        $currentIp = $request->ip();
        $currentUa = $request->userAgent() ?? '';

        $blockedUsers = UserBlock::where('blocker_id', $user->id)
            ->with('blocked')
            ->latest()
            ->get();

        return view('community.settings.index', compact('user', 'sessions', 'currentIp', 'currentUa', 'blockedUsers'));
    }

    // ── Save notification preferences ─────────────────────────────────────

    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications'          => 'nullable|array',
            'email_notifications.events'   => 'nullable|boolean',
            'email_notifications.jobs'     => 'nullable|boolean',
            'email_notifications.stories'  => 'nullable|boolean',
        ]);

        $user = AlumniUser::findOrFail(session('alumni_id'));

        $user->update([
            'email_notifications' => [
                'events'  => $request->boolean('email_notifications.events'),
                'jobs'    => $request->boolean('email_notifications.jobs'),
                'stories' => $request->boolean('email_notifications.stories'),
            ],
        ]);

        return back()->with('success', 'Notification preferences saved.')->with('tab', 'notifications');
    }

    // ── Save appearance + visibility ──────────────────────────────────────

    public function updatePreferences(Request $request)
    {
        $request->validate([
            'appearance'         => 'required|in:light,dark',
            'profile_visibility' => 'nullable|in:public,alumni-only',
        ]);

        $user = AlumniUser::findOrFail(session('alumni_id'));

        $updates = ['appearance' => $request->appearance];

        if ($request->filled('profile_visibility')) {
            $updates['profile_visibility'] = $request->profile_visibility;
        }

        $user->update($updates);

        return back()->with('success', 'Preferences updated.')->with('tab', 'preferences');
    }

    // ── Revoke a session ──────────────────────────────────────────────────

    public function revokeSession(AlumniSession $session)
    {
        // Ensure the session belongs to the authenticated user
        if ($session->alumni_user_id !== session('alumni_id')) {
            abort(403);
        }

        $session->delete();

        return back()->with('success', 'Session revoked.')->with('tab', 'sessions');
    }
}