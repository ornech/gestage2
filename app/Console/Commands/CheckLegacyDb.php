<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnostic en lecture seule de la base legacy.
 * N'écrit RIEN dans la base applicative.
 * Signale tous les problèmes qui causeront des erreurs ou des pertes de données
 * lors de l'exécution de php artisan import:legacy-db.
 */
class CheckLegacyDb extends Command
{
    protected $signature = 'import:legacy-check';
    protected $description = 'Vérifie la base legacy et liste les problèmes avant import (lecture seule)';

    private int $warnings = 0;
    private int $errors   = 0;

    // -------------------------------------------------------------------------

    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>╔══════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║   Diagnostic import:legacy-db  (lecture seule)      ║</>');
        $this->line('<fg=cyan>╚══════════════════════════════════════════════════════╝</>');
        $this->newLine();

        if (! $this->checkConnection()) {
            return Command::FAILURE;
        }

        $this->checkLocalSchema();
        $this->checkStudents();
        $this->checkEntreprises();
        $this->checkEmployes();
        $this->checkStages();
        $this->checkForeignKeys();
        $this->printSummary();

        return $this->errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    // =========================================================================
    // Étape 0 – Connexion
    // =========================================================================

    private function checkConnection(): bool
    {
        $this->section('Connexion à la base legacy');

        try {
            DB::connection('legacy')->getPdo();
            $db = DB::connection('legacy')->getDatabaseName();
            $this->printOk("Connecté à « {$db} »");
        } catch (\Exception $e) {
            $this->printErr('Connexion impossible : ' . $e->getMessage());
            $this->line('  → Configurez DB_LEGACY_* dans .env et importez le dump SQL :');
            $this->line('      mysql -u root btssio17_legacy < backup.sql');
            return false;
        }

        // Vérifier que les tables attendues existent
        $needed = ['user', 'entreprise', 'employe', 'stage'];
        foreach ($needed as $t) {
            $exists = DB::connection('legacy')
                ->getSchemaBuilder()
                ->hasTable($t);
            if ($exists) {
                $count = DB::connection('legacy')->table($t)->count();
                $this->printOk("Table « {$t} » trouvée ({$count} lignes)");
            } else {
                $this->printErr("Table « {$t} » introuvable dans la base legacy");
            }
        }

        return true;
    }

    // =========================================================================
    // Étape 1 – Schéma local
    // =========================================================================

    private function checkLocalSchema(): void
    {
        $this->section('Schéma local (migrations)');

        // Vérifier que les tables applicatives existent
        foreach (['users', 'entreprises', 'employes', 'stages'] as $t) {
            if (! Schema::hasTable($t)) {
                $this->printErr("Table locale « {$t} » absente → lancez php artisan migrate");
                return;
            }
            $this->printOk("Table locale « {$t} » présente");
        }

        // date_debut_semaine doit être nullable (migration d'ajustement)
        if (Schema::hasTable('journal_entries')) {
            $nullable = collect(DB::select("SHOW COLUMNS FROM journal_entries LIKE 'date_debut_semaine'"))
                ->first()?->Null === 'YES';
            if ($nullable) {
                $this->printOk('journal_entries.date_debut_semaine est nullable');
            } else {
                $this->printWarn('journal_entries.date_debut_semaine n\'est pas nullable → lancez php artisan migrate');
            }
        }

        // employes.email doit être nullable
        $emailNullable = collect(DB::select("SHOW COLUMNS FROM employes LIKE 'email'"))
            ->first()?->Null === 'YES';
        if ($emailNullable) {
            $this->printOk('employes.email est nullable');
        } else {
            $this->printWarn('employes.email n\'est pas nullable → lancez php artisan migrate');
        }

        // Vérifier que les rôles Spatie existent
        if (Schema::hasTable('roles')) {
            $roles = DB::table('roles')->pluck('name')->toArray();
            if (in_array('Etudiant', $roles)) {
                $this->printOk('Rôle Spatie « Etudiant » présent');
            } else {
                $this->printWarn('Rôle « Etudiant » absent → il sera créé automatiquement à l\'import');
            }
        } else {
            $this->printErr('Table roles absente → lancez php artisan migrate puis php artisan db:seed --class=RoleSeeder');
        }
    }

    // =========================================================================
    // Étape 2 – Étudiants
    // =========================================================================

