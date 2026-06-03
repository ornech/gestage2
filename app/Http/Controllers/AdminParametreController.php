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
            // Début = lundi, fin = vendredi de la N-ième semaine → +N×7j −3j
            $fin      = $debut ? \Carbon\Carbon::parse($debut)->addDays($semaines * 7 - 3)->format('Y-m-d') : null;

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

    public function convention()
    {
        $etablissement = [
            'nom'      => Parametre::get('convention_etablissement_nom',   config('app.name')),
            'proviseur_nom'   => Parametre::get('convention_proviseur_nom',   ''),
            'proviseur_titre' => Parametre::get('convention_proviseur_titre', 'Proviseur(e)'),
            'adresse'  => Parametre::get('convention_etablissement_adresse',  ''),
            'bp'       => Parametre::get('convention_etablissement_bp',       ''),
            'cp_ville' => Parametre::get('convention_etablissement_cp_ville', ''),
            'tel'      => Parametre::get('convention_etablissement_tel',      ''),
            'mel'      => Parametre::get('convention_etablissement_mel',      ''),
            'lieu'     => Parametre::get('convention_lieu',                   ''),
        ];

        // Charger tous les articles depuis Parametre (avec valeurs par défaut vides)
        $cles = [
            'conv_art1','conv_art2','conv_art3','conv_art4','conv_art5','conv_art6',
            'conv_art7','conv_art8','conv_art9','conv_art10','conv_art11',
            'conv_part1','conv_part2',
        ];
        $articles = [];
        foreach ($cles as $cle) {
            $articles[$cle] = [
                'titre' => Parametre::get($cle . '_titre', ''),
                'corps' => Parametre::get($cle . '_corps', ''),
            ];
        }

        return view('admin.parametres.convention', compact('etablissement', 'articles', 'cles'));
    }

    public function updateConvention(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'etablissement.nom'            => 'nullable|string|max:200',
            'etablissement.proviseur_nom'  => 'nullable|string|max:100',
            'etablissement.proviseur_titre'=> 'nullable|string|max:100',
            'etablissement.adresse'        => 'nullable|string|max:200',
            'etablissement.bp'             => 'nullable|string|max:50',
            'etablissement.cp_ville'       => 'nullable|string|max:100',
            'etablissement.tel'            => 'nullable|string|max:20',
            'etablissement.mel'            => 'nullable|email|max:100',
            'etablissement.lieu'           => 'nullable|string|max:100',
        ]);

        $map = [
            'nom'            => 'convention_etablissement_nom',
            'proviseur_nom'  => 'convention_proviseur_nom',
            'proviseur_titre'=> 'convention_proviseur_titre',
            'adresse'        => 'convention_etablissement_adresse',
            'bp'             => 'convention_etablissement_bp',
            'cp_ville'       => 'convention_etablissement_cp_ville',
            'tel'            => 'convention_etablissement_tel',
            'mel'            => 'convention_etablissement_mel',
            'lieu'           => 'convention_lieu',
        ];
        foreach ($map as $champ => $cle) {
            Parametre::set($cle, $request->input("etablissement.{$champ}", ''));
        }

        // Articles
        foreach ($request->input('articles', []) as $cle => $data) {
            Parametre::set($cle . '_titre', $data['titre'] ?? '');
            Parametre::set($cle . '_corps', $data['corps'] ?? '');
        }

        return back()->with('success', 'Paramètres de la convention mis à jour.');
    }
}
