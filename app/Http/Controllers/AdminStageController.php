<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationStage;
use App\Models\Employe;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\Request;

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

        $query = Stage::with(['etudiant', 'entreprise', 'maitreDeStage']);

        // Recherche globale : ignore les filtres d'année
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('etudiant', fn($s) =>
                        $s->where('nom', 'like', "%$search%")->orWhere('prenom', 'like', "%$search%"))
                  ->orWhereHas('entreprise', fn($s) =>
                        $s->where('raison_sociale', 'like', "%$search%"));
            });

        } elseif ($filtre === 'tout') {
            // Tout afficher sans restriction d'année

        } else {
            // Filtrage par année scolaire sélectionnée
            $query->whereHas('etudiant', fn($q) => $q->whereIn('promo', [$promoSio1, $promoSio2]));

            if ($filtre === 'sio1') {
                $query->whereHas('etudiant', fn($q) => $q->where('promo', $promoSio1));
            } elseif ($filtre === 'sio2') {
                $query->whereHas('etudiant', fn($q) => $q->where('promo', $promoSio2));
            } elseif ($filtre === 'sans_maitre') {
                $query->whereNull('maitre_de_stage_id');
            }
        }

        $stages  = $query->orderBy('date_debut')->paginate(20)->withQueryString();
        $tuteurs = Employe::orderBy('nom')->get();

        $stats = [
            'total'       => Stage::whereHas('etudiant', fn($q) => $q->whereIn('promo', [$promoSio1, $promoSio2]))->count(),
            'sio1'        => Stage::whereHas('etudiant', fn($q) => $q->where('promo', $promoSio1))->count(),
            'sio2'        => Stage::whereHas('etudiant', fn($q) => $q->where('promo', $promoSio2))->count(),
            'sans_maitre' => Stage::whereNull('maitre_de_stage_id')
                                ->whereHas('etudiant', fn($q) => $q->whereIn('promo', [$promoSio1, $promoSio2]))
                                ->count(),
        ];

        return view('admin.stages.index', compact(
            'stages', 'tuteurs', 'annees', 'anneeSelectionnee', 'anneeActive',
            'syInt', 'filtre', 'stats'
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
