@extends('layouts.app')

@section('title', 'Join Community')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/signup.css') }}" />
@endpush

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
    .req { color: #e53e3e; margin-left: 2px; font-weight: 700; }
    .field-error { display: block; color: #e53e3e; font-size: 12px; margin-top: 5px; }
    .input-error { border-color: #e53e3e !important; box-shadow: 0 0 0 3px rgba(229,62,62,.12) !important; }
    .alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; }
    .alert-danger { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; }
    .alert-danger ul { margin: 0; padding-left: 18px; }
    .alert-danger li { margin-top: 4px; }
    .pw-strength { margin-top: 6px; height: 4px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
    .pw-strength__bar { height: 100%; width: 0; border-radius: 4px; transition: width .3s, background .3s; }
    .pw-hint { font-size: 11px; color: #64748b; margin-top: 4px; }
    .toggle-pw { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#64748b; padding:4px; }
    .pw-wrap { position: relative; }
    .pw-wrap input { padding-right: 44px !important; }

    /* ── Toggle sections ─────────────────────────────────────────────── */
    .signup-section { display: none; }
    .signup-section.active { display: block; }

    /* ── ICCR question card ──────────────────────────────────────────── */
    .iccr-question-wrap {
        background: #f0f6ff;
        border: 1.5px solid #c7deff;
        border-radius: 14px;
        padding: 20px 22px 16px;
        margin-bottom: 24px;
    }
    .iccr-question-wrap label {
        font-size: 15px;
        font-weight: 700;
        color: var(--secondary);
        display: block;
        margin-bottom: 10px;
    }
    .iccr-question-wrap select {
        width: 100%;
        height: 52px;
        border: 1.5px solid #bbd3f5;
        border-radius: 12px;
        padding: 0 16px;
        font-size: 15px;
        background: #fff;
        color: var(--secondary);
        transition: border-color 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .iccr-question-wrap select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
    }
    .iccr-pending-hint {
        margin-top: 10px;
        font-size: 13px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .iccr-pending-hint.hidden { display: none; }

    /* ── Section divider label ───────────────────────────────────────── */
    .section-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--primary);
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin: 20px 0 14px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
    }
</style>

<section class="signup-page">
<div class="signup-container">

    {{-- Left panel --}}
    <div class="signup-showcase">
        <div class="showcase-badge">ICCR Alumni Network</div>
        <h1>Connect with Alumni Across the Globe</h1>
        <p>Join a professional network of ICCR alumni, discover opportunities, participate in events, and build meaningful global connections.</p>
        <div class="stats-grid">
            <div class="stat-card"><h3>120+</h3><span>Countries</span></div>
            <div class="stat-card"><h3>25K+</h3><span>Alumni</span></div>
            <div class="stat-card"><h3>500+</h3><span>Events</span></div>
            <div class="stat-card"><h3>1000+</h3><span>Success Stories</span></div>
        </div>
    </div>

    {{-- Right form --}}
    <div class="signup-card">

        <div class="form-header">
            <h2>Join Community</h2>
            <p>Create your alumni account and become part of the ICCR network.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('register.store') }}" method="POST"
              novalidate autocomplete="off" id="signupForm">
            @csrf

            {{-- ════════════════════════════════════════════════════════
                 STEP 1 — ICCR Alumni question (always visible)
                 ════════════════════════════════════════════════════════ --}}
            <div class="iccr-question-wrap">
                <label for="iccrSelect">Are you an ICCR Alumni? <span class="req">*</span></label>
                <select name="is_iccr_alumni" id="iccrSelect"
                        class="{{ $errors->has('is_iccr_alumni') ? 'input-error' : '' }}">
                    <option value="">— Please select —</option>
                    <option value="yes" {{ old('is_iccr_alumni') === 'yes' ? 'selected' : '' }}>Yes — I studied in India under ICCR Scholarship</option>
                    <option value="no"  {{ old('is_iccr_alumni') === 'no'  ? 'selected' : '' }}>No — I am associated with the alumni network otherwise</option>
                </select>
                @error('is_iccr_alumni')<span class="field-error">{{ $message }}</span>@enderror
                <p class="iccr-pending-hint" id="iccrHint">
                    <i class="fa-solid fa-circle-info" style="color:#3b82f6;"></i>
                    Please answer the question above to see the registration form.
                </p>
            </div>

            {{-- ════════════════════════════════════════════════════════
                 STEP 2 — Common fields (both YES and NO)
                 ════════════════════════════════════════════════════════ --}}
            <div id="commonFields" class="signup-section">
                <p class="section-label">Personal Information</p>
                <div class="form-grid">

                    {{-- Full Name --}}
                    <div class="form-group">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="full_name"
                               value="{{ old('full_name') }}"
                               placeholder="Enter your full name"
                               maxlength="150"
                               class="{{ $errors->has('full_name') ? 'input-error' : '' }}"
                               autocomplete="name">
                        @error('full_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label>Email Address <span class="req">*</span></label>
                        <input type="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="you@example.com"
                               maxlength="255"
                               class="{{ $errors->has('email') ? 'input-error' : '' }}"
                               autocomplete="email">
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Nationality --}}
                    <div class="form-group full-width">
                        <label>Nationality <span class="req">*</span></label>
                        <select name="nationality" class="{{ $errors->has('nationality') ? 'input-error' : '' }}">
                            <option value="">Select Nationality</option>
                            <option value="Afghan" {{ old('nationality')=='Afghan' ? 'selected' : '' }}>Afghan</option>
                            <option value="Albanian" {{ old('nationality')=='Albanian' ? 'selected' : '' }}>Albanian</option>
                            <option value="Algerian" {{ old('nationality')=='Algerian' ? 'selected' : '' }}>Algerian</option>
                            <option value="American" {{ old('nationality')=='American' ? 'selected' : '' }}>American</option>
                            <option value="Andorran" {{ old('nationality')=='Andorran' ? 'selected' : '' }}>Andorran</option>
                            <option value="Angolan" {{ old('nationality')=='Angolan' ? 'selected' : '' }}>Angolan</option>
                            <option value="Antiguans" {{ old('nationality')=='Antiguans' ? 'selected' : '' }}>Antiguans</option>
                            <option value="Argentinean" {{ old('nationality')=='Argentinean' ? 'selected' : '' }}>Argentinean</option>
                            <option value="Armenian" {{ old('nationality')=='Armenian' ? 'selected' : '' }}>Armenian</option>
                            <option value="Australian" {{ old('nationality')=='Australian' ? 'selected' : '' }}>Australian</option>
                            <option value="Austrian" {{ old('nationality')=='Austrian' ? 'selected' : '' }}>Austrian</option>
                            <option value="Azerbaijani" {{ old('nationality')=='Azerbaijani' ? 'selected' : '' }}>Azerbaijani</option>
                            <option value="Bahamian" {{ old('nationality')=='Bahamian' ? 'selected' : '' }}>Bahamian</option>
                            <option value="Bahraini" {{ old('nationality')=='Bahraini' ? 'selected' : '' }}>Bahraini</option>
                            <option value="Bangladeshi" {{ old('nationality')=='Bangladeshi' ? 'selected' : '' }}>Bangladeshi</option>
                            <option value="Barbadian" {{ old('nationality')=='Barbadian' ? 'selected' : '' }}>Barbadian</option>
                            <option value="Barbudans" {{ old('nationality')=='Barbudans' ? 'selected' : '' }}>Barbudans</option>
                            <option value="Batswana" {{ old('nationality')=='Batswana' ? 'selected' : '' }}>Batswana</option>
                            <option value="Belarusian" {{ old('nationality')=='Belarusian' ? 'selected' : '' }}>Belarusian</option>
                            <option value="Belgian" {{ old('nationality')=='Belgian' ? 'selected' : '' }}>Belgian</option>
                            <option value="Belizean" {{ old('nationality')=='Belizean' ? 'selected' : '' }}>Belizean</option>
                            <option value="Beninese" {{ old('nationality')=='Beninese' ? 'selected' : '' }}>Beninese</option>
                            <option value="Bhutanese" {{ old('nationality')=='Bhutanese' ? 'selected' : '' }}>Bhutanese</option>
                            <option value="Bolivian" {{ old('nationality')=='Bolivian' ? 'selected' : '' }}>Bolivian</option>
                            <option value="Bosnian" {{ old('nationality')=='Bosnian' ? 'selected' : '' }}>Bosnian</option>
                            <option value="Brazilian" {{ old('nationality')=='Brazilian' ? 'selected' : '' }}>Brazilian</option>
                            <option value="British" {{ old('nationality')=='British' ? 'selected' : '' }}>British</option>
                            <option value="Bruneian" {{ old('nationality')=='Bruneian' ? 'selected' : '' }}>Bruneian</option>
                            <option value="Bulgarian" {{ old('nationality')=='Bulgarian' ? 'selected' : '' }}>Bulgarian</option>
                            <option value="Burkinabe" {{ old('nationality')=='Burkinabe' ? 'selected' : '' }}>Burkinabe</option>
                            <option value="Burmese" {{ old('nationality')=='Burmese' ? 'selected' : '' }}>Burmese</option>
                            <option value="Burundian" {{ old('nationality')=='Burundian' ? 'selected' : '' }}>Burundian</option>
                            <option value="Cambodian" {{ old('nationality')=='Cambodian' ? 'selected' : '' }}>Cambodian</option>
                            <option value="Cameroonian" {{ old('nationality')=='Cameroonian' ? 'selected' : '' }}>Cameroonian</option>
                            <option value="Canadian" {{ old('nationality')=='Canadian' ? 'selected' : '' }}>Canadian</option>
                            <option value="Cape Verdean" {{ old('nationality')=='Cape Verdean' ? 'selected' : '' }}>Cape Verdean</option>
                            <option value="Central African" {{ old('nationality')=='Central African' ? 'selected' : '' }}>Central African</option>
                            <option value="Chadian" {{ old('nationality')=='Chadian' ? 'selected' : '' }}>Chadian</option>
                            <option value="Chilean" {{ old('nationality')=='Chilean' ? 'selected' : '' }}>Chilean</option>
                            <option value="Chinese" {{ old('nationality')=='Chinese' ? 'selected' : '' }}>Chinese</option>
                            <option value="Colombian" {{ old('nationality')=='Colombian' ? 'selected' : '' }}>Colombian</option>
                            <option value="Comoran" {{ old('nationality')=='Comoran' ? 'selected' : '' }}>Comoran</option>
                            <option value="Congolese" {{ old('nationality')=='Congolese' ? 'selected' : '' }}>Congolese</option>
                            <option value="Costa Rican" {{ old('nationality')=='Costa Rican' ? 'selected' : '' }}>Costa Rican</option>
                            <option value="Croatian" {{ old('nationality')=='Croatian' ? 'selected' : '' }}>Croatian</option>
                            <option value="Cuban" {{ old('nationality')=='Cuban' ? 'selected' : '' }}>Cuban</option>
                            <option value="Cypriot" {{ old('nationality')=='Cypriot' ? 'selected' : '' }}>Cypriot</option>
                            <option value="Czech" {{ old('nationality')=='Czech' ? 'selected' : '' }}>Czech</option>
                            <option value="Danish" {{ old('nationality')=='Danish' ? 'selected' : '' }}>Danish</option>
                            <option value="Djibouti" {{ old('nationality')=='Djibouti' ? 'selected' : '' }}>Djibouti</option>
                            <option value="Dominican" {{ old('nationality')=='Dominican' ? 'selected' : '' }}>Dominican</option>
                            <option value="Dutch" {{ old('nationality')=='Dutch' ? 'selected' : '' }}>Dutch</option>
                            <option value="East Timorese" {{ old('nationality')=='East Timorese' ? 'selected' : '' }}>East Timorese</option>
                            <option value="Ecuadorean" {{ old('nationality')=='Ecuadorean' ? 'selected' : '' }}>Ecuadorean</option>
                            <option value="Egyptian" {{ old('nationality')=='Egyptian' ? 'selected' : '' }}>Egyptian</option>
                            <option value="Emirian" {{ old('nationality')=='Emirian' ? 'selected' : '' }}>Emirian</option>
                            <option value="Equatorial Guinean" {{ old('nationality')=='Equatorial Guinean' ? 'selected' : '' }}>Equatorial Guinean</option>
                            <option value="Eritrean" {{ old('nationality')=='Eritrean' ? 'selected' : '' }}>Eritrean</option>
                            <option value="Estonian" {{ old('nationality')=='Estonian' ? 'selected' : '' }}>Estonian</option>
                            <option value="Ethiopian" {{ old('nationality')=='Ethiopian' ? 'selected' : '' }}>Ethiopian</option>
                            <option value="Fijian" {{ old('nationality')=='Fijian' ? 'selected' : '' }}>Fijian</option>
                            <option value="Filipino" {{ old('nationality')=='Filipino' ? 'selected' : '' }}>Filipino</option>
                            <option value="Finnish" {{ old('nationality')=='Finnish' ? 'selected' : '' }}>Finnish</option>
                            <option value="French" {{ old('nationality')=='French' ? 'selected' : '' }}>French</option>
                            <option value="Gabonese" {{ old('nationality')=='Gabonese' ? 'selected' : '' }}>Gabonese</option>
                            <option value="Gambian" {{ old('nationality')=='Gambian' ? 'selected' : '' }}>Gambian</option>
                            <option value="Georgian" {{ old('nationality')=='Georgian' ? 'selected' : '' }}>Georgian</option>
                            <option value="German" {{ old('nationality')=='German' ? 'selected' : '' }}>German</option>
                            <option value="Ghanaian" {{ old('nationality')=='Ghanaian' ? 'selected' : '' }}>Ghanaian</option>
                            <option value="Greek" {{ old('nationality')=='Greek' ? 'selected' : '' }}>Greek</option>
                            <option value="Grenadian" {{ old('nationality')=='Grenadian' ? 'selected' : '' }}>Grenadian</option>
                            <option value="Guatemalan" {{ old('nationality')=='Guatemalan' ? 'selected' : '' }}>Guatemalan</option>
                            <option value="Guinea-Bissauan" {{ old('nationality')=='Guinea-Bissauan' ? 'selected' : '' }}>Guinea-Bissauan</option>
                            <option value="Guinean" {{ old('nationality')=='Guinean' ? 'selected' : '' }}>Guinean</option>
                            <option value="Guyanese" {{ old('nationality')=='Guyanese' ? 'selected' : '' }}>Guyanese</option>
                            <option value="Haitian" {{ old('nationality')=='Haitian' ? 'selected' : '' }}>Haitian</option>
                            <option value="Herzegovinian" {{ old('nationality')=='Herzegovinian' ? 'selected' : '' }}>Herzegovinian</option>
                            <option value="Honduran" {{ old('nationality')=='Honduran' ? 'selected' : '' }}>Honduran</option>
                            <option value="Hungarian" {{ old('nationality')=='Hungarian' ? 'selected' : '' }}>Hungarian</option>
                            <option value="I-Kiribati" {{ old('nationality')=='I-Kiribati' ? 'selected' : '' }}>I-Kiribati</option>
                            <option value="Icelander" {{ old('nationality')=='Icelander' ? 'selected' : '' }}>Icelander</option>
                            <option value="Indian" {{ old('nationality')=='Indian' ? 'selected' : '' }}>Indian</option>
                            <option value="Indonesian" {{ old('nationality')=='Indonesian' ? 'selected' : '' }}>Indonesian</option>
                            <option value="Iranian" {{ old('nationality')=='Iranian' ? 'selected' : '' }}>Iranian</option>
                            <option value="Iraqi" {{ old('nationality')=='Iraqi' ? 'selected' : '' }}>Iraqi</option>
                            <option value="Irish" {{ old('nationality')=='Irish' ? 'selected' : '' }}>Irish</option>
                            <option value="Israeli" {{ old('nationality')=='Israeli' ? 'selected' : '' }}>Israeli</option>
                            <option value="Italian" {{ old('nationality')=='Italian' ? 'selected' : '' }}>Italian</option>
                            <option value="Ivorian" {{ old('nationality')=='Ivorian' ? 'selected' : '' }}>Ivorian</option>
                            <option value="Jamaican" {{ old('nationality')=='Jamaican' ? 'selected' : '' }}>Jamaican</option>
                            <option value="Japanese" {{ old('nationality')=='Japanese' ? 'selected' : '' }}>Japanese</option>
                            <option value="Jordanian" {{ old('nationality')=='Jordanian' ? 'selected' : '' }}>Jordanian</option>
                            <option value="Kazakhstani" {{ old('nationality')=='Kazakhstani' ? 'selected' : '' }}>Kazakhstani</option>
                            <option value="Kenyan" {{ old('nationality')=='Kenyan' ? 'selected' : '' }}>Kenyan</option>
                            <option value="Kittian and Nevisian" {{ old('nationality')=='Kittian and Nevisian' ? 'selected' : '' }}>Kittian and Nevisian</option>
                            <option value="Kuwaiti" {{ old('nationality')=='Kuwaiti' ? 'selected' : '' }}>Kuwaiti</option>
                            <option value="Kyrgyz" {{ old('nationality')=='Kyrgyz' ? 'selected' : '' }}>Kyrgyz</option>
                            <option value="Laotian" {{ old('nationality')=='Laotian' ? 'selected' : '' }}>Laotian</option>
                            <option value="Latvian" {{ old('nationality')=='Latvian' ? 'selected' : '' }}>Latvian</option>
                            <option value="Lebanese" {{ old('nationality')=='Lebanese' ? 'selected' : '' }}>Lebanese</option>
                            <option value="Liberian" {{ old('nationality')=='Liberian' ? 'selected' : '' }}>Liberian</option>
                            <option value="Libyan" {{ old('nationality')=='Libyan' ? 'selected' : '' }}>Libyan</option>
                            <option value="Liechtensteiner" {{ old('nationality')=='Liechtensteiner' ? 'selected' : '' }}>Liechtensteiner</option>
                            <option value="Lithuanian" {{ old('nationality')=='Lithuanian' ? 'selected' : '' }}>Lithuanian</option>
                            <option value="Luxembourger" {{ old('nationality')=='Luxembourger' ? 'selected' : '' }}>Luxembourger</option>
                            <option value="Macedonian" {{ old('nationality')=='Macedonian' ? 'selected' : '' }}>Macedonian</option>
                            <option value="Malagasy" {{ old('nationality')=='Malagasy' ? 'selected' : '' }}>Malagasy</option>
                            <option value="Malawian" {{ old('nationality')=='Malawian' ? 'selected' : '' }}>Malawian</option>
                            <option value="Malaysian" {{ old('nationality')=='Malaysian' ? 'selected' : '' }}>Malaysian</option>
                            <option value="Maldivian" {{ old('nationality')=='Maldivian' ? 'selected' : '' }}>Maldivian</option>
                            <option value="Malian" {{ old('nationality')=='Malian' ? 'selected' : '' }}>Malian</option>
                            <option value="Maltese" {{ old('nationality')=='Maltese' ? 'selected' : '' }}>Maltese</option>
                            <option value="Marshallese" {{ old('nationality')=='Marshallese' ? 'selected' : '' }}>Marshallese</option>
                            <option value="Mauritanian" {{ old('nationality')=='Mauritanian' ? 'selected' : '' }}>Mauritanian</option>
                            <option value="Mauritian" {{ old('nationality')=='Mauritian' ? 'selected' : '' }}>Mauritian</option>
                            <option value="Mexican" {{ old('nationality')=='Mexican' ? 'selected' : '' }}>Mexican</option>
                            <option value="Micronesian" {{ old('nationality')=='Micronesian' ? 'selected' : '' }}>Micronesian</option>
                            <option value="Moldovan" {{ old('nationality')=='Moldovan' ? 'selected' : '' }}>Moldovan</option>
                            <option value="Monacan" {{ old('nationality')=='Monacan' ? 'selected' : '' }}>Monacan</option>
                            <option value="Mongolian" {{ old('nationality')=='Mongolian' ? 'selected' : '' }}>Mongolian</option>
                            <option value="Moroccan" {{ old('nationality')=='Moroccan' ? 'selected' : '' }}>Moroccan</option>
                            <option value="Mosotho" {{ old('nationality')=='Mosotho' ? 'selected' : '' }}>Mosotho</option>
                            <option value="Motswana" {{ old('nationality')=='Motswana' ? 'selected' : '' }}>Motswana</option>
                            <option value="Mozambican" {{ old('nationality')=='Mozambican' ? 'selected' : '' }}>Mozambican</option>
                            <option value="Namibian" {{ old('nationality')=='Namibian' ? 'selected' : '' }}>Namibian</option>
                            <option value="Nauruan" {{ old('nationality')=='Nauruan' ? 'selected' : '' }}>Nauruan</option>
                            <option value="Nepali" {{ old('nationality')=='Nepali' ? 'selected' : '' }}>Nepali</option>
                            <option value="New Zealander" {{ old('nationality')=='New Zealander' ? 'selected' : '' }}>New Zealander</option>
                            <option value="Ni-Vanuatu" {{ old('nationality')=='Ni-Vanuatu' ? 'selected' : '' }}>Ni-Vanuatu</option>
                            <option value="Nicaraguan" {{ old('nationality')=='Nicaraguan' ? 'selected' : '' }}>Nicaraguan</option>
                            <option value="Nigerian" {{ old('nationality')=='Nigerian' ? 'selected' : '' }}>Nigerian</option>
                            <option value="Nigerien" {{ old('nationality')=='Nigerien' ? 'selected' : '' }}>Nigerien</option>
                            <option value="North Korean" {{ old('nationality')=='North Korean' ? 'selected' : '' }}>North Korean</option>
                            <option value="Norwegian" {{ old('nationality')=='Norwegian' ? 'selected' : '' }}>Norwegian</option>
                            <option value="Omani" {{ old('nationality')=='Omani' ? 'selected' : '' }}>Omani</option>
                            <option value="Pakistani" {{ old('nationality')=='Pakistani' ? 'selected' : '' }}>Pakistani</option>
                            <option value="Palauan" {{ old('nationality')=='Palauan' ? 'selected' : '' }}>Palauan</option>
                            <option value="Palestinian" {{ old('nationality')=='Palestinian' ? 'selected' : '' }}>Palestinian</option>
                            <option value="Panamanian" {{ old('nationality')=='Panamanian' ? 'selected' : '' }}>Panamanian</option>
                            <option value="Papua New Guinean" {{ old('nationality')=='Papua New Guinean' ? 'selected' : '' }}>Papua New Guinean</option>
                            <option value="Paraguayan" {{ old('nationality')=='Paraguayan' ? 'selected' : '' }}>Paraguayan</option>
                            <option value="Peruvian" {{ old('nationality')=='Peruvian' ? 'selected' : '' }}>Peruvian</option>
                            <option value="Polish" {{ old('nationality')=='Polish' ? 'selected' : '' }}>Polish</option>
                            <option value="Portuguese" {{ old('nationality')=='Portuguese' ? 'selected' : '' }}>Portuguese</option>
                            <option value="Qatari" {{ old('nationality')=='Qatari' ? 'selected' : '' }}>Qatari</option>
                            <option value="Romanian" {{ old('nationality')=='Romanian' ? 'selected' : '' }}>Romanian</option>
                            <option value="Russian" {{ old('nationality')=='Russian' ? 'selected' : '' }}>Russian</option>
                            <option value="Rwandan" {{ old('nationality')=='Rwandan' ? 'selected' : '' }}>Rwandan</option>
                            <option value="Saint Lucian" {{ old('nationality')=='Saint Lucian' ? 'selected' : '' }}>Saint Lucian</option>
                            <option value="Salvadoran" {{ old('nationality')=='Salvadoran' ? 'selected' : '' }}>Salvadoran</option>
                            <option value="Samoan" {{ old('nationality')=='Samoan' ? 'selected' : '' }}>Samoan</option>
                            <option value="San Marinese" {{ old('nationality')=='San Marinese' ? 'selected' : '' }}>San Marinese</option>
                            <option value="Sao Tomean" {{ old('nationality')=='Sao Tomean' ? 'selected' : '' }}>Sao Tomean</option>
                            <option value="Saudi" {{ old('nationality')=='Saudi' ? 'selected' : '' }}>Saudi</option>
                            <option value="Senegalese" {{ old('nationality')=='Senegalese' ? 'selected' : '' }}>Senegalese</option>
                            <option value="Serbian" {{ old('nationality')=='Serbian' ? 'selected' : '' }}>Serbian</option>
                            <option value="Seychellois" {{ old('nationality')=='Seychellois' ? 'selected' : '' }}>Seychellois</option>
                            <option value="Sierra Leonean" {{ old('nationality')=='Sierra Leonean' ? 'selected' : '' }}>Sierra Leonean</option>
                            <option value="Singaporean" {{ old('nationality')=='Singaporean' ? 'selected' : '' }}>Singaporean</option>
                            <option value="Slovakian" {{ old('nationality')=='Slovakian' ? 'selected' : '' }}>Slovakian</option>
                            <option value="Slovenian" {{ old('nationality')=='Slovenian' ? 'selected' : '' }}>Slovenian</option>
                            <option value="Solomon Islander" {{ old('nationality')=='Solomon Islander' ? 'selected' : '' }}>Solomon Islander</option>
                            <option value="Somali" {{ old('nationality')=='Somali' ? 'selected' : '' }}>Somali</option>
                            <option value="South African" {{ old('nationality')=='South African' ? 'selected' : '' }}>South African</option>
                            <option value="South Korean" {{ old('nationality')=='South Korean' ? 'selected' : '' }}>South Korean</option>
                            <option value="South Sudanese" {{ old('nationality')=='South Sudanese' ? 'selected' : '' }}>South Sudanese</option>
                            <option value="Spanish" {{ old('nationality')=='Spanish' ? 'selected' : '' }}>Spanish</option>
                            <option value="Sri Lankan" {{ old('nationality')=='Sri Lankan' ? 'selected' : '' }}>Sri Lankan</option>
                            <option value="Sudanese" {{ old('nationality')=='Sudanese' ? 'selected' : '' }}>Sudanese</option>
                            <option value="Surinamer" {{ old('nationality')=='Surinamer' ? 'selected' : '' }}>Surinamer</option>
                            <option value="Swazi" {{ old('nationality')=='Swazi' ? 'selected' : '' }}>Swazi</option>
                            <option value="Swedish" {{ old('nationality')=='Swedish' ? 'selected' : '' }}>Swedish</option>
                            <option value="Swiss" {{ old('nationality')=='Swiss' ? 'selected' : '' }}>Swiss</option>
                            <option value="Syrian" {{ old('nationality')=='Syrian' ? 'selected' : '' }}>Syrian</option>
                            <option value="Taiwanese" {{ old('nationality')=='Taiwanese' ? 'selected' : '' }}>Taiwanese</option>
                            <option value="Tajik" {{ old('nationality')=='Tajik' ? 'selected' : '' }}>Tajik</option>
                            <option value="Tanzanian" {{ old('nationality')=='Tanzanian' ? 'selected' : '' }}>Tanzanian</option>
                            <option value="Thai" {{ old('nationality')=='Thai' ? 'selected' : '' }}>Thai</option>
                            <option value="Togolese" {{ old('nationality')=='Togolese' ? 'selected' : '' }}>Togolese</option>
                            <option value="Tongan" {{ old('nationality')=='Tongan' ? 'selected' : '' }}>Tongan</option>
                            <option value="Trinidadian and Tobagonian" {{ old('nationality')=='Trinidadian and Tobagonian' ? 'selected' : '' }}>Trinidadian and Tobagonian</option>
                            <option value="Tunisian" {{ old('nationality')=='Tunisian' ? 'selected' : '' }}>Tunisian</option>
                            <option value="Turkish" {{ old('nationality')=='Turkish' ? 'selected' : '' }}>Turkish</option>
                            <option value="Tuvaluan" {{ old('nationality')=='Tuvaluan' ? 'selected' : '' }}>Tuvaluan</option>
                            <option value="Ugandan" {{ old('nationality')=='Ugandan' ? 'selected' : '' }}>Ugandan</option>
                            <option value="Ukrainian" {{ old('nationality')=='Ukrainian' ? 'selected' : '' }}>Ukrainian</option>
                            <option value="Uruguayan" {{ old('nationality')=='Uruguayan' ? 'selected' : '' }}>Uruguayan</option>
                            <option value="Uzbekistani" {{ old('nationality')=='Uzbekistani' ? 'selected' : '' }}>Uzbekistani</option>
                            <option value="Venezuelan" {{ old('nationality')=='Venezuelan' ? 'selected' : '' }}>Venezuelan</option>
                            <option value="Vietnamese" {{ old('nationality')=='Vietnamese' ? 'selected' : '' }}>Vietnamese</option>
                            <option value="Yemenite" {{ old('nationality')=='Yemenite' ? 'selected' : '' }}>Yemenite</option>
                            <option value="Zambian" {{ old('nationality')=='Zambian' ? 'selected' : '' }}>Zambian</option>
                            <option value="Zimbabwean" {{ old('nationality')=='Zimbabwean' ? 'selected' : '' }}>Zimbabwean</option>
                        </select>
                        @error('nationality')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                </div>{{-- /form-grid --}}
            </div>{{-- /commonFields --}}

            {{-- ════════════════════════════════════════════════════════
                 STEP 3 — NO-path only fields
                 ════════════════════════════════════════════════════════ --}}
            <div id="noOnlyFields" class="signup-section">
                <p class="section-label">Academic Details</p>
                <div class="form-grid">

                    {{-- Batch Year --}}
                    <div class="form-group">
                        <label>Batch Year <span class="req">*</span></label>
                        <select name="batch_name" class="{{ $errors->has('batch_name') ? 'input-error' : '' }}">
                            <option value="">Select Batch Year</option>
                            @for($y = date('Y'); $y >= 1980; $y--)
                                <option value="{{ $y }}" {{ old('batch_name') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        @error('batch_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Phone --}}
                    <div class="form-group">
                        <label>Phone Number <span class="req">*</span></label>
                        <input type="tel" name="phone"
                               value="{{ old('phone') }}"
                               placeholder="+91 9876543210"
                               maxlength="20"
                               class="{{ $errors->has('phone') ? 'input-error' : '' }}"
                               autocomplete="tel">
                        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Department --}}
                    <div class="form-group">
                        <label>Department <span class="req">*</span></label>
                        <select name="department" class="{{ $errors->has('department') ? 'input-error' : '' }}">
                            <option value="">Select Department</option>
                            <option value="STEM"     {{ old('department') == 'STEM'     ? 'selected' : '' }}>STEM</option>
                            <option value="Non-STEM" {{ old('department') == 'Non-STEM' ? 'selected' : '' }}>Non-STEM</option>
                        </select>
                        @error('department')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Passing Year --}}
                    <div class="form-group">
                        <label>Passing Year <span class="req">*</span></label>
                        <select name="passing_year" class="{{ $errors->has('passing_year') ? 'input-error' : '' }}">
                            <option value="">Select Passing Year</option>
                            @for($y = date('Y'); $y >= 1980; $y--)
                                <option value="{{ $y }}" {{ old('passing_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        @error('passing_year')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Roll Number (optional) --}}
                    <div class="form-group">
                        <label>ID / Roll Number <span style="font-weight:400;font-size:12px;color:#888;">(optional)</span></label>
                        <input type="text" name="roll_number"
                               value="{{ old('roll_number') }}"
                               placeholder="Your ID/Roll Number"
                               maxlength="50"
                               class="{{ $errors->has('roll_number') ? 'input-error' : '' }}">
                        @error('roll_number')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Gender --}}
                    <div class="form-group">
                        <label>Gender <span class="req">*</span></label>
                        <select name="gender" class="{{ $errors->has('gender') ? 'input-error' : '' }}">
                            <option value="">Select Gender</option>
                            <option value="Male"   {{ old('gender') == 'Male'   ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other"  {{ old('gender') == 'Other'  ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Birth Date (optional) --}}
                    <div class="form-group">
                        <label>Birth Date <span style="font-weight:400;font-size:12px;color:#888;">(optional)</span></label>
                        <input type="date" name="birth_date"
                               value="{{ old('birth_date') }}"
                               max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                               class="{{ $errors->has('birth_date') ? 'input-error' : '' }}">
                        @error('birth_date')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Current Position (optional) --}}
                    <div class="form-group">
                        <label>Current Position <span style="font-weight:400;font-size:12px;color:#888;">(optional)</span></label>
                        <input type="text" name="current_position"
                               value="{{ old('current_position') }}"
                               placeholder="e.g. Software Engineer at Acme Corp"
                               maxlength="255"
                               class="{{ $errors->has('current_position') ? 'input-error' : '' }}">
                        @error('current_position')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Institute — 134-university dropdown with optgroups --}}
                    <div class="form-group full-width">
                        <label>Institution <span class="req">*</span></label>
                        <select name="institute" class="{{ $errors->has('institute') ? 'input-error' : '' }}"
                                style="height:auto;min-height:55px;padding-top:14px;padding-bottom:14px;">
                            <option value="">— Select your ICCR-empanelled institution —</option>

                            <optgroup label="Central Universities">
                                @php $centralUnis = [
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
                                ]; @endphp
                                @foreach($centralUnis as $uni)
                                    <option value="{{ $uni }}" {{ old('institute') === $uni ? 'selected' : '' }}>{{ $uni }}</option>
                                @endforeach
                            </optgroup>

                            <optgroup label="State Universities">
                                @php $stateUnis = [
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
                                ]; @endphp
                                @foreach($stateUnis as $uni)
                                    <option value="{{ $uni }}" {{ old('institute') === $uni ? 'selected' : '' }}>{{ $uni }}</option>
                                @endforeach
                            </optgroup>

                            <optgroup label="Centrally Funded Technical Institutes">
                                @php $techInstitutes = [
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
                                ]; @endphp
                                @foreach($techInstitutes as $uni)
                                    <option value="{{ $uni }}" {{ old('institute') === $uni ? 'selected' : '' }}>{{ $uni }}</option>
                                @endforeach
                            </optgroup>

                            <optgroup label="Dance / Music / Traditional Knowledge Institutes">
                                @php $artsInstitutes = [
                                    'Dev Sanskriti Vishwavidyalaya, Shantikunj Gayatrikunj, Haridwar, Uttarakhand',
                                    'Indira Kala Sangeet Vishwavidyalaya, Khairagarh, Chhattisgarh',
                                    'Kalakshetra Foundation, Chennai, Tamil Nadu',
                                    'Kathak Kendra, New Delhi, Delhi',
                                    'Kendriya Hindi Sansthan, New Delhi, Delhi',
                                    'Pracheen Kala Kendra, Chandigarh',
                                    'National School of Drama, New Delhi, Delhi',
                                    'Satyajit Ray Film and Television Institute, Kolkata, West Bengal',
                                ]; @endphp
                                @foreach($artsInstitutes as $uni)
                                    <option value="{{ $uni }}" {{ old('institute') === $uni ? 'selected' : '' }}>{{ $uni }}</option>
                                @endforeach
                            </optgroup>

                            <optgroup label="Agricultural Universities">
                                @php $agriUnis = [
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
                                ]; @endphp
                                @foreach($agriUnis as $uni)
                                    <option value="{{ $uni }}" {{ old('institute') === $uni ? 'selected' : '' }}>{{ $uni }}</option>
                                @endforeach
                            </optgroup>

                            <optgroup label="Ayurveda / Yoga / Traditional Medicine Institutes">
                                @php $ayurvedaUnis = [
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
                                ]; @endphp
                                @foreach($ayurvedaUnis as $uni)
                                    <option value="{{ $uni }}" {{ old('institute') === $uni ? 'selected' : '' }}>{{ $uni }}</option>
                                @endforeach
                            </optgroup>

                            <optgroup label="─────────────────────────">
                                <option value="other" {{ old('institute') === 'other' ? 'selected' : '' }}>Other (not in list — please specify below)</option>
                            </optgroup>

                        </select>
                        @error('institute')<span class="field-error">{{ $message }}</span>@enderror

                        {{-- Other institution text input — shown only when "Other" is selected --}}
                        <div id="instituteOtherWrap" style="margin-top:10px;{{ old('institute') === 'other' ? '' : 'display:none;' }}">
                            <input type="text" name="institute_other"
                                   id="instituteOtherInput"
                                   value="{{ old('institute_other') }}"
                                   placeholder="Enter your institution / university name"
                                   maxlength="255"
                                   class="{{ $errors->has('institute_other') ? 'input-error' : '' }}"
                                   style="width:100%;height:55px;border:1px solid var(--border);border-radius:14px;padding:0 16px;font-size:15px;background:#fff;transition:0.3s;">
                            @error('institute_other')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                </div>{{-- /form-grid --}}
            </div>{{-- /noOnlyFields --}}

            {{-- ════════════════════════════════════════════════════════
                 STEP 4 — Password + CAPTCHA + Terms + Submit
                          (both YES and NO paths)
                 ════════════════════════════════════════════════════════ --}}
            <div id="bottomSection" class="signup-section">

                <p class="section-label">Account Security</p>

                {{-- Passwords --}}
                <div class="form-grid password-grid">
                    <div class="form-group">
                        <label>Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password" id="password"
                                   placeholder="Min 8 characters"
                                   maxlength="128"
                                   autocomplete="new-password"
                                   class="{{ $errors->has('password') ? 'input-error' : '' }}">
                            <button type="button" class="toggle-pw" data-target="password" aria-label="Show password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="pw-strength"><div class="pw-strength__bar" id="pwBar"></div></div>
                        <span class="pw-hint" id="pwHint">Use 8+ chars with uppercase, numbers &amp; symbols</span>
                        @error('password')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Confirm Password <span class="req">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   placeholder="Repeat password"
                                   maxlength="128"
                                   autocomplete="new-password"
                                   class="{{ $errors->has('password_confirmation') ? 'input-error' : '' }}">
                            <button type="button" class="toggle-pw" data-target="password_confirmation" aria-label="Show confirm password">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <span class="field-error" id="pwMatchErr" style="display:none;">Passwords do not match.</span>
                        @error('password_confirmation')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- CAPTCHA --}}
                <div class="captcha-section">
                    <label>Verification <span class="req">*</span></label>
                    <div class="captcha-wrapper">
                        <span id="captcha-image">{!! captcha_img('flat') !!}</span>
                        <button type="button" id="reloadCaptcha" class="captcha-refresh" aria-label="Refresh CAPTCHA">
                            <i class="fas fa-arrows-rotate"></i>
                        </button>
                    </div>
                    <input type="text" name="captcha" placeholder="Enter CAPTCHA" maxlength="20" autocomplete="off">
                    @error('captcha')<small class="field-error">{{ $message }}</small>@enderror
                </div>

                {{-- Terms --}}
                <div class="terms-check">
                    <input type="checkbox" id="terms" name="terms" {{ old('terms') ? 'checked' : '' }}>
                    <label for="terms">I agree to the <a href="#" style="color:var(--primary);">Terms &amp; Conditions</a> and Community Guidelines.</label>
                </div>
                @error('terms')<span class="field-error" style="margin-top:-14px;margin-bottom:12px;display:block;">{{ $message }}</span>@enderror

                <button type="submit" class="signup-btn" id="submitBtn">Join Community</button>

            </div>{{-- /bottomSection --}}

        </form>

        <div class="login-link">
            Already a member? <a href="{{ route('login') }}">Sign In</a>
        </div>

    </div>
