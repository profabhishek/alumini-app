<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorCategory;
use App\Models\MentorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MentorAdminController extends Controller
{
    // ── Mentor Requests ───────────────────────────────────────────────────

    public function requests(Request $request)
    {
        $filter = $request->get('status', 'pending');

        $query = MentorProfile::with(['alumni', 'categories', 'reviewer'])
            ->withCount('acceptedConnections');

        if (in_array($filter, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $filter);
        }

        $profiles = $query->latest('applied_at')->paginate(20)->withQueryString();

        $counts = [
            'pending'  => MentorProfile::pending()->count(),
            'approved' => MentorProfile::approved()->count(),
            'rejected' => MentorProfile::where('status', 'rejected')->count(),
        ];

        return view('admin.mentors.requests', compact('profiles', 'filter', 'counts'));
    }

    public function approve(MentorProfile $mentor)
    {
        $mentor->update([
            'status'      => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => session('alumni_id'),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Mentor application approved for {$mentor->alumni->full_name}.");
    }

    public function reject(Request $request, MentorProfile $mentor)
    {
        $data = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $mentor->update([
            'status'           => 'rejected',
            'reviewed_at'      => now(),
            'reviewed_by'      => session('alumni_id'),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        return back()->with('success', "Application rejected for {$mentor->alumni->full_name}.");
    }

    // ── Mentor Categories ─────────────────────────────────────────────────

    public function categories()
    {
        $categories = MentorCategory::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.mentors.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80|unique:mentor_categories,name',
            'description' => 'nullable|string|max:250',
            'color'       => 'required|string|max:20',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        MentorCategory::create([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'color'       => $data['color'],
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);

        return back()->with('success', "Category '{$data['name']}' created.");
    }

    public function updateCategory(Request $request, MentorCategory $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80|unique:mentor_categories,name,' . $category->id,
            'description' => 'nullable|string|max:250',
            'color'       => 'required|string|max:20',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'color'       => $data['color'],
            'sort_order'  => $data['sort_order'] ?? $category->sort_order,
        ]);

        return back()->with('success', "Category updated.");
    }

    public function destroyCategory(MentorCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    public function toggleCategory(MentorCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        return back()->with('success', 'Category ' . ($category->is_active ? 'activated' : 'deactivated') . '.');
    }
}
