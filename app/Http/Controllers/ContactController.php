<?php

namespace App\Http\Controllers;

use App\Jobs\SendContactEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact.index');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'min:2', 'max:100'],
            'email'   => ['required', 'email', 'max:200'],
            'subject' => ['required', 'string', 'min:3', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.required'    => 'Please enter your name.',
            'email.required'   => 'Please enter your email address.',
            'email.email'      => 'Please enter a valid email address.',
            'subject.required' => 'Please enter a subject.',
            'message.required' => 'Please enter your message.',
            'message.min'      => 'Your message must be at least 10 characters.',
        ]);

        $to      = config('services.contact_email');
        $subject = 'Contact Form: ' . $validated['subject'];
        $name    = e($validated['name']);
        $email   = e($validated['email']);
        $subj    = e($validated['subject']);
        $msg     = nl2br(e($validated['message']));
        $time    = now()->format('d M Y, g:i A');

        $html = "<html><body style='font-family:Arial,sans-serif;padding:24px;background:#f4f4f7'>
            <div style='max-width:560px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e4e4e7'>
                <div style='background:#0c0b1a;padding:24px 28px'>
                    <h2 style='color:#daa520;margin:0;font-size:17px'>New Contact Message</h2>
                    <p style='color:rgba(255,255,255,.45);margin:4px 0 0;font-size:12px'>ICCR Alumni Network</p>
                </div>
                <div style='padding:24px 28px'>
                    <p style='margin:0 0 12px'><strong>From:</strong> {$name} &lt;{$email}&gt;</p>
                    <p style='margin:0 0 12px'><strong>Subject:</strong> {$subj}</p>
                    <p style='margin:0 0 8px'><strong>Message:</strong></p>
                    <div style='background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:14px 16px'>
                        <p style='margin:0;line-height:1.7;color:#374151'>{$msg}</p>
                    </div>
                </div>
                <div style='padding:12px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;color:#9ca3af'>
                    Sent from ICCR &nbsp;·&nbsp; {$time}
                </div>
            </div>
        </body></html>";

        SendContactEmail::dispatch(
            $to,
            $subject,
            $html,
            $validated['email'],
            $validated['name'],
        );

        Log::info('Contact email queued for ' . $to);

        return redirect()->route('contact')
            ->with('success', 'Your message has been sent! We\'ll get back to you within 24–48 hours.');
    }
}