    private function checkStudents(): void
    {
        $this->section('Utilisateurs (étudiants + professeurs)');

        $all = DB::connection('legacy')
            ->table('user')
            ->where('isDeleted', 0)
            ->where('id', '>', 0)
            ->get();

        $total    = count($all);
        $profs    = $all->filter(fn($u) => trim((string)($u->classe ?? '')) === 'Enseignant')->count();
        $etuds    = $total - $profs;

        $this->info("  Total à importer : {$total}  ({$etuds} étudiants · {$profs} professeurs)");
        $this->line("  Règle de rôle : classe='Enseignant' → Professeur, sinon → Etudiant");
        $this->line("  Colonnes ignorées : isDeleted, dateFirstConn, inactif, password_reset, login");

        // ── Sans email ───────────────────────────────────────────────────────
        $noEmail = $all->filter(fn($u) => trim((string)($u->email ?? '')) === '');
        if ($noEmail->count()) {
            $this->printWarn("{$noEmail->count()} utilisateur(s) sans email → placeholder généré : u{id}.prenom-nom@import.local");
            foreach ($noEmail->take(5) as $u) {
                $this->line("    id={$u->id}  {$u->nom} {$u->prenom}");
            }
            if ($noEmail->count() > 5) {
                $this->line('    ... et ' . ($noEmail->count() - 5) . ' autre(s)');
            }
        } else {
            $this->printOk('Tous les utilisateurs ont un email');
        }

        // ── Doublons d'email dans le legacy ──────────────────────────────────
        $emailDups = $all
            ->filter(fn($u) => trim((string)($u->email ?? '')) !== '')
            ->groupBy(fn($u) => strtolower(trim($u->email)))
            ->filter(fn($g) => $g->count() > 1);

        if ($emailDups->count()) {
            $this->printWarn("{$emailDups->count()} email(s) en doublon dans le legacy → suffixe u{id}. ajouté au second");
            foreach ($emailDups->take(5) as $email => $group) {
                $ids = $group->pluck('id')->implode(', ');
                $this->line("    « {$email} »  ids=[{$ids}]");
            }
        } else {
            $this->printOk('Aucun doublon d\'email dans le legacy');
        }

        // ── Conflits avec la base locale ─────────────────────────────────────
        $localEmails = DB::table('users')->pluck('email')->map(fn($e) => strtolower($e))->flip();
        $conflicts   = $all->filter(function ($u) use ($localEmails) {
            $email = strtolower(trim((string)($u->email ?? '')));
            return $email !== '' && isset($localEmails[$email]);
        });
        if ($conflicts->count()) {
            $this->printWarn("{$conflicts->count()} email(s) déjà présents en base locale → updateOrCreate mettra à jour ces lignes");
            foreach ($conflicts->take(5) as $u) {
                $this->line("    id={$u->id}  {$u->email}");
            }
        } else {
            $this->printOk('Aucun conflit d\'email avec la base locale');
        }

        // ── Encodage mojibake ────────────────────────────────────────────────
        $mojibake = $all->filter(
            fn($u) => preg_match('/Ã|Ã©|Ã¨|Ã /u', (string)($u->nom ?? '') . (string)($u->prenom ?? ''))
        );
        if ($mojibake->count()) {
            $this->printWarn("{$mojibake->count()} nom(s)/prénom(s) avec encodage cassé → corrigés automatiquement");
            foreach ($mojibake->take(3) as $u) {
                $fixed = html_entity_decode(
                    mb_convert_encoding((string) $u->prenom, 'UTF-8', 'ISO-8859-1'),
                    ENT_QUOTES | ENT_HTML5, 'UTF-8'
                );
                $this->line("    id={$u->id}  « {$u->prenom} » → « {$fixed} »");
            }
        } else {
            $this->printOk('Aucun problème d\'encodage sur les noms');
        }

        // ── Rôles : valeurs inattendues dans `classe` ─────────────────────────
        // La connexion legacy est en latin1 → les accents arrivent en mojibake.
        // On applique le même fixEncoding() que l'import pour comparer correctement.
        $knownClasses = ['Enseignant', 'SIO1', 'SIO2', 'Ancien étudiant'];
        $unexpected = $all->filter(function ($u) use ($knownClasses) {
            $fixed = html_entity_decode(
                mb_convert_encoding(trim((string) ($u->classe ?? '')), 'UTF-8', 'ISO-8859-1'),
                ENT_QUOTES | ENT_HTML5, 'UTF-8'
            );
            return $fixed !== '' && ! in_array($fixed, $knownClasses);
        });
        if ($unexpected->count()) {
            $values = $unexpected->pluck('classe')
                ->map(fn($v) => mb_convert_encoding((string)$v, 'UTF-8', 'ISO-8859-1'))
                ->unique()->implode(', ');
            $this->printWarn("{$unexpected->count()} utilisateur(s) avec valeur de classe inconnue → traités comme Etudiant : {$values}");
        } else {
            $this->printOk('Toutes les valeurs de `classe` sont reconnues (Enseignant / SIO1 / SIO2 / Ancien étudiant)');
        }

        // ── Note email conflicts + --fresh ────────────────────────────────────
        $localEmails = DB::table('users')->pluck('id', 'email')
            ->mapWithKeys(fn($id, $email) => [strtolower($email) => $id]);
        $conflicts = $all->filter(function ($u) use ($localEmails) {
            $email = strtolower(trim((string) ($u->email ?? '')));
            return $email !== '' && isset($localEmails[$email]) && $localEmails[$email] !== $u->id;
        });
        if ($conflicts->count()) {
            $localIdsConflict = $conflicts->map(fn($u) => $localEmails[strtolower($u->email)])->sort()->values();
            $allAbove3 = $localIdsConflict->every(fn($id) => $id >= 3);
            if ($allAbove3) {
                $this->printOk(
                    "{$conflicts->count()} email(s) en conflit avec la base locale, "
                    . "mais tous les users locaux concernés ont id>=3 "
                    . "→ résolus automatiquement par --fresh"
                );
            } else {
                $this->printWarn(
                    "{$conflicts->count()} email(s) en conflit avec des users locaux id<3 "
                    . "(comptes système) → non résolus par --fresh, import partiel possible"
                );
                foreach ($conflicts->take(5) as $u) {
                    $lid = $localEmails[strtolower($u->email)];
                    $this->line("    legacy_id={$u->id}  email={$u->email}  local_id={$lid}");
                }
            }
        }
    }

