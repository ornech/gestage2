<?php

namespace App\Mail;

use App\Models\Employe;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class CommunicationBTS extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Employe $employe,
        public string  $sujet,
        public string  $corps,
        public ?User   $expediteur,
    ) {}

    public function headers(): Headers
    {
        // List-Unsubscribe : reconnu par Gmail, Outlook, Apple Mail
        // Réduit drastiquement le risque d'être marqué spam sur envoi en masse
        $unsubscribeEmail = $this->expediteur?->email ?? config('mail.from.address');

        return new Headers(
            text: [
                'List-Unsubscribe'      => '<mailto:' . $unsubscribeEmail . '?subject=Désinscription>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                'Precedence'            => 'bulk',
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->sujet,
            replyTo: $this->expediteur?->email ? [$this->expediteur->email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.communication-bts');
    }
}
