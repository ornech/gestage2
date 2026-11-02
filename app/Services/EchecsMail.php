<?php

namespace App\Services;

use App\Models\EchecMail;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Throwable;

class EchecsMail
{
    /** Enregistre un échec pour chaque adresse concernée (sans jamais lever d'exception). */
    public function enregistrer(array $emails, string $mail, Throwable|string $erreur): void
    {
        $message = $erreur instanceof Throwable ? $erreur->getMessage() : $erreur;

        foreach (array_unique(array_map('strtolower', array_filter($emails))) as $email) {
            try {
                EchecMail::create(['email' => $email, 'mail' => $mail, 'erreur' => mb_substr($message, 0, 2000)]);
            } catch (Throwable $e) {
                Log::error("Échec mail non enregistré pour {$email} : ".$e->getMessage());
            }
        }
    }

    /** Un mail est parti vers cette adresse : les alertes précédentes sont closes. */
    public function resoudre(string $email): void
    {
        EchecMail::nonResolu()->where('email', strtolower($email))->update(['resolu_at' => now()]);
    }

    /** Mail mis en file qui a échoué définitivement (écouteur de JobFailed). */
    public function depuisJobEchoue(JobFailed $event): void
    {
        try {
            $commande = unserialize($event->job->payload()['data']['command'] ?? '');
        } catch (Throwable) {
            return;
        }

        if (! $commande instanceof SendQueuedMailable) {
            return;
        }

        $emails = collect($commande->mailable->to)->pluck('address')->all();
        $this->enregistrer($emails, class_basename($commande->mailable), $event->exception);
    }
}
