<?php

namespace App\Http\Controllers\Community\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastAnnouncementEmailsJob;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationHelper;
use App\Services\EmailBroadcastService;

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

        $job->update(['status' => 'published', 'published_at' => now()]);
        Cache::forget('pending_jobs_count');

        if ($job->created_by && $job->created_by !== session('alumni_id')) {
            NotificationHelper::fire(
                recipientId: $job->created_by,
                actorId:     session('alumni_id'),
                type:        'job_approved',
                preview:     $job->title,
            );
        }

        // Dispatch background job — returns instantly; worker handles bulk email
        BroadcastAnnouncementEmailsJob::dispatch('job', $job->id, $job->created_by ?? null);

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
            'published_at'     => null,
        ]);
        Cache::forget('pending_jobs_count');

        if ($job->created_by && $job->created_by !== session('alumni_id')) {
            NotificationHelper::fire(
                recipientId: $job->created_by,
                actorId:     session('alumni_id'),
                type:        'job_rejected',
                preview:     $job->title,
            );
        }

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
            'banner_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'description'          => 'required|string',
            'requirements'         => 'nullable|string',
            'salary_min'           => 'nullable|integer|min:0',
            'salary_max'           => 'nullable|integer|min:0',
        ]);

        $wasAlreadyPublished = ($job->status === 'published');

        $updates = [
            'title'                => $request->title,
            'company_name'         => $request->company_name,
            'location'             => $request->location,
            'job_type'             => $request->job_type,
            'work_mode'            => $request->work_mode,
            'status'               => $request->status,
            'application_deadline' => $request->application_deadline ?? null,
            'description'          => $request->description,
            'requirements'         => $request->requirements ?: null,
            'salary_min'           => $request->filled('salary_min') ? (int) $request->salary_min : null,
            'salary_max'           => $request->filled('salary_max') ? (int) $request->salary_max : null,
        ];

        // Stamp published_at when transitioning to published
        if ($request->status === 'published' && !$wasAlreadyPublished) {
            $updates['published_at'] = now();
        }

        if ($request->hasFile('banner_image')) {
            if ($job->banner_image) {
                Storage::disk('public')->delete($job->banner_image);
            }
            $updates['banner_image'] = $request->file('banner_image')
                ->store('jobs/banners', 'public');
        }

        $job->update($updates);

        // Dispatch background job when newly published via edit form
        if (!$wasAlreadyPublished && $job->fresh()->status === 'published') {
            BroadcastAnnouncementEmailsJob::dispatch('job', $job->id, $job->created_by ?? null);
        }

        $message = '"' . $job->title . '" updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => $message,
                'banner_image' => $job->fresh()->banner_image
                    ? asset('storage/' . $job->fresh()->banner_image)
                    : null,
            ]);
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