<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RechercheSiretTrait;
use App\Mail\BienvenueMaitreDeStage;
use App\Models\ConfigurationStage;
use App\Models\ConventionPapier;
use App\Models\Employe;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminStageController extends Controller
{
    use RechercheSiretTrait;

    public function index(Request $request)
    {
        $anneeActive       = Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));
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
        $classe     = $request->get('classe', 'tous');  // tous | sio1 | sio2
        $filtre     = $request->get('filtre', 'tous');  // tous | sans_stage | a_faire_signer | en_attente | validee

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
        $query = User::role('Etudiant')
            ->whereIn('statut', ['actif'])
            ->whereIn('promo', $classeFiltre ? [$classeFiltre] : [$promoSio1, $promoSio2])
            ->with([
                'stages' => fn($q) => $q
                    ->with(['entreprise', 'maitreDeStage'])
                    ->withCount('journalEntries')
                    // Restreindre les stages chargés au filtre actif → cohérence couleurs
                    ->when(in_array($filtre, ['a_faire_signer', 'en_attente', 'validee']),
                        fn($s) => $s->where('statut_convention', $filtre))
                    ->orderBy('date_debut', 'desc'),
                'conventionPapier',
            ]);

        // Filtre sur le statut de la convention (indépendant de la classe)
        if ($filtre === 'sans_stage') {
            $query->whereDoesntHave('stages')->whereDoesntHave('conventionPapier');
        } elseif ($filtre === 'hors_app') {
            $query->whereHas('conventionPapier', fn($cp) => $cp->where('statut', 'hors_app'))
                  ->whereDoesntHave('stages');
        } elseif (in_array($filtre, ['a_faire_signer', 'en_attente', 'validee'])) {
            $query->where(function ($q) use ($filtre) {
                $q->whereHas('stages', fn($s) => $s->where('statut_convention', $filtre))
                  ->orWhere(function ($q2) use ($filtre) {
                      $q2->whereHas('conventionPapier', fn($cp) => $cp->where('statut', $filtre))
                         ->whereDoesntHave('stages');
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
        $promos = $classeFiltre ? [$classeFiltre] : [$promoSio1, $promoSio2];
        $baseCount = fn() => User::role('Etudiant')->whereIn('statut', ['actif'])->whereIn('promo', $promos);

        $compteurs = [
            'tous'           => ($baseCount)()->count(),
            'sans_stage'     => ($baseCount)()->whereDoesntHave('stages')->whereDoesntHave('conventionPapier')->count(),
            'hors_app'       => ($baseCount)()
                                    ->whereHas('conventionPapier', fn($cp) => $cp->where('statut', 'hors_app'))
                                    ->whereDoesntHave('stages')
                                    ->count(),
            'a_faire_signer' => ($baseCount)()->where(fn($q) =>
                $q->whereHas('stages', fn($s) => $s->where('statut_convention', 'a_faire_signer'))
                  ->orWhere(fn($q2) => $q2->whereHas('conventionPapier', fn($cp) => $cp->where('statut', 'a_faire_signer'))->whereDoesntHave('stages'))
            )->count(),
            'en_attente'     => ($baseCount)()->where(fn($q) =>
                $q->whereHas('stages', fn($s) => $s->where('statut_convention', 'en_attente'))
                  ->orWhere(fn($q2) => $q2->whereHas('conventionPapier', fn($cp) => $cp->where('statut', 'en_attente'))->whereDoesntHave('stages'))
            )->count(),
            'validee'        => ($baseCount)()->where(fn($q) =>
                $q->whereHas('stages', fn($s) => $s->where('statut_convention', 'validee'))
                  ->orWhere(fn($q2) => $q2->whereHas('conventionPapier', fn($cp) => $cp->where('statut', 'validee'))->whereDoesntHave('stages'))
            )->count(),
        ];

        return view('admin.stages.index', compact(
            'etudiants', 'tuteurs', 'annees', 'anneeSelectionnee', 'anneeActive',
            'syInt', 'filtre', 'classe', 'classeStr', 'compteurs'
        ));
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

        $annee = Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));

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
    public function creer(Request $request)
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

        if (Stage::where('etudiant_id', $etudiant->id)->where('classe', $etudiant->classe_courante)->exists()) {
            return back()->withInput()->withErrors(
                "Cet étudiant a déjà un stage enregistré pour cette année. Modifie-le directement depuis « Tous les stages »."
            );
        }

        $entreprise = \App\Models\Entreprise::findOrFail($validated['entreprise_id']);
        $dateDebut  = \Carbon\Carbon::parse($validated['date_debut']);
        $dateFin    = $dateDebut->copy()->addWeeks((int) $validated['duree']);

        $convPapier = ConventionPapier::where('etudiant_id', $etudiant->id)->first();
        $statutConvention = match ($convPapier?->statut) {
            'hors_app' => 'en_attente',
            null       => 'a_faire_signer',
            default    => $convPapier->statut,
        };

        Stage::create([
            'titre'              => "Stage chez {$entreprise->raison_sociale}",
            'entreprise_id'      => $entreprise->id,
            'maitre_de_stage_id' => $validated['maitre_de_stage_id'],
            'etudiant_id'        => $etudiant->id,
            'classe'             => $etudiant->classe_courante,
            'date_debut'         => $dateDebut,
            'date_fin'           => $dateFin,
            'statut_convention'  => $statutConvention,
            'statut_validation'  => $convPapier ? 'valide' : 'en_attente',
        ]);

        $convPapier?->delete();

        return redirect()->route('admin.dashboard')
            ->with('success', "Stage créé pour {$etudiant->prenom} {$etudiant->nom}.");
    }

    public function assign(Request $request, Stage $stage)
    {
        $request->validate([
            'maitre_de_stage_id' => 'nullable|exists:employes,id',
        ]);

        $stage->update(['maitre_de_stage_id' => $request->maitre_de_stage_id]);

        return back()->with('success', 'Maître de stage assigné.');
    }

    public function updateConvention(Stage $stage, string $statut)
    {
        $valides = ['a_faire_signer', 'en_attente', 'validee'];

        abort_unless(in_array($statut, $valides), 422);

        $data = ['statut_convention' => $statut];

        // Déposer la convention pour signature = valider implicitement le stage
        if ($statut === 'en_attente' && $stage->statut_validation === 'en_attente') {
            $data['statut_validation'] = 'valide';
            $data['note_rejet']        = null;
        }

        $stage->update($data);

        // Mail de bienvenue au maître de stage — une seule fois, à la validation de la convention
        if ($statut === 'validee' && $stage->mail_bienvenue_envoye_at === null) {
            $stage->load(['maitreDeStage', 'etudiant.tuteur']);
            $employe = $stage->maitreDeStage;
            if ($employe?->email) {
                Mail::to($employe->email)->send(
                    new BienvenueMaitreDeStage($employe, $stage->etudiant, $stage->etudiant->tuteur)
                );
                $stage->update(['mail_bienvenue_envoye_at' => now()]);
            }
        }

        return back();
    }

    /**
     * Crée un placeholder de convention quand un étudiant a remis sa convention
     * directement sans passer par l'application (statut initial "hors app").
     */
    public function marquerHorsAppli(User $user)
    {
        ConventionPapier::updateOrCreate(
            ['etudiant_id' => $user->id],
            ['statut' => 'hors_app']
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
        $precedent = [
            'en_attente' => 'a_faire_signer',
            'validee'      => 'en_attente',
        ];

        $prev = $precedent[$stage->statut_convention] ?? null;

        if ($prev) {
            $data = ['statut_convention' => $prev];
            if ($prev === 'a_faire_signer') {
                $data['statut_validation'] = 'en_attente';
            }
            $stage->update($data);
        }

        return back();
    }

    public function valider(Stage $stage)
    {
        $stage->update([
            'statut_validation' => 'valide',
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
            'statut_validation' => 'rejete',
            'note_rejet'        => $request->note_rejet,
        ]);

        return back()->with('success', "Stage rejeté — l'étudiant a été notifié.");
    }
}
