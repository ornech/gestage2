<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Importe depuis l'ancienne base btssio17_gestion_stage :
 *   Utilisateurs · Entreprises · Employés · Stages
 *
 * Règle de rôle (colonne `classe`) :
 *   'Enseignant'  → Professeur  (classe/promo = null)
 *   toute autre   → Etudiant    (classe/promo conservés et décodés)
 *
 * Colonnes legacy NON importées :
 *   isDeleted, dateFirstConn, inactif, password_reset, login, statut, idClasse
 *
 * Note technique :
 *   On utilise DB::table()->updateOrInsert() et non Model::updateOrCreate()
 *   pour préserver les IDs legacy. Model::updateOrCreate() ne set pas l'id
 *   à l'INSERT car `id` n'est pas dans $fillable → MySQL auto-incrémenterait,
 *   cassant toutes les clés étrangères.
 */
class ImportLegacyDb extends Command
{
    protected $signature = 'import:legacy-db
                            {--fresh : Vide les tables applicatives avant import}';

    protected $description = 'Importe utilisateurs, entreprises, employés et stages depuis la base legacy';

    private array $effectifMap = [
        '00' => 0,    '01' => 1,    '02' => 4,    '03' => 7,
        '11' => 15,   '12' => 35,   '21' => 75,   '22' => 150,
        '31' => 150,  '32' => 375,  '41' => 750,  '42' => 1500,
        '51' => 3500, '52' => 7500, '53' => 10000, 'NN' => 0,
    ];

    // -------------------------------------------------------------------------

