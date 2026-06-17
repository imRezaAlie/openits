<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $subjectLine,
        public string $messageBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [new Address(config('services.contact.email'))],
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: 'OpenITS Contact: '.$this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
        );
    }
}
