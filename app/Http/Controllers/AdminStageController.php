<?php

namespace App\Http\Controllers;

use App\Enums\StatutConvention;
use App\Enums\StatutConventionPapier;
use App\Enums\StatutValidation;
use App\Http\Controllers\Concerns\RechercheSiretTrait;
use App\Models\ConfigurationStage;
use App\Models\ConventionPapier;
use App\Models\Employe;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use App\Services\CreationStage;
use App\Services\MailBienvenueStage;
use Illuminate\Http\Request;

class AdminStageController extends Controller
{
    use RechercheSiretTrait;

    public function index(Request $request)
    {
        $anneeActive       = Parametre::anneeActive();
        $anneeSelectionnee = $request->get('annee', $anneeActive);
        $syInt             = (int) explode('-', $anneeSelectionnee)[0];
        $filtre            = $request->get('filtre', 'tous');

        $promoSio1 = $syInt + 2;
        $promoSio2 = $syInt + 1;

        // Années disponibles depuis les promos existantes
        $annees = User::role('Etudiant')
            ->whereNotNull('promo')
            ->pluck('promo')
            ->flatMap(fn($p) => [($p - 2).'-'.($p - 1), ($p - 1).'-'.$p])
            ->merge(ConfigurationStage::toutesLesAnnees())
            ->prepend($anneeActive)
            ->unique()
            ->sortDesc()
            ->values();

        $tuteurs = Employe::orderBy('nom')->get();

        // ── 3 filtres indépendants et cumulables ─────────────────────────
        // Une année passée garde ceux qui en sont sortis depuis (diplômés, échecs, archivés : ils n'y étaient plus « actifs » aujourd'hui mais y étaient)
        $statuts    = $anneeSelectionnee < $anneeActive ? ['actif', 'diplome', 'echec', 'archive'] : ['actif'];
        $classe     = $request->get('classe', 'tous');  // tous | sio1 | sio2
        $filtre     = $request->get('filtre', 'tous');  // tous | sans_stage | a_faire_signer | en_attente | validee

        // Un stage appartient à une année scolaire : tout ce qui concerne les stages
        // (liste, filtres, compteurs) est restreint à l'année sélectionnée
        $deLAnnee       = fn($s) => $s->where('annee_scolaire', $anneeSelectionnee);
        $papierDeLAnnee = fn($cp) => $cp->where('annee_scolaire', $anneeSelectionnee);

        $classeFiltre = match($classe) {
            'sio1'  => $promoSio1,
            'sio2'  => $promoSio2,
            default => null,
        };

        $classeStr = match($classe) {
            'sio1'  => 'SIO1',
            'sio2'  => 'SIO2',
            default => 'SIO1 + SIO2',
        };

        // ── Requête étudiant-centrique ────────────────────────────────────
        // Un étudiant fait partie de la liste s'il est dans la classe cette année-là d'après sa promo,
        // ou s'il y a eu un stage cette année-là dans cette classe (sa promo a pu changer depuis : redoublement).
        $promos     = $classeFiltre ? [$classeFiltre] : [$promoSio1, $promoSio2];
        $appartient = fn($q) => $q
            ->where(fn($q2) => $q2->whereIn('statut', $statuts)->whereIn('promo', $promos))
            ->orWhereHas('stages', fn($s) => $deLAnnee($s)
                ->when($classe !== 'tous', fn($s2) => $s2->where('classe', strtoupper($classe))));

        $query = User::role('Etudiant')
            ->where($appartient)
            ->with([
                'stages' => fn($q) => $q
                    ->where('annee_scolaire', $anneeSelectionnee)
                    ->with(['entreprise', 'maitreDeStage'])
                    ->withCount('journalEntries')
                    // Restreindre les stages chargés au filtre actif → cohérence couleurs
                    ->when(in_array($filtre, ['a_faire_signer', 'en_attente', 'validee']),
                        fn($s) => $s->where('statut_convention', $filtre))
                    ->orderBy('date_debut', 'desc'),
                'conventionsPapier' => $papierDeLAnnee,
            ]);

        // Filtre sur le statut de la convention (indépendant de la classe)
        if ($filtre === 'sans_stage') {
            $query->whereDoesntHave('stages', $deLAnnee)->whereDoesntHave('conventionsPapier', $papierDeLAnnee);
        } elseif ($filtre === 'hors_app') {
            $query->whereHas('conventionsPapier', fn($cp) => $papierDeLAnnee($cp)->where('statut', 'hors_app'))
                  ->whereDoesntHave('stages', $deLAnnee);
        } elseif (in_array($filtre, ['a_faire_signer', 'en_attente', 'validee'])) {
            $query->where(function ($q) use ($filtre, $deLAnnee, $papierDeLAnnee) {
                $q->whereHas('stages', fn($s) => $deLAnnee($s)->where('statut_convention', $filtre))
                  ->orWhere(function ($q2) use ($filtre, $deLAnnee, $papierDeLAnnee) {
                      $q2->whereHas('conventionsPapier', fn($cp) => $papierDeLAnnee($cp)->where('statut', $filtre))
                         ->whereDoesntHave('stages', $deLAnnee);
                  });
            });
        }

        // Recherche par nom/prénom (préservée avec les autres filtres)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('nom',    'like', "%$search%")
                ->orWhere('prenom', 'like', "%$search%")
            );
        }

        $etudiants = $query->orderBy('promo')->orderBy('nom')->get();

        // ── Compteurs par statut pour les double tags ─────────────────────
        $baseCount = fn() => User::role('Etudiant')->where($appartient);

        $compteurs = [
            'tous'           => ($baseCount)()->count(),
            'sans_stage'     => ($baseCount)()->whereDoesntHave('stages', $deLAnnee)->whereDoesntHave('conventionsPapier', $papierDeLAnnee)->count(),
            'hors_app'       => ($baseCount)()
                                    ->whereHas('conventionsPapier', fn($cp) => $papierDeLAnnee($cp)->where('statut', 'hors_app'))
                                    ->whereDoesntHave('stages', $deLAnnee)
                                    ->count(),
            'a_faire_signer' => ($baseCount)()->where(fn($q) =>
                $q->whereHas('stages', fn($s) => $deLAnnee($s)->where('statut_convention', 'a_faire_signer'))
                  ->orWhere(fn($q2) => $q2->whereHas('conventionsPapier', fn($cp) => $papierDeLAnnee($cp)->where('statut', 'a_faire_signer'))->whereDoesntHave('stages', $deLAnnee))
            )->count(),
            'en_attente'     => ($baseCount)()->where(fn($q) =>
                $q->whereHas('stages', fn($s) => $deLAnnee($s)->where('statut_convention', 'en_attente'))
                  ->orWhere(fn($q2) => $q2->whereHas('conventionsPapier', fn($cp) => $papierDeLAnnee($cp)->where('statut', 'en_attente'))->whereDoesntHave('stages', $deLAnnee))
            )->count(),
            'validee'        => ($baseCount)()->where(fn($q) =>
                $q->whereHas('stages', fn($s) => $deLAnnee($s)->where('statut_convention', 'validee'))
                  ->orWhere(fn($q2) => $q2->whereHas('conventionsPapier', fn($cp) => $papierDeLAnnee($cp)->where('statut', 'validee'))->whereDoesntHave('stages', $deLAnnee))
            )->count(),
        ];

        return view('admin.stages.index', compact(
            'etudiants', 'tuteurs', 'annees', 'anneeSelectionnee', 'anneeActive',
            'syInt', 'filtre', 'classe', 'classeStr', 'compteurs'
        ));
    }

    /**
     * Export CSV des maîtres de stage (nom, email, adresse entreprise)
     * filtré par année scolaire et optionnellement par section (SIO1/SIO2).
     */
    public function exportMaitresCsv(Request $request)
    {
        $annee  = $request->get('annee', Parametre::anneeActive());
        $classe = $request->get('classe');

        $query = Stage::with(['maitreDeStage', 'entreprise', 'etudiant'])
            ->where('annee_scolaire', $annee)
            ->whereNotNull('maitre_de_stage_id');

        if ($classe) {
            $query->where('classe', strtoupper($classe));
        }

        $stages = $query->orderBy('classe')->get();

        $slug    = 'maitres-de-stage_' . $annee . ($classe ? '_' . strtolower($classe) : '') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$slug}\"",
        ];

        $callback = function () use ($stages) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Section', 'Étudiant nom', 'Étudiant prénom', 'Maître nom', 'Maître prénom', 'Email', 'Téléphone', 'Entreprise', 'Adresse', 'Code postal', 'Ville'], ';');

            foreach ($stages as $stage) {
                $emp  = $stage->maitreDeStage;
                $ent  = $stage->entreprise;
                $etu  = $stage->etudiant;
                fputcsv($out, [
                    $stage->classe ?? '',
                    $etu?->nom     ?? '',
                    $etu?->prenom  ?? '',
                    $emp?->nom     ?? '',
                    $emp?->prenom  ?? '',
                    $emp?->email   ?? '',
                    $emp?->telephone ?? '',
                    $ent?->raison_sociale ?? '',
                    $ent?->adresse        ?? '',
                    $ent?->code_postal    ?? '',
                    $ent?->ville          ?? '',
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Formulaire de saisie d'un stage par le staff, pour le compte d'un étudiant
     * bloqué (passe-droit) — entreprise introuvable, maître de stage manquant, etc.
     */
    public function creerForm()
    {
        $etudiants = User::role('Etudiant')
            ->where('statut', 'actif')
            ->orderBy('classe')
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'classe', 'classe_id']);

        $annee = Parametre::anneeActive();

        return view('admin.stages.creer', compact('etudiants', 'annee'));
    }

    /**
     * Recherche d'entreprise par SIRET (passe-droit staff) — réutilise la même
     * logique que la recherche étudiant (base locale puis API INSEE Sirene).
     */
    public function rechercheSiret(Request $request)
    {
        return $this->rechercherEntrepriseParSiret($request);
    }

    /**
     * Ajout en AJAX d'un nouveau maître de stage depuis le formulaire de création
     * de stage par le staff (passe-droit) — miroir de StageController::ajouterMaitreDeStage.
     */
    public function ajouterMaitreDeStage(Request $request)
    {
        $validated = $request->validate([
            'entreprise_id' => 'required|exists:entreprises,id',
            'nom'           => 'required|string|max:255',
            'prenom'        => 'required|string|max:255',
            'email'         => 'nullable|email|unique:employes,email',
            'telephone'     => 'nullable|string|max:30',
        ]);

        $employe = Employe::create($validated + ['creator_id' => auth()->id()]);

        return response()->json([
            'id'    => $employe->id,
            'label' => "{$employe->prenom} {$employe->nom}",
        ]);
    }

    /**
     * Enregistre le stage saisi par le staff pour le compte de l'étudiant choisi.
     * Reprend la logique de StageController::store, en ciblant l'étudiant sélectionné
     * plutôt que l'utilisateur connecté.
     */
    public function creer(Request $request, CreationStage $creation)
    {
        $validated = $request->validate([
            'etudiant_id'        => 'required|exists:users,id',
            'entreprise_id'      => 'required|exists:entreprises,id',
            'maitre_de_stage_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('employes', 'id')->where('entreprise_id', $request->entreprise_id),
            ],
            'date_debut'         => 'required|date',
            'duree'              => 'required|integer|min:1',
        ]);

        $etudiant = User::findOrFail($validated['etudiant_id']);
        abort_unless($etudiant->hasRole('Etudiant'), 422, "L'utilisateur sélectionné n'est pas un étudiant.");

        // Sans classe, le stage serait créé sans classe : à régler d'abord (diplômé, redoublant, promo)
        if (!$etudiant->classe_courante) {
            return back()->withInput()->withErrors(
                "Cet étudiant n'a pas de classe cette année : indique d'abord s'il est diplômé ou s'il redouble, ou corrige sa promo."
            );
        }

        if ($etudiant->aUnStageCetteAnnee()) {
            return back()->withInput()->withErrors(
                "Cet étudiant a déjà un stage enregistré pour cette année. Modifie-le directement depuis « Tous les stages »."
            );
        }

        $creation->creer(
            $etudiant,
            (int) $validated['entreprise_id'],
            (int) $validated['maitre_de_stage_id'],
            $validated['date_debut'],
            (int) $validated['duree'],
        );

        return redirect()->route('admin.dashboard')
            ->with('success', "Stage créé pour {$etudiant->prenom} {$etudiant->nom}.");
    }

    public function assign(Request $request, Stage $stage)
    {
        $request->validate([
            'maitre_de_stage_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('employes', 'id')->where('entreprise_id', $stage->entreprise_id),
            ],
        ]);

        $stage->update(['maitre_de_stage_id' => $request->maitre_de_stage_id]);

        return back()->with('success', 'Maître de stage assigné.');
    }

    public function updateConvention(Stage $stage, string $statut, MailBienvenueStage $mails)
    {
        $nouveau = StatutConvention::tryFrom($statut);

        abort_unless($nouveau !== null, 422);

        $data = ['statut_convention' => $nouveau->value];

        // Déposer la convention pour signature = valider implicitement le stage
        if ($nouveau === StatutConvention::EnAttente && $stage->statut_validation === StatutValidation::EnAttente->value) {
            $data['statut_validation'] = StatutValidation::Valide->value;
            $data['note_rejet']        = null;
        }

        $stage->update($data);

        // Mail de bienvenue au maître de stage — une seule fois, à la validation de la convention
        if ($nouveau === StatutConvention::Validee) {
            $mails->envoyer($stage);
        }

        return back();
    }

    /**
     * Crée un placeholder de convention quand un étudiant a remis sa convention
     * directement sans passer par l'application (statut initial "hors app").
     */
    public function marquerHorsAppli(Request $request, User $user)
    {
        $annee = $request->validate(['annee' => ['nullable', 'regex:/^\d{4}-\d{4}$/']])['annee'] ?? Parametre::anneeActive();

        ConventionPapier::updateOrCreate(
            ['etudiant_id' => $user->id, 'annee_scolaire' => $annee],
            ['statut' => StatutConventionPapier::HorsApp->value]
        );

        return back();
    }

    public function avancerConventionPapier(ConventionPapier $convention)
    {
        $suivant = $convention->statutSuivant();

        if ($suivant) {
            $convention->update(['statut' => $suivant]);
        }

        return back();
    }

    public function revertConventionPapier(ConventionPapier $convention)
    {
        $precedent = $convention->statutPrecedent();

        if ($precedent) {
            $convention->update(['statut' => $precedent]);
        } else {
            // Retour depuis le 1er statut = suppression (l'étudiant n'a pas de convention)
            $convention->delete();
        }

        return back();
    }

    public function revertConvention(Stage $stage)
    {
        $prev = StatutConvention::tryFrom($stage->statut_convention)?->precedent();

        if ($prev) {
            $data = ['statut_convention' => $prev->value];
            if ($prev === StatutConvention::AFaireSigner) {
                $data['statut_validation'] = StatutValidation::EnAttente->value;
            }
            $stage->update($data);
        }

        return back();
    }

    /** Supprime un stage mal saisi, pour que l'étudiant (ou le staff) puisse le ressaisir. */
    public function destroy(Stage $stage)
    {
        $this->authorize('delete', $stage);

        if ($stage->journalEntries()->exists()) {
            return back()->withErrors("Ce stage a déjà des entrées de journal : il ne peut pas être supprimé.");
        }

        $stage->delete();

        return back()->with('success', 'Stage supprimé.');
    }

    public function valider(Stage $stage)
    {
        $stage->update([
            'statut_validation' => StatutValidation::Valide->value,
            'note_rejet'        => null,
        ]);

        return back()->with('success', "Stage de {$stage->etudiant?->prenom} {$stage->etudiant?->nom} validé.");
    }

    public function rejeter(Request $request, Stage $stage)
    {
        $request->validate([
            'note_rejet' => 'required|string|max:500',
        ]);

        $stage->update([
            'statut_validation' => StatutValidation::Rejete->value,
            'note_rejet'        => $request->note_rejet,
        ]);

        return back()->with('success', "Stage rejeté — le motif est visible par l'étudiant sur son tableau de bord.");
    }
}
