<?php

namespace App\Services;

use App\Mail\InvitationCompte;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Remplace le mot de passe provisoire partagé : le compte reçoit un mot de passe aléatoire
 * que personne ne connaît, et son titulaire le choisit via un lien à usage unique envoyé par mail.
 */
class InvitationService
{
    /** Pause entre deux mails pour ne pas être signalé comme spammeur (cf. AdminCommunicationController). */
    private const ECART_SECONDES = 2;

    /**
     * @param  int  $rang  position dans un envoi groupé (étale les envois)
     * @return array{lien: string, mail_envoye: bool} `mail_envoye` = false si l'adresse n'est pas
     *                                                 distribuable : l'administrateur doit alors transmettre le lien lui-même.
     */
    public function inviter(User $user, int $rang = 0): array
    {
        $user->forceFill([
            'password'              => Hash::make(Str::random(64)),
            'force_password_change' => true,
        ])->save();

        $token = Password::broker()->createToken($user);
        $lien  = route('password.reset', ['token' => $token, 'email' => $user->email]);

        if (! $this->adresseDistribuable($user)) {
            return ['lien' => $lien, 'mail_envoye' => false];
        }

        $jours = (int) round(config('auth.passwords.'.config('auth.defaults.passwords').'.expire') / 1440);

        Mail::to($user->email)->later(
            now()->addSeconds($rang * self::ECART_SECONDES),
            new InvitationCompte($user, $lien, max($jours, 1))
        );

        return ['lien' => $lien, 'mail_envoye' => true];
    }

    /** Les comptes créés sans email réel (placeholder d'import) ne peuvent pas recevoir de mail. */
    public function adresseDistribuable(User $user): bool
    {
        return filled($user->email)
            && filter_var($user->email, FILTER_VALIDATE_EMAIL)
            && ! str_ends_with(strtolower($user->email), '@import.local');
    }
}
