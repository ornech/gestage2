<?php

namespace App\Mail;

use App\Models\Employe;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class BienvenueMaitreDeStage extends Mailable
{
    use Queueable, SerializesModels;

    public string $lienRgpd;
    public string $introTexte;
    public string $complementTexte;

    public function __construct(
        public Employe $employe,
        public User    $etudiant,
        public ?User   $tuteur,
    ) {
        $this->lienRgpd = URL::temporarySignedRoute(
            'rgpd.employe.supprimer-email',
            now()->addDays(90),
            ['employe' => $employe->id]
        );

        $this->introTexte = Parametre::get(
            'mail_bienvenue_intro',
            "Nous vous remercions chaleureusement d'accueillir [PRENOM] [NOM], étudiant(e) en BTS SIO au sein de votre structure pour un stage.\n\nVotre accompagnement est précieux pour la formation de nos étudiant(e)s, et nous vous en sommes sincèrement reconnaissants."
        );

        $this->complementTexte = Parametre::get('mail_bienvenue_intro_custom', '');
    }

    public function envelope(): Envelope
    {
        $prefixObjet = Parametre::get('mail_bienvenue_objet_prefix', 'Accueil en stage de');
        $sujet       = trim($prefixObjet) . ' ' . $this->etudiant->prenom . ' ' . $this->etudiant->nom;

        return new Envelope(
            subject: $sujet,
            replyTo: $this->tuteur?->email ? [$this->tuteur->email] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.bienvenue-maitre-de-stage');
    }
}
