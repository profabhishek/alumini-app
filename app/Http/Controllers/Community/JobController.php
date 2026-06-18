<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\JobApplication;

class JobController extends Controller
{
    // ── Public jobs listing ───────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Job::query()
            ->where('status', 'published')
            ->when($request->filled('search'), fn($q) =>
                $q->where(function ($q) use ($request) {
                    $q->where('title',        'like', "%{$request->search}%")
                      ->orWhere('company_name', 'like', "%{$request->search}%")
                      ->orWhere('location',     'like', "%{$request->search}%");
                })
            )
            ->when($request->filled('job_type'), fn($q) =>
                $q->where('job_type', $request->job_type)
            )
            ->when($request->filled('work_mode'), fn($q) =>
                $q->where('work_mode', $request->work_mode)
            )
            ->when($request->filter === 'active', fn($q) =>
                $q->where(function ($q) {
                    $q->whereNull('application_deadline')
                      ->orWhereDate('application_deadline', '>=', now());
                })
            )
            ->when($request->filter === 'expired', fn($q) =>
                $q->whereDate('application_deadline', '<', now())
            )
            ->latest()
            ->paginate(9)
            ->appends($request->query());

            $appliedJobIds = [];

            if (session()->has('alumni_id')) {
                $appliedJobIds = JobApplication::where('alumni_id', session('alumni_id'))
                    ->pluck('job_id')
                    ->toArray();
            }

