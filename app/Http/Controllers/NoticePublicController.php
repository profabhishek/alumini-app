<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Http\Request;

class NoticePublicController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::published()->with('category')
            ->orderByDesc('published_at');

        if ($categorySlug = $request->get('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
        }

        $notices = $query->paginate(10)->withQueryString();

        $categories = NoticeCategory::where('status', true)
            ->whereHas('notices', fn($q) => $q->published())
            ->orderBy('name')
            ->get();

        return view('notice.index', compact('notices', 'categories'));
    }

    public function show(Notice $notice)
    {
        if (!($notice->status === 'published' && $notice->published_at?->lte(now()))) {
            abort(404);
        }

        return view('notice.show', compact('notice'));
    }
}