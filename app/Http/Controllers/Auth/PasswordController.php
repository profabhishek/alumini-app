<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\AlumniPasswordResetToken;
use App\Models\AlumniUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    // Token expires after 60 minutes
    private const EXPIRES_IN_MINUTES = 60;

    // Throttle: one email per 60 seconds
    private const RESEND_THROTTLE_SECONDS = 60;

    // ── Step 1 — Show "Forgot Password" form ─────────────────────────────

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // ── Step 2 — Handle email submission ─────────────────────────────────

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email'   => 'required|email',
            'captcha' => 'required|captcha',
        ], [
            'captcha.captcha' => 'The CAPTCHA code is incorrect.',
        ]);

        // Always return the same success message to prevent user enumeration.
        // We still do the actual work if the user exists.
        $genericMessage = 'If that email is registered, a password reset link has been sent.';

        $user = AlumniUser::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('info', $genericMessage);
        }

        // Throttle: prevent spamming the same address
        $recent = AlumniPasswordResetToken::where('email', $request->email)
            ->where('created_at', '>=', now()->subSeconds(self::RESEND_THROTTLE_SECONDS))
            ->exists();

        if ($recent) {
            return back()->with(
                'error',
                'A reset link was sent recently. Please wait ' . self::RESEND_THROTTLE_SECONDS . ' seconds before trying again.'
            );
        }

        $this->generateAndSendResetLink($user);

        return back()->with('info', $genericMessage);
    }

    // ── Step 3 — Show "Reset Password" form (via email link) ─────────────

    public function showResetForm(Request $request, string $token)
    {
        // Validate token exists and belongs to a real email
        $email  = $request->query('email');
        $record = $this->findValidToken($email, $token);

        if (!$record) {
            return redirect()
                ->route('password.forgot')
                ->with('error', 'This password reset link is invalid or has expired. Please request a new one.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    // ── Step 4 — Handle new password submission ───────────────────────────

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',       // at least one uppercase
                'regex:/[0-9]/',       // at least one digit
                'regex:/[@$!%*?&#]/',  // at least one special char
            ],
        ], [
            'password.regex'     => 'Password must contain at least one uppercase letter, one number, and one special character (@$!%*?&#).',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min'       => 'Password must be at least 8 characters long.',
        ]);

        $record = $this->findValidToken($request->email, $request->token);

        if (!$record) {
            return back()
                ->withInput()
                ->with('error', 'This reset link is invalid or has expired. Please request a new one.');
        }

        $user = AlumniUser::where('email', $request->email)->first();

        if (!$user) {
            return redirect()
                ->route('password.forgot')
                ->with('error', 'No account found for that email address.');
        }

        // Update password
        $user->password       = Hash::make($request->password);
        $user->remember_token = null; // invalidate all existing "remember me" sessions
        $user->save();

        // Consume the token (one-time use)
        AlumniPasswordResetToken::where('email', $request->email)->delete();

        return redirect()
            ->route('login')
            ->with('success', 'Your password has been reset successfully. Please sign in with your new password.');
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * Generate a cryptographically-secure token, hash it for storage,
     * persist it, and email the plain token to the user.
     */
    private function generateAndSendResetLink(AlumniUser $user): void
    {
        // Delete any existing tokens for this email
        AlumniPasswordResetToken::where('email', $user->email)->delete();

        $plainToken = Str::random(64);

        AlumniPasswordResetToken::create([
            'email'      => $user->email,
            'token'      => Hash::make($plainToken),
            'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset', [
            'token' => $plainToken,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(
            new PasswordResetMail($resetUrl, $user->full_name, self::EXPIRES_IN_MINUTES)
        );
    }

    /**
     * Look up a token record and verify the plain token against the hash.
     * Returns the record on success, null on failure.
     */
    private function findValidToken(?string $email, ?string $plainToken): ?AlumniPasswordResetToken
    {
        if (!$email || !$plainToken) {
            return null;
        }

        $record = AlumniPasswordResetToken::where('email', $email)
            ->latest('created_at')
            ->first();

        if (!$record) {
            return null;
        }

        if ($record->isExpired()) {
            $record->delete();
            return null;
        }

        if (!Hash::check($plainToken, $record->token)) {
            return null;
        }

        return $record;
    }
}