<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Event;

class DashboardController extends Controller
{
    public function index()
    {
        $latestJobs = Job::where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        $sidebarUpcomingEvents = Event::where('status', 'published')
            ->whereDate('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(5)
            ->get();

        return view('dashboard.home', compact(
            'latestJobs',
            'sidebarUpcomingEvents'
        ));
    }
}