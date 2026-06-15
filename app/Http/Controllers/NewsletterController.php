<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterUnsubscribeMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        // Honeypot — silently "succeed" without persisting anything.
        if ($request->filled('website')) {
            return response()->json([
                'success' => true,
                'message' => 'Thanks for subscribing!',
            ]);
        }

        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing) {
            if ($existing->status === 'active') {
                return response()->json([
                    'success' => true,
                    'message' => "You're already subscribed — thanks for sticking around!",
                ]);
            }

            $existing->update([
                'status'           => 'active',
                'subscribed_at'    => now(),
                'unsubscribed_at'  => null,
                'ip_address'       => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Welcome back! You've been resubscribed.",
            ]);
        }

        NewsletterSubscriber::create([
            'email'         => $email,
            'status'        => 'active',
            'subscribed_at' => now(),
            'ip_address'    => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanks for subscribing! You\'ll hear from us soon.',
        ]);
    }

    /**
     * One-click unsubscribe via a token from a confirmation email.
     * This is the ONLY action that actually unsubscribes — reaching this
     * URL proves the person controls the inbox for that address.
     *
     * Redirects to the homepage with a flash "toast" message, distinguishing
     * between "just unsubscribed" and "was already unsubscribed" (e.g. the
     * link was clicked twice).
     */
    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            abort(404);
        }

        if ($subscriber->status === 'unsubscribed') {
            return redirect()->route('home')->with('toast', [
                'type'    => 'info',
                'message' => "You're already unsubscribed from our newsletter.",
            ]);
        }

        $subscriber->update([
            'status'          => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return redirect()->route('home')->with('toast', [
            'type'    => 'success',
            'message' => "You've been unsubscribed from our newsletter.",
        ]);
    }

    /**
     * Show the "enter your email to unsubscribe" form.
     */
    public function unsubscribeForm()
    {
        return view('newsletter.unsubscribe-form');
    }

    /**
     * Handle the email-entry unsubscribe form.
     *
     * - Unknown email: generic "if subscribed, we've sent a link" message
     *   (doesn't confirm/deny whether the address is in our list).
     * - Already unsubscribed: tells the user directly, no email sent.
     * - Active subscriber: emails THEM a confirmation link (the token-based
     *   route above). Only clicking that link — proving inbox access —
     *   actually unsubscribes.
     */
    public function unsubscribeByEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if ($subscriber && $subscriber->status === 'unsubscribed') {
            return redirect()->route('newsletter.unsubscribe.form')->with('toast', [
                'type'    => 'info',
                'message' => "You're already unsubscribed from our newsletter.",
            ]);
        }

        if ($subscriber && $subscriber->status === 'active') {
            Mail::to($subscriber->email)->send(new NewsletterUnsubscribeMail($subscriber));
        }

        return redirect()->route('newsletter.unsubscribe.form')->with('toast', [
            'type'    => 'info',
            'message' => "If {$email} is subscribed, we've sent a confirmation link to that address.",
        ]);
    }
}