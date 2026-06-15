<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class AdminNewsletterController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query()->latest();

        if ($request->filled('q')) {
            $query->where('email', 'like', '%' . trim($request->q) . '%');
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'unsubscribed'])) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->paginate(15)->withQueryString();

        $stats = [
            'total'        => NewsletterSubscriber::count(),
            'active'       => NewsletterSubscriber::where('status', 'active')->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
        ];

        return view('admin.newsletter.index', compact('subscribers', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:newsletter_subscribers,email',
        ]);

        NewsletterSubscriber::create([
            'email'         => strtolower(trim($validated['email'])),
            'status'        => 'active',
            'subscribed_at' => now(),
        ]);

        $message = 'Subscriber added successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function toggleStatus(Request $request, NewsletterSubscriber $newsletterSubscriber)
    {
        if ($newsletterSubscriber->status === 'active') {
            $newsletterSubscriber->update([
                'status'          => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        } else {
            $newsletterSubscriber->update([
                'status'          => 'active',
                'subscribed_at'   => now(),
                'unsubscribed_at' => null,
            ]);
        }

        $message = $newsletterSubscriber->status === 'active'
            ? 'Subscriber resubscribed.'
            : 'Subscriber unsubscribed.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status'  => $newsletterSubscriber->status,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, NewsletterSubscriber $newsletterSubscriber)
    {
        $email = $newsletterSubscriber->email;
        $newsletterSubscriber->delete();

        $message = "Removed {$email} from the subscriber list.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function export()
    {
        $filename = 'newsletter-subscribers-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Email', 'Status', 'Subscribed At', 'Unsubscribed At']);

            NewsletterSubscriber::orderBy('email')->each(function ($sub) use ($handle) {
                fputcsv($handle, [
                    $sub->email,
                    $sub->status,
                    $sub->subscribed_at?->format('Y-m-d H:i'),
                    $sub->unsubscribed_at?->format('Y-m-d H:i'),
                ]);
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}