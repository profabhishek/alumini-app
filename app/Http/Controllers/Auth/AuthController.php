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
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // ── Remember Me config ────────────────────────────────────────────────

    private const REMEMBER_LIFETIME_DAYS = 90;
    private const REMEMBER_COOKIE_NAME   = 'alumni_remember';

    // ── All 134 ICCR-empanelled institutes (A.Y. 2023-24 onwards) ────────
    // Source: Official ICCR PDF list — names match dropdown values exactly
    private const VALID_INSTITUTES = [
        // Central Universities (1–16)
        'Aligarh Muslim University, Aligarh, Uttar Pradesh',
        'Babasaheb Bhimrao Ambedkar University, Lucknow, Uttar Pradesh',
        'Banaras Hindu University, Varanasi, Uttar Pradesh',
        'Central University of Gujarat, Gandhinagar, Gujarat',
        'Central University of Punjab, Bathindia, Punjab',
        'English and Foreign Language University, Hyderabad, Telangana',
        'Jamia Milia Islamia, New Delhi, Delhi',
        'Mahatma Gandhi Antarrashtriya Hindi Vishwavidhyalaya, Wardha, Maharashtra',
        'Manipur University, Imphal, Manipur',
        'Mizoram University, Aizawi, Mizoram',
        'Nalanda University, Rajgir, Bihar',
        'National Forensic Science University, Gandhinagar, Gujarat',
        'Tezpur University, Tezpur, Assam',
        'University of Delhi, New Delhi, Delhi',
        'University of Hyderabad, Hyderabad, Telangana',
        'Visva Bharati University, Santiniketan, West Bengal',
        // State Universities (17–54)
        'Alagappa University, Karaikudi, Tamil Nadu',
        'Andhra University, Visakhapatnam, Andhra Pradesh',
        'Anna University, Chennai, Tamil Nadu',
        'Bangalore University, Bangalore, Karnataka',
        'Bharathiar University, Coimbatore, Tamil Nadu',
        'Cochin University of Science and Technology, Kochi, Kerala',
        'Delhi Technological University, New Delhi, Delhi',
        'Dibrugarh University, Dibrugarh, Assam',
        'Dr. Babasaheb Ambedkar Marathawada University, Aurangabad',
        'Gauhati University, Guwahati, Assam',
        'Goa University, Taleigao, Goa',
        'Gujarat Technological University, Ahmedabad, Gujarat',
        'Gujarat University, Ahmedabad, Gujarat',
        'Guru Gobind Singh Indraprastha University, New Delhi, Delhi',
        'Guru Nanak Dev University, Amritsar',
        'Indraprastha Institute of Information Technology, New Delhi, Delhi',
        'Jawaharlal Nehru Technological University, Hyderabad, Telangana',
        'Kerala University, Thiruvananthapuram, Kerala',
        'Kurukshetra University, Thanesar, Haryana',
        'Mahatma Gandhi University, Kottayam, Kerala',
        'Mangalore University, Mangaluru, Karnataka',
        'Mumbai University, Mumbai, Maharashtra',
        'Osmania University, Hyderabad, Telangana',
        'Panjab University, Chandigarh',
        'Punjabi University, Patiala, Punjab',
        'Rabindra Bharati University, Kolkata, West Bengal',
        'Sardar Patel University, Vallabh Vidyanagar, Gujarat',
        'Savitribai Phule Pune University, Pune, Maharashtra',
        'Shivaji University, Kolhapur, Maharashtra',
        'Shree Somnath Sanskrit University, Veraval, Gujarat',
        'The Maharaja Sayajirao University, Vadodara, Gujarat',
        'University of Calcutta, Kolkata, West Bengal',
        'University of Kashmir, Srinagar, Jammu and Kashmir',
        'University of Lucknow, Lucknow, Uttar Pradesh',
        'University of Mysore, Mysore, Karnataka',
        'University of Jammu, Jammu, Jammu and Kashmir',
        'Utkal University, Bhubaneswar, Odisha',
        'Veer Narmad South Gujarat University, Surat, Gujarat',
        // Centrally Funded Technical Institutes (55–80)
        'IIT Roorkee, Uttarakhand',
        'IIT Kanpur, Uttar Pradesh',
        'IIT (BHU), Varanasi, Uttar Pradesh',
        'IIT Gandhinagar, Palaj, Gujarat',
        'IIT Patna, Bihar',
        'IIT Kharagpur, West Bengal',
        'IIT Madras, Chennai, Tamil Nadu',
        'IIT (ISM) Dhanbad, Jharkhand',
        'IIT Bombay, Maharashtra',
        'IIT Indore, Madhya Pradesh',
        'IIT Hyderabad, Kandi, Telangana',
        'IIT Ropar, Punjab',
        'IIT Delhi, New Delhi, Delhi',
        'MNIT Jaipur, Rajasthan',
        'NIT Calicut, Kattangal, Kerala',
        'NIT Durgapur, West Bengal',
        'NIT Hamirpur, Himachal Pradesh',
        'NIT Jalandhar, Punjab',
        'NIT Kurukshetra, Thanesar, Haryana',
        'NIT Meghalaya, Shillong, Meghalaya',
        'NIT Rourkela, Rourkela, Odisha',
        'NIT Tiruchirappalli, Tamil Nadu',
        'NIT Warangal, Hanamkonda, Telangana',
        'NIT Surathkal, Mangaluru, Karnataka',
        'NIT Silchar, Assam',
        'Sardar Vallabhbhai National Institute Of Technology (SVNIT), Surat, Gujarat',
        // Dance/Music/Traditional Knowledge Institutes (81–88)
        'Dev Sanskriti Vishwavidyalaya, Shantikunj Gayatrikunj, Haridwar, Uttarakhand',
        'Indira Kala Sangeet Vishwavidyalaya, Khairagarh, Chhattisgarh',
        'Kalakshetra Foundation, Chennai, Tamil Nadu',
        'Kathak Kendra, New Delhi, Delhi',
        'Kendriya Hindi Sansthan, New Delhi, Delhi',
        'Pracheen Kala Kendra, Chandigarh',
        'National School of Drama, New Delhi, Delhi',
        'Satyajit Ray Film and Television Institute, Kolkata, West Bengal',
        // Agricultural Universities (89–102)
        'Acharya Narendra Deva University of Agriculture and Technology, Kumarganj, Uttar Pradesh',
        'Ch. Sarwan Kumar Krishi Vishvavidyalaya, Palampur, Himachal Pradesh',
        'Chaudhary Charan Singh Haryana Agricultural University, Hisar, Haryana',
        'Dr. Yaswant Singh Parmar University of Horticulture & Forestry, Nauni-Solan, Himachal Pradesh',
        'Guru Angad Dev Veterinary and Animal Sciences University (GADVASU), Ludhiana',
        'Jawaharlal Nehru Krishi Vishwa Vidyalaya, Jabalpur, Madhya Pradesh',
        'Kerala Agricultural University, Thrissur, Kerala',
        'Nanaji Deshmukh Veterinary Science University, Jabalpur, Madhya Pradesh',
        'Punjab Agricultural University, Ludhiana, Punjab',
        'Sardar Vallabhbhai Patel University of Agriculture and Technology, Meerut, Uttar Pradesh',
        'Sher-e-Kashmir University of Agricultural Sciences and Technology of Jammu, Jammu & Kashmir',
        'Sher-e-Kashmir University of Agricultural Sciences and Technology of Kashmir, Srinagar',
        'University of Agricultural Sciences, Dharwad, Karnataka',
        'University of Agricultural Sciences, Bangalore, Karnataka',
        // Ayurveda Universities (103–134)
        'Bhartiya Sanskriti Darshan Trust Ayurved Mahavidyalaya, Wagholi, Pune, Maharashtra',
        'Ch. Brahm Prakash Ayurved Charak Sansthan, Khera Dabar, Najafgarh',
        'Dr BRKR Govt Ayurveda College, Hyderabad',
        'Government Ayurved College Wazirabad, Nanded, Maharashtra',
        'Government Ayurveda Medical College, Dhanvantari Road, Bangalore',
        'Govt Ayurved Mahavidyalaya Raje Raghuji Nagar, Nagpur, Maharashtra',
        'Govt Ayurvedic College, Gwalior, Madhya Pradesh',
        'Govt Nizamia Tibbi College, Hyderabad, Telangana',
        'Govt Siddha Medical College, Palayamkottai, Tirunelveli, Tamil Nadu',
        'Institute of Training and Research in Ayurved, Jamnagar',
        'JS Ayurved Mahavidyalaya, Nadiad, Gujarat',
        'JSPS Govt Homoeopathic Medical College, Hyderabad, Telangana',
        'Kaivalyadhama Yoga Institute, Lonavia, Pune, Maharashtra',
        'KLE Shri B.M. Kankanawadi Ayurveda Mahavidyalaya, Belagavi, Karnataka',
        'Maharashtra Arogya Mandala Sumati Bhai Shah Ayurved Mahavidyalaya, Pune, Maharashtra',
        'Morarji Desai National Institute of Yoga, New Delhi, Delhi',
        'National Institute of Ayurved, Jaipur, Rajasthan',
        'National Institute of Homoeopathy, Kolkata, West Bengal',
        'National Institute of Siddha, Chennai, Tamil Nadu',
        'National Institute of Unani Medicine, Bengaluru',
        'Pt. Khushilal Sharma Government Ayurveda College, Bhopal, Madhya Pradesh',
        'RA Podar Ayurved College, Worli, Mumbai, Maharashtra',
        'Rajiv Gandhi Government Post Graduate Ayurvedic College, Paprola, Himachal Pradesh',
        'SDM College of Ayurveda and Hospital, Kuthpady, Udupi',
        'Shri Radha Krishna Toshniwal Ayurved Mahavidyalaya, Akola, Maharashtra',
        'Sri Dharmasthala Manjunatheshwara College of Ayurveda and Hospital, Hassan',
        'The North Eastern Institute of Ayurveda and Homoeopathy, Shillong, Meghalaya',
        'Tilak Ayurveda Mahavidyalaya, Pune, Maharashtra',
        'Swami Vivekananda Yoga Anusandhana Samsthana (SVYASA), Bengaluru, Karnataka',
        'Government Ayurveda College, Tripunithura, Ernakulam, Kerala',
        'Government Ayurveda College, Kannur, Pariyaram, Kerala',
        'Government Siddha Medical College, Chennai, Tamil Nadu',
    ];

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
        $currentYear    = (int) date('Y');
        $isIccrAlumni   = $request->input('is_iccr_alumni');

        // ── Base rules (both YES and NO paths) ───────────────────────────
        $rules = [
            'is_iccr_alumni'   => 'required|in:yes,no',
            'full_name'        => 'required|string|max:150|regex:/^[\pL\s\-\'\.]+$/u',
            'email'            => 'required|email:rfc,dns|max:255|unique:alumni_users,email',
            'nationality'      => ['required', 'string', 'in:' . implode(',', self::VALID_NATIONALITIES)],
            'password'         => 'required|confirmed|min:8|max:128',
            'terms'            => 'accepted',
            'captcha'          => 'required|captcha',
        ];

        // ── Extra rules for NO path only ─────────────────────────────────
        if ($isIccrAlumni === 'no') {
            $rules['batch_name']       = "required|integer|min:1980|max:{$currentYear}";
            $rules['phone']            = 'required|string|min:7|max:20|regex:/^\+?[0-9\s\-\(\)]+$/';
            $rules['department']       = 'required|in:STEM,Non-STEM';
            $rules['passing_year']     = "required|integer|min:1980|max:{$currentYear}";
            $rules['gender']           = 'required|in:Male,Female,Other';
            $rules['institute']        = ['required', 'string', Rule::in(array_merge(self::VALID_INSTITUTES, ['other']))];
            $rules['institute_other']  = $request->input('institute') === 'other'
                ? 'required|string|max:255'
                : 'nullable|string|max:255';
            $rules['roll_number']      = 'nullable|string|max:50';
            $rules['birth_date']       = 'nullable|date|before:today';
            $rules['current_position'] = 'nullable|string|max:255';
        }

        $request->validate($rules, [
            'is_iccr_alumni.required' => 'Please indicate whether you are an ICCR Alumni.',
            'is_iccr_alumni.in'       => 'Please select Yes or No for ICCR Alumni.',
            'full_name.required'      => 'Full name is required.',
            'full_name.regex'         => 'Full name may only contain letters, spaces, hyphens, apostrophes, and dots.',
            'email.required'          => 'Email address is required.',
            'email.email'             => 'Please enter a valid email address.',
            'email.unique'            => 'This email address is already registered.',
            'nationality.required'    => 'Nationality is required.',
            'nationality.in'          => 'Please select a valid nationality from the list.',
            'password.required'       => 'Password is required.',
            'password.confirmed'      => 'Password confirmation does not match.',
            'password.min'            => 'Password must be at least 8 characters.',
            'password.max'            => 'Password must not exceed 128 characters.',
            'terms.accepted'          => 'You must accept the Terms & Conditions.',
            'captcha.required'        => 'Please enter the CAPTCHA code.',
            'captcha.captcha'         => 'The CAPTCHA code is incorrect. Please try again.',
            'batch_name.required'     => 'Batch year is required.',
            'batch_name.integer'      => 'Batch year must be a valid year.',
            'batch_name.min'          => 'Batch year must be 1980 or later.',
            'batch_name.max'          => "Batch year cannot exceed {$currentYear}.",
            'phone.required'          => 'Phone number is required.',
            'phone.regex'             => 'Phone number contains invalid characters.',
            'department.required'     => 'Department is required.',
            'department.in'           => 'Department must be STEM or Non-STEM.',
            'passing_year.required'   => 'Passing year is required.',
            'passing_year.integer'    => 'Passing year must be a valid year.',
            'passing_year.min'        => 'Passing year must be 1980 or later.',
            'passing_year.max'        => "Passing year cannot exceed {$currentYear}.",
            'gender.required'         => 'Gender is required.',
            'gender.in'               => 'Please select a valid gender.',
            'institute.required'        => 'Please select your institute.',
            'institute.in'              => 'Please select a valid ICCR-empanelled institute from the list.',
            'institute_other.required'  => 'Please enter your institution name.',
            'birth_date.before'       => 'Birth date must be in the past.',
        ]);

        session([
            'otp_signup_data' => [
                'full_name'        => $request->full_name,
                'email'            => $request->email,
                'nationality'      => $request->nationality,
                'is_iccr_alumni'   => $isIccrAlumni === 'yes',
                'password'         => Hash::make($request->password),
                // NO-path fields — null for YES-path users
                'batch_name'       => $isIccrAlumni === 'no' ? $request->batch_name       : null,
                'phone'            => $isIccrAlumni === 'no' ? $request->phone             : null,
                'department'       => $isIccrAlumni === 'no' ? $request->department        : null,
                'passing_year'     => $isIccrAlumni === 'no' ? $request->passing_year      : null,
                'gender'           => $isIccrAlumni === 'no' ? $request->gender            : null,
                'institute'        => $isIccrAlumni === 'no'
                    ? ($request->institute === 'other' ? trim($request->institute_other) : $request->institute)
                    : null,
                'roll_number'      => $isIccrAlumni === 'no' ? ($request->roll_number ?? null) : null,
                'birth_date'       => $isIccrAlumni === 'no' ? ($request->birth_date ?: null) : null,
                'current_position' => $isIccrAlumni === 'no' ? ($request->current_position ?? null) : null,
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

        // ── Session tracking is handled entirely by AlumniAuth middleware ────
        // (No insert here — middleware upserts by user_agent to deduplicate)
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