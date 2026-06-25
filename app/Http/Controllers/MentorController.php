<?php

namespace App\Http\Controllers;

use App\Models\MentorCategory;
use App\Models\MentorProfile;
use App\Models\MenteeConnection;
use App\Models\AlumniUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MentorController extends Controller
{
    // ── Browse approved mentors ───────────────────────────────────────────

    public function index(Request $request)
    {
        $categories = MentorCategory::active()->get();

        $query = MentorProfile::approved()
            ->with(['alumni', 'categories'])
            ->withCount('acceptedConnections');

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) =>
                $q->where('slug', $request->category)
            );
        }

        // Search by name / expertise
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->whereHas('alumni', fn ($u) =>
                    $u->where('full_name', 'like', "%{$search}%")
                      ->orWhere('current_job_title', 'like', "%{$search}%")
                      ->orWhere('current_company', 'like', "%{$search}%")
                )
                ->orWhere('expertise', 'like', "%{$search}%");
            });
        }

        $mentors = $query->latest()->paginate(12)->withQueryString();

        // Current user's connection statuses (if logged in)
        $myConnections = [];
        if ($alumniId = session('alumni_id')) {
            $myConnections = MenteeConnection::where('mentee_id', $alumniId)
                ->pluck('status', 'mentor_profile_id')
                ->toArray();
        }

        return view('community.mentors.index', compact('mentors', 'categories', 'myConnections'));
    }

    // ── Single mentor profile ─────────────────────────────────────────────

    public function show(MentorProfile $mentor)
    {
        if ($mentor->status !== 'approved') {
            abort(404);
        }

        $mentor->load(['alumni', 'categories', 'acceptedConnections.mentee']);

        $connection = null;
        if ($alumniId = session('alumni_id')) {
            $connection = MenteeConnection::where('mentor_profile_id', $mentor->id)
                ->where('mentee_id', $alumniId)
                ->first();
        }

        return view('community.mentors.show', compact('mentor', 'connection'));
    }

    // ── Apply to become a mentor ──────────────────────────────────────────

    public function applyForm()
    {
        $alumniId = session('alumni_id');
        if (!$alumniId) return redirect()->route('login');

        $existing = MentorProfile::where('alumni_user_id', $alumniId)->first();
        $categories = MentorCategory::active()->get();

        return view('community.mentors.apply', compact('existing', 'categories'));
    }

    public function applyStore(Request $request)
    {
        $alumniId = session('alumni_id');
        if (!$alumniId) return redirect()->route('login');

        $data = $request->validate([
            'bio'              => 'required|string|min:50|max:1000',
            'expertise'        => 'required|string|max:150',
            'experience_years' => 'required|integer|min:0|max:50',
            'availability'     => 'required|string|max:100',
            'max_mentees'      => 'required|integer|min:1|max:20',
            'categories'       => 'required|array|min:1|max:5',
            'categories.*'     => 'exists:mentor_categories,id',
        ]);

        $existing = MentorProfile::where('alumni_user_id', $alumniId)->first();

        if ($existing && $existing->status === 'approved') {
            // Update profile
            $existing->update([
                'bio'              => $data['bio'],
                'expertise'        => $data['expertise'],
                'experience_years' => $data['experience_years'],
                'availability'     => $data['availability'],
                'max_mentees'      => $data['max_mentees'],
            ]);
            $existing->categories()->sync($data['categories']);

            return back()->with('success', 'Your mentor profile has been updated.');
        }

        if ($existing && $existing->status === 'pending') {
            return back()->with('info', 'Your application is already under review.');
        }

        // Create new application
        $profile = MentorProfile::create([
            'alumni_user_id'   => $alumniId,
            'bio'              => $data['bio'],
            'expertise'        => $data['expertise'],
            'experience_years' => $data['experience_years'],
            'availability'     => $data['availability'],
            'max_mentees'      => $data['max_mentees'],
            'status'           => 'pending',
            'applied_at'       => now(),
        ]);

        $profile->categories()->sync($data['categories']);

        return back()->with('success', 'Your mentor application has been submitted! An admin will review it shortly.');
    }

    // ── Mentee connects to a mentor ───────────────────────────────────────

    public function connect(Request $request, MentorProfile $mentor)
    {
        $alumniId = session('alumni_id');
        if (!$alumniId) return response()->json(['error' => 'Unauthenticated'], 401);

        if ($mentor->status !== 'approved') {
            return response()->json(['error' => 'Mentor not available'], 422);
        }

        if ($mentor->alumni_user_id == $alumniId) {
            return response()->json(['error' => 'You cannot connect with yourself'], 422);
        }

        if (!$mentor->hasCapacity()) {
            return response()->json(['error' => 'This mentor has reached their maximum mentee capacity'], 422);
        }

        $existing = MenteeConnection::where('mentor_profile_id', $mentor->id)
            ->where('mentee_id', $alumniId)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'You have already sent a request to this mentor'], 422);
        }

        $data = $request->validate(['message' => 'nullable|string|max:500']);

        MenteeConnection::create([
            'mentor_profile_id' => $mentor->id,
            'mentee_id'         => $alumniId,
            'status'            => 'pending',
            'message'           => $data['message'] ?? null,
        ]);

        return response()->json(['success' => true, 'status' => 'pending']);
    }

    // ── Mentor responds to a connection request ───────────────────────────

    public function respond(Request $request, MenteeConnection $connection)
    {
        $alumniId = session('alumni_id');

        if ($connection->mentor->alumni_user_id != $alumniId) {
            abort(403);
        }

        $data = $request->validate([
            'action'      => 'required|in:accept,decline',
            'mentor_note' => 'nullable|string|max:300',
        ]);

        $status = $data['action'] === 'accept' ? 'accepted' : 'declined';

        $connection->update([
            'status'       => $status,
            'mentor_note'  => $data['mentor_note'] ?? null,
            'connected_at' => $status === 'accepted' ? now() : null,
        ]);

        return back()->with('success', 'Connection request ' . $status . '.');
    }

    // ── Cancel / withdraw a connection ────────────────────────────────────

    public function cancelConnection(MenteeConnection $connection)
    {
        $alumniId = session('alumni_id');

        // Either the mentee or the mentor can cancel
        $isMentee  = $connection->mentee_id == $alumniId;
        $isMentor  = $connection->mentor->alumni_user_id == $alumniId;

        if (!$isMentee && !$isMentor) abort(403);

        $connection->delete();

        return back()->with('success', 'Connection removed.');
    }

    // ── My connections (as mentee OR mentor) ──────────────────────────────

    public function myConnections()
    {
        $alumniId = session('alumni_id');
        if (!$alumniId) return redirect()->route('login');

        // As mentee: requests I've sent
        $asMentee = MenteeConnection::where('mentee_id', $alumniId)
            ->with(['mentor.alumni', 'mentor.categories'])
            ->latest()
            ->get();

        // As mentor (if user is an approved mentor)
        $myProfile = MentorProfile::where('alumni_user_id', $alumniId)
            ->where('status', 'approved')
            ->with(['connections.mentee', 'categories'])
            ->first();

        return view('community.mentors.my-connections', compact('asMentee', 'myProfile'));
    }
}