</div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── ICCR toggle logic ─────────────────────────────────────────────────
    var iccrSelect    = document.getElementById('iccrSelect');
    var commonFields  = document.getElementById('commonFields');
    var noOnlyFields  = document.getElementById('noOnlyFields');
    var bottomSection = document.getElementById('bottomSection');
    var iccrHint      = document.getElementById('iccrHint');

    function applyIccrState(val) {
        if (val === 'yes') {
            commonFields.classList.add('active');
            noOnlyFields.classList.remove('active');
            bottomSection.classList.add('active');
            iccrHint.classList.add('hidden');
        } else if (val === 'no') {
            commonFields.classList.add('active');
            noOnlyFields.classList.add('active');
            bottomSection.classList.add('active');
            iccrHint.classList.add('hidden');
        } else {
            commonFields.classList.remove('active');
            noOnlyFields.classList.remove('active');
            bottomSection.classList.remove('active');
            iccrHint.classList.remove('hidden');
        }
    }

    iccrSelect.addEventListener('change', function () {
        applyIccrState(this.value);
    });

    // Restore state on page load (handles server-side validation errors)
    applyIccrState('{{ old("is_iccr_alumni", "") }}');

    // ── Institute "Other" toggle ──────────────────────────────────────────
    var instituteSelect     = document.querySelector('select[name="institute"]');
    var instituteOtherWrap  = document.getElementById('instituteOtherWrap');
    var instituteOtherInput = document.getElementById('instituteOtherInput');

    if (instituteSelect) {
        instituteSelect.addEventListener('change', function () {
            if (this.value === 'other') {
                instituteOtherWrap.style.display = 'block';
                instituteOtherInput.required = true;
            } else {
                instituteOtherWrap.style.display = 'none';
                instituteOtherInput.required = false;
                instituteOtherInput.value = '';
            }
        });
    }

    // ── CAPTCHA reload ────────────────────────────────────────────────────
    document.getElementById('reloadCaptcha')?.addEventListener('click', function () {
        fetch("{{ route('refresh.captcha') }}")
            .then(r => r.json())
            .then(d => { document.getElementById('captcha-image').innerHTML = d.captcha; });
    });

    // ── Password show/hide ────────────────────────────────────────────────
    document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var inp  = document.getElementById(this.dataset.target);
            var icon = this.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                inp.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // ── Password strength ─────────────────────────────────────────────────
    var pwInput = document.getElementById('password');
    var pwBar   = document.getElementById('pwBar');
    var pwHint  = document.getElementById('pwHint');

    function scorePassword(p) {
        var s = 0;
        if (p.length >= 8)  s++;
        if (p.length >= 12) s++;
        if (/[A-Z]/.test(p)) s++;
        if (/[0-9]/.test(p)) s++;
        if (/[^A-Za-z0-9]/.test(p)) s++;
        return s;
    }

    pwInput && pwInput.addEventListener('input', function () {
        var score  = scorePassword(this.value);
        var pct    = Math.min(score / 5 * 100, 100);
        var colors = ['#e53e3e','#dd6b20','#d69e2e','#38a169','#2b6cb0'];
        var labels = ['Very Weak','Weak','Fair','Strong','Very Strong'];
        pwBar.style.width      = pct + '%';
        pwBar.style.background = colors[Math.max(score - 1, 0)];
        pwHint.textContent     = this.value ? labels[Math.max(score - 1, 0)] : 'Use 8+ chars with uppercase, numbers & symbols';
    });

    // ── Password match ────────────────────────────────────────────────────
    var pwConf   = document.getElementById('password_confirmation');
    var matchErr = document.getElementById('pwMatchErr');

    pwConf && pwConf.addEventListener('input', function () {
        matchErr.style.display = (this.value && this.value !== pwInput.value) ? 'block' : 'none';
    });

    // ── Form submit guard ─────────────────────────────────────────────────
    document.getElementById('signupForm').addEventListener('submit', function (e) {
        // Ensure ICCR selection was made
        if (!iccrSelect.value) {
            e.preventDefault();
            iccrSelect.focus();
            iccrSelect.classList.add('input-error');
            return;
        }
        // Password match check
        if (pwConf && pwInput && pwConf.value !== pwInput.value) {
            matchErr.style.display = 'block';
            e.preventDefault();
            return;
        }
        if (pwInput && pwInput.value.length < 8) {
            e.preventDefault();
            return;
        }
        var btn = document.getElementById('submitBtn');
        btn.disabled    = true;
        btn.textContent = 'Submitting...';
    });

});
</script>
@endpush
@endsection
