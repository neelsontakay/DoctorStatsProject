<?php

namespace App\Mail;

use App\Models\AnalysisJob;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnalysisCompleteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AnalysisJob $job,
        public Report $report,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your DoctorStats analysis is complete',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.html.analysis-complete',
            text: 'mail.analysis-complete',
        );
    }
}
