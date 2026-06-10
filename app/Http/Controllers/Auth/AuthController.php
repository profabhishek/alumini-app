<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\AlumniUser;
use App\Models\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ── Remember Me config ────────────────────────────────────────────────

    // 90 days in minutes (for cookie lifetime)
    private const REMEMBER_LIFETIME_DAYS   = 90;
    private const REMEMBER_COOKIE_NAME     = 'alumni_remember';

    // ── Login ─────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'captcha'  => 'required|captcha',
        ], [
            'captcha.captcha' => 'The CAPTCHA code is incorrect.',
        ]);

        $user = AlumniUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.');
        }

        if (!$user->is_approved) {
            return back()->with(
                'error',
                'Your account is awaiting administrator approval.'
            );
        }

        // ── Hydrate session ───────────────────────────────────────────────
        $this->hydrateSession($user);

        // ── Remember Me ───────────────────────────────────────────────────
        if ($request->boolean('remember')) {
            $this->setRememberCookie($user);
        }

        $redirect = $request->input('redirect');

        if ($redirect && str_starts_with($redirect, '/')) {
            return redirect($redirect);
        }

        return redirect()->route('dashboard.home');
    }

    public function logout(Request $request)
    {
        // Invalidate the remember-me token in the DB so the cookie can't be reused
        if ($cookie = $request->cookie(self::REMEMBER_COOKIE_NAME)) {
            $payload = $this->parseRememberCookie($cookie);

            if ($payload) {
                $user = AlumniUser::find($payload['id']);

                if ($user) {
                    $user->remember_token = null;
                    $user->save();
                }
            }
        }

        session()->forget([
            'alumni_id',
            'alumni_name',
            'alumni_email',
            'alumni_role',
            'alumni_permissions',
        ]);

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/')
            ->with('success', 'You have been logged out successfully.')
            ->withCookie(Cookie::forget(self::REMEMBER_COOKIE_NAME));
    }

    // ── Register — Step 1: Validate + store in session + send OTP ─────────

    public function showRegister()
    {
        return view('auth.signup');
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name'    => 'required|max:255',
            'batch_name'   => 'required',
            'phone'        => 'required|min:10|max:15',
            'email'        => 'required|email|unique:alumni_users,email',
            'department'   => 'required',
            'passing_year' => 'required',
            'roll_number'  => 'required',
            'birth_date'   => 'required',
            'gender'       => 'required',
            'institute'    => 'required',
            'attachment'   => 'required|mimes:pdf|max:2048',
            'password'     => 'required|confirmed|min:8',
            'terms'        => 'accepted',
            'captcha'      => 'required|captcha',
        ], [
            'captcha.required' => 'Please enter the CAPTCHA code.',
            'captcha.captcha'  => 'The CAPTCHA code is incorrect. Please try again.',
            'terms.accepted'   => 'You must accept the Terms & Conditions.',
            'email.unique'     => 'This email address is already registered.',
            'attachment.mimes' => 'Only PDF files are allowed.',
            'attachment.max'   => 'PDF file size must not exceed 2 MB.',
        ]);

        $tmpFileName = null;

        if ($request->hasFile('attachment')) {
            $file        = $request->file('attachment');
            $tmpFileName = 'tmp_' . time() . '_' . $file->getClientOriginalName();
            $file->storeAs('tmp-uploads', $tmpFileName, 'public');
        }

        session([
            'otp_signup_data' => [
                'full_name'    => $request->full_name,
                'batch_name'   => $request->batch_name,
                'phone'        => $request->phone,
                'email'        => $request->email,
                'department'   => $request->department,
                'passing_year' => $request->passing_year,
                'roll_number'  => $request->roll_number,
                'tmp_file'     => $tmpFileName,
                'birth_date'   => $request->birth_date,
                'gender'       => $request->gender,
                'institute'    => $request->institute,
                'password'     => Hash::make($request->password),
            ],
        ]);

        $this->generateAndSendOtp($request->email, $request->full_name);

        return redirect()
            ->route('otp.verify.show')
            ->with('info', 'A 6-digit verification code has been sent to ' . $request->email);
    }

    // ── OTP — Show verification page ─────────────────────────────────────

    public function showOtpVerify()
    {
        if (!session()->has('otp_signup_data')) {
            return redirect()
                ->route('register')
                ->with('error', 'Session expired. Please fill the form again.');
        }

        return view('auth.otp-verify', [
            'email' => session('otp_signup_data.email'),
        ]);
    }

    // ── OTP — Verify submitted code + create user ─────────────────────────

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.digits' => 'The OTP must be exactly 6 digits.',
        ]);

        $signupData = session('otp_signup_data');

        if (!$signupData) {
            return redirect()
                ->route('register')
                ->with('error', 'Session expired. Please fill the form again.');
        }

        $record = EmailOtp::where('email', $signupData['email'])
            ->where('otp', $request->otp)
            ->latest()
            ->first();

        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please check the code and try again.']);
        }

        if ($record->isExpired()) {
            $record->delete();
            return back()->withErrors(['otp' => 'This OTP has expired. Please request a new one.']);
        }

        $finalFileName = null;

        if ($signupData['tmp_file']) {
            $finalFileName = ltrim(str_replace('tmp_', '', $signupData['tmp_file']), '_');

            \Illuminate\Support\Facades\Storage::disk('public')->copy(
                'tmp-uploads/' . $signupData['tmp_file'],
                'alumni-documents/' . $finalFileName,
            );

            \Illuminate\Support\Facades\Storage::disk('public')->delete(
                'tmp-uploads/' . $signupData['tmp_file']
            );
        }

        AlumniUser::create([
            'full_name'    => $signupData['full_name'],
            'batch_name'   => $signupData['batch_name'],
            'phone'        => $signupData['phone'],
            'email'        => $signupData['email'],
            'department'   => $signupData['department'],
            'passing_year' => $signupData['passing_year'],
            'roll_number'  => $signupData['roll_number'],
            'attachment'   => $finalFileName,
            'birth_date'   => $signupData['birth_date'],
            'gender'       => $signupData['gender'],
            'institute'    => $signupData['institute'],
            'password'     => $signupData['password'],
            'role'         => 'alumni',
            'is_approved'  => 0,
        ]);

        $record->delete();
        session()->forget('otp_signup_data');

        return redirect()
            ->route('login')
            ->with('success', 'Email verified! Your registration has been submitted and is awaiting approval.');
    }

    // ── OTP — Resend ──────────────────────────────────────────────────────

    public function resendOtp(Request $request)
    {
        $signupData = session('otp_signup_data');

        if (!$signupData) {
            return redirect()
                ->route('register')
                ->with('error', 'Session expired. Please fill the form again.');
        }

        $recent = EmailOtp::where('email', $signupData['email'])
            ->where('created_at', '>=', now()->subSeconds(60))
            ->exists();

        if ($recent) {
            return back()->with('error', 'Please wait 60 seconds before requesting a new OTP.');
        }

        $this->generateAndSendOtp($signupData['email'], $signupData['full_name']);

        return back()->with('info', 'A new OTP has been sent to ' . $signupData['email']);
    }

    // ── Remember Me helpers ───────────────────────────────────────────────

    /**
     * Public alias used by AlumniAuth middleware for token rotation.
     */
    public function setRememberCookieForUser(AlumniUser $user): void
    {
        $this->setRememberCookie($user);
    }

    /**
     * Generate a fresh remember-me token, store its hash in the DB,
     * and queue a long-lived cookie containing id + plain token.
     *
     * Cookie format:  <user_id>|<random_token>
     */
    private function setRememberCookie(AlumniUser $user): void
    {
        $plainToken = Str::random(60);

        $user->remember_token = Hash::make($plainToken);
        $user->save();

        $cookieValue = $user->id . '|' . $plainToken;

        // minutes → seconds not needed; Cookie::make takes minutes
        $lifetimeMinutes = self::REMEMBER_LIFETIME_DAYS * 24 * 60;

        Cookie::queue(
            Cookie::make(
                name:     self::REMEMBER_COOKIE_NAME,
                value:    $cookieValue,
                minutes:  $lifetimeMinutes,
                path:     '/',
                domain:   null,
                secure:   true,    // HTTPS only in production
                httpOnly: true,    // JS cannot read this cookie
                sameSite: 'Lax',
            )
        );
    }

    /**
     * Parse the remember-me cookie into ['id' => ..., 'token' => ...].
     * Returns null if the cookie format is invalid.
     */
    public function parseRememberCookie(string $cookie): ?array
    {
        $parts = explode('|', $cookie, 2);

        if (count($parts) !== 2 || !is_numeric($parts[0])) {
            return null;
        }

        return ['id' => (int) $parts[0], 'token' => $parts[1]];
    }

    /**
     * Write the standard alumni session keys from a user model.
     */
    private function hydrateSession(AlumniUser $user): void
    {
        session()->regenerate(); // prevent session fixation

        session([
            'alumni_id'          => $user->id,
            'alumni_name'        => $user->full_name,
            'alumni_email'       => $user->email,
            'alumni_role'        => $user->role,
            'alumni_permissions' => $user->permissions ?? [],
        ]);
    }

    // ── OTP helper ────────────────────────────────────────────────────────

    private function generateAndSendOtp(string $email, string $fullName): void
    {
        EmailOtp::where('email', $email)->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtp::create([
            'email'      => $email,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($email)->send(new OtpMail($otp, $fullName));
    }
}