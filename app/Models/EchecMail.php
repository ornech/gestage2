<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Mail qui n'a pas pu être remis (refus du serveur, adresse inconnue, hôte injoignable…).
 * Rattaché à une adresse, pas à un compte : l'alerte suit l'adresse et disparaît si elle est corrigée.
 */
class EchecMail extends Model
{
    protected $table = 'echecs_mail';

    protected $fillable = ['email', 'mail', 'erreur', 'resolu_at'];

    protected $casts = ['resolu_at' => 'datetime'];

    public function scopeNonResolu(Builder $query): Builder
    {
        return $query->whereNull('resolu_at');
    }

    /** Explication en clair, déduite du code SMTP ou du message. */
    public function diagnostic(): string
    {
        $e = strtolower($this->erreur);

        return match (true) {
            (bool) preg_match('/\b5\.1\.[1-6]\b|user unknown|no such user|does not exist|unknown recipient|mailbox unavailable|invalid recipient/', $e)
                => "L'adresse n'existe pas (ou plus) chez le fournisseur : à faire corriger par l'étudiant.",
            (bool) preg_match('/\b5\.2\.2\b|mailbox full|quota/', $e)
                => "La boîte de l'étudiant est pleine.",
            (bool) preg_match('/spam|blocked|blacklist|reputation|policy|denied|refused|rejected|\b5\.7\./', $e)
                => "Le serveur du destinataire refuse nos mails (filtre anti-spam ou politique) : utiliser le mode lien.",
            (bool) preg_match('/could not resolve|dns|host|connection|timed? ?out|unable to connect/', $e)
                => "Serveur injoignable ou domaine de l'adresse inexistant (faute de frappe dans le domaine ?).",
            (bool) preg_match('/authenticat|credentials|\b535\b/', $e)
                => "Problème de connexion de l'application à son serveur d'envoi (identifiants SMTP) : touche tous les mails.",
            default => 'Cause non identifiée : voir le message du serveur ci-dessous.',
        };
    }
}
