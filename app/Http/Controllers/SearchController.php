<?php

namespace App\Http\Controllers;

use App\Models\AlumniUser;
use App\Models\Event;
use App\Models\Job;
use App\Models\News;
use App\Models\Notice;
use App\Models\Story;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global header search — returns grouped JSON results for the
     * live-search dropdown in layouts/community.blade.php.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // ── Alumni ───────────────────────────────────────────────
        $alumni = AlumniUser::where('is_approved', 1)
            ->where(function ($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                       ->orWhere('department', 'like', "%{$q}%");
            })
            ->limit(3)
            ->get(['id', 'full_name', 'photo', 'department']);

        foreach ($alumni as $alum) {
            $results[] = [
                'type'     => 'alumni',
                'title'    => $alum->full_name,
                'sub'      => $alum->department,
                'photo'    => $alum->photo ? asset('storage/' . $alum->photo) : null,
                'initials' => $alum->initials,
                'url'      => route('alumni.profile', $alum->id),
            ];
        }

        // ── Events ───────────────────────────────────────────────
        $events = Event::where('status', 'published')
            ->where('title', 'like', "%{$q}%")
            ->latest()
            ->limit(3)
            ->get(['id', 'title', 'slug', 'start_date']);

        foreach ($events as $event) {
            $results[] = [
                'type'  => 'event',
                'title' => $event->title,
                'sub'   => optional($event->start_date)->format('d M Y'),
                'url'   => route('events.show', $event->slug ?? $event->id),
            ];
        }

        // ── Stories ──────────────────────────────────────────────
        $stories = Story::where('status', 'published')
            ->where('title', 'like', "%{$q}%")
            ->latest()
            ->limit(3)
            ->get(['id', 'title', 'slug']);

        foreach ($stories as $story) {
            $results[] = [
                'type'  => 'story',
                'title' => $story->title,
                'sub'   => 'Story',
                'url'   => route('stories.show', $story->slug ?? $story->id),
            ];
        }

        // ── Jobs ─────────────────────────────────────────────────
        $jobs = Job::where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                       ->orWhere('company_name', 'like', "%{$q}%");
            })
            ->latest()
            ->limit(3)
            ->get(['id', 'title', 'slug', 'company_name']);

        foreach ($jobs as $job) {
            $results[] = [
                'type'  => 'job',
                'title' => $job->title,
                'sub'   => $job->company_name,
                'url'   => route('jobs.show', $job->slug ?? $job->id),
            ];
        }

        // ── Notices ──────────────────────────────────────────────
        $notices = Notice::published()
            ->where('title', 'like', "%{$q}%")
            ->latest('published_at')
            ->limit(2)
            ->get(['id', 'title', 'slug']);

        foreach ($notices as $notice) {
            $results[] = [
                'type'  => 'notice',
                'title' => $notice->title,
                'sub'   => 'Notice',
                'url'   => route('notice.show', $notice->slug ?? $notice->id),
            ];
        }

        // ── News ─────────────────────────────────────────────────
        $news = News::published()
            ->where('title', 'like', "%{$q}%")
            ->latest('published_at')
            ->limit(2)
            ->get(['id', 'title', 'slug']);

        foreach ($news as $item) {
            $results[] = [
                'type'  => 'news',
                'title' => $item->title,
                'sub'   => 'News',
                'url'   => route('news.show', $item->slug ?? $item->id),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
