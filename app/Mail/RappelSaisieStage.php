<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelSaisieStage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $etudiant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : saisie de votre stage dans ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.rappel-saisie-stage',
        );
    }
}
