# Gestage

Application web de **gestion des stages** d'un lycée proposant un BTS SIO (Services Informatiques aux Organisations).

Elle couvre tout le cycle d'un stage : import des étudiants depuis Pronote, saisie du stage et de l'entreprise, **génération de la convention de stage en PDF**, suivi des signatures, validation par les professeurs et journal de compétences hebdomadaire.

## Ce que l'application permet

### Étudiant

- Saisir son stage : entreprise (recherche par SIRET via l'API INSEE Sirene), maître de stage, dates et durée.
- **Voir ou télécharger la convention de stage en PDF**, générée avec les informations du stage.
- Tenir son **journal de stage** : un compte rendu par semaine, avec les compétences du référentiel BTS SIO mises en œuvre.
- Suivre l'état de son stage (en attente, validé, rejeté avec le motif) et corriger sa saisie si elle est rejetée.

### Professeur

- Suivre les stages de ses classes (SIO1 et SIO2), par année scolaire, avec des compteurs par état de convention.
- Valider ou rejeter un stage (le rejet est motivé et visible par l'étudiant), consulter et télécharger les conventions.
- Gérer les entreprises et les maîtres de stage.
- Traiter les étudiants sortis du cycle (diplômés, redoublants) en une action groupée.

### Administrateur

Tout ce que fait un professeur, plus :

- gestion des comptes et import des élèves depuis Pronote (aperçu puis confirmation) ;
- paramétrage : préparation de l'année scolaire (dates de stage et professeur principal par classe), textes de la convention, informations de l'établissement ;
- affectation des spécialités (SLAM / SISR) ;
- communication aux maîtres de stage (mail de bienvenue personnalisable), conventions reçues sur papier, RGPD (anonymisation, suppression).

## Principes à connaître

- **Un stage appartient à une année scolaire**, pas à une promo : un étudiant a au plus un stage par année scolaire. Un redoublant a donc un stage par année, dans la même classe.
- **L'année scolaire est calculée** : elle bascule automatiquement le 1er septembre.
- **La saisie des stages est fermée** pour un étudiant tant qu'il n'a pas de classe cette année, ou que les dates de stage de sa classe ne sont pas renseignées.
- **Deux voies pour la convention** : générée par l'application (PDF), ou signée sur papier hors application (le suivi est alors fait par le staff, puis repris par le stage quand l'étudiant le saisit).
- **Le diplôme n'est jamais déduit de la promo** : un professeur le valide explicitement, tout comme un redoublement.
- **Un compte reçoit un mot de passe aléatoire** et un e-mail d'invitation à usage unique (ou un lien affiché au staff pour les domaines dont la messagerie refuse les mails).

### À la rentrée

1. Avant le 1er septembre : dans **Paramètres**, préparer la nouvelle année (dates de stage et professeur principal de SIO1 et SIO2).
2. Traiter les étudiants sans classe depuis l'encadré du tableau de bord (diplômés, redoublants).
3. Après le 1er septembre : importer la liste des nouveaux SIO1 depuis Pronote.

## Technologies

| Domaine | Technologies |
|---|---|
| Backend | PHP 8.2+, Laravel 12, Fortify (authentification), Spatie Permission (rôles `Etudiant`, `Professeur`, `Administrateur`) |
| PDF | mPDF |
| Base de données | MySQL / MariaDB |
| Frontend | Blade, Bulma, Tailwind CSS v4 + Vite, JavaScript natif et Axios |
| Sauvegardes | Spatie Laravel Backup (base de données, quotidien) |
| Qualité | PHPUnit, Laravel Pint, PHPStan, CI GitHub Actions (PHP 8.2 et 8.4, MariaDB 11.4) |

## Installation

Prérequis : PHP 8.2+, Composer, Node.js, MySQL ou MariaDB, et `poppler-utils` (`pdftotext`) si tu veux exécuter le test du contenu du PDF.

```bash
git clone https://github.com/ornech/gestage2.git
cd gestage2
composer run setup        # dépendances, .env, clé, migrations, build des assets
```

Renseigne ensuite dans `.env` la base de données, le courrier sortant (SMTP) et, si besoin, la clé de l'API Sirene (`services.sirene.key`). Les trois rôles (`Administrateur`, `Professeur`, `Etudiant`) sont créés par `php artisan db:seed --class=RoleSeeder` ; en environnement local, `php artisan db:seed` ajoute en plus des comptes de démonstration. Le déploiement en production est décrit dans `docs/deploiement.md`.

```bash
composer run dev          # serveur, file d'attente, journaux et Vite en parallèle
```

## Tests

```bash
# base dédiée, à créer une fois :

# CREATE DATABASE gestage2_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

composer run test                    # tous les tests
php artisan test --filter NomDuTest  # un test
./vendor/bin/pint                    # style PHP
```

Les tests tournent sur `gestage2_test` (jamais sur la base de développement).

## Tâches planifiées

Le planificateur Laravel (`php artisan schedule:run`, à lancer chaque minute par cron) exécute : sauvegarde quotidienne de la base, nettoyage et contrôle des sauvegardes, rappel hebdomadaire aux étudiants sans stage (`stages:rappel`) et traitement des mails en file d'attente.

## Documentation

- [Guide administrateur](docs/guide-administrateur.md) : comptes, import Pronote, suivi des stages et conventions.
- [Déploiement](docs/deploiement.md) : premier déploiement (hébergement mutualisé), `.env`, cron, mises à jour.
- [CLAUDE.md](CLAUDE.md) : architecture, modèles et règles de développement.

## Organisation du code

```text
app/Http/Controllers/   contrôleurs par rôle et par sujet (stages, import Pronote, PDF, journal…)
app/Http/Middleware/    changement de mot de passe, CGU, compte actif, étudiant avec stage
app/Models/             User, Stage, Entreprise, Employe, JournalEntry, ConventionPapier, Parametre…
app/Services/           création de stage, chiffres des tableaux de bord, mails, API Sirene, invitations
app/Enums/              statuts (validation, convention, étudiant), identiques aux ENUM MySQL
app/Policies/           autorisations fines (StagePolicy, EmployePolicy)
resources/views/        vues Blade ; la convention PDF est stages/convention.blade.php
```

## Sécurité et données personnelles

Politique de sécurité des contenus (CSP avec nonce), limitation des tentatives de connexion et des recherches, journal d'audit, acceptation des CGU, et outils RGPD (anonymisation, suppression de compte, adresse e-mail d'un maître de stage effaçable).
