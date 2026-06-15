<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class TransactionalEmailService
{
    public function __construct(
        private readonly ZeptoMailService $zeptoMailService,
    ) {}

    public function sendMailable(string $toEmail, string $toName, Mailable $mailable): void
    {
        if ($this->zeptoMailService->isConfigured()) {
            $envelope = $mailable->envelope();
            $content = $mailable->content();
            $view = $content->html ?? $content->text;

            if ($view === null) {
                Mail::to($toEmail)->send($mailable);

                return;
            }

            $html = View::make($view, $mailable->buildViewData())->render();

            $this->zeptoMailService->sendHtml(
                $toEmail,
                $toName,
                $envelope->subject,
                $html,
            );

            return;
        }

        Mail::to($toEmail)->send($mailable);
    }

    public function sendView(string $toEmail, string $toName, string $subject, string $view, array $data = []): void
    {
        if ($this->zeptoMailService->isConfigured()) {
            $html = View::make($view, $data)->render();
            $this->zeptoMailService->sendHtml($toEmail, $toName, $subject, $html);

            return;
        }

        Mail::send($view, $data, function ($message) use ($toEmail, $toName, $subject): void {
            $message->to($toEmail, $toName)->subject($subject);
        });
    }
}
