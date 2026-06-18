<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AlumniUser;

class NotificationController extends Controller
{
    public function markRead(Request $request)
    {
        $user = AlumniUser::find(session('alumni_id'));
        if ($user) {
            $user->notifications_read_at = now();
            $user->save();
        }
        return response()->json(['ok' => true]);
    }

    public function personal()
    {
        $alumniId = session('alumni_id');

        $notifications = \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->with('actor')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'       => $n->id,
                'type'     => $n->type,
                'is_read'  => $n->is_read,
                'preview'  => $n->preview,
                'post_url' => url('/posts/' . $n->post_id),
                'actor'    => $n->actor?->full_name ?? 'Someone',
                'avatar'   => $n->actor?->photo ? asset('storage/' . $n->actor->photo) : null,
                'initials' => strtoupper(substr($n->actor?->full_name ?? 'A', 0, 1)),
                'time'     => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    public function markPersonalRead()
    {
        \App\Models\AlumniNotification::where('recipient_id', session('alumni_id'))
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function markOneRead(Request $request, $id)
    {
        $alumniId = session('alumni_id');
        if (!$alumniId) {
            return response()->json(['ok' => false], 401);
        }

        $notification = \App\Models\AlumniNotification::where('id', $id)
            ->where('recipient_id', $alumniId)
            ->first();

        if (!$notification) {
            return response()->json(['ok' => false], 404);
        }

        $notification->is_read = true;
        $notification->save();

        $unreadCount = \App\Models\AlumniNotification::where('recipient_id', $alumniId)
            ->where('is_read', false)
            ->count();

        return response()->json(['ok' => true, 'unread_count' => $unreadCount]);
    }
}