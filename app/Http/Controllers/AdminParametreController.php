<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationStage;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Http\Request;

class AdminParametreController extends Controller
{
    public function index(Request $request)
    {
        $anneeActive      = Parametre::get('annee_scolaire', '2025-2026');
        $anneeSelectionnee = $request->get('annee', $anneeActive);

        // Toutes les années configurées + l'année active (toujours présente)
        $annees = ConfigurationStage::toutesLesAnnees()
            ->prepend($anneeActive)
            ->unique()
            ->sortDesc()
            ->values();

        $configs = ConfigurationStage::forAnnee($anneeSelectionnee);
        $profs   = User::role('Professeur')->orderBy('nom')->get();

        return view('admin.parametres.index', compact(
            'annees', 'anneeSelectionnee', 'anneeActive', 'configs', 'profs'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'annee_scolaire'         => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'sio1.prof_principal_id' => 'nullable|exists:users,id',
            'sio1.stage_date_debut'  => 'nullable|date',
            'sio1.duree_semaines'    => 'nullable|integer|min:1|max:26',
            'sio2.prof_principal_id' => 'nullable|exists:users,id',
            'sio2.stage_date_debut'  => 'nullable|date',
            'sio2.duree_semaines'    => 'nullable|integer|min:1|max:26',
        ]);

        $annee = $request->annee_scolaire;

        foreach (['SIO1' => 'sio1', 'SIO2' => 'sio2'] as $classe => $key) {
            $debut    = $request->input("{$key}.stage_date_debut") ?: null;
            $semaines = (int) ($request->input("{$key}.duree_semaines") ?: 6);
            $fin      = $debut ? \Carbon\Carbon::parse($debut)->addWeeks($semaines)->format('Y-m-d') : null;

            ConfigurationStage::updateOrCreate(
                ['annee_scolaire' => $annee, 'classe' => $classe],
                [
                    'prof_principal_id' => $request->input("{$key}.prof_principal_id") ?: null,
                    'stage_date_debut'  => $debut,
                    'stage_date_fin'    => $fin,
                ]
            );
        }

        return redirect()
            ->route('admin.parametres.index', ['annee' => $annee])
            ->with('success', "Configuration {$annee} enregistrée.");
    }

    public function nouvelleAnnee(Request $request)
    {
        $request->validate([
            'annee_scolaire' => ['required', 'regex:/^\d{4}-\d{4}$/', 'unique:configurations_stage,annee_scolaire'],
        ]);

        // Créer les deux entrées vides pour la nouvelle année
        foreach (['SIO1', 'SIO2'] as $classe) {
            ConfigurationStage::create([
                'annee_scolaire' => $request->annee_scolaire,
                'classe'         => $classe,
            ]);
        }

        // Mettre à jour l'année scolaire active
        Parametre::set('annee_scolaire', $request->annee_scolaire);

        return redirect()
            ->route('admin.parametres.index', ['annee' => $request->annee_scolaire])
            ->with('success', "Année {$request->annee_scolaire} créée et activée.");
    }

    public function setActive(Request $request)
    {
        $request->validate([
            'annee_scolaire' => ['required', 'regex:/^\d{4}-\d{4}$/'],
        ]);

        Parametre::set('annee_scolaire', $request->annee_scolaire);

        return redirect()
            ->route('admin.parametres.index', ['annee' => $request->annee_scolaire])
            ->with('success', "Année {$request->annee_scolaire} définie comme année active.");
    }

    public function toggleSpe()
    {
        $current = Parametre::get('spe_assignments_open', '0');
        Parametre::set('spe_assignments_open', $current === '1' ? '0' : '1');
        $etat = $current === '1' ? 'fermée' : 'ouverte';
        return back()->with('success', "Affectation des spécialités {$etat}.");
    }
}
