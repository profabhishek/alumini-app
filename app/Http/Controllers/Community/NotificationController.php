<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\AlumniUser;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark the "recent activity" notifications as read for the current
     * alumni user. Called when the notification dropdown is opened.
     */
    public function markRead(Request $request)
    {
        AlumniUser::where('id', session('alumni_id'))
            ->update(['notifications_read_at' => now()]);

        return response()->json(['success' => true]);
    }
}