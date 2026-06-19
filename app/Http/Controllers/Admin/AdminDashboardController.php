<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Story;
use App\Models\Notice;
use App\Models\News;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupMember;
use App\Models\NewsletterSubscriber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    private function dateRange(string $range, ?string $from = null, ?string $to = null): array
    {
        return match($range) {
            '7d'     => [now()->subDays(7)->startOfDay(),   now()->endOfDay()],
            '4w'     => [now()->subWeeks(4)->startOfDay(),  now()->endOfDay()],
            '1y'     => [now()->subYear()->startOfDay(),    now()->endOfDay()],
            'custom' => [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ],
            default  => [now()->subDays(7)->startOfDay(),   now()->endOfDay()],
        };
    }

    private function groupFormat(Carbon $start, Carbon $end): string
    {
        $days = $start->diffInDays($end);
        if ($days <= 14)  return 'day';
        if ($days <= 90)  return 'week';
        return 'month';
    }

    public function index(): \Illuminate\View\View
    {
        return view('admin.dashboard.index');
    }

    public function stats(Request $request): JsonResponse
    {
        $range  = $request->input('range', '7d');
        $from   = $request->input('from');
        $to     = $request->input('to');
        [$start, $end] = $this->dateRange($range, $from, $to);

        // ── Totals (all-time) ──────────────────────────────────────────
        $totals = [
            'alumni'       => AlumniUser::where('is_approved', true)->whereIn('role', ['alumni','moderator'])->count(),
            'pending'      => AlumniUser::where('is_approved', false)->whereIn('role', ['alumni','moderator'])->count(),
            'posts'        => Post::where('status', 'active')->count(),
            'events'       => Event::where('status', 'published')->count(),
            'jobs'         => Job::where('status', 'published')->count(),
            'stories'      => Story::where('status', 'published')->count(),
            'notices'      => Notice::count(),
            'groups'       => CommunityGroup::count(),
            'newsletter'   => NewsletterSubscriber::where('status', 'active')->count(),
        ];

        // ── Period stats ───────────────────────────────────────────────
        $period = [
            'new_members'   => AlumniUser::where('is_approved', true)->whereBetween('created_at', [$start, $end])->count(),
            'new_posts'     => Post::where('status', 'active')->whereBetween('created_at', [$start, $end])->count(),
            'new_events'    => Event::whereBetween('created_at', [$start, $end])->count(),
            'new_jobs'      => Job::where('status', 'published')->whereBetween('created_at', [$start, $end])->count(),
            'new_stories'   => Story::whereBetween('created_at', [$start, $end])->count(),
            'new_comments'  => PostComment::whereBetween('created_at', [$start, $end])->count(),
            'new_likes'     => PostLike::whereBetween('created_at', [$start, $end])->count(),
            'event_regs'    => EventRegistration::whereBetween('created_at', [$start, $end])->count(),
            'job_apps'      => JobApplication::whereBetween('created_at', [$start, $end])->count(),
            'new_newsletter'=> NewsletterSubscriber::where('status','active')->whereBetween('subscribed_at', [$start, $end])->count(),
        ];

        // ── Activity chart (posts + members + events per bucket) ───────
        $fmt   = $this->groupFormat($start, $end);
        $sqlFmt = match($fmt) {
            'day'   => '%Y-%m-%d',
            'week'  => '%Y-%u',
            'month' => '%Y-%m',
        };

        $chartData = $this->buildChart($start, $end, $sqlFmt, $fmt);

        // ── Top 5 most active alumni (posts + comments) ────────────────
        $topAlumni = DB::table('alumni_users as u')
            ->select('u.id', 'u.full_name', 'u.photo',
                DB::raw('(SELECT COUNT(*) FROM posts p WHERE p.alumni_id = u.id AND p.created_at BETWEEN ? AND ?) as post_count'),
                DB::raw('(SELECT COUNT(*) FROM post_comments pc WHERE pc.alumni_id = u.id AND pc.created_at BETWEEN ? AND ?) as comment_count')
            )
            ->where('u.is_approved', true)
            ->addBinding([$start, $end, $start, $end], 'select')
            ->orderByRaw('post_count + comment_count DESC')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'name'     => $r->full_name,
                'photo'    => $r->photo ? asset('storage/' . $r->photo) : null,
                'activity' => $r->post_count + $r->comment_count,
                'posts'    => $r->post_count,
                'comments' => $r->comment_count,
            ]);

        return response()->json([
            'totals'     => $totals,
            'period'     => $period,
            'chart'      => $chartData,
            'top_alumni' => $topAlumni,
            'range'      => ['start' => $start->toDateString(), 'end' => $end->toDateString(), 'fmt' => $fmt],
        ]);
    }

    private function buildChart(Carbon $start, Carbon $end, string $sqlFmt, string $fmt): array
    {
        $tables = [
            'posts'   => ['table' => 'posts',        'date_col' => 'created_at', 'where' => "status = 'active'"],
            'members' => ['table' => 'alumni_users',  'date_col' => 'created_at', 'where' => "is_approved = 1"],
            'events'  => ['table' => 'events',        'date_col' => 'created_at', 'where' => null],
            'jobs'    => ['table' => 'jobs',           'date_col' => 'created_at', 'where' => "status = 'published'"],
        ];

        $buckets = [];

        foreach ($tables as $key => $cfg) {
            $q = DB::table($cfg['table'])
                ->selectRaw("DATE_FORMAT({$cfg['date_col']}, ?) as bucket, COUNT(*) as cnt", [$sqlFmt])
                ->whereBetween($cfg['date_col'], [$start, $end]);
            if ($cfg['where']) $q->whereRaw($cfg['where']);
            $rows = $q->groupBy('bucket')->orderBy('bucket')->get();

            foreach ($rows as $row) {
                $buckets[$row->bucket][$key] = (int) $row->cnt;
            }
        }

        // Build ordered labels
        $labels = [];
        $cur = $start->copy();
        while ($cur->lte($end)) {
            $key = match($fmt) {
                'day'   => $cur->format('Y-m-d'),
                'week'  => $cur->format('Y-W'),
                'month' => $cur->format('Y-m'),
            };
            if (!isset($labels[$key])) $labels[$key] = match($fmt) {
                'day'   => $cur->format('d M'),
                'week'  => 'W'.$cur->weekOfYear.' '.$cur->format('M'),
                'month' => $cur->format('M Y'),
            };
            $cur->addDay();
        }

        $series = ['posts' => [], 'members' => [], 'events' => [], 'jobs' => []];
        foreach (array_keys($labels) as $bucket) {
            foreach ($series as $key => $_) {
                $series[$key][] = $buckets[$bucket][$key] ?? 0;
            }
        }

        return [
            'labels'  => array_values($labels),
            'series'  => $series,
        ];
    }
}
