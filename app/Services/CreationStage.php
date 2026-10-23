<?php

namespace App\Services;

use App\Enums\StatutConvention;
use App\Enums\StatutValidation;
use App\Models\ConfigurationStage;
use App\Models\Entreprise;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Création du stage d'un étudiant pour l'année scolaire active (un seul par année).
 * Utilisé à l'identique par l'étudiant et par le staff.
 */
class CreationStage
{
    /**
     * Pourquoi la saisie est fermée à cet étudiant (null = ouverte) :
     * il lui faut une classe cette année, et les dates de stage de cette classe doivent être connues.
     */
    public function raisonFermeture(User $etudiant): ?string
    {
        $classe = $etudiant->classe_courante;

        if (!$classe) {
            return "Votre compte n'est rattaché à aucune classe cette année : contactez un professeur.";
        }

        $config = ConfigurationStage::where('annee_scolaire', Parametre::anneeActive())
            ->where('classe', $classe)
            ->first();

        if (!$config?->stage_date_debut || !$config->stage_date_fin) {
            return "La saisie des stages n'est pas encore ouverte pour les {$classe} : les dates de stage ne sont pas renseignées.";
        }

        return null;
    }

    public function __construct(private MailBienvenueStage $mails)
    {
    }

    public function creer(User $etudiant, int $entrepriseId, int $maitreDeStageId, string $dateDebut, int $dureeSemaines): Stage
    {
        $annee      = Parametre::anneeActive();
        $entreprise = Entreprise::findOrFail($entrepriseId);
        $debut      = Carbon::parse($dateDebut);

        try {
            $stage = DB::transaction(function () use ($etudiant, $annee, $entreprise, $maitreDeStageId, $debut, $dureeSemaines) {
                // Convention papier de cette année : le stage la reprend (voie papier déjà validée côté direction)
                $papier = $etudiant->conventionsPapier()->where('annee_scolaire', $annee)->first();

                $stage = Stage::create([
                    'titre'              => "Stage chez {$entreprise->raison_sociale}",
                    'entreprise_id'      => $entreprise->id,
                    'maitre_de_stage_id' => $maitreDeStageId,
                    'etudiant_id'        => $etudiant->id,
                    'classe'             => $etudiant->classe_courante,
                    'annee_scolaire'     => $annee,
                    'date_debut'         => $debut,
                    'date_fin'           => $debut->copy()->addWeeks($dureeSemaines),
                    'statut_convention'  => StatutConvention::depuisConventionPapier($papier)->value,
                    'statut_validation'  => ($papier ? StatutValidation::Valide : StatutValidation::EnAttente)->value,
                ]);

                $papier?->delete();

                return $stage;
            });
        } catch (UniqueConstraintViolationException) {
            // Deux envois simultanés : la clé unique (étudiant, année) a déjà tranché
            throw ValidationException::withMessages(['stage' => 'Un stage existe déjà pour cette année.']);
        }

        // La convention reprise du papier peut être déjà validée : le maître de stage est alors prévenu tout de suite
        if ($stage->statut_convention === StatutConvention::Validee->value) {
            $this->mails->envoyer($stage);
        }

        return $stage;
    }
}
