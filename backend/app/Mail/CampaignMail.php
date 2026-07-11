<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** A marketing campaign email (premium — campaigns). Plain-text body. */
class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Campaign $campaign) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<div style="font-family:sans-serif;font-size:15px;color:#2b2320;white-space:pre-wrap;">'
                .e($this->campaign->body).'</div>',
        );
    }
}
