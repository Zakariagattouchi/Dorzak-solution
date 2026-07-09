<?php

namespace App\Mail;

use App\Models\StaffInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public StaffInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->invitation->store->name}",
        );
    }

    public function content(): Content
    {
        $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return new Content(
            view: 'emails.staff_invitation',
            with: [
                'store' => $this->invitation->store->name,
                'role' => $this->invitation->role->label(),
                'acceptUrl' => "{$frontend}/accept-invite?token={$this->invitation->token}",
            ],
        );
    }
}
