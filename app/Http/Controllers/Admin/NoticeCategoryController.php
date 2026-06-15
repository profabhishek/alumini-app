<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoticeCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NoticeCategoryController extends Controller
{
    public function index()
    {
        $categories = NoticeCategory::withCount('notices')
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:notice_categories,name',
        ]);

        $category = NoticeCategory::create([
            'name'   => $validated['name'],
            'status' => true,
        ]);

        return response()->json(['category' => $category->loadCount('notices')], 201);
    }

    public function update(Request $request, NoticeCategory $noticeCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('notice_categories', 'name')->ignore($noticeCategory->id)],
        ]);

        $noticeCategory->name = $validated['name'];
        $noticeCategory->slug = NoticeCategory::uniqueSlug($validated['name'], $noticeCategory->id);
        $noticeCategory->save();

        return response()->json(['category' => $noticeCategory->loadCount('notices')]);
    }

    public function toggle(NoticeCategory $noticeCategory)
    {
        $noticeCategory->status = !$noticeCategory->status;
        $noticeCategory->save();

        return response()->json(['category' => $noticeCategory->loadCount('notices')]);
    }

    public function destroy(NoticeCategory $noticeCategory)
    {
        $noticeCategory->delete();

        return response()->json(['success' => true]);
    }
}