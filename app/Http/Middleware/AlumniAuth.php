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
     * Keep an AlumniSession row in sync with this browser session, so the
     * Settings > Active Sessions tab can list and revoke it.
     *
     * Returns false if this session was previously tracked but its row no
     * longer exists — i.e. it was revoked — signalling the caller to log
     * the user out.
     */
    private function trackSession(Request $request): bool
    {
        $sessionId = session()->getId();

        $exists = AlumniSession::where('session_id', $sessionId)->exists();

        if (! $exists && session('alumni_session_tracked')) {
            return false;
        }

        $userAgent = $request->userAgent() ?? '';

        AlumniSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'alumni_user_id' => session('alumni_id'),
                'ip_address'     => $request->ip(),
                'user_agent'     => $userAgent,
                'device'         => AlumniSession::parseDevice($userAgent),
                'last_active_at' => now(),
            ]
        );

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