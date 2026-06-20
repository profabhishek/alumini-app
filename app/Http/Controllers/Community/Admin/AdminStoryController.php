<?php

namespace App\Http\Controllers\Community\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastAnnouncementEmailsJob;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationHelper;
use App\Services\EmailBroadcastService;

class AdminStoryController extends Controller
{
    // ── Pending queue ─────────────────────────────────────────────────────

    public function pending(Request $request)
    {
        $stories = Story::pending()
            ->with('creator')
            ->when($request->filled('q'), fn($q) =>
                $q->where('title', 'like', "%{$request->q}%")
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('community.stories.admin.pending', compact('stories'));
    }

    // ── Approve ───────────────────────────────────────────────────────────

    public function approve(Story $story)
    {
        if ($story->status !== 'pending') {
            return response()->json(['error' => 'Story is not in pending state.'], 422);
        }

        $story->update([
            'status'           => 'published',
            'rejection_reason' => null,
            'published_at'     => now(),
        ]);
        Cache::forget('pending_stories_count');

        if ($story->created_by && $story->created_by !== session('alumni_id')) {
            NotificationHelper::fire(
                recipientId: $story->created_by,
                actorId:     session('alumni_id'),
                type:        'story_approved',
                preview:     $story->title,
            );
        }

        // Dispatch background job — returns instantly; worker handles bulk email
        BroadcastAnnouncementEmailsJob::dispatch('story', $story->id, $story->created_by ?? null);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Story approved and published.']);
        }

        return back()->with('success', '"' . $story->title . '" has been approved and published.');
    }

    // ── Reject ────────────────────────────────────────────────────────────

    public function reject(Request $request, Story $story)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if ($story->status !== 'pending') {
            return response()->json(['error' => 'Story is not in pending state.'], 422);
        }

        $story->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason ?? null,
            'published_at'     => null,
        ]);
        Cache::forget('pending_stories_count');

        if ($story->created_by && $story->created_by !== session('alumni_id')) {
            NotificationHelper::fire(
                recipientId: $story->created_by,
                actorId:     session('alumni_id'),
                type:        'story_rejected',
                preview:     $story->title,
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Story rejected.']);
        }

        return back()->with('success', '"' . $story->title . '" has been rejected.');
    }

    // ── All stories (admin overview) ──────────────────────────────────────

    public function index(Request $request)
    {
        $stories = Story::with('creator')
            ->when($request->filled('q'), fn($q) =>
                $q->where('title', 'like', "%{$request->q}%")
            )
            ->when($request->filled('status'), fn($q) =>
                $q->where('status', $request->status)
            )
            ->latest()
            ->paginate(15)
            ->appends($request->all());

        $counts = [
            'all'       => Story::count(),
            'pending'   => Story::pending()->count(),
            'published' => Story::published()->count(),
            'rejected'  => Story::where('status', 'rejected')->count(),
        ];

        return view('community.stories.admin.index', compact('stories', 'counts'));
    }

    // ── Hard delete (admin only) ──────────────────────────────────────────

    public function destroy(Story $story)
    {
        $story->forceDelete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Story permanently deleted.']);
        }

        return back()->with('success', 'Story permanently deleted.');
    }

    public function update(Request $request, Story $story)
    {
        $validated = $request->validate([
            'title'            => 'required|max:255',
            'category'         => 'required',
            'status'           => 'required|in:draft,pending,published,rejected',
            'excerpt'          => 'nullable|max:400',
            'rejection_reason' => 'nullable|max:500',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        // Track whether this is a first-time publish (for broadcast)
        $wasAlreadyPublished = ($story->status === 'published');

        // Track published_at so re-publishing bumps the bell feed position
        if ($validated['status'] === 'published' && $story->status !== 'published') {
            $validated['published_at'] = now();
        } elseif (in_array($validated['status'], ['draft', 'pending', 'rejected'])) {
            $validated['published_at'] = null;
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            if ($story->cover_image) {
                Storage::disk('public')->delete($story->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')
                ->store('stories/covers', 'public');
        } else {
            unset($validated['cover_image']);
        }

        $story->update($validated);

        // Dispatch background job when newly published via the edit modal
        if (!$wasAlreadyPublished && $story->fresh()->status === 'published') {
            BroadcastAnnouncementEmailsJob::dispatch('story', $story->id, $story->created_by ?? null);
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Story updated successfully',
            'cover_image' => $story->fresh()->cover_image
                ? asset('storage/' . $story->fresh()->cover_image)
                : null,
        ]);
    }
}