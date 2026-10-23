<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Les comptes créés avant l'introduction des invitations ont le mot de passe commun « achanger »
 * tant que leur titulaire ne s'est pas connecté. Cette commande les repère (par vérification du hash,
 * jamais par supposition) et leur envoie une invitation, ce qui invalide le mot de passe commun.
 */
class InviterMotDePasseProvisoire extends Command
{
    protected $signature = 'comptes:invitations-mdp-provisoire
                            {--dry-run : liste les comptes concernés sans rien modifier ni envoyer}
                            {--sans-mail : aucun mail envoyé, les liens à transmettre à la main sont affichés}';

    protected $description = "Invalide le mot de passe provisoire « achanger » et envoie une invitation aux comptes concernés";

    public function handle(InvitationService $invitations): int
    {
        $concernes = User::query()
            ->orderBy('nom')
            ->get()
            ->filter(fn (User $u) => Hash::check('achanger', $u->password));

        if ($concernes->isEmpty()) {
            $this->info('Aucun compte n\'utilise le mot de passe provisoire.');

            return self::SUCCESS;
        }

        $this->table(
            ['Nom', 'Prénom', 'Email', 'Rôle(s)', 'Mail possible'],
            $concernes->map(fn (User $u) => [
                $u->nom, $u->prenom, $u->email,
                $u->getRoleNames()->implode(', '),
                $invitations->adresseDistribuable($u) ? 'oui' : 'NON',
            ])->all()
        );

        if ($this->option('dry-run')) {
            $this->warn($concernes->count().' compte(s) concerné(s) — simulation, rien n\'a été modifié.');

            return self::SUCCESS;
        }

        $liens = [];
        foreach ($concernes->values() as $rang => $user) {
            $resultat = $invitations->inviter($user, $rang, ! $this->option('sans-mail'));

            if (! $resultat['mail_envoye']) {
                $liens[] = [$user->prenom.' '.$user->nom, $resultat['lien']];
            }
        }

        $this->info(($concernes->count() - count($liens)).' invitation(s) mise(s) en file d\'envoi.');

        if ($liens) {
            $this->warn(count($liens).' compte(s) sans mail envoyé : transmettez-leur ce lien (personnel, valable 7 jours).');
            $this->table(['Utilisateur', 'Lien'], $liens);
        }

        return self::SUCCESS;
    }
}
