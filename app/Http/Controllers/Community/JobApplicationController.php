<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationStatusMail;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    // ── Show Apply Form ───────────────────────────────────────────────────

    public function create(Job $job)
    {
        if ($job->status !== 'published') {
            abort(404);
        }

        $alreadyApplied = JobApplication::where('job_id', $job->id)
            ->where('alumni_id', session('alumni_id'))
            ->exists();

        if ($alreadyApplied) {
            return redirect()
                ->route('jobs.index')
                ->with('info', 'You have already applied for this job.');
        }

        return view('community.jobs.apply', compact('job'));
    }

    // ── Submit Application ────────────────────────────────────────────────

    public function store(Request $request, Job $job)
    {
        if ($job->status !== 'published') {
            abort(404);
        }

        $alreadyApplied = JobApplication::where('job_id', $job->id)
            ->where('alumni_id', session('alumni_id'))
            ->exists();

        if ($alreadyApplied) {
            return back()->with('error', 'You have already applied for this job.');
        }

        $validated = $request->validate([
            'phone'        => 'required|string|max:30',
            'resume'       => 'required|mimes:pdf,doc,docx|max:5120',
            'cover_letter' => 'nullable|string|max:5000',
        ]);

        // Store in private (non-public) disk so the file is NOT web-accessible directly.
        // Use the 'downloadResume' route to serve it through an authenticated controller.
        $resumePath = $request->file('resume')->store('job-resumes', 'local');

        JobApplication::create([
            'job_id'       => $job->id,
            'alumni_id'    => session('alumni_id'),
            'full_name'    => session('alumni_name'),
            'email'        => session('alumni_email'),
            'phone'        => $validated['phone'],
            'resume'       => $resumePath,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'status'       => 'submitted',
        ]);

        return redirect()
            ->route('jobs.my-applications')
            ->with('success', 'Application submitted successfully.');
    }

    // ── Secure Resume Download ────────────────────────────────────────────
    // Only the applicant themselves OR the job owner can download a resume.

    public function downloadResume(JobApplication $application)
    {
        $alumniId = session('alumni_id');

        // Allow: applicant themselves
        $isApplicant = $application->alumni_id === $alumniId;

        // Allow: job owner
        $isJobOwner = $application->job && $application->job->created_by === $alumniId;

        // Allow: admin / super_admin
        $isAdmin = in_array(session('alumni_role'), ['admin', 'super_admin']);

        if (!$isApplicant && !$isJobOwner && !$isAdmin) {
            abort(403, 'You do not have permission to download this resume.');
        }

        if (!$application->resume || !Storage::disk('local')->exists($application->resume)) {
            abort(404, 'Resume not found.');
        }

        $originalName = basename($application->resume);
        $extension    = pathinfo($originalName, PATHINFO_EXTENSION);
        $downloadName = 'resume_' . $application->full_name . '.' . $extension;

        return Storage::disk('local')->download($application->resume, $downloadName);
    }

    // ── My Applications ───────────────────────────────────────────────────

    public function myApplications()
    {
        $applications = JobApplication::with('job')
            ->where('alumni_id', session('alumni_id'))
            ->latest()
            ->paginate(10);

        $now = now()->toDateTimeString();
        session(['applications_last_seen' => $now]);
        \App\Models\AlumniUser::where('id', session('alumni_id'))->update(['applications_last_seen' => $now]);

        return view('community.jobs.my-applications', compact('applications'));
    }

    // ── Applicants for a job (job owner only) ─────────────────────────────

    public function applicants(Request $request, Job $job)
    {
        // Only the job creator can view applicants
        if ($job->created_by != session('alumni_id')) {
            abort(403, 'You do not have permission to view these applicants.');
        }

        $query = JobApplication::with('applicant')
            ->where('job_id', $job->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name / email / phone
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email',    'like', "%{$search}%")
                  ->orWhere('phone',    'like', "%{$search}%");
            });
        }

        $applications = $query->latest()->paginate(15)->appends($request->query());

        // One aggregate query instead of 5 separate COUNTs
        $rawStats = JobApplication::where('job_id', $job->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'submitted')   as submitted,
                SUM(status = 'shortlisted') as shortlisted,
                SUM(status = 'hired')       as hired,
                SUM(status = 'rejected')    as rejected
            ")
            ->first();

        $stats = [
            'total'       => (int) ($rawStats->total ?? 0),
            'submitted'   => (int) ($rawStats->submitted ?? 0),
            'shortlisted' => (int) ($rawStats->shortlisted ?? 0),
            'hired'       => (int) ($rawStats->hired ?? 0),
            'rejected'    => (int) ($rawStats->rejected ?? 0),
        ];

        return view('community.jobs.applicants', compact('job', 'applications', 'stats'));
    }

    // ── Update Application Status (job owner only) ────────────────────────

    public function updateStatus(Request $request, Job $job, JobApplication $application)
    {
        // Guard: only job creator can update status
        if ($job->created_by != session('alumni_id')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Guard: application must belong to this job
        if ($application->job_id !== $job->id) {
            return response()->json(['success' => false, 'message' => 'Application does not belong to this job.'], 422);
        }

        $request->validate([
            'status'           => 'required|in:submitted,shortlisted,hired,rejected',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        // Rejection must have a reason
        if ($request->status === 'rejected' && empty(trim($request->rejection_reason ?? ''))) {
            return response()->json([
                'success' => false,
                'errors'  => ['rejection_reason' => ['Please provide a reason for rejection.']],
            ], 422);
        }

        $oldStatus = $application->status;

        $application->update([
            'status'           => $request->status,
            'rejection_reason' => $request->status === 'rejected'
                ? $request->rejection_reason
                : null,
                    Mail::to($freshApplication->email)->send(
                        new ApplicationStatusMail(
                            $freshApplication,
                            $poster->full_name,
                            $poster->email,
                        )
                    );
                    \Illuminate\Support\Facades\Log::info('ApplicationStatusMail sent to ' . $freshApplication->email . ' status=' . $request->status);
                } else {
                    \Illuminate\Support\Facades\Log::warning('ApplicationStatusMail skipped: poster=' . ($poster ? 'found' : 'null') . ' job=' . ($freshApplication->job ? 'found' : 'null'));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('ApplicationStatusMail failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }
        }

        $labels = [
            'submitted'   => 'Submitted',
            'shortlisted' => 'Shortlisted',
            'hired'       => 'Hired',
            'rejected'    => 'Not Selected',
        ];

        return response()->json([
            'success' => true,
            'message' => "{$application->full_name} has been marked as {$labels[$request->status]}.",
            'status'  => $request->status,
        ]);
    }
}