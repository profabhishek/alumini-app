<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\EventRegistrationConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    // ── Public events listing ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $today = now()->toDateString();

        $events = Event::query()
            ->where('status', 'published')
            ->when($request->filled('search'), fn($q) =>
                $q->where(function ($q) use ($request) {
                    $q->where('title',    'like', "%{$request->search}%")
                    ->orWhere('location', 'like', "%{$request->search}%")
                    ->orWhere('category', 'like', "%{$request->search}%");
                })
            )
            ->when($request->filter === 'upcoming', fn($q) =>
                $q->whereDate('start_date', '>', $today)
            )
            ->when($request->filter === 'ongoing', fn($q) =>
                $q->whereDate('start_date', '<=', $today)
                ->whereDate('end_date',   '>=', $today)
            )
            ->when($request->filter === 'past', fn($q) =>
                $q->whereDate('end_date', '<', $today)
            )
            ->orderBy('start_date')
            ->paginate(9)
            ->withQueryString();

        $registeredEventIds = [];

        if (session('alumni_id')) {
            $registeredEventIds = \App\Models\EventRegistration::where('user_id', session('alumni_id'))
                ->pluck('event_id')
                ->toArray();
        }

        return view('events.index', compact('events', 'registeredEventIds'));
    }

    public function show(Event $event)
    {
        $alumniId = session('alumni_id');
        $role     = session('alumni_role');
    
        $isOwner = $alumniId && $alumniId == $event->created_by;
        $isAdmin = in_array($role, ['admin', 'super_admin']);
    
        // Only published events are publicly viewable.
        // Owners/admins can preview pending/draft/rejected events.
        if ($event->status !== 'published' && !$isOwner && !$isAdmin) {
            abort(404);
        }
    
        $alreadyRegistered = false;
    
        if ($alumniId) {
            $alreadyRegistered = \App\Models\EventRegistration::where('event_id', $event->id)
                ->where('user_id', $alumniId)
                ->exists();
        }
    
        $relatedEvents = Event::where('status', 'published')
            ->where('id', '!=', $event->id)
            ->whereDate('start_date', '>=', now()->toDateString())
            ->orderBy('start_date')
            ->take(3)
            ->get();
    
        return view('events.show', compact('event', 'alreadyRegistered', 'relatedEvents'));
    }

    // ── Create form ───────────────────────────────────────────────────────
    public function create()
    {
        return view('community.events.create');
    }

    // ── Store new event ───────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'category'              => 'required|string|max:255',
            'event_mode'            => 'required|string|max:255',
            'location'              => 'nullable|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'nullable|date|after_or_equal:start_date',
            'start_time'            => 'required',
            'end_time'              => 'nullable',
            'description'           => 'required|string',
            'event_type'            => 'required|in:Free,Paid',
            'ticket_price'          => 'nullable|numeric|min:0',
            'total_seats'           => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date',
            'registration_required' => 'required|boolean',
            'banner_image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $bannerPath = null;

        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('events', 'public');
        }

        Event::create([
            'created_by'            => session('alumni_id'),
            'creator_role'          => session('alumni_role'),
            'title'                 => $validated['title'],
            'slug'                  => Str::slug($validated['title']) . '-' . time(),
            'category'              => $validated['category'],
            'event_mode'            => $validated['event_mode'],
            'location'              => $validated['location'] ?? null,
            'start_date'            => $validated['start_date'],
            'end_date'              => $validated['end_date'] ?? null,
            'start_time'            => $validated['start_time'],
            'end_time'              => $validated['end_time'] ?? null,
            'description'           => $validated['description'],
            'event_type'            => $validated['event_type'],
            'ticket_price'          => $validated['ticket_price'] ?? 0,
            'total_seats'           => $validated['total_seats'] ?? null,
            'registration_deadline' => $validated['registration_deadline'] ?? null,
            'registration_required' => $validated['registration_required'],
            'banner_image'          => $bannerPath,
            'status'                => 'pending',
        ]);

        return redirect()->route('events.my')->with('success', 'Event created successfully.');
    }

    // ── My Events (community dashboard) ──────────────────────────────────
    public function myEvents(Request $request)
    {
        $query = Event::with('creator')
            ->where('created_by', session('alumni_id'))
            ->latest();

        if ($request->filled('q')) {
            $search = trim($request->get('q'));
            $query->where(function ($q) use ($search) {
                $q->where('title',    'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $allowedStatuses = ['pending','published','draft','cancelled','completed','rejected'];

        if ($request->filled('status') && in_array($request->status, $allowedStatuses)) {
            $query->where('status', $request->status);
        }

        $events = $query->paginate(10)->appends($request->query());

        $stats = [
            'total'     => Event::where('created_by', session('alumni_id'))->count(),
            'pending'   => Event::where('created_by', session('alumni_id'))->where('status', 'pending')->count(),
            'published' => Event::where('created_by', session('alumni_id'))->where('status', 'published')->count(),
            'draft'     => Event::where('created_by', session('alumni_id'))->where('status', 'draft')->count(),
            'upcoming'  => Event::where('created_by', session('alumni_id'))->whereDate('start_date', '>=', now()->toDateString())->count(),
        ];

        return view('community.events.my-events', compact('events', 'stats'));
    }

    // ── Ownership guard ───────────────────────────────────────────────────
    private function ensureOwnership(Event $event)
    {
        $userId = session('alumni_id');
        $role   = session('alumni_role');

        if (
            $event->created_by != $userId &&
            $role !== 'admin'
        ) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function destroy(Event $event)
    {
        $this->ensureOwnership($event);
        $event->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event deleted successfully.']);
        }

        return redirect()->route('events.my')->with('success', 'Event deleted successfully.');
    }

    public function update(Request $request, Event $event)
    {
        $this->ensureOwnership($event);

        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'category'              => 'required|string|max:255',
            'event_mode'            => 'required|string|max:255',
            'location'              => 'nullable|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'nullable|date|after_or_equal:start_date',
            'start_time'            => 'required',
            'end_time'              => 'nullable',
            'description'           => 'required|string',
            'event_type'            => 'required|in:Free,Paid',
            'ticket_price'          => 'nullable|numeric|min:0',
            'total_seats'           => 'nullable|integer|min:1',
            'registration_deadline' => 'nullable|date',
            'registration_required' => 'required|boolean',
            'banner_image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if (
            $event->status === 'published' &&
            ($event->registered_count ?? 0) > 0
        ) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Published events with registrations cannot be edited.'], 422);
            }
            return back()->with('error', 'Published events with registrations cannot be edited.');
        }

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('events', 'public');
        }

        $event->update([
            'title'                 => $validated['title'],
            'category'              => $validated['category'],
            'event_mode'            => $validated['event_mode'],
            'location'              => $validated['location'] ?? null,
            'start_date'            => $validated['start_date'],
            'end_date'              => $validated['end_date'] ?? null,
            'start_time'            => $validated['start_time'],
            'end_time'              => $validated['end_time'] ?? null,
            'description'           => $validated['description'],
            'event_type'            => $validated['event_type'],
            'ticket_price'          => $validated['ticket_price'] ?? 0,
            'total_seats'           => $validated['total_seats'] ?? null,
            'registration_deadline' => $validated['registration_deadline'] ?? null,
            'registration_required' => $validated['registration_required'],
            'banner_image'          => $validated['banner_image'] ?? $event->banner_image,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event updated successfully.']);
        }

        return redirect()->route('events.my')->with('success', 'Event updated successfully.');
    }

    public function edit(Event $event)
    {
        $this->ensureOwnership($event);
        // Return JSON for the AJAX modal
        return response()->json($event);
    }

    public function register(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $already = \App\Models\EventRegistration::where('event_id', $id)
            ->where('user_id', session('alumni_id'))
            ->exists();

        if ($already) {
            return response()->json([
                'success' => false,
                'message' => 'You have already registered for this event.'
            ], 409);
        }

        $validated = $request->validate([
            'full_name'    => 'required|string|max:100',
            'email'        => 'required|email|max:100',
            'phone'        => 'nullable|string|max:20',
            'country'      => 'nullable|string|max:100',
            'batch_year'   => 'nullable|string|max:20',
            'no_of_people' => 'required|integer|min:1|max:20',
            'message'      => 'nullable|string|max:500',
        ]);

        if ($event->total_seats) {
            $registered = $event->registrations()->sum('no_of_people');
            $seatsLeft  = $event->total_seats - $registered;

            if ($validated['no_of_people'] > $seatsLeft) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$seatsLeft} seat(s) left. You requested {$validated['no_of_people']}."
                ], 422);
            }
        }

        $registration = \App\Models\EventRegistration::create([
            ...$validated,
            'event_id' => $event->id,
            'user_id'  => session('alumni_id'),
        ]);

        try {
            Mail::to($registration->email)
                ->send(new EventRegistrationConfirmationMail($event, $registration));
        } catch (\Throwable $e) {
            Log::error('Event registration confirmation email failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'   => true,
            'message'   => 'You have successfully registered for this event!',
            'new_count' => $event->fresh()->registered_count,
        ]);
    }

    public function registrations(Request $request, $event)
    {
        $event = \App\Models\Event::where('id', $event)
            ->where('created_by', session('alumni_id'))
            ->firstOrFail();

        $registrations = \App\Models\EventRegistration::where('event_id', $event->id)
            ->with('alumni')
            ->orderBy('created_at', 'desc')
            ->get();

        // CSV export
        if ($request->query('export') === 'csv') {
            $filename = 'registrations-' . $event->slug . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($registrations, $event) {
                $handle = fopen('php://output', 'w');

                // CSV heading row
                fputcsv($handle, [
                    'Full Name', 'Email', 'Phone', 'Country',
                    'Batch/Year', 'No. of People', 'Message', 'Registered At'
                ]);

                foreach ($registrations as $reg) {
                    fputcsv($handle, [
                        $reg->full_name,
                        $reg->email,
                        $reg->phone,
                        $reg->country,
                        $reg->batch_year,
                        $reg->no_of_people,
                        $reg->message,
                        $reg->created_at->format('d M Y, g:i A'),
                    ]);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        return response()->json([
            'success'    => true,
            'event'      => $event->title,
            'total'      => $registrations->sum('no_of_people'),
            'count'      => $registrations->count(),
            'registrations' => $registrations->map(fn($r) => [
                'name'        => $r->full_name,
                'email'       => $r->email,
                'phone'       => $r->phone      ?? '—',
                'country'     => $r->country    ?? '—',
                'batch'       => $r->batch_year ?? '—',
                'people'      => $r->no_of_people,
                'message'     => $r->message    ?? '—',
                'registered'  => $r->created_at->format('d M Y, g:i A'),
                'photo'       => $r->alumni?->photo ? asset('storage/' . $r->alumni->photo) : null,
                'profile_url' => $r->alumni?->id ? url('/members/' . $r->alumni->id) : null,
                'initials'    => strtoupper(substr($r->full_name, 0, 1)),
            ])
        ]);
    }
}