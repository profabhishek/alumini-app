<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\AuthController;
use App\Models\AlumniSession;
use App\Models\AlumniUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AlumniAuth
{
    private const REMEMBER_COOKIE_NAME = 'alumni_remember';

    public function handle(Request $request, Closure $next): Response
    {
        // ── 1. Active session — fast path ─────────────────────────────────
        if (session()->has('alumni_id')) {
            if (! $this->trackSession($request)) {
                // This session's AlumniSession row was deleted — either the
                // user revoked it from another device, or revoked "this
                // device" themselves via Settings > Active Sessions.
                session()->flush();

                return redirect()
                    ->route('login')
                    ->with('error', 'You have been logged out because this session was revoked.');
            }

            return $next($request);
        }

        // ── 2. No session — try remember-me cookie ────────────────────────
        $cookie = $request->cookie(self::REMEMBER_COOKIE_NAME);

        if ($cookie && $this->attemptRememberLogin($request, $cookie)) {
            $this->trackSession($request);

            return $next($request);
        }

        // ── 3. Neither — redirect to login ───────────────────────────────
        return redirect()
            ->route('login')
            ->with('error', 'Please sign in to continue.');
    }

    // ── Private ───────────────────────────────────────────────────────────

    /**
     * Keep ONE AlumniSession row per device (user_agent) in sync.
     * Netflix-style: same browser on same OS = one entry, last_active_at
     * updates on each request. A new login rotates the session_id on the
     * existing device row rather than creating a duplicate.
     *
     * Returns false if this session was previously tracked but its row no
     * longer exists — i.e. it was revoked — signalling the caller to log out.
     */
    private function trackSession(Request $request): bool
    {
        $sessionId = session()->getId();
        $alumniId  = session('alumni_id');
        $userAgent = $request->userAgent() ?? '';
        $device    = AlumniSession::parseDevice($userAgent);

        // ── 1. Check if this exact session_id is already tracked ─────────
        $row = AlumniSession::where('session_id', $sessionId)->first();

        if ($row) {
            // Throttle DB writes: only update once per minute per session
            if (! $row->last_active_at || $row->last_active_at->diffInSeconds(now()) >= 60) {
                $row->update(['last_active_at' => now(), 'ip_address' => $request->ip()]);
            }
            session(['alumni_session_tracked' => true]);
            return true;
        }

        // ── 2. No row for this session_id ─────────────────────────────────
        // If we previously flagged this session as tracked but the row is
        // gone, it was revoked — boot the user.
        if (session('alumni_session_tracked')) {
            return false;
        }

        // ── 3. First request of a new session — find existing device row ──
        // Same user + same user_agent = same physical device/browser.
        // Reuse that row (update session_id) instead of creating a duplicate.
        $existing = AlumniSession::where('alumni_user_id', $alumniId)
            ->where('user_agent', $userAgent)
            ->first();

        if ($existing) {
            $existing->update([
                'session_id'     => $sessionId,
                'ip_address'     => $request->ip(),
                'last_active_at' => now(),
            ]);
        } else {
            // Genuinely new device/browser — create a fresh row
            AlumniSession::create([
                'session_id'     => $sessionId,
                'alumni_user_id' => $alumniId,
                'ip_address'     => $request->ip(),
                'user_agent'     => $userAgent,
                'device'         => $device,
                'last_active_at' => now(),
            ]);
        }

        session(['alumni_session_tracked' => true]);
        return true;
    }

    /**
     * Validate the remember-me cookie, re-hydrate the session,
     * and rotate the token to prevent cookie reuse (token rotation).
     */
    private function attemptRememberLogin(Request $request, string $cookie): bool
    {
        $authController = app(AuthController::class);
        $payload        = $authController->parseRememberCookie($cookie);

        if (!$payload) {
            return false;
        }

        $user = AlumniUser::find($payload['id']);

        if (!$user || !$user->remember_token) {
            return false;
        }

        // Constant-time hash comparison (prevents timing attacks)
        if (!Hash::check($payload['token'], $user->remember_token)) {
            return false;
        }

        if (!$user->is_approved) {
            return false;
        }

        // ── Valid cookie — hydrate session ────────────────────────────────
        session()->regenerate();

        session([
            'alumni_id'          => $user->id,
            'alumni_name'        => $user->full_name,
            'alumni_email'       => $user->email,
            'alumni_role'        => $user->role,
            'alumni_permissions' => $user->permissions ?? [],
            'alumni_avatar'      => $user->photo,
        ]);

        // ── Rotate the token (prevents cookie reuse if cookie is stolen) ──
        $authController->setRememberCookieForUser($user);

        return true;
    }
}