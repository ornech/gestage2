@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <div class="level">
        <div class="level-left">
            <div>
                <h1 class="title">Anciennes promos</h1>
                <p class="subtitle is-6 has-text-grey">Diplômés et démissionnaires</p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.users.index') }}" class="button is-primary">
                <i class="fas fa-users mr-2"></i> Étudiants actifs
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif

    <form method="GET">
        <input type="hidden" name="filtre" value="anciens">
        <div class="columns is-vcentered mb-4">
            <div class="column">
                <div class="control has-icons-left">
                    <input class="input" type="text" name="search"
                           placeholder="Nom, prénom ou email…"
                           value="{{ request('search') }}">
                    <span class="icon is-left"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="select">
                    <select id="promo-select" name="promo">
                        <option value="">Toutes les promos</option>
                        @foreach($promos as $p)
                            <option value="{{ $p }}" {{ $promoFiltre == $p ? 'selected' : '' }}>
                                Promo {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="column is-narrow">
                <button class="button is-info">Rechercher</button>
            </div>
        </div>
    </form>

    <div class="table-scroll"><table class="table is-striped is-fullwidth is-hoverable">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Promo</th>
                <th>Statut</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            @php
                $isDiplome = $user->promo && $user->promo <= $sy;
            @endphp
            <tr>
                <td>{{ $user->nom }}</td>
                <td>{{ $user->prenom }}</td>
                <td class="is-size-7">{{ $user->email }}</td>
                <td>{{ $user->promo ?? '—' }}</td>
                <td>
                    @if($user->statut === 'demissionnaire')
                        <span class="tag is-danger is-light">Démissionnaire</span>
                    @elseif($isDiplome)
                        <span class="tag is-success is-light">Diplômé {{ $user->promo }}</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.users.edit', $user) }}" class="button is-small is-light">
                        <i class="fas fa-pen"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="has-text-centered has-text-grey py-5">
                    Aucun ancien étudiant.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table></div>

    {{ $users->withQueryString()->links() }}

</div>
@endsection

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    document.getElementById('promo-select').addEventListener('change', function () {
        this.closest('form').submit();
    });
</script>
@endpush
