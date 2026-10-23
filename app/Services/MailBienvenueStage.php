<?php

namespace App\Services;

use App\Mail\BienvenueMaitreDeStage;
use App\Models\Stage;
use Illuminate\Support\Facades\Mail;

/** Mail de bienvenue au maître de stage, envoyé quand la convention du stage est validée. */
class MailBienvenueStage
{
    /**
     * @param string|null $email  adresse à utiliser à la place de celle du maître de stage (changement d'adresse)
     * @param bool        $renvoi envoyer même si le mail est déjà parti
     */
    public function envoyer(Stage $stage, ?string $email = null, bool $renvoi = false): bool
    {
        if (!$renvoi && $stage->mail_bienvenue_envoye_at !== null) {
            return false;
        }

        $stage->loadMissing(['maitreDeStage', 'etudiant.tuteur']);
        $employe = $stage->maitreDeStage;
        $email ??= $employe?->email;

        // Un stage dont l'étudiant a été supprimé n'a plus personne à présenter au maître de stage
        if (!$employe || !$email || !$stage->etudiant) {
            return false;
        }

        Mail::to($email)->send(new BienvenueMaitreDeStage($employe, $stage->etudiant, $stage->etudiant->tuteur));
        $stage->update(['mail_bienvenue_envoye_at' => now()]);

        return true;
    }
}
