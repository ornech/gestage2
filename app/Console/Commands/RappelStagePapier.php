<?php

namespace App\Console\Commands;

use App\Mail\RappelConventionHorsApp;
use App\Mail\RappelSaisieStage;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RappelStagePapier extends Command
{
    protected $signature   = 'stages:rappel';
    protected $description = "Envoie un rappel aux étudiants actifs n'ayant rien saisi, ou ayant remis une convention hors app sans avoir saisi leur stage";

    public function handle(): int
    {
        $anneeActive = Parametre::anneeActive();
        $promosActives = array_values(Parametre::promos($anneeActive));

        // Un étudiant est concerné s'il n'a pas saisi de stage pour l'ANNÉE SCOLAIRE ACTIVE
        // (et non "aucun stage du tout" ni "pour sa classe" : le stage de SIO1 de l'an dernier,
        // ou celui d'une première année de SIO1 avant redoublement, ne dispense pas du nouveau).
        $base = fn() => User::role('Etudiant')
            ->where('statut', 'actif')
            ->whereIn('promo', $promosActives)
            ->whereDoesntHave('stages', fn($s) => $s->deLAnnee($anneeActive))
            ->with('tuteur');

        // 1) Rien saisi du tout (ni stage, ni convention hors app)
        $sansRien = (clone $base())
            ->whereDoesntHave('conventionPapier')
            ->get();

        // 2) Convention hors app remise (quel que soit son statut), mais stage non saisi
        //    (entreprise + maître de stage manquants) : avoir une convention validée ne
        //    dispense pas l'étudiant de saisir son stage dans l'application.
        $horsApp = (clone $base())
            ->whereHas('conventionPapier')
            ->get();

        if ($sansRien->isEmpty() && $horsApp->isEmpty()) {
            $this->info('Aucun étudiant concerné.');
            return self::SUCCESS;
        }

        $envoyes = 0;

        foreach ($sansRien as $etudiant) {
            if (!$etudiant->email) {
                continue;
            }
            Mail::to($etudiant->email)->send(new RappelSaisieStage($etudiant));
            $envoyes++;
            $this->line("  → [sans stage] {$etudiant->prenom} {$etudiant->nom} <{$etudiant->email}>");
        }

        foreach ($horsApp as $etudiant) {
            if (!$etudiant->email) {
                continue;
            }
            Mail::to($etudiant->email)->send(new RappelConventionHorsApp($etudiant));
            $envoyes++;
            $this->line("  → [convention hors app] {$etudiant->prenom} {$etudiant->nom} <{$etudiant->email}>");
        }

        $this->info("Rappels envoyés : {$envoyes}");
        return self::SUCCESS;
    }
}
