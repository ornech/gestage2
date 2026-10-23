# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Présentation

**Gestage** est une application Laravel de gestion des stages pour un lycée BTS SIO (Services Informatiques aux Organisations). Elle couvre le cycle complet : import des élèves depuis Pronote, création et suivi des stages, génération de conventions PDF, journal de compétences hebdomadaire, et validation administrative.

## Commandes essentielles

```bash
# Installation complète (première fois)
composer run setup

# Développement (PHP + queue + logs + Vite en parallèle)
composer run dev

# Tests (MySQL/MariaDB, base gestage2_test)
composer run test

# Test unique
php artisan test --filter NomDuTest

# Linting PHP
./vendor/bin/pint

# Build assets production
npm run build
```

## Architecture

### Rôles et flux utilisateurs

Trois rôles Spatie (`Etudiant`, `Professeur`, `Administrateur`) déterminent toutes les autorisations. Le `RedirectController` aiguille chaque rôle vers son dashboard après connexion.

- **Etudiant** → `/etudiant/` : créer/voir ses stages, journal de compétences, télécharger sa convention
- **Professeur** → `/dashboard` : voir/valider les stages de sa classe, gérer entreprises et employés
- **Administrateur** → `/admin` : tout + gestion utilisateurs, import Pronote, paramétrage, communications, RGPD

### Middlewares spécifiques au projet

Quatre middlewares custom (dans `app/Http/Middleware/`) s'enchaînent sur les routes étudiants :

- `ForcePasswordChange` — redirige vers changement de mot de passe si `force_password_change = true`. Tout compte créé (import Pronote, création admin, réinitialisation) reçoit un mot de passe aléatoire inconnu et un **email d'invitation** (`InvitationService`, lien Fortify `password.reset`, usage unique, 7 jours) ; il n'existe plus de mot de passe provisoire commun. `php artisan comptes:invitations-mdp-provisoire [--dry-run] [--sans-mail]` traite d'éventuels comptes anciens encore en « achanger ». **Mode lien** : pour les domaines de `config/invitations.php` (`INVITATION_DOMAINES_SANS_MAIL`, par défaut `ac-poitiers.fr,education.gouv.fr`, dont la messagerie refuse les mails de l'hébergeur) ou si la case « Ne pas envoyer d'email » est cochée, aucun mail n'est envoyé : le lien est affiché à l'administrateur (création, réinitialisation, import)
- `EnsureCguAccepted` — force l'acceptation des CGU avant tout accès
- `EnsureStudentActive` — bloque les comptes désactivés
- `EnsureStudentHasStage` — requis avant d'accéder au journal (un étudiant doit avoir un stage actif)

### Modèles et relations clés

```
User (HasRoles)
 ├── has_many stages (etudiant_id)
 ├── belongs_to tuteur (self-référence, tuteur_id → autre User de rôle Professeur)
 └── has_one conventionPapier

Stage
 ├── belongs_to entreprise, etudiant (User), professeur (User), maitreDeStage (Employe)
 └── has_many journalEntries

Entreprise
 └── has_many employes, stages

JournalEntry
 └── competences stockées en bitmask (6 compétences BTS SIO, constantes dans le modèle)

Parametre (table clé-valeur)
 └── Stocke toute la configuration école : nom, adresses, textes des articles de convention, année scolaire
```

La **`StagePolicy`** affine l'accès : un étudiant ne peut modifier que ses propres stages en statut `en_attente` ou `rejeté` ; un professeur/admin peut tout modifier.

### Intégrations externes

**Import Pronote** (`AdminImportController`) : flux en deux temps (preview → confirm). Le CSV Pronote est en UTF-8 BOM, délimiteur `;`. La logique de matching associe d'abord par email, puis par nom normalisé (gestion des particules DE, LE, DU…). Les redoublants sont détectés par recoupement classe + date d'entrée différente.

**API INSEE Sirene** (`App\Services\SireneClient`) : recherche de SIRET lors de la création d'un stage. Cherche d'abord en base locale, puis appelle `api.insee.fr/api-sirene/3.11/siret/{siret}`. La clé API est dans `config/services.php` → `services.sirene.key`.

**Génération PDF** (`PdfController` + mPDF) : la convention est un template Blade (`resources/views/stages/convention.blade.php`) rendu via mPDF. Les textes des articles particuliers sont stockés dans la table `Parametre` avec des placeholders `{DATE_DEBUT}`, `{DATE_FIN}`, etc. remplacés à la génération.

### Statuts

Enums PHP dans `app/Enums/` (`StatutValidation`, `StatutConvention`, `StatutConventionPapier`, `StatutEtudiant`), identiques aux ENUM MySQL (verrouillé par `EnumsMatchSchemaTest`). Écrire un statut en base = utiliser l'enum, jamais une chaîne libre. Le statut étudiant `diplome` est posé **par un professeur** (`admin.users.statut`, avec `diplome_at`/`diplome_par`) et désactive l'accès ; il n'est jamais déduit de la promo.

### Configuration dynamique via `Parametre`

La table `Parametre` (clé-valeur) remplace les fichiers de config pour tout ce qui est configurable par l'admin : infos de l'établissement, textes juridiques de la convention, année scolaire active, activation de la SPE. À lire avant de modifier tout texte affiché "en dur" dans les vues — il peut venir de cette table.

### Convention papier

Flux d'état distinct du statut de validation du stage : `hors_app` → `en_attente` → `validee`. Modèle `ConventionPapier`, géré par l'admin via `AdminStageController`. C'est l'autre voie pour valider un stage (convention signée sur papier, non générée par l'application) : une par étudiant **et par année scolaire**, reprise puis supprimée à la création du stage de cette année (`App\Services\CreationStage`). `User::conventionPapier` = celle de l'année active, `conventionsPapier` = toutes.

### Stage et année scolaire

Un stage appartient à une **année scolaire** (`stages.annee_scolaire`, obligatoire), jamais à une promo : un seul stage par étudiant et par année (clé unique), donc un redoublant a un stage par année dans la même classe. Toute requête sur les stages doit être restreinte à l'année voulue (`Stage::deLAnnee()`, `User::aUnStageCetteAnnee()`) ; `Parametre::anneeActive()` donne l'année en cours : elle est **calculée** (bascule le 1er septembre), aucune valeur n'est stockée. La saisie d'un stage par l'étudiant est fermée sans classe courante ou sans dates de stage dans `ConfigurationStage` (`CreationStage::raisonFermeture`) ; les étudiants actifs sans classe (diplôme/redoublement à traiter par un professeur) sont listés sur les tableaux de bord.

## Tests

- MySQL/MariaDB en test, comme en production : base dédiée `gestage2_test` (à créer une fois : `CREATE DATABASE gestage2_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`). Hôte et identifiants sont lus dans `.env`, mais `phpunit.xml` force `DB_DATABASE=gestage2_test` pour ne jamais toucher la base de dev
- Suites : `tests/Unit/` et `tests/Feature/`
- Bcrypt rounds réduits à 4 en test pour la vitesse
- Factories : `User::factory()->etudiant()|professeur()|administrateur()` (rôle Spatie + CGU acceptées)
- CI : `.github/workflows/tests.yml` (build Vite puis `php artisan test`)

## Frontend

Tailwind CSS v4 + Vite. Points d'entrée : `resources/css/app.css` et `resources/js/app.js`. Pas de framework JS — vanilla + Axios pour les requêtes AJAX (recherche SIRET notamment).
