<?php

namespace App\Console\Commands;

use App\Mail\RappelSaisieStage;
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
        $etudiants = User::role('Etudiant')
            ->where('statut', 'actif')
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
