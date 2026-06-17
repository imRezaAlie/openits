<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Mail::send(new ContactMail(
            senderName: $validated['name'],
            senderEmail: $validated['email'],
            subjectLine: $validated['subject'],
            messageBody: $validated['message'],
        ));

        return redirect()
            ->to(url('/').'#contact')
            ->with('contact_success', 'Thank you for reaching out! We will get back to you soon.');
    }
}
