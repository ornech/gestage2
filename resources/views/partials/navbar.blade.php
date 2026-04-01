<nav class="navbar is-link shadow-lg" role="navigation" aria-label="main navigation">
    <div class="container">
        {{-- Section Logo et Menu Mobile (Burger) --}}
        <div class="navbar-brand">
            <a class="navbar-item is-size-4 has-text-weight-bold" href="/">
                <span class="icon is-medium mr-2"><i class="fas fa-graduation-cap"></i></span>
                GESTAGE
            </a>

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarGestage">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        {{-- Section Liens (Cachée sur mobile, visible sur PC) --}}
        <div id="navbarGestage" class="navbar-menu">
            <div class="navbar-start ml-4">
                
                @auth
                    {{-- Menu Étudiant --}}
                    @role('Etudiant')
                        <a href="/stages" class="navbar-item"><i class="fas fa-file-contract mr-2"></i> Mes Conventions</a>
                      <a href="{{ route('entreprises.index') }}" class="navbar-item">
                        <i class="fas fa-building mr-2"></i> Entreprises
                    </a>

                    @endrole

                    {{-- Menu Professeur --}}
                    @role('Professeur')
                        <a href="/dashboard" class="navbar-item"><i class="fas fa-chart-line mr-2"></i> Tableau de bord</a>
                        <a href="#" class="navbar-item"><i class="fas fa-users mr-2"></i> Mes Étudiants</a>
                    @endrole

                    {{-- Menu Administrateur --}}
                    @role('Administrateur')
                        <a href="/admin" class="navbar-item"><i class="fas fa-cogs mr-2"></i> Administration</a>
                        <a href="#" class="navbar-item"><i class="fas fa-user-shield mr-2"></i> Comptes</a>
                        <a href="{{ route('admin.stages.index') }}" class="navbar-item">
    <i class="fas fa-briefcase mr-2"></i> Gestion des stages
</a>

                   
                        @endrole

                @endauth

            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    @guest
                        <div class="buttons">
                            <a href="/login" class="button is-light is-outlined">
                                <strong>Se connecter</strong>
                            </a>
                        </div>
                    @endguest

                    @auth
                        {{-- Menu déroulant pour l'utilisateur connecté --}}
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link">
                                <span class="icon"><i class="fas fa-user-circle"></i></span>
                                <span class="ml-2">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</span>
                            </a>

                            <div class="navbar-dropdown is-right">
                                <a href="{{ route('profile.show') }}" class="navbar-item">
                                    <i class="fas fa-user-cog mr-2"></i> Mon Profil
                                </a>
                                <hr class="navbar-divider">
                                {{-- Le lien de déconnexion est maintenant dans un formulaire pour la sécurité (POST + CSRF) --}}
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                      <button type="submit" class="navbar-item button is-white is-fullwidth" style="border: none; background: none;">
                                        Se déconnecter
                                      </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</nav>