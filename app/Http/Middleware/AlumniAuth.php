<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\AuthController;
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
            return $next($request);
        }

        // ── 2. No session — try remember-me cookie ────────────────────────
        $cookie = $request->cookie(self::REMEMBER_COOKIE_NAME);

        if ($cookie && $this->attemptRememberLogin($request, $cookie)) {
            return $next($request);
        }

        // ── 3. Neither — redirect to login ───────────────────────────────
        return redirect()
            ->route('login')
            ->with('error', 'Please sign in to continue.');
    }

    // ── Private ───────────────────────────────────────────────────────────

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
        ]);

        // ── Rotate the token (prevents cookie reuse if cookie is stolen) ──
        $authController->setRememberCookieForUser($user);

        return true;
    }
}