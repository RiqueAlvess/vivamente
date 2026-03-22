<?php

namespace App\Mail;

use App\Models\SurveyInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SurveyInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SurveyInvite $invite,
        public readonly string $surveyUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convite para Pesquisa HSE-IT — ' . $this->invite->campaign->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.survey-invite',
        );
    }
}
