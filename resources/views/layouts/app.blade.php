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
    </style>
</head>
<body class="has-background-light" style="min-height: 100vh;">

    {{-- L'INCLUSION MAGIQUE DU MENU EST ICI --}}
    {{-- On n'affiche le menu QUE si on n'est pas sur la page de connexion --}}
    @if(!request()->routeIs('login'))
        @include('partials.navbar')
    @endif

    {{-- Le contenu spécifique à chaque page viendra s'insérer là --}}
    <main class="mt-5">
        @yield('content')
    </main>

    {{-- Script JavaScript minimaliste de Bulma pour faire fonctionner le menu sur Mobile --}}
    <script {{ isset($cspNonce) ? 'nonce='.$cspNonce : '' }}>

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