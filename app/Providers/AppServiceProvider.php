<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Models\Job;
use App\Models\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.community', function ($view) {

            $latestJobs = Job::where('status', 'published')
                ->latest()
                ->take(5)
                ->get();

            $sidebarUpcomingEvents = Event::where('status', 'published')
                ->whereDate('start_date', '>=', now())
                ->orderBy('start_date')
                ->take(5)
                ->get();

            $view->with([
                'latestJobs' => $latestJobs,
                'sidebarUpcomingEvents' => $sidebarUpcomingEvents,
            ]);
        });
    }
}