<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestage</title>
    {{-- CSS de Bulma et FontAwesome pour les icônes --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Petit style perso pour l'ombre du menu --}}
    <style>
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }

        /* ── Marges identiques sur toutes les pages ── */
        main .container { margin-top: 2rem !important; padding-left: 1.5rem !important; padding-right: 1.5rem !important; }

        /* ── Taille de police uniforme ── */
        body { font-size: 0.9375rem; }
        .table td, .table th { font-size: 0.875rem; }
        .input, .select select, .textarea { font-size: 0.875rem !important; }
        .label { font-size: 0.875rem !important; }
        .is-size-7 { font-size: 0.8125rem !important; }
        .help { font-size: 0.8125rem !important; }
        .tag { font-size: 0.8125rem !important; }

        /* ── Tableaux : pas de retour à la ligne ── */
        td, th { white-space: nowrap; vertical-align: middle !important; padding: 0.6em 0.75em !important; }
        td > div  { display: flex !important; flex-wrap: nowrap !important; align-items: center !important; gap: 4px; }
        td form   { display: inline !important; }
        .notification, .box, .message, p, label, .help { white-space: normal; }
        .table-scroll { overflow-x: auto; width: 100%; }
        table { min-width: 100%; }
    </style>
    @stack('styles')
</head>
<body class="has-background-light" style="min-height: 100vh;">

    {{-- L'INCLUSION MAGIQUE DU MENU EST ICI --}}
    {{-- On n'affiche le menu QUE si on n'est pas sur la page de connexion --}}
    @if(!request()->routeIs('login'))
        @include('partials.navbar')
    @endif

    @include('partials.alerte_stage_manquant')

    {{-- Le contenu spécifique à chaque page viendra s'insérer là --}}
    <main class="mt-5">
        @if(session('error'))
            <div class="container" style="max-width:860px;">
                <div class="notification is-warning is-light py-2 mb-0">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer style="background:#1a1a2e; color:#b0b0c8; margin-top:4rem; padding:2.5rem 1.5rem 1.5rem;">
        <div class="container">
            <div class="columns is-variable is-6 mb-4">

                {{-- Colonne 1 : identité de l'appli --}}
                <div class="column is-one-third">
                    <p style="color:#fff; font-weight:700; font-size:1.1rem; margin-bottom:.4rem;">
                        <i class="fas fa-graduation-cap mr-2" style="color:#7c6fe0;"></i>{{ config('app.name') }}
                    </p>
                    <p style="font-size:.82rem; line-height:1.6;">
                        Application de gestion des stages<br>
                        BTS Services Informatiques aux Organisations<br>
                        <span style="color:#7c6fe0;">Section SIO — Lycée Merleau-Ponty</span>
                    </p>
                </div>

                {{-- Colonne 2 : liens utiles --}}
                <div class="column">
                    <p style="color:#fff; font-weight:600; font-size:.85rem; margin-bottom:.6rem; text-transform:uppercase; letter-spacing:.05em;">
                        Liens utiles
                    </p>
                    <ul style="font-size:.82rem; line-height:2;">
                        <li><a href="{{ route('cgu.show') }}" style="color:#b0b0c8;">
                            <i class="fas fa-file-contract mr-1"></i> Conditions générales d'utilisation
                        </a></li>
                        @auth
                        <li><a href="{{ route('profile.show') }}" style="color:#b0b0c8;">
                            <i class="fas fa-user-circle mr-1"></i> Mon profil
                        </a></li>
                        @endauth
                        @role('Administrateur')
                        <li><a href="{{ route('admin.communication.index') }}" style="color:#b0b0c8;">
                            <i class="fas fa-user-shield mr-1"></i> RGPD &amp; Communications
                        </a></li>
                        @endrole
                        <li><a href="https://github.com/ornech/gestage2/issues/new" target="_blank" rel="noopener" style="color:#b0b0c8;">
                            <i class="fas fa-bug mr-1"></i> Signaler un bug
                        </a></li>
                    </ul>
                </div>

                {{-- Colonne 3 : infos techniques --}}
                <div class="column">
                    <p style="color:#fff; font-weight:600; font-size:.85rem; margin-bottom:.6rem; text-transform:uppercase; letter-spacing:.05em;">
                        Informations
                    </p>
                    <ul style="font-size:.82rem; line-height:2; color:#b0b0c8;">
                        @php $annee = \App\Models\Parametre::get('annee_scolaire', date('Y').'-'.(date('Y')+1)); @endphp
                        <li><i class="fas fa-calendar-alt mr-1"></i> Année scolaire {{ $annee }}</li>
                        <li><i class="fas fa-code-branch mr-1"></i> Gestage {{ config('app.version') }} — Laravel {{ app()->version() }}</li>
                        <li><i class="fas fa-shield-alt mr-1"></i> Données hébergées en France</li>
                    </ul>
                </div>

            </div>

            {{-- Barre de copyright --}}
            <div style="border-top:1px solid #2e2e4e; padding-top:1rem; display:flex; justify-content:space-between; align-items:center; font-size:.78rem; flex-wrap:wrap; gap:.5rem;">
                <span>
                    &copy; {{ date('Y') }} Lycée Merleau-Ponty — Application réalisée dans le cadre du BTS SIO.
                </span>
                <span style="color:#4a4a6a;">
                    Données personnelles traitées conformément au <abbr title="Règlement Général sur la Protection des Données" style="cursor:help; color:#7c6fe0;">RGPD</abbr>
                </span>
            </div>
        </div>
    </footer>

    {{-- Script JavaScript minimaliste de Bulma pour faire fonctionner le menu sur Mobile --}}
    @stack('scripts')
    <script nonce="{{ $cspNonce ?? '' }}">

        document.addEventListener('DOMContentLoaded', () => {
            const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
            $navbarBurgers.forEach( el => {
                el.addEventListener('click', () => {
                    const target = el.dataset.target;
                    const $target = document.getElementById(target);
                    el.classList.toggle('is-active');
                    $target.classList.toggle('is-active');
                });
            });

            // Gestion du clic sur le lien de déconnexion pour soumettre le formulaire parent
            const logoutLink = document.getElementById('logout-link');
            if (logoutLink) {
                logoutLink.addEventListener('click', (event) => {
                    // Empêche le lien de suivre son href (qui ferait une requête GET)
                    event.preventDefault();
                    // Trouve le formulaire parent le plus proche et le soumet (en POST)
                    logoutLink.closest('form').submit();
                });
            }
        });
    </script>
</body>
</html>