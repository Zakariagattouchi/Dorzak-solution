<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Proves a store's email channel end-to-end from the Messaging settings. */
class TestChannelMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $storeName,
        public string $fromAddress,
        public ?string $fromName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Test email from {$this->storeName}",
            from: new Address($this->fromAddress, $this->fromName ?? $this->storeName),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<div style="font-family:sans-serif;font-size:15px;color:#2b2320;">'
                .'This is a test from your <b>'.e($this->storeName).'</b> marketing settings. '
                .'If you can read this, your email channel is working.</div>',
        );
    }
}
