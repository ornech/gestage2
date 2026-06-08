<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Entreprise;
use Illuminate\Http\Request;

/**
 * Recherche d'une entreprise par SIRET, partagée entre le formulaire de saisie
 * étudiant et le formulaire de saisie staff (passe-droit) :
 * 1) en base locale, 2) sinon via l'API INSEE Sirene avec création automatique.
 */
trait RechercheSiretTrait
{
    private function rechercherEntrepriseParSiret(Request $request): \Illuminate\Http\JsonResponse
    {
        $siret = preg_replace('/\D/', '', $request->get('siret', ''));

        if (strlen($siret) !== 14) {
            return response()->json(['found' => false, 'error' => 'SIRET invalide (14 chiffres attendus).']);
        }

        // ── 1. Recherche dans la base locale ────────────────────────
        $entreprise = Entreprise::where('siret', $siret)->with('employes')->first();

        if ($entreprise) {
            return $this->entrepriseJson($entreprise);
        }

        // ── 2. Appel API INSEE Sirene ────────────────────────────────
        $apiKey = config('services.sirene.key');
        $url    = config('services.sirene.url') . $siret;

        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders([
                'Accept'                       => 'application/json',
                'X-INSEE-Api-Key-Integration'  => $apiKey,
            ])
            ->get($url);

        if ($response->failed()) {
            $status = $response->status();
            $msg = match(true) {
                $status === 404 => 'SIRET introuvable dans la base INSEE.',
                $status === 403 => 'Accès API refusé — vérifiez la clé.',
                default         => "Erreur INSEE ({$status}).",
            };
            return response()->json(['found' => false, 'error' => $msg]);
        }

        // ── 3. Parsing de la réponse INSEE ───────────────────────────
        $etab    = $response->json('etablissement');
        $unite   = $etab['uniteLegale']           ?? [];
        $adr     = $etab['adresseEtablissement']  ?? [];
        $periode = ($etab['periodesEtablissement'] ?? [[]])[0] ?? [];

        $raisonSociale = $unite['denominationUniteLegale']
            ?? trim(($unite['nomUniteLegale'] ?? '') . ' ' . ($unite['prénomUsuelUniteLegale'] ?? ''));

        $numVoie   = trim(($adr['numeroVoieEtablissement'] ?? '') . ' ' . ($adr['indiceRepetitionEtablissement'] ?? ''));
        $adresseLigne = trim("{$numVoie} " . ($adr['typeVoieEtablissement'] ?? '') . ' ' . ($adr['libelleVoieEtablissement'] ?? ''));
        $codePostal = $adr['codePostalEtablissement']     ?? '';
        $ville      = $adr['libelleCommuneEtablissement'] ?? '';
        $codeNaf    = $periode['activitePrincipaleEtablissement']
            ?? ($unite['activitePrincipaleUniteLegale'] ?? '');

        // ── 4. Création automatique de la fiche entreprise ───────────
        $entreprise = Entreprise::create([
            'raison_sociale'  => strtoupper($raisonSociale),
            'siret'           => $siret,
            'code_naf'        => $codeNaf,
            'adresse'         => $adresseLigne,
            'code_postal'     => $codePostal,
            'ville'           => strtoupper($ville),
            'departement_code'=> substr($codePostal, 0, 2),
            'est_valide'      => true,
            'user_id'         => auth()->id(),
        ]);

        return $this->entrepriseJson($entreprise->load('employes'), created: true);
    }

    private function entrepriseJson(Entreprise $e, bool $created = false): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'found'   => true,
            'created' => $created,
            'id'      => $e->id,
            'siret'   => $e->siret,
            'nom'     => $e->raison_sociale,
            'adresse' => trim("{$e->adresse} {$e->code_postal} {$e->ville}"),
            'contacts'=> $e->employes->map(fn($emp) => [
                'id'    => $emp->id,
                'label' => "{$emp->prenom} {$emp->nom}" . ($emp->fonction ? " — {$emp->fonction}" : ''),
            ])->values(),
        ]);
    }
}
