<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelConventionHorsApp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $etudiant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : complétez les informations de votre stage dans ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.rappel-convention-hors-app',
        );
    }
}
