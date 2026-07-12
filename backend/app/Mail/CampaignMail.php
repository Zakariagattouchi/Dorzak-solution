<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** A marketing campaign email, sent from the store's configured identity. */
class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public ?string $fromAddress = null,
        public ?string $fromName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
            from: $this->fromAddress ? new Address($this->fromAddress, $this->fromName) : null,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<div style="font-family:sans-serif;font-size:15px;color:#2b2320;white-space:pre-wrap;">'
                .e($this->campaign->body).'</div>',
        );
    }
}
