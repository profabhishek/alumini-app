<?php

namespace App\Http\Controllers\Community\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoryCategoryController extends Controller
{
    private function authorizeUser(): void
    {
        $role = session('alumni_role');
        if (!in_array($role, ['admin', 'super_admin', 'moderator'])) {
            abort(403, 'Unauthorized.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeUser();

        $query = StoryCategory::latest();

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->paginate(12)->appends($request->query());

        $stats = [
            'total'    => StoryCategory::count(),
            'active'   => StoryCategory::where('is_active', true)->count(),
            'inactive' => StoryCategory::where('is_active', false)->count(),
        ];

        return view('community.admin.stories.categories.index', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        $this->authorizeUser();

        $request->validate([
            'name'        => 'required|string|max:100|unique:story_categories,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        StoryCategory::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $message = 'Category "' . $request->name . '" created successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function show(StoryCategory $storyCategory)
    {
        $this->authorizeUser();
        return response()->json($storyCategory);
    }

    public function update(Request $request, StoryCategory $storyCategory)
    {
        $this->authorizeUser();

        $request->validate([
            'name'        => 'required|string|max:100|unique:story_categories,name,' . $storyCategory->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $storyCategory->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $message = 'Category "' . $storyCategory->name . '" updated successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function toggle(Request $request, StoryCategory $storyCategory)
    {
        $this->authorizeUser();

        $storyCategory->update(['is_active' => !$storyCategory->is_active]);

        $state   = $storyCategory->is_active ? 'activated' : 'deactivated';
        $message = 'Category "' . $storyCategory->name . '" has been ' . $state . '.';

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => $message,
                'is_active' => $storyCategory->is_active,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, StoryCategory $storyCategory)
    {
        $this->authorizeUser();

        $name = $storyCategory->name;
        $storyCategory->delete();

        $message = 'Category "' . $name . '" deleted successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
