<?php

namespace App\Http\Controllers\Community\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;

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
        ]);

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
        ]);

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
            'title' => 'required|max:255',
            'category' => 'required',
            'status' => 'required',
            'excerpt' => 'nullable|max:400',
            'rejection_reason' => 'nullable|max:500',
        ]);

        $story->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Story updated successfully',
        ]);
    }
}