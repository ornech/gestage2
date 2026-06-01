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

        // ── Vue "Anciennes promos" ───────────────────────────────────────
        if ($filtre === 'anciens') {
            $users = User::role('Etudiant')
                ->where(function ($q) use ($syActif) {
                    // Diplômés (promo <= année active) OU démissionnaires
                    $q->where('promo', '<=', $syActif)
                      ->orWhere('statut', 'demissionnaire');
                })
                ->orderByDesc('promo')
                ->orderBy('nom')
                ->paginate(30)
                ->withQueryString();

            $sy = $syActif;
            return view('admin.users.anciens', compact('users', 'sy'));
        }

        // ── Requête standard : actifs de l'année en cours ────────────────
        $query = User::role('Etudiant')
                     ->whereIn('statut', ['actif']);

        // Filtre par classe (depuis le menu navbar ?classe=SIO1/SIO2)
        if ($classeParam === 'SIO1') {
            $query->where('promo', $promoSio1);
        } elseif ($classeParam === 'SIO2') {
            $query->where('promo', $promoSio2);
        } else {
            $query->whereIn('promo', [$promoSio1, $promoSio2]);
        }

        // Filtre par spécialité
        if ($request->filled('spe')) {
            $spe = $request->spe;
            if ($spe === 'aucune') {
                $query->whereNull('spe')->orWhere('spe', '');
            } else {
                $query->where('spe', $spe);
            }
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

    public function create()
    {
        $annee       = \App\Models\Parametre::get('annee_scolaire', '2025-2026');
        $currentYear = (int) explode('-', $annee)[0];
        $tuteurs     = User::role('Professeur')->orderBy('nom')->get();

        return view('admin.users.create', compact('tuteurs', 'currentYear'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'role'      => 'required|in:Etudiant,Professeur,Administrateur',
            'classe'    => 'nullable|in:SIO1,SIO2',
            'promo'     => 'nullable|integer|min:2020|max:2040',
            'spe'       => 'nullable|in:SLAM,SISR',
            'tuteur_id' => 'nullable|exists:users,id',
        ]);

        $user = User::create([
            'nom'       => strtoupper($request->nom),
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'password'  => bcrypt('achanger'),
            'classe'    => $request->classe,
            'promo'     => $request->promo,
            'spe'       => $request->spe,
            'tuteur_id' => $request->tuteur_id,
            'statut'    => 'actif',
            'force_password_change' => true,
        ]);

        $user->assignRole($request->role);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', "{$user->prenom} {$user->nom} créé(e). Un mot de passe devra être défini à la première connexion.");
    }

    public function professeurs()
    {
        $professeurs = User::role('Professeur')
            ->with('roles')
            ->orderBy('nom')
            ->get();

        return view('admin.users.professeurs', compact('professeurs'));
    }

    public function resetPasswordForm()
    {
        $users = User::orderBy('nom')->get(['id', 'nom', 'prenom', 'email']);
        return view('admin.users.reset-password', compact('users'));
    }

    public function resetPassword(\Illuminate\Http\Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::findOrFail($request->user_id);

        $user->update([
            'password'              => bcrypt('achanger'),
            'force_password_change' => true,
        ]);

        return back()->with('success', "Mot de passe de {$user->prenom} {$user->nom} réinitialisé. Il devra se connecter avec « achanger » et en choisir un nouveau.");
    }

    public function toggleAdmin(User $user)
    {
        if ($user->hasRole('Administrateur')) {
            $user->removeRole('Administrateur');
            $msg = "{$user->prenom} {$user->nom} n'est plus administrateur.";
        } else {
            $user->assignRole('Administrateur');
            $msg = "{$user->prenom} {$user->nom} est maintenant administrateur.";
        }

        return back()->with('success', $msg);
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
