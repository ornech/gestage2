<?php

namespace App\Http\Controllers;

use App\Enums\StatutEtudiant;
use App\Models\ConfigurationStage;
use App\Models\Parametre;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'actifs'         => (clone $baseActifs)->count(),
            'demissionnaires'=> User::role('Etudiant')->where('statut', 'demissionnaire')->count(),
            'slam'           => (clone $baseActifs)->where('spe', 'SLAM')->count(),
            'sisr'           => (clone $baseActifs)->where('spe', 'SISR')->count(),
        ];

        // ── Vue "Anciennes promos" ───────────────────────────────────────
        if ($filtre === 'anciens') {
            $promos = User::role('Etudiant')
                ->where(function ($q) use ($syActif) {
                    $q->where('promo', '<=', $syActif)
                      ->orWhereIn('statut', ['demissionnaire', 'diplome']);
                })
                ->whereNotNull('promo')
                ->pluck('promo')
                ->unique()
                ->sortDesc()
                ->values();

            $promoFiltre = $request->get('promo');

            $query = User::role('Etudiant')
                ->where(function ($q) use ($syActif) {
                    $q->where('promo', '<=', $syActif)
                      ->orWhereIn('statut', ['demissionnaire', 'diplome']);
                });

            if ($promoFiltre) {
                $query->where('promo', $promoFiltre);
            }

            $users = $query->orderByDesc('promo')
                ->orderBy('nom')
                ->paginate(30)
                ->withQueryString();

            $sy = $syActif;
            return view('admin.users.anciens', compact('users', 'sy', 'promos', 'promoFiltre'));
        }

        // ── Requête standard : actifs de l'année en cours ────────────────
        // Les diplômés validés restent consultables dans l'historique des années passées
        $query = User::role('Etudiant')
                     ->whereIn('statut', ['actif', 'diplome']);

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
        $annee       = \App\Models\Parametre::anneeActive();
        $currentYear = (int) explode('-', $annee)[0];
        $tuteurs     = User::role('Professeur')->orderBy('nom')->get();

        return view('admin.users.create', compact('tuteurs', 'currentYear'));
    }

    public function store(Request $request, InvitationService $invitations)
    {
        // Un Professeur ne peut créer que des comptes Étudiant ou Professeur :
        // seul un Administrateur peut accorder le rôle Administrateur (cohérent avec toggle-admin).
        $rolesAutorises = auth()->user()->hasRole('Administrateur')
            ? ['Etudiant', 'Professeur', 'Administrateur']
            : ['Etudiant', 'Professeur'];

        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'role'      => ['required', Rule::in($rolesAutorises)],
            'classe'    => 'nullable|in:SIO1,SIO2',
            'promo'     => 'nullable|integer|min:2020|max:2040',
            'spe'       => 'nullable|in:SLAM,SISR',
            'tuteur_id' => 'nullable|exists:users,id',
        ]);

        // Calcul automatique de la promo depuis la classe
        $annee       = \App\Models\Parametre::anneeActive();
        $currentYear = (int) explode('-', $annee)[0];
        $promo = match($request->classe) {
            'SIO1' => $currentYear + 2,
            'SIO2' => $currentYear + 1,
            default => $request->promo,
        };

        $user = User::create([
            'nom'       => strtoupper($request->nom),
            'prenom'    => $request->prenom,
            'email'     => $request->email,
            'password'  => bcrypt(Str::random(64)), // remplacé par l'invitation ci-dessous
            'classe'    => $request->classe,
            'promo'     => $promo,
            'spe'       => $request->spe,
            'tuteur_id' => $request->tuteur_id,
            'statut'    => 'actif',
            'force_password_change' => true,
        ]);

        $user->assignRole($request->role);

        $invitation = $invitations->inviter($user, 0, ! $request->boolean('sans_mail'));

        return redirect()
            ->route('admin.users.edit', $user)
            ->with($this->confirmationInvitation($user, $invitation, "{$user->prenom} {$user->nom} créé(e)."));
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

    public function resetPassword(\Illuminate\Http\Request $request, InvitationService $invitations)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::findOrFail($request->user_id);

        // L'ancien mot de passe est invalidé ; l'utilisateur en choisit un nouveau via un lien par mail.
        $invitation = $invitations->inviter($user, 0, ! $request->boolean('sans_mail'));

        return back()->with($this->confirmationInvitation($user, $invitation, "Mot de passe de {$user->prenom} {$user->nom} invalidé."));
    }

    /**
     * Message de confirmation après une invitation ; en « mode lien », le lien est transmis à la vue
     * (jamais écrit dans les logs) pour que l'administrateur le copie.
     *
     * @return array<string, mixed>
     */
    private function confirmationInvitation(User $user, array $invitation, string $debut): array
    {
        if ($invitation['mail_envoye']) {
            return ['success' => "{$debut} Un email lui a été envoyé pour choisir son mot de passe."];
        }

        return [
            'success'         => "{$debut} Aucun email envoyé ({$invitation['raison']}) : transmettez-lui le lien ci-dessous.",
            'lien_invitation' => [
                'nom'    => "{$user->prenom} {$user->nom}",
                'email'  => $user->email,
                'lien'   => $invitation['lien'],
                'raison' => $invitation['raison'],
            ],
        ];
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
        abort_unless($user->hasRole('Etudiant'), 404);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        abort_unless($user->hasRole('Etudiant'), 404);

        $tuteurs     = User::role('Professeur')->orderBy('nom')->get();
        $annee       = \App\Models\Parametre::anneeActive();
        $currentYear = (int) explode('-', $annee)[0];

        $user->load([
            'stages'          => fn($q) => $q->with(['entreprise', 'maitreDeStage'])->orderBy('date_debut', 'desc'),
            'conventionPapier',
        ]);

        return view('admin.users.edit', compact('user', 'tuteurs', 'currentYear', 'annee'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->hasRole('Etudiant'), 404);

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
        abort_unless($user->hasRole('Etudiant'), 404);

        $request->validate([
            'statut' => ['required', Rule::enum(StatutEtudiant::class)],
        ]);

        // Validation du diplôme : on trace qui et quand ; tout autre statut efface la trace.
        $diplome = $request->statut === StatutEtudiant::Diplome->value;

        $user->update([
            'statut'      => $request->statut,
            'diplome_at'  => $diplome ? now() : null,
            'diplome_par' => $diplome ? $request->user()->id : null,
        ]);

        return back()->with('success', "Statut de {$user->prenom} {$user->nom} mis à jour.");
    }

    public function redoubler(User $user)
    {
        abort_unless($user->hasRole('Etudiant'), 404);

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

    /**
     * Traite en une fois plusieurs étudiants actifs sans classe (diplômés ou redoublants).
     * Seuls ces étudiants sont concernés : un identifiant d'un autre étudiant est ignoré.
     */
    public function actionGroupee(Request $request)
    {
        $data = $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer', 'exists:users,id'],
            'action' => ['required', Rule::in(['diplome', 'redouble'])],
        ], ['ids.required' => 'Sélectionne au moins un étudiant.']);

        $etudiants = User::role('Etudiant')->where('statut', 'actif')->sansClasse()
            ->whereIn('id', $data['ids'])->get();

        if ($data['action'] === 'diplome') {
            foreach ($etudiants as $etudiant) {
                $etudiant->update([
                    'statut'      => StatutEtudiant::Diplome->value,
                    'diplome_at'  => now(),
                    'diplome_par' => $request->user()->id,
                ]);
            }

            return back()->with('success', $etudiants->count().' étudiant(s) marqué(s) diplômé(s).');
        }

        $sansPromo = $etudiants->whereNull('promo');
        foreach ($etudiants->whereNotNull('promo') as $etudiant) {
            $etudiant->update(['promo' => $etudiant->promo + 1]);
        }

        $traites         = $etudiants->count() - $sansPromo->count();
        $encoreSansClasse = $etudiants->whereNotNull('promo')->filter(fn($e) => $e->fresh()->classe_courante === null)->count();

        $message = "{$traites} étudiant(s) passé(s) en promo + 1.";
        if ($encoreSansClasse > 0) {
            $message .= " {$encoreSansClasse} n'ont toujours pas de classe (promo très ancienne) : à traiter encore.";
        }
        if ($sansPromo->isNotEmpty()) {
            $message .= " {$sansPromo->count()} sans promo ignoré(s) : à corriger depuis leur fiche.";
        }

        return back()->with('success', $message);
    }

    public function destroy(User $user)
    {
        // Les stages sont conservés sans nom (clé étrangère « mise à null ») ; le journal, personnel, est supprimé avec le compte
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Étudiant supprimé : ses stages sont conservés sans nom.');
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
        abort_unless($user->hasRole('Etudiant'), 404);

        $request->validate(['tuteur_id' => 'nullable|exists:users,id']);
        $user->update(['tuteur_id' => $request->tuteur_id]);
        return back()->with('success', 'Tuteur assigné.');
    }

    public function nettoyage()
    {
        // Comptes avec email placeholder
        $importLocal = User::where('email', 'like', '%@import.local')
            ->orderBy('nom')
            ->get();

        // Détection doublons (même nom normalisé + prénom + promo)
        $tous = User::role('Etudiant')
            ->whereIn('statut', ['actif', 'demissionnaire'])
            ->get(['id', 'nom', 'prenom', 'promo', 'email', 'statut', 'classe']);

        $doublons = $tous->groupBy(function ($u) {
            return preg_replace('/\s+/', ' ', trim($u->nom)) . '|' .
                   preg_replace('/\s+/', ' ', trim($u->prenom)) . '|' .
                   $u->promo;
        })->filter(fn($g) => $g->count() > 1)->values();

        return view('admin.users.nettoyage', compact('importLocal', 'doublons'));
    }

    public function updateEmail(Request $request, User $user)
    {
        $request->validate(['email' => 'required|email|unique:users,email,' . $user->id]);
        $user->update(['email' => $request->email]);
        return back()->with('success', 'Email mis à jour pour ' . $user->prenom . ' ' . $user->nom . '.');
    }

    public function fusionner(Request $request)
    {
        $request->validate([
            'garder_id'    => 'required|exists:users,id',
            'supprimer_id' => 'required|exists:users,id|different:garder_id',
        ]);

        $garder    = User::with(['conventionsPapier'])->findOrFail($request->garder_id);
        $supprimer = User::with(['conventionsPapier'])->findOrFail($request->supprimer_id);

        // Un seul stage par année scolaire : la fusion ne peut pas en créer deux la même année
        $conflits = $garder->stages()->pluck('annee_scolaire')
            ->intersect($supprimer->stages()->pluck('annee_scolaire'));

        if ($conflits->isNotEmpty()) {
            return back()->withErrors(
                "Les deux comptes ont un stage pour " . $conflits->implode(', ') . " : supprime l'un des deux stages avant de fusionner."
            );
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($garder, $supprimer) {
            $DB = \Illuminate\Support\Facades\DB::class;

            // 1. Journal de stage — CRITIQUE : CASCADE DELETE sur user_id
            //    À faire AVANT la suppression du compte
            $DB::table('journal_entries')
                ->where('user_id', $supprimer->id)
                ->update(['user_id' => $garder->id]);

            // 2. Stages étudiant
            $DB::table('stages')
                ->where('etudiant_id', $supprimer->id)
                ->update(['etudiant_id' => $garder->id]);

            // 3. Conventions papier — contrainte UNIQUE (étudiant, année)
            $anneesGarder = $garder->conventionsPapier->pluck('annee_scolaire');
            foreach ($supprimer->conventionsPapier as $convention) {
                if ($anneesGarder->contains($convention->annee_scolaire)) {
                    // Les deux ont une convention cette année → on garde celle du compte conservé
                    $convention->delete();
                } else {
                    $convention->update(['etudiant_id' => $garder->id]);
                }
            }

            // 4. Rôles Spatie — CASCADE DELETE, il faut les transférer avant
            $modelType    = get_class($garder);
            $rolesGarder  = $DB::table('model_has_roles')
                ->where('model_id', $garder->id)->where('model_type', $modelType)
                ->pluck('role_id');
            $rolesSuppr   = $DB::table('model_has_roles')
                ->where('model_id', $supprimer->id)->where('model_type', $modelType)
                ->pluck('role_id');

            foreach ($rolesSuppr->diff($rolesGarder) as $roleId) {
                $DB::table('model_has_roles')->insert([
                    'role_id'    => $roleId,
                    'model_type' => $modelType,
                    'model_id'   => $garder->id,
                ]);
            }

            // 5. Autres étudiants qui ont ce compte comme tuteur
            $DB::table('users')
                ->where('tuteur_id', $supprimer->id)
                ->update(['tuteur_id' => $garder->id]);

            // 6. Entreprises et employés créés par ce compte
            $DB::table('entreprises')->where('user_id', $supprimer->id)->update(['user_id' => $garder->id]);
            $DB::table('employes')->where('creator_id', $supprimer->id)->update(['creator_id' => $garder->id]);

            // 7. Normaliser le nom + récupérer l'email réel si le compte conservé est @import.local
            $emailFinal = $garder->email;
            if (str_ends_with($garder->email, '@import.local') && !str_ends_with($supprimer->email, '@import.local')) {
                $emailFinal = $supprimer->email;
            }

            $garder->update([
                'nom'   => preg_replace('/\s+/', ' ', trim($garder->nom)),
                'email' => $emailFinal,
            ]);

            // 8. Suppression — toutes les FK critiques ont été migrées
            $supprimer->delete();
        });

        return back()->with('success',
            'Comptes fusionnés — ' . $garder->prenom . ' ' . trim($garder->nom) . ' conservé (ID ' . $garder->id . ').');
    }
}
