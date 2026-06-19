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

    private const REMEMBER_LIFETIME_DAYS = 90;
    private const REMEMBER_COOKIE_NAME   = 'alumni_remember';

    // ── All 195 UN-recognised nationalities ──────────────────────────────
    private const VALID_NATIONALITIES = [
        'Afghan','Albanian','Algerian','American','Andorran','Angolan','Antiguans',
        'Argentinean','Armenian','Australian','Austrian','Azerbaijani','Bahamian',
        'Bahraini','Bangladeshi','Barbadian','Barbudans','Batswana','Belarusian',
        'Belgian','Belizean','Beninese','Bhutanese','Bolivian','Bosnian','Brazilian',
        'British','Bruneian','Bulgarian','Burkinabe','Burmese','Burundian','Cambodian',
        'Cameroonian','Canadian','Cape Verdean','Central African','Chadian','Chilean',
        'Chinese','Colombian','Comoran','Congolese','Costa Rican','Croatian','Cuban',
        'Cypriot','Czech','Danish','Djibouti','Dominican','Dutch','East Timorese',
        'Ecuadorean','Egyptian','Emirian','Equatorial Guinean','Eritrean','Estonian',
        'Ethiopian','Fijian','Filipino','Finnish','French','Gabonese','Gambian',
        'Georgian','German','Ghanaian','Greek','Grenadian','Guatemalan','Guinea-Bissauan',
        'Guinean','Guyanese','Haitian','Herzegovinian','Honduran','Hungarian','I-Kiribati',
        'Icelander','Indian','Indonesian','Iranian','Iraqi','Irish','Israeli','Italian',
        'Ivorian','Jamaican','Japanese','Jordanian','Kazakhstani','Kenyan','Kittian and Nevisian',
        'Kuwaiti','Kyrgyz','Laotian','Latvian','Lebanese','Liberian','Libyan',
        'Liechtensteiner','Lithuanian','Luxembourger','Macedonian','Malagasy','Malawian',
        'Malaysian','Maldivian','Malian','Maltese','Marshallese','Mauritanian','Mauritian',
        'Mexican','Micronesian','Moldovan','Monacan','Mongolian','Moroccan','Mosotho',
        'Motswana','Mozambican','Namibian','Nauruan','Nepali','New Zealander','Ni-Vanuatu',
        'Nicaraguan','Nigerian','Nigerien','North Korean','Norwegian','Omani','Pakistani',
        'Palauan','Palestinian','Panamanian','Papua New Guinean','Paraguayan','Peruvian',
        'Polish','Portuguese','Qatari','Romanian','Russian','Rwandan','Saint Lucian',
        'Salvadoran','Samoan','San Marinese','Sao Tomean','Saudi','Senegalese','Serbian',
        'Seychellois','Sierra Leonean','Singaporean','Slovakian','Slovenian','Solomon Islander',
        'Somali','South African','South Korean','South Sudanese','Spanish','Sri Lankan',
        'Sudanese','Surinamer','Swazi','Swedish','Swiss','Syrian','Taiwanese','Tajik',
        'Tanzanian','Thai','Togolese','Tongan','Trinidadian and Tobagonian','Tunisian',
        'Turkish','Tuvaluan','Ugandan','Ukrainian','Uruguayan','Uzbekistani','Venezuelan',
        'Vietnamese','Yemenite','Zambian','Zimbabwean',
    ];

    // ── Login ─────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|string|max:128',
            'captcha'  => 'required|captcha',
        ], [
            'captcha.required' => 'Please enter the CAPTCHA code.',
            'captcha.captcha'  => 'The CAPTCHA code is incorrect.',
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

        // Validate the redirect is a safe relative path on this site:
        // - must start with exactly one '/' (not '//' which is protocol-relative)
        // - must not contain ':' (blocks 'javascript:' and similar schemes)
        if (
            $redirect &&
            preg_match('#^/[^/]#', $redirect) &&
            strpos($redirect, ':') === false
        ) {
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
            'alumni_avatar',
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
        $validNationalities = self::VALID_NATIONALITIES;
        $currentYear = (int) date('Y');

        $request->validate([
            'full_name'        => 'required|string|max:150|regex:/^[\pL\s\-\'\.]+$/u',
            'batch_name'       => "required|integer|min:1980|max:{$currentYear}",
            'phone'            => 'required|string|min:7|max:20|regex:/^\+?[0-9\s\-\(\)]+$/',
            'email'            => 'required|email:rfc,dns|max:255|unique:alumni_users,email',
            'department'       => 'required|in:STEM,Non-STEM',
            'passing_year'     => "required|integer|min:1980|max:{$currentYear}",
            'roll_number'      => 'nullable|string|max:50',
            'birth_date'       => 'nullable|date|before:today',
            'gender'           => 'required|in:Male,Female,Other',
            'institute'        => 'required|string|max:255',
            'nationality'      => ['required', 'string', 'in:' . implode(',', $validNationalities)],
            'is_iccr_alumni'   => 'required|in:yes,no',
            'current_position' => 'nullable|string|max:255',
            'password'         => 'required|confirmed|min:8|max:128',
            'terms'            => 'accepted',
            'captcha'          => 'required|captcha',
        ], [
            'captcha.required'        => 'Please enter the CAPTCHA code.',
            'captcha.captcha'         => 'The CAPTCHA code is incorrect. Please try again.',
            'terms.accepted'          => 'You must accept the Terms & Conditions.',
            'email.unique'            => 'This email address is already registered.',
            'email.email'             => 'Please enter a valid email address.',
            'department.in'           => 'Department must be STEM or Non-STEM.',
            'nationality.required'    => 'Nationality is required.',
            'nationality.in'          => 'Please select a valid nationality from the list.',
            'is_iccr_alumni.required' => 'Please indicate whether you are an ICCR Alumni.',
            'is_iccr_alumni.in'       => 'Please select Yes or No for ICCR Alumni.',
            'full_name.regex'         => 'Full name may only contain letters, spaces, hyphens, apostrophes, and dots.',
            'phone.regex'             => 'Phone number contains invalid characters.',
            'batch_name.min'          => 'Batch year must be 1980 or later.',
            'batch_name.max'          => "Batch year cannot exceed {$currentYear}.",
            'passing_year.min'        => 'Passing year must be 1980 or later.',
            'passing_year.max'        => "Passing year cannot exceed {$currentYear}.",
            'birth_date.before'       => 'Birth date must be in the past.',
            'gender.in'               => 'Please select a valid gender.',
            'password.max'            => 'Password must not exceed 128 characters.',
        ]);

        session([
            'otp_signup_data' => [
                'full_name'        => $request->full_name,
                'batch_name'       => $request->batch_name,
                'phone'            => $request->phone,
                'email'            => $request->email,
                'department'       => $request->department,
                'passing_year'     => $request->passing_year,
                'roll_number'      => $request->roll_number ?? null,
                'birth_date'       => $request->birth_date,
                'gender'           => $request->gender,
                'institute'        => $request->institute,
                'nationality'      => $request->nationality,
                'is_iccr_alumni'   => $request->is_iccr_alumni === 'yes',
                'current_position' => $request->current_position,
                'password'         => Hash::make($request->password),
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

        AlumniUser::create([
            'full_name'        => $signupData['full_name'],
            'batch_name'       => $signupData['batch_name'],
            'phone'            => $signupData['phone'],
            'email'            => $signupData['email'],
            'department'       => $signupData['department'],
            'passing_year'     => $signupData['passing_year'],
            'roll_number'      => $signupData['roll_number'],
            'birth_date'       => $signupData['birth_date'] ?: null,
            'gender'           => $signupData['gender'],
            'institute'        => $signupData['institute'],
            'nationality'      => $signupData['nationality'],
            'is_iccr_alumni'   => $signupData['is_iccr_alumni'],
            'current_position' => $signupData['current_position'] ?? null,
            'password'         => $signupData['password'],
            'role'             => 'alumni',
            'is_approved'      => 0,
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
            'alumni_id'               => $user->id,
            'alumni_name'             => $user->full_name,
            'alumni_email'            => $user->email,
            'alumni_role'             => $user->role,
            'alumni_permissions'      => $user->permissions ?? [],
            'alumni_avatar'           => $user->photo,
            // Restore last-seen timestamps from DB so sidebar badges survive logout/login
            'applications_last_seen'  => $user->applications_last_seen?->toDateTimeString(),
            'my_jobs_last_seen'       => $user->my_jobs_last_seen?->toDateTimeString(),
            'my_stories_last_seen'    => $user->my_stories_last_seen?->toDateTimeString(),
        ]);

        // Restore events_regs_seen map: use events_regs_seen_at as a per-event fallback
        if ($user->events_regs_seen_at) {
            $ts = $user->events_regs_seen_at->toDateTimeString();
            $eventIds = \App\Models\Event::where('created_by', $user->id)->pluck('id');
            $seenMap  = [];
            foreach ($eventIds as $eid) {
                $seenMap[$eid] = $ts;
            }
            session(['events_regs_seen' => $seenMap]);
        }

        // ── Record this device in alumni_sessions ─────────────────────────
        \App\Models\AlumniSession::updateOrCreate(
            [
                'alumni_user_id' => $user->id,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent() ?? '',
            ],
            [
                'device'         => \App\Models\AlumniSession::parseDevice(request()->userAgent() ?? ''),
                'last_active_at' => now(),
            ]
        );
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