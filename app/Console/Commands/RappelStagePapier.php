<?php

namespace App\Console\Commands;

use App\Mail\RappelSaisieStage;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class RappelStagePapier extends Command
{
    protected $signature   = 'stages:rappel';
    protected $description = 'Envoie un rappel aux étudiants actifs sans stage ni convention papier saisis';

    public function handle(): int
    {
        $anneeActive = Parametre::get('annee_scolaire', date('Y') . '-' . (date('Y') + 1));
        $syInt       = (int) explode('-', $anneeActive)[0];
        $promosActives = [$syInt + 1, $syInt + 2]; // SIO2 et SIO1 de l'année en cours

        $etudiants = User::role('Etudiant')
            ->where('statut', 'actif')
            ->whereIn('promo', $promosActives)
            ->whereDoesntHave('stages')
            ->whereDoesntHave('conventionPapier')
            ->with('tuteur')
            ->get();

        if ($etudiants->isEmpty()) {
            $this->info('Aucun étudiant concerné.');
            return self::SUCCESS;
        }

        $envoyes = 0;
        foreach ($etudiants as $etudiant) {
            if (!$etudiant->email) {
                continue;
            }
            Mail::to($etudiant->email)->send(new RappelSaisieStage($etudiant));
            $envoyes++;
            $this->line("  → {$etudiant->prenom} {$etudiant->nom} <{$etudiant->email}>");
        }

        $this->info("Rappels envoyés : {$envoyes}");
        return self::SUCCESS;
    }
}
