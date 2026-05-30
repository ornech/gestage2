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
        // Année scolaire courante uniquement (pas d'anticipation)
        $mois        = (int) date('m');
        $anneeCalc   = $mois >= 9
            ? date('Y').'-'.((int) date('Y') + 1)
            : ((int) date('Y') - 1).'-'.date('Y');
        $anneeActive = Parametre::get('annee_scolaire', $anneeCalc);

        // Années disponibles : uniquement passées + actuelle (pas de futur)
        $syActif = (int) explode('-', $anneeActive)[0];
        $annees = User::role('Etudiant')
            ->whereNotNull('promo')
            ->pluck('promo')
            ->flatMap(fn($p) => [($p - 2).'-'.($p - 1), ($p - 1).'-'.$p])
            ->merge(ConfigurationStage::toutesLesAnnees())
            ->prepend($anneeActive)
            ->unique()
            ->filter(fn($a) => (int) explode('-', $a)[0] <= $syActif) // exclure les années futures
            ->sortDesc()
            ->values();

        // Année sélectionnée (uniquement courante ou passée)
        $anneeSelectionnee = $request->get('annee', $anneeActive);
        if ((int) explode('-', $anneeSelectionnee)[0] > $syActif) {
            $anneeSelectionnee = $anneeActive; // forcer sur l'année active si URL trafiquée
        }
        $syInt = (int) explode('-', $anneeSelectionnee)[0];

        $classeParam = $request->get('classe', 'tous');
        $filtre      = $request->get('filtre', 'annee');

        $promoSio1 = $syInt + 2;
        $promoSio2 = $syInt + 1;

        // Stats
        $baseActifs = User::role('Etudiant')->where('statut', 'actif');
        if ($classeParam === 'SIO1') {
            $baseActifs->where('promo', $promoSio1);
        } elseif ($classeParam === 'SIO2') {
            $baseActifs->where('promo', $promoSio2);
        } else {
            $baseActifs->whereIn('promo', [$promoSio1, $promoSio2]);
        }

        $stats = [
            'actifs'         => $baseActifs->count(),
            'demissionnaires'=> User::role('Etudiant')->where('statut', 'demissionnaire')->count(),
        ];

        // ── Requête selon le filtre ──────────────────────────────────────
        $query = User::role('Etudiant')
                     ->whereIn('statut', ['actif']); // toujours actifs uniquement

        // Filtre par classe (depuis le menu navbar ?classe=SIO1/SIO2)
        if ($classeParam === 'SIO1') {
            $query->where('promo', $promoSio1);
        } elseif ($classeParam === 'SIO2') {
            $query->where('promo', $promoSio2);
        } else {
            $query->whereIn('promo', [$promoSio1, $promoSio2]);
        }

        // Recherche par nom/prénom/email (globale mais toujours actifs)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom',    'like', "%$search%")
                  ->orWhere('prenom', 'like', "%$search%")
                  ->orWhere('email',  'like', "%$search%");
            });
        }

        $query->orderBy('promo')->orderBy('nom');

        $users = $query->paginate(30)->withQueryString();

        $syActif = (int) explode('-', $anneeActive)[0];

        return view('admin.users.index', compact(
            'users', 'annees', 'anneeSelectionnee', 'anneeActive',
            'filtre', 'classeParam', 'syInt', 'syActif', 'stats'
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

        $user->load([
            'stages'          => fn($q) => $q->with(['entreprise', 'maitreDeStage'])->orderBy('date_debut', 'desc'),
            'conventionPapier',
        ]);

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

        // Rediriger vers la liste de la classe de l'étudiant
        $classe = $request->classe ?? $user->fresh()->classe;
        return redirect()
            ->route('admin.users.index', array_filter(['classe' => $classe]))
            ->with('success', "{$user->prenom} {$user->nom} mis à jour.");
    }

    public function updateStatut(Request $request, User $user)
    {
        $request->validate([
            'statut' => 'required|in:actif,demissionnaire',
        ]);

        $user->update(['statut' => $request->statut]);

        return back()->with('success', "Statut de {$user->prenom} {$user->nom} mis à jour.");
    }

    public function redoubler(User $user)
    {
        if (!$user->promo) {
            return back()->withErrors("Promotion non définie pour cet étudiant.");
        }

        $anciennePromo = $user->promo;
        $user->update([
            'promo'  => $anciennePromo + 1,
            'statut' => 'actif',
        ]);

        return back()->with('success',
            "{$user->prenom} {$user->nom} redouble — promo {$anciennePromo} → " . ($anciennePromo + 1) . "."
        );
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