    public function handle(): int
    {
        if (! $this->checkConnection()) {
            return Command::FAILURE;
        }

        $this->preflightWarnings();

        if ($this->option('fresh')) {
            if (! $this->confirm('Vider toutes les tables applicatives avant import ?', true)) {
                return Command::SUCCESS;
            }
            $this->truncate();
        }

        $this->importUsers();
        $this->importEntreprises();
        $this->importEmployes();
        $this->importStages();

        $this->newLine();
        $this->info('Import termine. Relancez import:legacy-check pour verifier.');
        $this->newLine();

        return Command::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Infrastructure
    // -------------------------------------------------------------------------

    private function checkConnection(): bool
    {
        try {
            DB::connection('legacy')->getPdo();
            return true;
        } catch (\Exception $e) {
            $this->error('Connexion impossible : ' . $e->getMessage());
            $this->line('Configurez DB_LEGACY_* dans .env.');
            return false;
        }
    }

    private function preflightWarnings(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>--- Pre-flight checks ---</>');

        // Conflits email sans --fresh
        if (! $this->option('fresh')) {
            $legacyEmails = DB::connection('legacy')
                ->table('user')->where('isDeleted', 0)->where('id', '>', 0)
                ->whereNotNull('email')->where('email', '!=', '')
                ->pluck('email', 'id');

            $localEmails = DB::table('users')
                ->pluck('id', 'email')
                ->mapWithKeys(fn($id, $e) => [strtolower($e) => $id]);

            $conflicts = $legacyEmails->filter(
                fn($e, $lid) => $localEmails->has(strtolower($e)) && $localEmails[strtolower($e)] !== $lid
            );
            if ($conflicts->count()) {
                $this->warn($conflicts->count() . ' email(s) en conflit → utilisez --fresh pour les resoudre.');
            }
        }

        // SIRETs dupliqués
        DB::connection('legacy')
            ->table('entreprise')->where('entreprise_valide', 1)
            ->whereNotNull('siret')->where('siret', '!=', '00000000000000')
            ->selectRaw('siret, GROUP_CONCAT(id ORDER BY id) as ids, COUNT(*) as nb')
            ->groupBy('siret')->having('nb', '>', 1)->get()
            ->each(function ($dup) {
                $ids   = explode(',', $dup->ids);
                $first = array_shift($ids);
                $this->warn("SIRET duplique {$dup->siret} : id={$first} importe, id(s)=" . implode(',', $ids) . " ignores.");
            });

        // Stages avec étudiant anonyme
        $anon = DB::connection('legacy')->table('stage')->where('idEtudiant', '<=', 0)->count();
        if ($anon) {
            $this->warn("{$anon} stage(s) avec etudiant anonyme : etudiant_id=NULL.");
        }

        // Employes sans email
        $noMail = DB::connection('legacy')->table('employe')->where('id', '>', 0)
            ->where(fn($q) => $q->whereNull('email')->orWhere('email', ''))->count();
        if ($noMail) {
            $this->warn("{$noMail} employe(s) sans email : NULL en base.");
        }

        $this->line('<fg=cyan>--- Debut de l\'import ---</>');
        $this->newLine();
    }

    private function truncate(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['stages', 'employes', 'entreprises', 'conventions_papier'] as $t) {
            DB::table($t)->truncate();
        }
        $userIds = DB::table('users')->where('id', '>=', 3)->pluck('id');
        if ($userIds->isNotEmpty()) {
            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->whereIn('model_id', $userIds)->delete();
            DB::table('users')->where('id', '>=', 3)->delete();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('Tables videes.');
    }

    /**
     * Corrige l'encodage mojibake (UTF-8 lu via connexion latin1).
     * "Jean-FranÃ§ois" → "Jean-François"
     * Décode aussi les entités HTML.
     */
    private function fix(?string $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }

        return html_entity_decode(
            mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1'),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
    }

    /**
     * Vérifie qu'un ID existe dans la base LOCALE.
     * Utilisé pour valider les FK avant d'insérer.
     */
    private function idExists(string $table, int|null $id): bool
    {
        return $id && $id > 0 && DB::table($table)->where('id', $id)->exists();
    }

    /** Recalibre l'AUTO_INCREMENT après insertion avec IDs explicites. */
    private function resetAutoIncrement(string $table): void
    {
        $max = (int) DB::table($table)->max('id');
        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = " . ($max + 1));
    }

    // -------------------------------------------------------------------------
    // Étape 1 : Utilisateurs
    // -------------------------------------------------------------------------

    private function importUsers(): void
    {
        $this->newLine();
        $this->info('Utilisateurs...');

        $rows = DB::connection('legacy')
            ->table('user')
            ->where('isDeleted', 0)
            ->where('id', '>', 0)
            ->get();

        $roleProf     = Role::firstOrCreate(['name' => 'Professeur', 'guard_name' => 'web']);
        $roleEtudiant = Role::firstOrCreate(['name' => 'Etudiant',   'guard_name' => 'web']);

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $ok = 0;
        $ko = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $old) {
            $isProfesseur = (trim((string) ($old->classe ?? '')) === 'Enseignant');
            $role         = $isProfesseur ? $roleProf : $roleEtudiant;

            $email = trim((string) ($old->email ?? ''));
            if ($email === '') {
                $slug  = Str::slug(($this->fix($old->nom) ?? 'inconnu') . '-' . ($this->fix($old->prenom) ?? ''));
                $email = "u{$old->id}.{$slug}@import.local";
            }
            // Déduplication si conflit email sur un id différent
            if (DB::table('users')->where('email', $email)->where('id', '!=', $old->id)->exists()) {
                $email = 'u' . $old->id . '.' . $email;
            }

            // fixEncoding() appliqué sur `classe` : "Ancien étudiant" stocké correctement
            $classe = $isProfesseur ? null : ($this->fix($old->classe) ?: null);
            $promo  = $isProfesseur ? null : ($old->promo ?: null);
            $spe    = ($old->spe !== '' && $old->spe !== null) ? $old->spe : null;

            // ── DB::table()->updateOrInsert() pour préserver l'ID legacy ──────
            // Model::updateOrCreate() ne force PAS l'id à l'INSERT (id absent
            // du $fillable) → MySQL auto-incrémenterait et casserait toutes les FK.
            $data = [
                'nom'                   => $this->fix($old->nom)    ?? 'Inconnu',
                'prenom'                => $this->fix($old->prenom) ?? '',
                'email'                 => $email,
                'password'              => $old->password ?? Hash::make(Str::random(32)),
                'date_entree'           => $old->date_entree ?: null,
                'telephone'             => $old->telephone   ?: null,
                'spe'                   => $spe,
                'classe'                => $classe,
                'promo'                 => $promo,
                'tuteur_id'             => null,
                'statut'                => 'actif',
                'force_password_change' => false,
                'updated_at'            => now(),
            ];

            try {
                $isNew = ! DB::table('users')->where('id', $old->id)->exists();

                DB::table('users')->updateOrInsert(
                    ['id' => $old->id],
                    $isNew ? array_merge($data, ['created_at' => now()]) : $data
                );

                // Spatie necessite un modele Eloquent pour syncRoles()
                $user = User::find($old->id);
                if ($user) {
                    $user->syncRoles([$role]);
                }

                $ok++;
            } catch (\Throwable $e) {
                $ko++;
            }

            $bar->advance();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $bar->finish();
        $this->newLine();

        // Second pass : tuteur_id (FK auto-référentielle, tous les users sont en base)
        $this->line('  Mise a jour tuteur_id...');
        DB::connection('legacy')
            ->table('user')
            ->whereNotNull('idTuteur')
            ->where('id', '>', 0)
            ->where('isDeleted', 0)
            ->orderBy('id')
            ->each(function ($old) {
                if ($this->idExists('users', $old->idTuteur)) {
                    DB::table('users')
                        ->where('id', $old->id)
                        ->update(['tuteur_id' => $old->idTuteur]);
                }
            });

        $this->resetAutoIncrement('users');
        $this->line("  {$ok} importes, {$ko} ignores.");
    }

    // -------------------------------------------------------------------------
    // Étape 2 : Entreprises
    // -------------------------------------------------------------------------

    private function importEntreprises(): void
    {
        $this->newLine();
        $this->info('Entreprises...');

        $rows = DB::connection('legacy')
            ->table('entreprise')
            ->where('entreprise_valide', 1)
            ->get();

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $ok = 0;
        $ko = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $old) {
            $naf      = $old->naf ? substr(str_replace('.', '', $old->naf), 0, 5) : null;
            $effectif = $this->effectifMap[$old->effectif ?? ''] ?? null;
            $siretRaw = trim((string) ($old->siret ?? ''));
            $siret    = ($siretRaw !== '' && $siretRaw !== '00000000000000')
                ? $siretRaw
                : str_pad((string) $old->id, 14, '0', STR_PAD_LEFT);

            $data = [
                'raison_sociale'     => $this->fix($old->nomEntreprise) ?: 'Sans nom',
                'siret'              => $siret,
                'code_naf'           => $naf,
                'adresse'            => $this->fix($old->adresse),
                'complement_adresse' => $this->fix($old->adresse2),
                'code_postal'        => $old->codePostal ?: null,
                'ville'              => $this->fix($old->ville),
                'departement_code'   => $old->dep_geo   ?: null,
                'telephone'          => $old->tel        ?: null,
                'type'               => $old->type       ?: null,
                'effectif'           => $effectif,
                'est_valide'         => true,
                'user_id'            => null,
                'updated_at'         => now(),
            ];

            try {
                $isNew = ! DB::table('entreprises')->where('id', $old->id)->exists();

                DB::table('entreprises')->updateOrInsert(
                    ['id' => $old->id],
                    $isNew ? array_merge($data, ['created_at' => $old->Created_Date ?? now()]) : $data
                );
                $ok++;
            } catch (\Throwable) {
                $ko++; // SIRET dupliqué
            }

            $bar->advance();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $bar->finish();
        $this->newLine();

        $this->resetAutoIncrement('entreprises');
        $this->line("  {$ok} importees, {$ko} ignorees (doublons SIRET).");
    }

    // -------------------------------------------------------------------------
    // Étape 3 : Employés
    // -------------------------------------------------------------------------

    private function importEmployes(): void
    {
        $this->newLine();
        $this->info('Employes...');

        $rows = DB::connection('legacy')
            ->table('employe')
            ->where('id', '>', 0)
            ->get();

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $ok = 0;
        $ko = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $old) {
            if (! $this->idExists('entreprises', $old->idEntreprise)) {
                $bar->advance();
                $ko++;
                continue;
            }

            $data = [
                'entreprise_id'  => $old->idEntreprise,
                'creator_id'     => null,
                'nom'            => $this->fix($old->nom) ?? 'Inconnu',
                'prenom'         => $this->fix($old->prenom),
                'email'          => ($old->email     !== '' ? $old->email     : null),
                'telephone'      => ($old->telephone !== '' ? $old->telephone : null),
                'service'        => $this->fix($old->service),
                'fonction'       => $this->fix($old->fonction),
                'contact_valide' => (bool) ($old->contact_valide ?? false),
                'newsletter'     => (bool) ($old->newsletter     ?? false),
                'jury'           => (bool) ($old->jury           ?? false),
                'updated_at'     => now(),
            ];

            try {
                $isNew = ! DB::table('employes')->where('id', $old->id)->exists();

                DB::table('employes')->updateOrInsert(
                    ['id' => $old->id],
                    $isNew ? array_merge($data, ['created_at' => $old->created_date ?? now()]) : $data
                );
                $ok++;
            } catch (\Throwable) {
                $ko++;
            }

            $bar->advance();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $bar->finish();
        $this->newLine();

        $this->resetAutoIncrement('employes');
        $this->line("  {$ok} importes, {$ko} ignores.");
    }

    // -------------------------------------------------------------------------
    // Étape 4 : Stages
    // -------------------------------------------------------------------------

    private function importStages(): void
    {
        $this->newLine();
        $this->info('Stages...');

        $rows = DB::connection('legacy')->table('stage')->get();

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $ok = 0;
        $ko = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $old) {
            $entrepriseId = $this->idExists('entreprises', $old->idEntreprise)    ? $old->idEntreprise    : null;
            $maitreId     = $this->idExists('employes',    $old->idMaitreDeStage) ? $old->idMaitreDeStage : null;
            $etudiantId   = $this->idExists('users',       $old->idEtudiant)      ? $old->idEtudiant      : null;
            $professeurId = $this->idExists('users',       $old->idProfesseur)    ? $old->idProfesseur    : null;

            $data = [
                'entreprise_id'      => $entrepriseId,
                'maitre_de_stage_id' => $maitreId,
                'etudiant_id'        => $etudiantId,
                'professeur_id'      => $professeurId,
                'classe'             => $old->classe      ?: null,
                'titre'              => null,
                'description'        => $this->fix($old->description),
                'date_debut'         => $old->dateDebut   ?: null,
                'date_fin'           => $old->dateFin     ?: null,
                'statut_validation'  => 'valide',
                'statut_convention'  => 'a_faire_signer',
                'updated_at'         => now(),
            ];

            try {
                $isNew = ! DB::table('stages')->where('id', $old->id)->exists();

                DB::table('stages')->updateOrInsert(
                    ['id' => $old->id],
                    $isNew ? array_merge($data, ['created_at' => now()]) : $data
                );
                $ok++;
            } catch (\Throwable) {
                $ko++;
            }

            $bar->advance();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $bar->finish();
        $this->newLine();

        $this->resetAutoIncrement('stages');
        $this->line("  {$ok} importes, {$ko} ignores.");
    }
}
