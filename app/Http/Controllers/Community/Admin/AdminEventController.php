<?php

namespace App\Http\Controllers\Community\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastAnnouncementEmailsJob;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\AlumniUser;
use App\Services\NotificationHelper;
use App\Services\EmailBroadcastService;

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
            'status'       => 'published',
            'published_at' => now(),
        ]);
        Cache::forget('pending_events_count');

        // Notify the event creator
        if ($event->created_by && $event->created_by !== session('alumni_id')) {
            NotificationHelper::fire(
                recipientId: $event->created_by,
                actorId:     session('alumni_id'),
                type:        'event_approved',
                preview:     $event->title,
            );
        }

        // Dispatch background job — returns instantly; worker handles bulk email
        BroadcastAnnouncementEmailsJob::dispatch('event', $event->id, $event->created_by ?? null);

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
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
            'published_at'     => null,
        ]);
        Cache::forget('pending_events_count');

        // Notify the event creator
        if ($event->created_by && $event->created_by !== session('alumni_id')) {
            NotificationHelper::fire(
                recipientId: $event->created_by,
                actorId:     session('alumni_id'),
                type:        'event_rejected',
                preview:     $event->title,
            );
        }

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
            'title'        => 'required|string|max:255',
            'status'       => 'required|in:pending,active,published,completed,cancelled',
            'start_date'   => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'location'     => 'nullable|string|max:255',
            'event_mode'   => 'required|in:In-Person,Online,Hybrid',
            'total_seats'  => 'nullable|integer|min:1',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        // Track whether this update is a first-time publish (for broadcast)
        $wasAlreadyPublished = ($event->status === 'published');

        // Track published_at so re-publishing bumps feed position
        if ($validated['status'] === 'published' && $event->status !== 'published') {
            $validated['published_at'] = now();
        } elseif (in_array($validated['status'], ['cancelled', 'pending', 'rejected'])) {
            $validated['published_at'] = null;
        }

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            if ($event->banner_image) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')
                ->store('events/banners', 'public');
        } else {
            unset($validated['banner_image']);
        }

        $event->update($validated);

        // Dispatch background job when newly published via the edit form
        if (!$wasAlreadyPublished && $event->fresh()->status === 'published') {
            BroadcastAnnouncementEmailsJob::dispatch('event', $event->id, $event->created_by ?? null);
        }

        return response()->json([
            'success'      => true,
            'message'      => 'Event updated successfully.',
            'banner_image' => $event->fresh()->banner_image
                ? asset('storage/' . $event->fresh()->banner_image)
                : null,
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