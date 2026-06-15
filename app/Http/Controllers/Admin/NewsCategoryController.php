<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsCategoryController extends Controller
{
    public function index()
    {
        $categories = NewsCategory::withCount('news')
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:news_categories,name',
        ]);

        $category = NewsCategory::create([
            'name'   => $validated['name'],
            'status' => true,
        ]);

        return response()->json(['category' => $category->loadCount('news')], 201);
    }

    public function update(Request $request, NewsCategory $newsCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('news_categories', 'name')->ignore($newsCategory->id)],
        ]);

        $newsCategory->name = $validated['name'];
        $newsCategory->slug = NewsCategory::uniqueSlug($validated['name'], $newsCategory->id);
        $newsCategory->save();

        return response()->json(['category' => $newsCategory->loadCount('news')]);
    }

    public function toggle(NewsCategory $newsCategory)
    {
        $newsCategory->status = !$newsCategory->status;
        $newsCategory->save();

        return response()->json(['category' => $newsCategory->loadCount('news')]);
    }

    public function destroy(NewsCategory $newsCategory)
    {
        $newsCategory->delete();

        return response()->json(['success' => true]);
    }
}