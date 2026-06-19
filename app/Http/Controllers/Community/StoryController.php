<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoryController extends Controller
{
    // ── Public: All published stories ─────────────────────────────────────

    public function index(Request $request)
    {
        $stories = Story::published()
            ->with('creator')
            ->when($request->filled('q'), fn($query) =>
                $query->where(function ($q) use ($request) {
                    $q->where('title',    'like', "%{$request->q}%")
                      ->orWhere('excerpt', 'like', "%{$request->q}%")
                      ->orWhere('category','like', "%{$request->q}%");
                })
            )
            ->when($request->filled('category'), fn($q) =>
                $q->where('category', $request->category)
            )
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $categories = Story::published()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('community.stories', compact('stories', 'categories'));
    }

    // ── Public: Single story detail ───────────────────────────────────────

    public function show(Story $story)
    {
        // Only published stories are publicly viewable
        // (owners can also preview their own drafts/pending)
        $isOwner = session('alumni_id') === $story->created_by;

        if ($story->status !== 'published' && !$isOwner) {
            abort(404);
        }

        $related = Story::published()
            ->where('id', '!=', $story->id)
            ->where('category', $story->category)
            ->latest()
            ->take(3)
            ->get();

        return view('community.stories.show', compact('story', 'related'));
    }

    // ── Auth: Create form ─────────────────────────────────────────────────

    public function create()
    {
        return view('community.stories.create');
    }

    // ── Auth: Store new story ─────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'body'        => 'required|string|min:100',
            'excerpt'     => 'nullable|string|max:400',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $coverPath = null;

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')
                ->store('stories', 'public');
        }

        Story::create([
            'created_by'   => session('alumni_id'),
            'creator_role' => session('alumni_role'),
            'title'        => $validated['title'],
            'slug'         => Str::slug($validated['title']) . '-' . time(),
            'category'     => $validated['category'],
            'body'         => $validated['body'],
            'excerpt'      => $validated['excerpt']
                                ?? Story::makeExcerpt($validated['body']),
            'cover_image'  => $coverPath,
            'status'       => 'pending',  // always goes to moderation queue
        ]);

        return redirect()
            ->route('stories.my')
            ->with('success', 'Story submitted! It will be visible once approved by an admin.');
    }

    // ── Auth: My stories list ─────────────────────────────────────────────

    public function myStories(Request $request)
    {
        $myStoriesLastSeen = session('my_stories_last_seen')
            ? \Carbon\Carbon::parse(session('my_stories_last_seen'))
            : now();

        // Update last seen AFTER reading
        $query = \App\Models\Story::where('created_by', session('alumni_id'))->latest();

        if ($request->filled('q')) {
            $search = trim($request->get('q'));
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stories = $query->paginate(12)->appends($request->query());

        // Stories with status changes since last visit
        $storyIds = collect($stories->items())->pluck('id')->toArray();
        $updatedStoryIds = empty($storyIds) ? [] : \App\Models\Story::whereIn('id', $storyIds)
            ->where('status', '!=', 'pending')
            ->where('updated_at', '>', $myStoriesLastSeen)
            ->pluck('id')
            ->toArray();

        // Update last seen after counting (session + DB so it survives logout/login)
        $now = now()->toDateTimeString();
        session(['my_stories_last_seen' => $now]);
        \App\Models\AlumniUser::where('id', session('alumni_id'))->update(['my_stories_last_seen' => $now]);

        $stats = [
            'total'     => \App\Models\Story::where('created_by', session('alumni_id'))->count(),
            'pending'   => \App\Models\Story::where('created_by', session('alumni_id'))->where('status', 'pending')->count(),
            'published' => \App\Models\Story::where('created_by', session('alumni_id'))->where('status', 'published')->count(),
            'rejected'  => \App\Models\Story::where('created_by', session('alumni_id'))->where('status', 'rejected')->count(),
        ];

        return view('community.stories.my-stories', compact('stories', 'stats', 'updatedStoryIds'));
    }

    // ── Auth: Edit form (owner only) ──────────────────────────────────────

    public function edit(Story $story)
    {
        $this->ensureOwnership($story);
        return response()->json($story);
    }

    // ── Auth: Update (owner only) ─────────────────────────────────────────

    public function update(Request $request, Story $story)
    {
        $this->ensureOwnership($story);

        // Published stories cannot be edited (re-submit flow would be needed)
        if ($story->status === 'published') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Published stories cannot be edited.'], 422);
            }
            return back()->with('error', 'Published stories cannot be edited.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'body'        => 'required|string|min:100',
            'excerpt'     => 'nullable|string|max:400',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $coverPath = $story->cover_image;

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')
                ->store('stories', 'public');
        }

        $story->update([
            'title'       => $validated['title'],
            'slug'        => Str::slug($validated['title']) . '-' . time(),
            'category'    => $validated['category'],
            'body'        => $validated['body'],
            'excerpt'     => $validated['excerpt']
                                ?? Story::makeExcerpt($validated['body']),
            'cover_image' => $coverPath,
            'status'      => 'pending',  // re-submit for approval on every edit
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Story updated and re-submitted for approval.']);
        }

        return redirect()->route('stories.my')->with('success', 'Story updated and re-submitted for approval.');
    }

    // ── Auth: Delete (owner only) ─────────────────────────────────────────

    public function destroy(Story $story)
    {
        $this->ensureOwnership($story);
        $story->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Story deleted successfully.']);
        }

        return redirect()->route('stories.my')->with('success', 'Story deleted successfully.');
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function ensureOwnership(Story $story): void
    {
        $userId = session('alumni_id');
        $role   = session('alumni_role');

        if ($story->created_by != $userId && !in_array($role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized action.');
        }
    }
}