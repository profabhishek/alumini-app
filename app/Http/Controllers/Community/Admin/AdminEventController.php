<?php

namespace App\Http\Controllers\Community\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\AlumniUser;


class AdminEventController extends Controller
{
    /**
     * Allow:
     * - admin
     * - super_admin
     * - moderator with approve_events permission
     */
    private function authorizeUser(): void
    {
        $role  = session('alumni_role');
        $perms = session('alumni_permissions', []);

        $allowed =
            in_array($role, ['admin', 'super_admin']) ||
            ($role === 'moderator' && !empty($perms['approve_events']));

        if (!$allowed) {
            abort(403, 'Unauthorized.');
        }
    }

    /**
     * Pending Events List
     */
    public function pending(Request $request)
    {
        $this->authorizeUser();

        $query = Event::with('creator')
            ->where('status', 'pending')
            ->latest();

        if ($request->filled('q')) {

            $search = trim($request->q);

            $query->where(function ($q) use ($search) {

                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")

                    ->orWhereHas('creator', function ($creator) use ($search) {

                        $creator->where('name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $events = $query
            ->paginate(12)
            ->appends($request->query());

        $stats = [
            'pending'   => Event::where('status', 'pending')->count(),
            'approved'  => Event::where('status', 'published')->count(),
            'rejected'  => Event::where('status', 'rejected')->count(),
            'total'     => Event::count(),
        ];

        return view(
            'community.admin.events.pending',
            compact('events', 'stats')
        );
    }

    /**
     * Approve Event
     */
    public function approve(Request $request, Event $event)
    {
        $this->authorizeUser();

        if ($event->status !== 'pending') {

            $message = 'Only pending events can be approved.';

            if ($request->expectsJson()) {

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        $event->update([
            'status' => 'published',
        ]);

        $message = '"' . $event->title . '" has been approved and published.';

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Reject Event
     */
    public function reject(Request $request, Event $event)
    {
        $this->authorizeUser();

        if ($event->status !== 'pending') {

            $message = 'Only pending events can be rejected.';

            if ($request->expectsJson()) {

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $event->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        $message = '"' . $event->title . '" has been rejected.';

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function allEvents(Request $request)
    {
        $query = Event::with('registrations')
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->filled('filter')) {
            $today = now()->toDateString();
            match($request->filter) {
                'upcoming' => $query->where('start_date', '>', $today),
                'ongoing'  => $query->where('start_date', '<=', $today)
                                    ->where(function ($q) use ($today) {
                                        $q->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $today);
                                    }),
                'past'     => $query->where(function ($q) use ($today) {
                                        $q->where('end_date', '<', $today)
                                        ->orWhere(function ($q2) use ($today) {
                                            $q2->whereNull('end_date')
                                                ->where('start_date', '<', $today);
                                        });
                                    }),
                default    => null,
            };
        }

        $events = $query->paginate(15)->withQueryString();

        return view('community.events.admin.all-events', compact('events'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'status'      => 'required|in:pending,active,published,completed,cancelled',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'location'    => 'nullable|string|max:255',
            'event_mode'  => 'required|in:In-Person,Online,Hybrid',
            'total_seats' => 'nullable|integer|min:1',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully.',
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.',
        ]);
    }
    
}