    // =========================================================================
    // Étape 3 – Entreprises
    // =========================================================================

    private function checkEntreprises(): void
    {
        $this->section('Entreprises');

        $all = DB::connection('legacy')
            ->table('entreprise')
            ->where('entreprise_valide', 1)
            ->get();

        $this->info('  Total à importer : ' . count($all));

        // ── Sans SIRET ───────────────────────────────────────────────────────
        $noSiret = $all->filter(fn($e) => empty(trim((string)($e->siret ?? ''))) || trim($e->siret) === '00000000000000');
        if ($noSiret->count()) {
            $this->printWarn("{$noSiret->count()} entreprise(s) sans SIRET valide → placeholder numérique 00…id généré");
            foreach ($noSiret->take(5) as $e) {
                $placeholder = str_pad((string)$e->id, 14, '0', STR_PAD_LEFT);
                $this->line("    id={$e->id}  « {$e->nomEntreprise} »  → siret={$placeholder}");
            }
        } else {
            $this->printOk('Toutes les entreprises ont un SIRET');
        }

        // ── SIRETs dupliqués dans le legacy ──────────────────────────────────
        $siretDups = $all
            ->filter(fn($e) => !empty(trim((string)($e->siret ?? ''))) && trim($e->siret) !== '00000000000000')
            ->groupBy(fn($e) => trim($e->siret))
            ->filter(fn($g) => $g->count() > 1);

        if ($siretDups->count()) {
            $this->printErr("{$siretDups->count()} SIRET(s) dupliqué(s) → updateOrCreate gardera le dernier inséré, les suivants seront ignorés");
            foreach ($siretDups->take(5) as $siret => $group) {
                $ids = $group->pluck('id')->implode(', ');
                $this->line("    SIRET {$siret}  ids=[{$ids}]");
            }
        } else {
            $this->printOk('Aucun SIRET dupliqué');
        }

        // ── Conflits SIRET avec la base locale ───────────────────────────────
        $localSirets = DB::table('entreprises')->pluck('siret')->flip();
        $conflicts   = $all->filter(fn($e) => isset($localSirets[trim((string)($e->siret ?? ''))]));
        if ($conflicts->count()) {
            $this->printWarn("{$conflicts->count()} SIRET(s) déjà en base locale → mise à jour (updateOrCreate)");
        } else {
            $this->printOk('Aucun conflit SIRET avec la base locale');
        }

        // ── Raisons sociales avec mojibake ───────────────────────────────────
        $badNames = $all->filter(fn($e) => preg_match('/Ã|Â|&#0/u', (string)($e->nomEntreprise ?? '')));
        if ($badNames->count()) {
            $this->printWarn("{$badNames->count()} raison(s) sociale(s) avec encodage à corriger → corrigées automatiquement");
        } else {
            $this->printOk('Aucun problème d\'encodage sur les raisons sociales');
        }
    }

