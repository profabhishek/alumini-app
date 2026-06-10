<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\SidebarComposer;
use App\Models\Job;
use App\Models\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
