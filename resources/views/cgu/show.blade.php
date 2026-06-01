@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:860px;">
    <div class="box">

        {{-- En-tête --}}
        <div class="has-text-centered mb-5">
            <span class="icon is-large has-text-link mb-2" style="font-size:2.5rem;">
                <i class="fas fa-file-contract"></i>
            </span>
            <h1 class="title is-3 mb-1">Conditions Générales d'Utilisation</h1>
            <p class="is-size-7 has-text-grey">
                Application <strong>{{ config('app.name') }}</strong> — Lycée Merleau-Ponty, section BTS SIO<br>
                Version en vigueur au 1<sup>er</sup> septembre {{ date('Y') }}
            </p>
        </div>

        @if(session('success'))
            <div class="notification is-success is-light">{{ session('success') }}</div>
        @endif

        <div class="content" style="font-size:.92rem; line-height:1.8;">

            {{-- 1 --}}
            <h2 class="title is-5"><span class="tag is-link is-light mr-2">1</span> Objet de l'application</h2>
            <p>
                <strong>{{ config('app.name') }}</strong> est une application interne au Lycée Merleau-Ponty
                dédiée à la gestion administrative des stages des étudiants du BTS Services Informatiques
                aux Organisations (SIO). Elle couvre la gestion des conventions de stage, le suivi
                pédagogique, la communication avec les maîtres de stage et la tenue du journal de bord.
            </p>
            <p>
                L'accès est réservé aux personnels autorisés (administrateurs, professeurs) et aux
                étudiants inscrits dans la section BTS SIO du Lycée Merleau-Ponty.
            </p>

            <hr>

            {{-- 2 --}}
            <h2 class="title is-5"><span class="tag is-link is-light mr-2">2</span> Conditions d'accès</h2>
            <p>
                Les comptes sont créés exclusivement par l'équipe pédagogique (import Pronote ou
                saisie manuelle par un administrateur). Toute tentative d'inscription autonome est
                techniquement bloquée.
            </p>
            <p>
                Chaque utilisateur est responsable de la confidentialité de ses identifiants. Tout
                accès effectué avec ses identifiants lui est imputé. En cas de suspicion de
                compromission, l'utilisateur doit immédiatement contacter l'administrateur.
            </p>
            <p>
                La première connexion impose un changement de mot de passe. Le mot de passe doit
                comporter au minimum 8 caractères (lettres, chiffres et caractères spéciaux recommandés).
            </p>

            <hr>

            {{-- 3 --}}
            <h2 class="title is-5"><span class="tag is-link is-light mr-2">3</span> Obligations des utilisateurs</h2>
            <ul>
                <li>N'accéder qu'aux données autorisées par son rôle ;</li>
                <li>Ne pas tenter de contourner les mécanismes de contrôle d'accès ;</li>
                <li>Ne pas utiliser l'application à des fins étrangères à la gestion des stages ;</li>
                <li>Signaler tout dysfonctionnement ou comportement suspect à l'administrateur ;</li>
                <li>Ne pas divulguer à des tiers les informations personnelles consultées ;</li>
                <li>Se déconnecter systématiquement sur un poste partagé.</li>
            </ul>
            <p>
                Tout manquement peut entraîner la suspension immédiate de l'accès et,
                si nécessaire, des poursuites disciplinaires ou judiciaires.
            </p>

            <hr>

            {{-- 4 --}}
            <h2 class="title is-5">
                <span class="tag is-link is-light mr-2">4</span>
                Protection des données personnelles
                <span class="tag is-info is-light ml-2" style="font-size:.7rem;">RGPD — Règl. UE 2016/679</span>
            </h2>

            <h3 class="subtitle is-6 mb-1 mt-3">4.1 Responsable du traitement</h3>
            <p>
                Le responsable du traitement est le <strong>Lycée Merleau-Ponty</strong>, représenté
                par son chef d'établissement. Pour exercer vos droits, contactez l'équipe pédagogique SIO.
            </p>

            <h3 class="subtitle is-6 mb-1 mt-3">4.2 Données collectées</h3>
            <p class="is-size-7 has-text-grey mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                Seules les données des <strong>personnes physiques</strong> sont couvertes par le RGPD.
                Les données des entreprises sont mentionnées à titre de transparence.
            </p>

            @foreach([
                ['Identité', 'is-info', 'Étudiants &amp; professeurs',
                    'Nom, prénom, adresse e-mail, numéro de téléphone.'],
                ['Scolarité', 'is-info', 'Étudiants',
                    'Classe, spécialité (SLAM/SISR), promotion, statut (actif/démissionnaire), dates d\'entrée et de sortie, acceptation des CGU.'],
                ['Stages', 'is-info', 'Étudiants',
                    'Titre, description, dates de début/fin, statut de convention et de validation, motif de rejet éventuel.'],
                ['Journal de bord', 'is-info', 'Étudiants',
                    'Semaine, dates, titre de la semaine, activités réalisées, compétences mobilisées.'],
                ['Maîtres de stage', 'is-primary', 'Maîtres de stage',
                    'Nom, prénom, e-mail, téléphone, service, fonction ; préférences de communication (jury, newsletter) ; date d\'exercice du droit RGPD si applicable.'],
                ['Entreprises', 'is-light', 'Entités morales — hors RGPD',
                    'Raison sociale, SIRET, code NAF, adresse complète, téléphone, type d\'établissement, effectif.'],
            ] as [$cat, $color, $qui, $data])
            <div style="display:flex; gap:.75rem; margin-bottom:.6rem; align-items:baseline;">
                <div style="min-width:155px; max-width:155px;">
                    <span class="tag {{ $color }} is-light" style="white-space:normal; height:auto; padding:.25rem .5rem;">
                        <strong>{{ $cat }}</strong>
                    </span><br>
                    <span class="is-size-7 has-text-grey" style="font-style:italic;">{!! $qui !!}</span>
                </div>
                <div class="is-size-7" style="flex:1; border-left:2px solid #e8e8e8; padding-left:.75rem; padding-top:.1rem;">
                    {!! $data !!}
                </div>
            </div>
            @endforeach

            <h3 class="subtitle is-6 mb-1 mt-3">4.3 Base légale et finalités</h3>
            <p>
                Les traitements reposent sur <strong>l'intérêt légitime</strong> de l'établissement
                (art. 6.1.f RGPD) pour la gestion des stages, et sur <strong>l'exécution d'une
                mission d'intérêt public</strong> (art. 6.1.e RGPD) pour les données de scolarité.
            </p>

            <h3 class="subtitle is-6 mb-1 mt-3">4.4 Durée de conservation</h3>
            <ul>
                <li>Données des étudiants : durée de la scolarité + <strong>5 ans</strong> ;</li>
                <li>Données des maîtres de stage : durée du stage + <strong>5 ans</strong> ;</li>
                <li>Journal de bord : durée du stage + <strong>3 ans</strong> ;</li>
            </ul>

            <h3 class="subtitle is-6 mb-1 mt-3">4.5 Vos droits</h3>
            <div class="columns is-multiline is-variable is-2 mt-1">
                @foreach([
                    ['fa-eye',            'Accès',        'Obtenir une copie des données vous concernant.'],
                    ['fa-pencil-alt',     'Rectification','Faire corriger des données inexactes.'],
                    ['fa-trash-alt',      'Effacement',   'Demander la suppression (sous réserve d\'obligations légales).'],
                    ['fa-ban',            'Opposition',   'Vous opposer aux traitements fondés sur l\'intérêt légitime.'],
                    ['fa-pause-circle',   'Limitation',   'Suspendre temporairement un traitement.'],
                    ['fa-download',       'Portabilité',  'Recevoir vos données dans un format structuré.'],
                ] as [$icon, $droit, $desc])
                <div class="column is-half">
                    <div style="background:#f5f7ff; border-left:3px solid #3273dc; padding:8px 12px; border-radius:0 4px 4px 0; font-size:.82rem;">
                        <strong><i class="fas {{ $icon }} mr-1 has-text-link"></i> {{ $droit }}</strong>
                        — {{ $desc }}
                    </div>
                </div>
                @endforeach
            </div>
            <p class="mt-3">
                Pour exercer ces droits, contactez l'équipe pédagogique ou le DPO de l'académie.
                Vous pouvez également saisir la <strong>CNIL</strong>
                (<a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer">www.cnil.fr</a>).
                En cas de violation de données, l'établissement s'engage à notifier la CNIL sous
                <strong>72 heures</strong> (art. 33 RGPD).
            </p>

            <hr>

            {{-- 5 --}}
            <h2 class="title is-5">
                <span class="tag is-link is-light mr-2">5</span>
                Sécurité des systèmes d'information
                <span class="tag is-warning is-light ml-2" style="font-size:.7rem;">NIS2 — Dir. UE 2022/2555</span>
            </h2>
            <p>
                Conformément aux exigences de la directive NIS2 transposée en droit français
                et aux recommandations de l'ANSSI, les mesures suivantes sont appliquées :
            </p>
            <div class="columns is-multiline is-variable is-2 mt-1">
                @foreach([
                    ['fa-lock',               'Chiffrement',         'Communications chiffrées (HTTPS/TLS). Mots de passe stockés en bcrypt.'],
                    ['fa-id-badge',           'Authentification',    'Identifiant + mot de passe. Changement forcé à la 1ère connexion.'],
                    ['fa-shield-alt',         'Contrôle d\'accès',   'Ségrégation des rôles (Étudiant / Professeur / Admin). Principe du moindre privilège.'],
                    ['fa-exclamation-circle', 'Gestion d\'incidents','Tout incident de sécurité doit être signalé à l\'administrateur sous 24 h.'],
                    ['fa-code',               'Sécurité applicative','Protections intégrées contre SQLi, XSS, CSRF, clickjacking et fuite d\'information.'],
                ] as [$icon, $titre, $desc])
                <div class="column is-half">
                    <div style="background:#fffef0; border-left:3px solid #f5a623; padding:8px 12px; border-radius:0 4px 4px 0; font-size:.82rem;">
                        <strong><i class="fas {{ $icon }} mr-1" style="color:#b36b00;"></i> {{ $titre }}</strong><br>
                        <span class="has-text-grey-dark">{{ $desc }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <hr>

            {{-- 6 --}}
            <h2 class="title is-5"><span class="tag is-link is-light mr-2">6</span> Cookies et sessions</h2>
            <p>
                L'application utilise uniquement des <strong>cookies de session technique</strong>
                nécessaires à l'authentification, sans dépôt de cookie tiers, analytique ou publicitaire.
                Ces cookies sont limités à la durée de la session et non transmis à des tiers.
                Aucun consentement supplémentaire n'est requis (art. 82 loi Informatique et Libertés).
            </p>

            <hr>

            {{-- 7 --}}
            <h2 class="title is-5"><span class="tag is-link is-light mr-2">7</span> Responsabilité</h2>
            <p>
                Le Lycée Merleau-Ponty met tout en œuvre pour assurer la disponibilité et l'intégrité
                de l'application. Sa responsabilité ne saurait être engagée en cas d'utilisation non
                conforme aux présentes CGU, de défaillance d'infrastructure extérieure ou d'actes
                malveillants de tiers malgré les mesures de sécurité mises en place.
            </p>

            <hr>

            {{-- 8 --}}
            <h2 class="title is-5"><span class="tag is-link is-light mr-2">8</span> Mise à jour des CGU</h2>
            <p>
                Les présentes CGU peuvent être modifiées à tout moment pour refléter les évolutions
                légales ou fonctionnelles. Toute modification substantielle sera notifiée aux utilisateurs,
                qui devront accepter la nouvelle version pour continuer à accéder à l'application.
            </p>

        </div>

        {{-- Bouton d'acceptation --}}
        @auth
            @if(!auth()->user()->cgu_accepted_at)
            <div class="notification is-warning is-light mt-4">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Vous devez accepter les CGU pour accéder à l'application.
            </div>
            <form action="{{ route('cgu.accept') }}" method="POST" class="mt-3">
                @csrf
                <label class="checkbox mb-3" style="display:flex; align-items:flex-start; gap:.5rem; cursor:pointer;">
                    <input type="checkbox" id="cgu-check" style="margin-top:3px; flex-shrink:0;">
                    <span>
                        J'ai lu et j'accepte les Conditions Générales d'Utilisation ainsi que
                        la politique de protection des données personnelles décrite ci-dessus.
                    </span>
                </label>
                <button type="submit" id="btn-accepter" class="button is-primary" disabled>
                    <i class="fas fa-check mr-2"></i> Accepter et accéder à l'application
                </button>
            </form>
            <script nonce="{{ $cspNonce ?? '' }}">
            document.getElementById('cgu-check').addEventListener('change', function () {
                document.getElementById('btn-accepter').disabled = !this.checked;
            });
            </script>
            @else
            <div class="notification is-success is-light mt-4">
                <i class="fas fa-check-circle mr-2"></i>
                CGU acceptées le {{ auth()->user()->cgu_accepted_at->format('d/m/Y à H:i') }}.
            </div>
            @endif
        @endauth

    </div>
</div>
@endsection
