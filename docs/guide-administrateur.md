# Guide administrateur — GéStage

## Sommaire

1. [Tableau de bord](#1-tableau-de-bord)
2. [Gestion des comptes utilisateurs](#2-gestion-des-comptes-utilisateurs)
3. [Import depuis Pronote](#3-import-depuis-pronote)
4. [Affectation des spécialités](#4-affectation-des-spécialités)
5. [Suivi des stages et conventions](#5-suivi-des-stages-et-conventions)
6. [Paramétrage de l'application](#6-paramétrage-de-lapplication)
7. [Communication avec les maîtres de stage](#7-communication-avec-les-maîtres-de-stage)
8. [Nettoyage des comptes](#8-nettoyage-des-comptes)

---

## 1. Tableau de bord

Le tableau de bord (`/admin`) est la page d'accueil après connexion. Il affiche :

- L'**année scolaire active**
- Des **accès rapides** vers les fonctions principales
- Des **compteurs par classe** (SIO1 / SIO2) : nombre d'élèves actifs, répartition par spécialité, état des conventions papier (à faire signer / en attente / validée)

---

## 2. Gestion des comptes utilisateurs

### Créer un compte

Depuis le tableau de bord ou le menu, cliquer sur **Créer un compte** (`/admin/users/create`).

Choisir d'abord le rôle :
- **Étudiant** — champs supplémentaires : classe, spécialité, tuteur référent
- **Professeur** — accès au suivi des stages et à l'import Pronote
- **Administrateur** — accès complet

Le mot de passe initial est automatiquement défini à `achanger`. L'utilisateur devra le modifier à sa première connexion.

### Consulter et modifier un compte étudiant

La liste des étudiants (`/admin/users`) permet de filtrer par année de promotion, classe, spécialité, ou de rechercher par nom/prénom/email.

Sur la fiche d'un étudiant, on peut :

- Modifier ses informations (classe, spécialité, tuteur)
- Changer son **statut** : Actif ou Démissionnaire
- Marquer un **redoublement** (la promotion est recalculée automatiquement)
- **Réinitialiser le mot de passe** (repassé à `achanger`)
- **Anonymiser le compte** (RGPD) : le nom devient "Anonyme" et l'email est remplacé par un identifiant neutre — action irréversible
- **Supprimer le compte** — action irréversible

La fiche affiche également l'historique complet des stages de l'étudiant avec l'état de chaque convention.

### Gérer les professeurs

La section `/admin/professeurs` liste tous les comptes professeurs. On peut y **attribuer ou retirer le rôle Administrateur** pour chaque enseignant. Il est impossible de modifier son propre compte depuis cette vue.

### Réinitialiser un mot de passe

La page `/admin/reset-password` permet de rechercher n'importe quel utilisateur (par nom, prénom ou email) et de remettre son mot de passe à `achanger` en un clic.

---

## 3. Import depuis Pronote

L'import Pronote permet de créer ou mettre à jour en masse les comptes étudiants à partir d'un export CSV issu de Pronote.

### Préparer le fichier CSV

Le fichier attendu est l'export standard Pronote, au format :
- Encodage **UTF-8 avec BOM**
- Séparateur **point-virgule** (`;`)
- Colonnes : Nom de l'élève, Email, Date d'entrée (jj/mm/aaaa), Date de sortie (jj/mm/aaaa)

### Étape 1 — Upload

Se rendre sur `/imports/pronote`, sélectionner la **classe** (SIO1 ou SIO2 — obligatoire car Pronote ne distingue pas les deux), puis importer le fichier.

### Étape 2 — Prévisualisation

L'application analyse chaque ligne et indique l'action prévue pour chaque étudiant :

| Action | Signification |
|--------|---------------|
| **Nouveau** | Email et nom introuvables → création d'un nouveau compte |
| **Mise à jour** | Compte retrouvé par email ou nom → mise à jour des informations |
| **Redoublant** | Même classe mais date d'entrée différente → la promotion est recalculée |
| **Démissionnaire** | Date de sortie dans le passé → le statut passe à "démissionnaire" |

Vérifier le tableau avant de confirmer.

### Étape 3 — Confirmation

Cliquer sur **Confirmer** pour appliquer les modifications. Un résumé indique le nombre de comptes créés, mis à jour, redoublants traités et démissionnaires marqués.

Tous les nouveaux comptes reçoivent le mot de passe `achanger` et seront invités à le modifier à la première connexion.

---

## 4. Affectation des spécialités

La section `/spe` permet d'affecter la spécialité **SLAM** ou **SISR** à chaque étudiant, classe par classe.

Sélectionner une classe, puis cocher la spécialité de chaque étudiant dans le tableau. La soumission enregistre toutes les modifications d'un coup.

---

## 5. Suivi des stages et conventions

### Vue d'ensemble (`/admin/stages`)

Cette page liste les étudiants et l'état de leurs conventions. Des onglets filtrent par statut :

- **Sans stage** — étudiants sans convention ou stage enregistré
- **À faire signer** — convention générée, en attente de signature par l'entreprise
- **En attente** — déposée à la direction pour validation
- **Validée** — retournée à l'étudiant, circuit terminé

Des filtres supplémentaires permettent d'affiner par classe ou année scolaire.

### Cycle de vie d'une convention papier

```
À faire signer  →  En attente (direction)  →  Validée (rendue à l'étudiant)
```

Chaque étape peut être **avancée ou reculée** via les boutons d'action sur la ligne de l'étudiant.

### Valider ou rejeter un stage

Sur chaque ligne, les boutons **Valider** et **Rejeter** permettent de statuer sur le stage proposé par l'étudiant. Un rejet nécessite d'indiquer un motif, qui sera visible par l'étudiant.

### Assigner un maître de stage

Si le maître de stage n'a pas été renseigné par l'étudiant, il est possible de l'affecter directement depuis cette vue via le bouton **Assigner un maître de stage**.

### Conventions "hors application"

Pour les stages dont la convention a été gérée en dehors de l'application (convention papier directe), utiliser le bouton **Marquer convention papier** pour créer un suivi manuel et faire avancer la convention dans le circuit habituel.

---

## 6. Paramétrage de l'application

### Années scolaires (`/admin/parametres`)

Chaque année scolaire possède sa propre configuration. Pour chaque classe (SIO1 / SIO2) :

- **Professeur principal** (sélection dans la liste des enseignants)
- **Date de début de stage**
- **Durée en semaines** (la date de fin est calculée automatiquement)

Pour créer une nouvelle année scolaire, utiliser le formulaire en bas de page (format : `YYYY-YYYY`, ex. `2026-2027`).

Pour définir l'année affichée par défaut dans l'application, cliquer sur **Définir comme active**.

### Informations établissement et articles de convention (`/admin/parametres/convention`)

Ce formulaire contient toutes les informations qui apparaissent dans les conventions PDF générées par l'application :

- Nom de l'établissement, nom et titre du/de la proviseur(e), adresse, téléphone, email
- Lieu de signature des conventions
- Texte des **11 articles** et des **2 parties** de la convention (modifiables librement)

Les textes des articles peuvent contenir des variables qui sont remplacées automatiquement à la génération du PDF : `{DATE_DEBUT}`, `{DATE_FIN}`, etc.

Toute modification est immédiatement prise en compte pour les prochains PDF générés.

---

## 7. Communication avec les maîtres de stage

### Envoyer un message (`/admin/communication`)

L'onglet **Envoyer une communication** permet d'envoyer un email à une sélection de maîtres de stage.

**Destinataires :**
- **Tous les contacts** — tous les employés ayant une adresse email valide
- **Membres du jury** — contacts marqués comme membres de jury
- **Sélection manuelle** — liste filtrée par nom ou entreprise, avec cases à cocher

Les emails sont envoyés progressivement (environ 1 200/heure) pour éviter d'être bloqués par les serveurs de messagerie.

### Email de bienvenue

L'onglet **Modèle d'email de bienvenue** permet de personnaliser le message automatiquement envoyé au maître de stage lorsqu'une convention est validée.

Des variables sont disponibles : `[PRENOM]`, `[NOM]`, `[TUTEUR]`. Un bouton **Prévisualiser** affiche le rendu final avant enregistrement.

### Suivi RGPD

L'onglet **Demandes RGPD** liste les maîtres de stage dont l'adresse email a été supprimée suite à une demande d'effacement. Ces suppressions sont effectuées via un lien sécurisé envoyé directement au contact concerné.

---

## 8. Nettoyage des comptes

### Comptes avec email provisoire

Lors d'un import Pronote, si aucun email n'est disponible pour un étudiant, un email temporaire du type `...@import.local` est attribué. La section `/admin/comptes/nettoyage` liste ces comptes pour permettre de saisir l'email définitif.

### Fusion de doublons

L'application détecte automatiquement les doublons : deux comptes avec le même nom normalisé et la même promotion. Pour chaque groupe :

1. Identifier le compte à **conserver** (généralement celui avec le vrai email et les stages)
2. Identifier le compte à **fusionner** (dont les données seront transférées vers le premier)
3. Cliquer sur **Fusionner**

La fusion transfère tous les stages, journaux et conventions du compte supprimé vers le compte conservé, puis supprime le doublon.

> **Attention** : cette opération est irréversible.
