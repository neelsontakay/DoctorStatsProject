<?php

namespace App\Mail;

use App\Models\ReportShare;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportShareMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ReportShare $share,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A DoctorStats report has been shared with you',
        );
    }

    public function content(): Content
    {
        $this->share->loadMissing(['report', 'sharer']);

        return new Content(
            html: 'mail.html.report-share',
            text: 'mail.report-share',
        );
    }
}