    // =========================================================================
    // Étape 4 – Employés
    // =========================================================================

    private function checkEmployes(): void
    {
        $this->section('Employés / Contacts');

        $all = DB::connection('legacy')
            ->table('employe')
            ->where('id', '>', 0)
            ->get();

        $this->info('  Total dans le legacy : ' . count($all));

        // ── Orphelins (entreprise non importée) ───────────────────────────────
        $legacyValidIds = DB::connection('legacy')
            ->table('entreprise')
            ->where('entreprise_valide', 1)
            ->pluck('id')
            ->flip();

        $orphans = $all->filter(fn($e) => ! isset($legacyValidIds[$e->idEntreprise]));
        if ($orphans->count()) {
            $this->printWarn("{$orphans->count()} employé(s) orphelin(s) (entreprise invalide ou absente) → ignorés à l'import");
            foreach ($orphans->take(5) as $e) {
                $this->line("    id={$e->id}  {$e->nom} {$e->prenom}  idEntreprise={$e->idEntreprise}");
            }
        } else {
            $this->printOk('Aucun employé orphelin');
        }

        $importable = $all->filter(fn($e) => isset($legacyValidIds[$e->idEntreprise]));
        $this->info('  Importables (entreprise valide) : ' . $importable->count());

        // ── Sans email ───────────────────────────────────────────────────────
        $noEmail = $importable->filter(fn($e) => trim((string)($e->email ?? '')) === '');
        if ($noEmail->count()) {
            $this->printWarn("{$noEmail->count()} employé(s) sans email → NULL en base (colonne nullable)");
        } else {
            $this->printOk('Tous les employés importables ont un email');
        }

        // ── Sans téléphone ───────────────────────────────────────────────────
        $noPhone = $importable->filter(fn($e) => trim((string)($e->telephone ?? '')) === '');
        if ($noPhone->count()) {
            $this->printWarn("{$noPhone->count()} employé(s) sans téléphone → NULL en base (colonne nullable)");
        } else {
            $this->printOk('Tous les employés importables ont un téléphone');
        }
    }

    // =========================================================================
    // Étape 5 – Stages
    // =========================================================================

    private function checkStages(): void
    {
        $this->section('Stages');

        $all = DB::connection('legacy')->table('stage')->get();
        $this->info('  Total à importer : ' . count($all));

        // Jeux d'IDs valides dans le legacy : TOUS les users importés (pas seulement les étudiants)
        $validStudents    = DB::connection('legacy')->table('user')
            ->where('isDeleted', 0)->where('id', '>', 0)
            ->pluck('id')->flip();

        $validEntreprises = DB::connection('legacy')->table('entreprise')
            ->where('entreprise_valide', 1)
            ->pluck('id')->flip();

        $validEmployes    = DB::connection('legacy')->table('employe')
            ->where('id', '>', 0)
            ->pluck('id')->flip();

        // ── FK étudiant manquante ────────────────────────────────────────────
        $noStudent = $all->filter(fn($s) => ! isset($validStudents[$s->idEtudiant]));
        if ($noStudent->count()) {
            $this->printWarn("{$noStudent->count()} stage(s) sans étudiant valide → etudiant_id=NULL (stage conservé mais sans lien étudiant)");
            foreach ($noStudent->take(5) as $s) {
                $this->line("    stage.id={$s->id}  idEtudiant={$s->idEtudiant}  classe={$s->classe}");
            }
        } else {
            $this->printOk('Tous les stages ont un étudiant valide');
        }

        // ── FK entreprise manquante ──────────────────────────────────────────
        $noEntreprise = $all->filter(fn($s) => ! isset($validEntreprises[$s->idEntreprise]));
        if ($noEntreprise->count()) {
            $this->printWarn("{$noEntreprise->count()} stage(s) sans entreprise valide → entreprise_id=NULL");
            foreach ($noEntreprise->take(5) as $s) {
                $this->line("    stage.id={$s->id}  idEntreprise={$s->idEntreprise}");
            }
        } else {
            $this->printOk('Tous les stages ont une entreprise valide');
        }

        // ── FK employé manquante ─────────────────────────────────────────────
        $noEmploye = $all->filter(fn($s) => ! isset($validEmployes[$s->idMaitreDeStage]));
        if ($noEmploye->count()) {
            $this->printWarn("{$noEmploye->count()} stage(s) sans maître de stage valide → maitre_de_stage_id=NULL");
            foreach ($noEmploye->take(5) as $s) {
                $this->line("    stage.id={$s->id}  idMaitreDeStage={$s->idMaitreDeStage}");
            }
        } else {
            $this->printOk('Tous les stages ont un maître de stage valide');
        }

        // ── Stages complètement orphelins ────────────────────────────────────
        $fullyOrphan = $all->filter(
            fn($s) => ! isset($validStudents[$s->idEtudiant])
                   && ! isset($validEntreprises[$s->idEntreprise])
                   && ! isset($validEmployes[$s->idMaitreDeStage])
        );
        if ($fullyOrphan->count()) {
            $this->printWarn("{$fullyOrphan->count()} stage(s) sans aucune FK valide → importés avec toutes FK à NULL");
        }

        // ── Dates manquantes ─────────────────────────────────────────────────
        $noDates = $all->filter(fn($s) => empty($s->dateDebut) || empty($s->dateFin));
        if ($noDates->count()) {
            $this->printWarn("{$noDates->count()} stage(s) sans dates → date_debut/date_fin=NULL (colonnes nullable)");
        } else {
            $this->printOk('Tous les stages ont des dates');
        }
    }

