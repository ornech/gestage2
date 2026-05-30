<nav class="navbar is-link shadow-lg" role="navigation" aria-label="main navigation">
    <div class="container">

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

        <div id="navbarGestage" class="navbar-menu">
            <div class="navbar-start ml-4">
                @auth

                    {{-- ======== ÉTUDIANT ======== --}}
                    @role('Etudiant')
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-briefcase mr-2"></i> Mes stages</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('etudiant.stages.index') }}" class="navbar-item">
                                    <i class="fas fa-list mr-2"></i> Mes stages
                                </a>
                                <a href="{{ route('etudiant.conventions.index') }}" class="navbar-item">
                                    <i class="fas fa-file-contract mr-2"></i> Mes conventions
                                </a>
                            </div>
                        </div>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-building mr-2"></i> Entreprises</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('entreprises.index') }}" class="navbar-item">
                                    <i class="fas fa-search mr-2"></i> Annuaire
                                </a>
                                <a href="{{ route('entreprises.create') }}" class="navbar-item">
                                    <i class="fas fa-plus mr-2"></i> Ajouter (manuelle)
                                </a>
                                <a href="{{ route('entreprises.import.form') }}" class="navbar-item">
                                    <i class="fas fa-cloud-download-alt mr-2"></i> Import SIRET
                                </a>
                            </div>
                        </div>
                    @endrole

                    {{-- ======== PROFESSEUR ======== --}}
                    @role('Professeur')
                        <a href="{{ route('professeur.dashboard') }}" class="navbar-item">
                            <i class="fas fa-chart-line mr-2"></i> Tableau de bord
                        </a>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-user-graduate mr-2"></i> Étudiants</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('admin.users.index', ['classe' => 'SIO1']) }}" class="navbar-item">
                                    <i class="fas fa-users mr-2"></i> SIO1
                                </a>
                                <a href="{{ route('admin.users.index', ['classe' => 'SIO2']) }}" class="navbar-item">
                                    <i class="fas fa-users mr-2"></i> SIO2
                                </a>
                                <a href="{{ route('admin.users.index', ['filtre' => 'anciens']) }}" class="navbar-item has-text-grey">
                                    <i class="fas fa-history mr-2"></i> Anciennes promos
                                </a>
                            </div>
                        </div>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-briefcase mr-2"></i> Stages</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('admin.stages.index', ['classe' => 'sio1']) }}" class="navbar-item">
                                    <i class="fas fa-users mr-2"></i> SIO1
                                </a>
                                <a href="{{ route('admin.stages.index', ['classe' => 'sio2']) }}" class="navbar-item">
                                    <i class="fas fa-users mr-2"></i> SIO2
                                </a>
                                <hr class="navbar-divider">
                                <a href="{{ route('admin.parametres.index') }}" class="navbar-item">
                                    <i class="fas fa-calendar-alt mr-2"></i> Définir les dates de stages
                                </a>
                            </div>
                        </div>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-building mr-2"></i> Entreprises</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('entreprises.index') }}" class="navbar-item">
                                    <i class="fas fa-search mr-2"></i> Annuaire
                                </a>
                                <a href="{{ route('entreprises.import.form') }}" class="navbar-item">
                                    <i class="fas fa-cloud-download-alt mr-2"></i> Import SIRET
                                </a>
                            </div>
                        </div>
                    @endrole

                    {{-- ======== ADMINISTRATEUR ======== --}}
                    @role('Administrateur')
                        <a href="{{ route('admin.dashboard') }}" class="navbar-item">
                            <i class="fas fa-cogs mr-2"></i> Administration
                        </a>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-user-graduate mr-2"></i> Étudiants</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('admin.users.index', ['classe' => 'SIO1']) }}" class="navbar-item">
                                    <i class="fas fa-users mr-2"></i> SIO1
                                </a>
                                <a href="{{ route('admin.users.index', ['classe' => 'SIO2']) }}" class="navbar-item">
                                    <i class="fas fa-users mr-2"></i> SIO2
                                </a>
                                <a href="{{ route('admin.users.index', ['filtre' => 'anciens']) }}" class="navbar-item has-text-grey">
                                    <i class="fas fa-history mr-2"></i> Anciennes promos
                                </a>
                            </div>
                        </div>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-briefcase mr-2"></i> Stages</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('admin.stages.index', ['classe' => 'sio1']) }}" class="navbar-item">
                                    <i class="fas fa-users mr-2"></i> SIO1
                                </a>
                                <a href="{{ route('admin.stages.index', ['classe' => 'sio2']) }}" class="navbar-item">
                                    <i class="fas fa-users mr-2"></i> SIO2
                                </a>
                                <hr class="navbar-divider">
                                <a href="{{ route('admin.parametres.index') }}" class="navbar-item">
                                    <i class="fas fa-calendar-alt mr-2"></i> Définir les dates de stages
                                </a>
                            </div>
                        </div>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-building mr-2"></i> Entreprises</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('entreprises.index') }}" class="navbar-item">
                                    <i class="fas fa-search mr-2"></i> Annuaire
                                </a>
                                <a href="{{ route('entreprises.create') }}" class="navbar-item">
                                    <i class="fas fa-plus mr-2"></i> Ajouter
                                </a>
                                <a href="{{ route('entreprises.import.form') }}" class="navbar-item">
                                    <i class="fas fa-cloud-download-alt mr-2"></i> Import SIRET
                                </a>
                            </div>
                        </div>
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"><i class="fas fa-shield-alt mr-2"></i> Traçabilité</a>
                            <div class="navbar-dropdown">
                                <a href="{{ route('admin.audit.index') }}" class="navbar-item">
                                    <i class="fas fa-history mr-2"></i> Journal d'actions
                                </a>
                            </div>
                        </div>
                    @endrole

                @endauth
            </div>

            {{-- Droite : utilisateur connecté --}}
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
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link">
                                <span class="icon"><i class="fas fa-user-circle"></i></span>
                                <span class="ml-2">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</span>
                            </a>
                            <div class="navbar-dropdown is-right">
                                <a href="{{ route('profile.show') }}" class="navbar-item">
                                    <i class="fas fa-user mr-2"></i> Mon profil
                                </a>
                                <a href="{{ route('profile.edit') }}" class="navbar-item">
                                    <i class="fas fa-user-edit mr-2"></i> Modifier mon profil
                                </a>
                                <a href="{{ route('cgu.show') }}" class="navbar-item">
                                    <i class="fas fa-file-alt mr-2"></i> CGU
                                </a>
                                <hr class="navbar-divider">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="navbar-item button is-white is-fullwidth"
                                            style="border:none; background:none;">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Se déconnecter
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
