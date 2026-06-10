<?php

namespace App\Http\Controllers\Community\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventCategoryController extends Controller
{
    /**
     * Allow:
     * - admin
     * - super_admin
     * - moderator with manage_event_categories permission
     */
    private function authorizeUser(): void
    {
        $role  = session('alumni_role');
        $perms = session('alumni_permissions', []);

        $allowed =
            in_array($role, ['admin', 'super_admin']) ||
            ($role === 'moderator' && !empty($perms['manage_event_categories']));

        if (!$allowed) {
            abort(403, 'Unauthorized.');
        }
    }

    // ── List all categories ───────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorizeUser();

        $query = EventCategory::latest();

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
            'total'    => EventCategory::count(),
            'active'   => EventCategory::where('is_active', true)->count(),
            'inactive' => EventCategory::where('is_active', false)->count(),
        ];

        return view('community.admin.events.categories.index', compact('categories', 'stats'));
    }

    // ── Store new category ────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->authorizeUser();

        $request->validate([
            'name'        => 'required|string|max:100|unique:event_categories,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        EventCategory::create([
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

    // ── Show single category (for edit modal via AJAX) ────────────────────

    public function show(EventCategory $eventCategory)
    {
        $this->authorizeUser();

        return response()->json($eventCategory);
    }

    // ── Update category ───────────────────────────────────────────────────

    public function update(Request $request, EventCategory $eventCategory)
    {
        $this->authorizeUser();

        $request->validate([
            'name'        => 'required|string|max:100|unique:event_categories,name,' . $eventCategory->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $eventCategory->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $message = 'Category "' . $eventCategory->name . '" updated successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // ── Toggle active/inactive ────────────────────────────────────────────

    public function toggle(Request $request, EventCategory $eventCategory)
    {
        $this->authorizeUser();

        $eventCategory->update(['is_active' => !$eventCategory->is_active]);

        $state   = $eventCategory->is_active ? 'activated' : 'deactivated';
        $message = 'Category "' . $eventCategory->name . '" has been ' . $state . '.';

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => $message,
                'is_active' => $eventCategory->is_active,
            ]);
        }

        return back()->with('success', $message);
    }

    // ── Delete category ───────────────────────────────────────────────────

    public function destroy(Request $request, EventCategory $eventCategory)
    {
        $this->authorizeUser();

        $name = $eventCategory->name;

        $eventCategory->delete();

        $message = 'Category "' . $name . '" deleted successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}