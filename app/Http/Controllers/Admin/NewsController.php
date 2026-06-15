<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::with('category')->latest('created_at');

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('news_category_id', $categoryId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $newsItems = $query->paginate(10)->withQueryString();
        $categories = NewsCategory::orderBy('name')->get();

        return view('admin.news.index', compact('newsItems', 'categories'));
    }

    public function create()
    {
        $categories = NewsCategory::where('status', true)->orderBy('name')->get();
        $news = new News(['status' => 'draft']);

        return view('admin.news.form', [
            'news'       => $news,
            'categories' => $categories,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateNews($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $validated['author_id'] = session('alumni_id');
        $validated['published_at'] = $this->resolvePublishedAt($request, null);

        News::create($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'News article created successfully.');
    }

    public function edit(News $news)
    {
        $categories = NewsCategory::orderBy('name')->get();

        return view('admin.news.form', [
            'news'       => $news,
            'categories' => $categories,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, News $news)
    {
        $validated = $this->validateNews($request, $news->id);

        if ($request->hasFile('image')) {
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }
            $validated['image'] = $request->file('image')->store('news', 'public');
        } elseif ($request->input('remove_image') === '1' && $news->image) {
            Storage::disk('public')->delete($news->image);
            $validated['image'] = null;
        }

        if ($validated['title'] !== $news->title) {
            $validated['slug'] = News::uniqueSlug($validated['title'], $news->id);
        }

        $validated['published_at'] = $this->resolvePublishedAt($request, $news);

        $news->update($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'News article updated successfully.');
    }

    public function destroy(News $news)
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }

        $title = $news->title;
        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', "\"{$title}\" has been deleted.");
    }

    public function toggleStatus(News $news)
    {
        $news->status = $news->status === 'published' ? 'draft' : 'published';

        if ($news->status === 'published' && !$news->published_at) {
            $news->published_at = now();
        }

        $news->save();

        return back()->with('success', "\"{$news->title}\" is now " . $news->status . '.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function validateNews(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'            => 'required|string|max:255',
            'news_category_id' => 'nullable|exists:news_categories,id',
            'excerpt'          => 'nullable|string|max:500',
            'body'             => 'required|string',
            'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'status'           => 'required|in:draft,published',
            'published_at'     => 'nullable|date',
        ]);
    }

    private function resolvePublishedAt(Request $request, ?News $existing): ?string
    {
        if ($request->status !== 'published') {
            return $existing?->published_at;
        }

        if ($request->filled('published_at')) {
            return \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->published_at, 'Asia/Kolkata')
                ->setTimezone(config('app.timezone'));
        }

        return $existing?->published_at ?: now();
    }
}