<?php

namespace App\Http\View\Composers;

use App\Models\Event;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $view->with('sidebarUpcomingEvents', 
            Event::query()
                ->where('status', 'published')
                ->whereDate('start_date', '>', now()->toDateString())
                ->orderBy('start_date')
                ->limit(3)
                ->get(['id', 'title', 'location', 'start_date', 'slug'])
        );
    }
}