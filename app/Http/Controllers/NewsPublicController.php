<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;

class NewsPublicController extends Controller
{
    public function index(Request $request)
    {
        $query = News::published()->with('category', 'author')
            ->orderByDesc('published_at');

        if ($categorySlug = $request->get('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
        }

        if ($request->filled('q')) {
            $search = '%' . trim($request->get('q')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title',   'like', $search)
                  ->orWhere('excerpt','like', $search)
                  ->orWhere('body',   'like', $search);
            });
        }

        $newsItems = $query->paginate(9)->withQueryString();

        $categories = NewsCategory::where('status', true)
            ->whereHas('news', fn($q) => $q->published())
            ->orderBy('name')
            ->get();

        return view('news.index', compact('newsItems', 'categories'));
    }

    public function show(News $news)
    {
        if (!$news->is_published) {
            abort(404);
        }

        $related = News::published()
            ->where('id', '!=', $news->id)
            ->when($news->news_category_id, fn($q) => $q->where('news_category_id', $news->news_category_id))
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('news.show', compact('news', 'related'));
    }
}