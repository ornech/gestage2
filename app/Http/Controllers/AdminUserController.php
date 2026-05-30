<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationStage;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        // Année scolaire : depuis Parametre, ou auto (septembre=nouvelle année)
        $mois      = (int) date('m');
        $anneeCalc = $mois >= 9
            ? date('Y').'-'.((int) date('Y') + 1)
            : ((int) date('Y') - 1).'-'.date('Y');

        $anneeActive       = Parametre::get('annee_scolaire', $anneeCalc);
        $anneeSelectionnee = $request->get('annee', $anneeActive);
        $syInt             = (int) explode('-', $anneeSelectionnee)[0];

        // Paramètre de classe : vient du menu (?classe=SIO1) ou de l'ancien filtre
        $classeParam = $request->get('classe', 'tous'); // SIO1 | SIO2 | tous
        $filtre      = $request->get('filtre', 'annee');

        // ── Années disponibles (depuis les promos + configurations) ──────
        $annees = User::role('Etudiant')
            ->whereNotNull('promo')
            ->pluck('promo')
            ->flatMap(fn($p) => [($p - 2).'-'.($p - 1), ($p - 1).'-'.$p])
            ->merge(ConfigurationStage::toutesLesAnnees())
            ->prepend($anneeActive)
            ->unique()
            ->sortDesc()
            ->values();

        // ── Statistiques pour l'année sélectionnée ───────────────────────
        $promoSio1 = $syInt + 2;
        $promoSio2 = $syInt + 1;

        $etudiantsAnnee = User::role('Etudiant')
            ->whereIn('promo', [$promoSio1, $promoSio2])
            ->get();

        $stats = [
            'sio1'           => $etudiantsAnnee->where('promo', $promoSio1)->whereIn('statut', ['actif', 'redoublant'])->count(),
            'sio2'           => $etudiantsAnnee->where('promo', $promoSio2)->whereIn('statut', ['actif', 'redoublant'])->count(),
            'redoublants'    => $etudiantsAnnee->where('statut', 'redoublant')->count(),
            'demissionnaires'=> User::role('Etudiant')->where('statut', 'demissionnaire')->count(),
        ];

        // ── Requête selon le filtre ──────────────────────────────────────
        $query = User::role('Etudiant');

        // La recherche est TOUJOURS globale (tous les étudiants, toutes années)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom',    'like', "%$search%")
                  ->orWhere('prenom', 'like', "%$search%")
                  ->orWhere('email',  'like', "%$search%");
            })->orderBy('nom');

        } elseif ($filtre === 'tout') {
            // Tout afficher : tous étudiants sans restriction
            $query->orderBy('promo')->orderBy('nom');

        } else {
            $query->whereIn('statut', ['actif', 'redoublant']);

            // Filtre par classe — via ?classe=SIO1 (navbar) ou ?filtre=sio1 (ancien)
            $classeEffective = match(true) {
                $classeParam === 'SIO1' || $filtre === 'sio1' => 'SIO1',
                $classeParam === 'SIO2' || $filtre === 'sio2' => 'SIO2',
                default => null,
            };

            if ($classeEffective === 'SIO1') {
                $query->where('promo', $promoSio1);
            } elseif ($classeEffective === 'SIO2') {
                $query->where('promo', $promoSio2);
            } else {
                $query->whereIn('promo', [$promoSio1, $promoSio2]);
            }

            $query->orderBy('promo')->orderBy('nom');
        }

        $users = $query->paginate(30)->withQueryString();

        $syActif = (int) explode('-', $anneeActive)[0];

        return view('admin.users.index', compact(
            'users', 'annees', 'anneeSelectionnee', 'anneeActive',
            'filtre', 'syInt', 'syActif', 'stats'
        ));
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $tuteurs     = User::role('Professeur')->orderBy('nom')->get();
        $annee       = \App\Models\Parametre::get('annee_scolaire', '2025-2026');
        $currentYear = (int) explode('-', $annee)[0];

        return view('admin.users.edit', compact('user', 'tuteurs', 'currentYear'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,'.$user->id,
            'classe'    => 'nullable|in:SIO1,SIO2',
            'promo'     => 'nullable|integer',
            'spe'       => 'nullable|in:SLAM,SISR,',
            'tuteur_id' => 'nullable|exists:users,id',
        ]);

        $user->update($request->only('nom', 'prenom', 'email', 'classe', 'promo', 'spe', 'tuteur_id'));

        return redirect()->back()->with('success', 'Étudiant mis à jour.');
    }

    public function updateStatut(Request $request, User $user)
    {
        $request->validate([
            'statut' => 'required|in:actif,redoublant,demissionnaire',
        ]);

        $user->update(['statut' => $request->statut]);

        return back()->with('success', "Statut de {$user->prenom} {$user->nom} mis à jour.");
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Étudiant supprimé.');
    }

    public function anonymize(User $user)
    {
        $user->update([
            'nom'       => 'Anonyme',
            'prenom'    => 'Anonyme',
            'email'     => 'anonyme_'.$user->id.'@supprime.invalid',
            'telephone' => null,
        ]);
        return redirect()->route('admin.users.index')->with('success', 'Étudiant anonymisé.');
    }

    public function assignTuteur(Request $request, User $user)
    {
        $request->validate(['tuteur_id' => 'nullable|exists:users,id']);
        $user->update(['tuteur_id' => $request->tuteur_id]);
        return back()->with('success', 'Tuteur assigné.');
    }
}
