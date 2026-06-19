<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // ── Show profile edit page ────────────────────────────────────────────

    public function index()
    {
        $user = AlumniUser::findOrFail(session('alumni_id'));

        return view('community.profile.index', compact('user'));
    }

    // ── Update basic info ─────────────────────────────────────────────────

    public function updateInfo(Request $request)
    {
        $user = AlumniUser::findOrFail(session('alumni_id'));

        $request->validate([
            'full_name'         => 'required|max:255',
            'phone'             => ['required', 'min:7', 'max:20', 'regex:/^[+\d\s\-()]{7,20}$/'],
            'bio'               => 'nullable|max:1000',
            'current_job_title' => 'nullable|max:255',
            'current_company'   => 'nullable|max:255',
            'current_city'      => 'nullable|max:255',
            'country'           => 'nullable|max:255',
            'linkedin_url'      => 'nullable|url|max:255',
            'twitter_url'       => 'nullable|url|max:255',
            'facebook_url'      => 'nullable|url|max:255',
            'website_url'       => 'nullable|url|max:255',
            'hide_email'        => 'nullable|boolean',
            'hide_phone'        => 'nullable|boolean',
        ]);

        $user->update([
            'full_name'         => $request->full_name,
            'phone'             => $request->phone,
            'bio'               => $request->bio,
            'current_job_title' => $request->current_job_title,
            'current_company'   => $request->current_company,
            'current_city'      => $request->current_city,
            'country'           => $request->country,
            'linkedin_url'      => $request->linkedin_url,
            'twitter_url'       => $request->twitter_url,
            'facebook_url'      => $request->facebook_url,
            'website_url'       => $request->website_url,
            'hide_email'        => $request->boolean('hide_email'),
            'hide_phone'        => $request->boolean('hide_phone'),
        ]);

        // Update session name in case it changed
        session(['alumni_name' => $user->full_name]);

        return back()->with('success', 'Profile updated successfully.');
    }

    // ── Upload cropped photo ──────────────────────────────────────────────

    public function updatePhoto(Request $request)
    {
        $request->validate([
            // base64 string sent from Cropper.js
            'cropped_photo' => 'required|string',
        ]);

        $user = AlumniUser::findOrFail(session('alumni_id'));

        $data = $request->input('cropped_photo');

        // Strip the data URI prefix:  data:image/jpeg;base64,...
        if (!preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
            return back()->with('error', 'Invalid image format.');
        }

        $imageData = substr($data, strpos($data, ',') + 1);
        $decoded   = base64_decode($imageData, true);

        if ($decoded === false || $decoded === '') {
            return back()->with('error', 'Could not process the image.');
        }

        // Max ~3 MB check on decoded bytes
        if (strlen($decoded) > 3 * 1024 * 1024) {
            return back()->with('error', 'Image is too large. Max 3 MB.');
        }

        // ── Security: verify bytes are a real image via GD, then re-encode ──
        // This strips any embedded payloads (e.g. PHP tags in EXIF data).
        $gdImage = @imagecreatefromstring($decoded);
        if ($gdImage === false) {
            return back()->with('error', 'Invalid image. Please upload a valid JPG or PNG.');
        }

        // Always output as JPEG (safe, strips metadata); use PNG only if transparency needed
        $mimeHint = strtolower($matches[1]);
        if ($mimeHint === 'png') {
            $extension = 'png';
            ob_start();
            imagepng($gdImage, null, 6); // compression 6
            $safeBytes = ob_get_clean();
        } else {
            $extension = 'jpg';
            ob_start();
            imagejpeg($gdImage, null, 90); // quality 90
            $safeBytes = ob_get_clean();
        }
        imagedestroy($gdImage);

        $filename = 'alumni-photos/' . session('alumni_id') . '_' . time() . '.' . $extension;

        // Delete old photo if exists
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        Storage::disk('public')->put($filename, $safeBytes);

        $user->update(['photo' => $filename]);

        // Update session avatar so header updates immediately
        session(['alumni_avatar' => $filename]);

        return back()->with('success', 'Profile photo updated.');
    }

    // ── Change password ───────────────────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'New passwords do not match.',
        ]);

        $user = AlumniUser::findOrFail(session('alumni_id'));

        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->with('tab', 'password');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.')->with('tab', 'password');
    }
}