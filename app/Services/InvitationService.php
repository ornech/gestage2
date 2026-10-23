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
     * @param  bool  $envoyerMail  false = « mode lien » : aucun mail, l'administrateur transmet le lien lui-même
     * @return array{lien: string, mail_envoye: bool, raison: ?string} `raison` explique pourquoi aucun mail n'est parti
     */
    public function inviter(User $user, int $rang = 0, bool $envoyerMail = true): array
    {
        $user->forceFill([
            'password'              => Hash::make(Str::random(64)),
            'force_password_change' => true,
        ])->save();

        $token = Password::broker()->createToken($user);
        $lien  = route('password.reset', ['token' => $token, 'email' => $user->email]);

        $raison = $envoyerMail ? $this->raisonSansMail($user) : 'envoi par mail non demandé';

        if ($raison !== null) {
            return ['lien' => $lien, 'mail_envoye' => false, 'raison' => $raison];
        }

        $jours = (int) round(config('auth.passwords.'.config('auth.defaults.passwords').'.expire') / 1440);

        Mail::to($user->email)->later(
            now()->addSeconds($rang * self::ECART_SECONDES),
            new InvitationCompte($user, $lien, max($jours, 1))
        );

        return ['lien' => $lien, 'mail_envoye' => true, 'raison' => null];
    }

    /** Null si un mail peut être envoyé ; sinon la raison, à afficher à l'administrateur. */
    public function raisonSansMail(User $user): ?string
    {
        $email = strtolower((string) $user->email);

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || str_ends_with($email, '@import.local')) {
            return 'adresse email absente ou factice';
        }

        $domaine = substr(strrchr($email, '@'), 1);
        foreach (config('invitations.domaines_sans_mail', []) as $refuse) {
            if ($domaine === $refuse || str_ends_with($domaine, '.'.$refuse)) {
                return "la messagerie @{$refuse} refuse les mails de ce serveur";
            }
        }

        return $this->anomalieAdresse($email);
    }

    public function adresseDistribuable(User $user): bool
    {
        return $this->raisonSansMail($user) === null;
    }

    /**
     * Adresse syntaxiquement valide mais probablement fausse : domaine proche d'un domaine courant
     * (faute de frappe) ou sans serveur de messagerie. Null si rien d'anormal.
     */
    public function anomalieAdresse(string $email): ?string
    {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'adresse email invalide';
        }

        $domaine = substr(strrchr($email, '@'), 1);
        $courants = config('invitations.domaines_courants', []);

        if (! in_array($domaine, $courants, true)) {
            foreach ($courants as $reference) {
                // Tolère 2 fautes sur les longs domaines (gmial.com = 2 pour gmail.com), 1 sur les courts
                $tolerance = strlen($reference) >= 9 ? 2 : 1;
                if (levenshtein($domaine, $reference) <= $tolerance) {
                    return "domaine suspect « {$domaine} » : vouliez-vous écrire « {$reference} » ?";
                }
            }

            if (config('invitations.verifier_dns') && ! $this->domaineRecoitDuCourrier($domaine)) {
                return "le domaine « {$domaine} » n'a pas de serveur de messagerie";
            }
        }

        return null;
    }

    private function domaineRecoitDuCourrier(string $domaine): bool
    {
        return checkdnsrr($domaine.'.', 'MX') || checkdnsrr($domaine.'.', 'A') || checkdnsrr($domaine.'.', 'AAAA');
    }
}
