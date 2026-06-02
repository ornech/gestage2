<?php

namespace App\Http\Controllers;

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
     * Crée un stage placeholder quand un étudiant a remis sa convention
     * directement sans passer par l'application.
     */
    public function marquerHorsAppli(User $user)
    {
        // La convention papier est remise par l'étudiant au prof :
        // l'employeur a déjà signé → on démarre directement à "en_attente" (prête à déposer à la direction)
        ConventionPapier::updateOrCreate(
            ['etudiant_id' => $user->id],
            ['statut' => 'en_attente']
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