        return view('jobs.index', [
            'jobs' => $query,
            'appliedJobIds' => $appliedJobIds,
        ]);
    }

    // ── Public job detail ─────────────────────────────────────────────────

    public function show(Job $job)
    {
        $alumniId = session('alumni_id');
        $role     = session('alumni_role');
    
        $isAdmin = in_array($role, ['admin', 'super_admin']);
        $isOwner = $alumniId && $job->created_by == $alumniId;
    
        // Only published jobs are publicly visible — owners/admins can preview.
        if ($job->status !== 'published' && !$isOwner && !$isAdmin) {
            abort(404);
        }
    
        $alreadyApplied = false;
        $application    = null;
    
        if ($alumniId) {
            $application = JobApplication::where('job_id', $job->id)
                ->where('alumni_id', $alumniId)   // was 'user_id'
                ->first();
    
            $alreadyApplied = (bool) $application;
        }
    
        $relatedJobs = Job::where('status', 'published')
            ->where('id', '!=', $job->id)
            ->latest()
            ->take(3)
            ->get();
    
        return view('jobs.show', compact('job', 'alreadyApplied', 'application', 'relatedJobs'));
    }

    // ── Create form ───────────────────────────────────────────────────────

    public function create()
    {
        return view('community.jobs.create');
    }

    // ── Store new job ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                => 'required|string|max:255',
            'company_name'         => 'required|string|max:255',
            'location'             => 'nullable|string|max:255',
            'job_type'             => 'required|in:Full-Time,Part-Time,Contract,Internship',
            'work_mode'            => 'required|in:Remote,On-site,Hybrid',
            'salary_min'           => 'nullable|integer|min:0',
            'salary_max'           => 'nullable|integer|min:0|gte:salary_min',
            'description'          => 'required|string',
            'requirements'         => 'nullable|string',
            'application_deadline' => 'nullable|date|after_or_equal:today',
            'application_link'     => 'nullable|url|max:500',
            'banner_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $bannerPath = null;

        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('jobs', 'public');
        }

        Job::create([
            'created_by'           => session('alumni_id'),
            'title'                => $validated['title'],
            'slug'                 => Str::slug($validated['title']) . '-' . time(),
            'company_name'         => $validated['company_name'],
            'location'             => $validated['location'] ?? null,
            'job_type'             => $validated['job_type'],
            'work_mode'            => $validated['work_mode'],
            'salary_min'           => $validated['salary_min'] ?? null,
            'salary_max'           => $validated['salary_max'] ?? null,
            'description'          => $validated['description'],
            'requirements'         => $validated['requirements'] ?? null,
            'application_deadline' => $validated['application_deadline'] ?? null,
            'application_link'     => $validated['application_link'] ?? null,
            'banner_image'         => $bannerPath,
            'status'               => 'pending',
        ]);

        return redirect()->route('jobs.my')->with('success', 'Job posted successfully and is pending approval.');
    }

    // ── My Jobs ───────────────────────────────────────────────────────────

    public function myJobs(Request $request)
    {
        // Read last seen BEFORE updating it
        $myJobsLastSeen = session('my_jobs_last_seen')
            ? \Carbon\Carbon::parse(session('my_jobs_last_seen'))
            : now()->subDays(7);

        $query = Job::with('creator')
            ->where('created_by', session('alumni_id'))
            ->latest();

        if ($request->filled('q')) {
            $search = trim($request->get('q'));
            $query->where(function ($q) use ($search) {
                $q->where('title',        'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('location',     'like', "%{$search}%");
            });
        }

        $allowedStatuses = ['pending', 'published', 'rejected'];
        if ($request->filled('status') && in_array($request->status, $allowedStatuses)) {
            $query->where('status', $request->status);
        }

        $jobs = $query->paginate(10)->appends($request->query());

        $jobIds = collect($jobs->items())->pluck('id')->toArray();

        $newApplicantCounts = empty($jobIds)
        ? collect()
        : \App\Models\JobApplication::whereIn('job_id', $jobIds)
            ->where('created_at', '>', $myJobsLastSeen)
            ->selectRaw('job_id, count(*) as cnt')
            ->groupBy('job_id')
            ->pluck('cnt', 'job_id');

        session(['my_jobs_last_seen' => now()->toDateTimeString()]);

        $stats = [
            'total'     => Job::where('created_by', session('alumni_id'))->count(),
            'pending'   => Job::where('created_by', session('alumni_id'))->where('status', 'pending')->count(),
            'published' => Job::where('created_by', session('alumni_id'))->where('status', 'published')->count(),
            'rejected'  => Job::where('created_by', session('alumni_id'))->where('status', 'rejected')->count(),
        ];

        return view('community.jobs.my-jobs', compact('jobs', 'stats', 'newApplicantCounts'));
    }
    // ── Ownership guard ───────────────────────────────────────────────────

    private function ensureOwnership(Job $job): void
    {
        $userId = session('alumni_id');
        $role   = session('alumni_role');

        if (
            $job->created_by != $userId &&
            !in_array($role, ['admin', 'super_admin'])
        ) {
            abort(403, 'Unauthorized action.');
        }
    }

    // ── Edit (returns JSON for AJAX modal) ────────────────────────────────

    public function edit(Job $job)
    {
        $this->ensureOwnership($job);

        return response()->json($job);
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, Job $job)
    {
        $this->ensureOwnership($job);

        $validated = $request->validate([
            'title'                => 'required|string|max:255',
            'company_name'         => 'required|string|max:255',
            'location'             => 'nullable|string|max:255',
            'job_type'             => 'required|in:Full-Time,Part-Time,Contract,Internship',
            'work_mode'            => 'required|in:Remote,On-site,Hybrid',
            'salary_min'           => 'nullable|integer|min:0',
            'salary_max'           => 'nullable|integer|min:0|gte:salary_min',
            'description'          => 'required|string',
            'requirements'         => 'nullable|string',
            'application_deadline' => 'nullable|date',
            'application_link'     => 'nullable|url|max:500',
            'banner_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('jobs', 'public');
        }

        $job->update([
            'title'                => $validated['title'],
            'company_name'         => $validated['company_name'],
            'location'             => $validated['location'] ?? null,
            'job_type'             => $validated['job_type'],
            'work_mode'            => $validated['work_mode'],
            'salary_min'           => $validated['salary_min'] ?? null,
            'salary_max'           => $validated['salary_max'] ?? null,
            'description'          => $validated['description'],
            'requirements'         => $validated['requirements'] ?? null,
            'application_deadline' => $validated['application_deadline'] ?? null,
            'application_link'     => $validated['application_link'] ?? null,
            'banner_image'         => $validated['banner_image'] ?? $job->banner_image,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Job updated successfully.']);
        }

        return redirect()->route('jobs.my')->with('success', 'Job updated successfully.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(Job $job)
    {
        $this->ensureOwnership($job);

        $job->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Job deleted successfully.']);
        }

        return redirect()->route('jobs.my')->with('success', 'Job deleted successfully.');
    }
}