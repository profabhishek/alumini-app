<?php

namespace App\Http\Controllers\Community\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class AdminJobController extends Controller
{
    /**
     * Allow:
     * - admin / super_admin
     * - moderator with approve_jobs permission
     */
    private function authorizeUser(): void
    {
        $role  = session('alumni_role');
        $perms = session('alumni_permissions', []);

        $allowed =
            in_array($role, ['admin', 'super_admin']) ||
            ($role === 'moderator' && !empty($perms['approve_jobs']));

        if (!$allowed) {
            abort(403, 'Unauthorized.');
        }
    }

    // ── Pending Jobs List ─────────────────────────────────────────────────

    public function pending(Request $request)
    {
        $this->authorizeUser();

        $query = Job::with('creator')
            ->where('status', 'pending')
            ->latest();

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('title',         'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('location',     'like', "%{$search}%")
                  ->orWhere('job_type',     'like', "%{$search}%")
                  ->orWhereHas('creator', function ($c) use ($search) {
                      $c->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email',   'like', "%{$search}%");
                  });
            });
        }

        $jobs = $query->paginate(12)->appends($request->query());

        $stats = [
            'pending'   => Job::where('status', 'pending')->count(),
            'published' => Job::where('status', 'published')->count(),
            'rejected'  => Job::where('status', 'rejected')->count(),
            'total'     => Job::count(),
        ];

        return view('community.admin.jobs.pending', compact('jobs', 'stats'));
    }

    // ── Approve Job ───────────────────────────────────────────────────────

    public function approve(Request $request, Job $job)
    {
        $this->authorizeUser();

        if ($job->status !== 'pending') {
            $message = 'Only pending jobs can be approved.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $job->update(['status' => 'published']);

        $message = '"' . $job->title . '" has been approved and published.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // ── Reject Job ────────────────────────────────────────────────────────

    public function reject(Request $request, Job $job)
    {
        $this->authorizeUser();

        if ($job->status !== 'pending') {
            $message = 'Only pending jobs can be rejected.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $job->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        $message = '"' . $job->title . '" has been rejected.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // ── All Jobs List ─────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorizeUser();

        $query = Job::with('creator')->latest();

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('title',         'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('location',     'like', "%{$search}%")
                  ->orWhereHas('creator', function ($c) use ($search) {
                      $c->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email',   'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        $jobs = $query->paginate(15)->appends($request->query());

        $stats = [
            'total'     => Job::count(),
            'pending'   => Job::where('status', 'pending')->count(),
            'published' => Job::where('status', 'published')->count(),
            'rejected'  => Job::where('status', 'rejected')->count(),
        ];

        return view('community.admin.jobs.index', compact('jobs', 'stats'));
    }

    // ── Edit (returns JSON for AJAX modal) ────────────────────────────────

    public function edit(Job $job)
    {
        $this->authorizeUser();

        return response()->json($job);
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, Job $job)
    {
        $this->authorizeUser();

        $request->validate([
            'title'                => 'required|string|max:255',
            'company_name'         => 'required|string|max:255',
            'location'             => 'nullable|string|max:255',
            'job_type'             => 'required|in:Full-Time,Part-Time,Contract,Internship',
            'work_mode'            => 'required|in:Remote,On-site,Hybrid',
            'status'               => 'required|in:pending,published,rejected',
            'application_deadline' => 'nullable|date',
        ]);

        $job->update([
            'title'                => $request->title,
            'company_name'         => $request->company_name,
            'location'             => $request->location,
            'job_type'             => $request->job_type,
            'work_mode'            => $request->work_mode,
            'status'               => $request->status,
            'application_deadline' => $request->application_deadline ?? null,
        ]);

        $message = '"' . $job->title . '" updated successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(Request $request, Job $job)
    {
        $this->authorizeUser();

        $job->delete();

        $message = 'Job deleted successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}