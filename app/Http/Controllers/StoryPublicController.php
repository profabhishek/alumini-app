<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;

class StoryPublicController extends Controller
{
    /**
     * Public "All Stories" listing page.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        // Featured = most recent published story (only on an unfiltered, first-page view)
        $featured = null;
        if ($search === '' && $request->query('page', 1) == 1) {
            $featured = Story::published()->with('creator')->latest()->first();
        }

        $query = Story::published()->with('creator')->latest();

        if ($featured) {
            $query->where('id', '!=', $featured->id);
        }

        if ($search !== '') {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $stories = $query->paginate(9)->withQueryString();

        return view('community.stories', [
            'featured' => $featured,
            'stories'  => $stories,
            'search'   => $search,
        ]);
    }

    /**
     * Single story detail page.
     */
    public function show(Story $story)
    {
        abort_unless($story->status === 'published', 404);

        $story->load('creator');

        $related = Story::published()
            ->where('id', '!=', $story->id)
            ->latest()
            ->take(3)
            ->get();

        return view('community.stories.show', [
            'story'   => $story,
            'related' => $related,
        ]);
    }
}