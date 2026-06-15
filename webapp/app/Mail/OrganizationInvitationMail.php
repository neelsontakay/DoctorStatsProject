<?php

namespace App\Mail;

use App\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OrganizationInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to join '.$this->invitation->organization->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.html.organization-invitation',
            text: 'mail.organization-invitation',
        );
    }
}