    // =========================================================================
    // Étape 6 – Intégrité croisée (legacy → local)
    // =========================================================================

    private function checkForeignKeys(): void
    {
        $this->section('Intégrité croisée legacy → base locale');

        // Étudiants legacy vs users locaux déjà présents
        $legacyStudentIds = DB::connection('legacy')
            ->table('user')
            ->where('statut', 'Etudiant')->where('isDeleted', 0)->where('id', '>', 0)
            ->pluck('id');

        $localUserIds = DB::table('users')->pluck('id')->flip();
        $collisions   = $legacyStudentIds->filter(fn($id) => isset($localUserIds[$id]));

        if ($collisions->count()) {
            $this->printWarn("{$collisions->count()} id(s) étudiant(s) déjà présents en base locale → updateOrCreate mettra à jour ces lignes");
            foreach ($collisions->take(5) as $id) {
                $localEmail  = DB::table('users')->where('id', $id)->value('email');
                $legacyLogin = DB::connection('legacy')->table('user')->where('id', $id)->value('login');
                $this->line("    id={$id}  local={$localEmail}  legacy_login={$legacyLogin}");
            }
        } else {
            $this->printOk('Aucune collision d\'ID entre les étudiants legacy et les users locaux');
        }

        // Entreprises legacy vs entreprises locales
        $localEntrepriseIds = DB::table('entreprises')->pluck('id')->flip();
        $legacyEntIds       = DB::connection('legacy')
            ->table('entreprise')->where('entreprise_valide', 1)->pluck('id');
        $entCollisions      = $legacyEntIds->filter(fn($id) => isset($localEntrepriseIds[$id]));

        if ($entCollisions->count()) {
            $this->printWarn("{$entCollisions->count()} entreprise(s) avec ID déjà en base locale → mise à jour");
        } else {
            $this->printOk('Aucune collision d\'ID pour les entreprises');
        }
    }

    // =========================================================================
    // Résumé final
    // =========================================================================

    private function printSummary(): void
    {
        $this->newLine();
        $this->line('─────────────────────────────────────────────');

        if ($this->errors === 0 && $this->warnings === 0) {
            $this->info('Aucun problème détecté. L\'import peut être lancé.');
        } elseif ($this->errors === 0) {
            $this->printWarn("Bilan : 0 erreur, {$this->warnings} avertissement(s).");
            $this->line('  → L\'import peut être lancé. Les avertissements seront');
            $this->line('    gérés automatiquement (nulls, placeholders, encodage).');
        } else {
            $this->error("Bilan : {$this->errors} erreur(s), {$this->warnings} avertissement(s).");
            $this->line('  → Corrigez les erreurs avant de lancer l\'import.');
        }

        $this->line('─────────────────────────────────────────────');
        $this->newLine();
        $this->line('Commande d\'import :  php artisan import:legacy-db --fresh');
        $this->newLine();
    }

    // =========================================================================
    // Helpers d'affichage
    // =========================================================================

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("<fg=yellow>▶ {$title}</>");
    }

    private function printOk(string $msg): void
    {
        $this->line("  <fg=green>✓</> {$msg}");
    }

    private function printWarn(string $msg): void
    {
        $this->warnings++;
        $this->line("  <fg=yellow>⚠</> {$msg}");
    }

    private function printErr(string $msg): void
    {
        $this->errors++;
        $this->line("  <fg=red>✗</> {$msg}");
    }
}
