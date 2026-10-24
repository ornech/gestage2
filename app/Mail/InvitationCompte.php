<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationCompte extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $utilisateur,
        public string $lien,
        public int $validiteJours,
    ) {
        // Mis en file seulement une fois la transaction validée (import) : le worker ne doit pas lire un compte absent
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre compte '.config('app.name').' : choisissez votre mot de passe',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.invitation-compte');
    }
}
