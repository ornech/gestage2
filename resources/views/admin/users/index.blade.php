@extends('layouts.app')

@section('content')
<div class="container mt-5">

    @php $speActif = request('spe', ''); @endphp

    {{-- ── En-tête ─────────────────────────────────────────────────────── --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem; margin-bottom:1rem;">
        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:.5rem;">

            <h1 class="title mb-0" style="line-height:1;">Étudiants</h1>

            {{-- Double tag Classe + Année --}}
            @if(isset($classeParam) && $classeParam && $classeParam !== 'tous')
            <div style="display:flex; align-items:center;">
                <span class="tag is-medium {{ $classeParam === 'SIO1' ? 'is-info' : 'is-primary' }}" style="border-radius:4px 0 0 4px; margin:0;">{{ $classeParam }}</span>
                <span class="tag is-medium" style="border-radius:0 4px 4px 0; margin:0; background:#e0e0e0; color:#444; border:1px solid #ccc; border-left:none;">{{ $classeParam === 'SIO1' ? 'Première année' : 'Deuxième année' }}</span>
            </div>
            @endif

            {{-- Séparateur --}}
            <span class="has-text-grey-light" style="font-size:.8rem;">|</span>

            {{-- Double tags spécialité avec compteurs --}}
            @foreach([
                [''     , 'Toutes', 'is-dark', $stats['actifs']],
                ['SLAM' , 'SLAM',   'is-info',  $stats['slam']],
                ['SISR' , 'SISR',   'is-link',  $stats['sisr']],
            ] as [$val, $label, $color, $count])
            <a href="{{ route('admin.users.index', array_merge(request()->except('spe'), array_filter(['classe' => $classeParam, 'spe' => $val]))) }}"
               style="text-decoration:none; display:flex; align-items:center;">
                <div style="display:flex; align-items:center; border:1px solid #dbdbdb; border-radius:4px; overflow:hidden;">
                    <span class="tag {{ $speActif === $val ? $color : $color.' is-light' }}" style="border-radius:0; margin:0; height:1.75em;">{{ $label }}</span>
                    <span class="tag is-white" style="border-radius:0; margin:0; border-left:1px solid #dbdbdb; height:1.75em;">{{ $count }}</span>
                </div>
            </a>
            @endforeach

        </div>
        <div class="level-right">
               {{-- ── Année dropdown ──────────────────────────────────────────────── --}}
    <div style="display:flex; justify-content:flex-end;" class="mb-3">
        <form method="GET">
            <input type="hidden" name="classe" value="{{ $classeParam }}">
            @if($speActif) <input type="hidden" name="spe" value="{{ $speActif }}"> @endif
            <div class="select is-small">
                <select name="annee" id="annee-select">
                    @foreach($annees as $annee)
                        <option value="{{ $annee }}" {{ $annee === $anneeSelectionnee ? 'selected' : '' }}>
                            {{ $annee }}{{ $annee === $anneeActive ? ' ●' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif

 

    {{-- ── Tableau ─────────────────────────────────────────────────────── --}}
    <div class="table-scroll"><table class="table is-striped is-fullwidth is-hoverable is-size-7">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Classe</th>
                <th>Spé</th>
                <th>Promo</th>
                <th>Statut</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            @php
                $anneeEtude = $user->promo ? (3 - ($user->promo - $syInt)) : null;
                $classeAnnee = ($anneeEtude >= 1 && $anneeEtude <= 2) ? 'SIO'.$anneeEtude : null;

                $statutColor = match($user->statut) {
                    'actif'          => 'is-success is-light',
                    'demissionnaire' => 'is-danger is-light',
                    default          => 'is-light',
                };
                $statutLabel = match($user->statut) {
                    'actif'          => 'Actif',
                    'demissionnaire' => 'Démiss.',
                    default          => '—',
                };
            @endphp
            <tr>
                <td>{{ $user->nom }}</td>
                <td>{{ $user->prenom }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($classeAnnee)
                        <span class="tag {{ $classeAnnee === 'SIO1' ? 'is-info' : 'is-primary' }}">{{ $classeAnnee }}</span>
                    @elseif($user->promo && $user->promo <= $syInt)
                        <span class="tag is-success is-light">Diplômé {{ $user->promo }}</span>
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>
                <td>{{ $user->spe ?? '—' }}</td>
                <td>{{ $user->promo ?? '—' }}</td>
                <td><span class="tag {{ $statutColor }}">{{ $statutLabel }}</span></td>
                <td>
                    @role('Administrateur')
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="button is-small is-warning" title="Modifier">
                            <i class="fas fa-pen"></i>
                        </a>
                    @else
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="button is-small is-light" title="Voir le profil">
                            <i class="fas fa-eye"></i>
                        </a>
                    @endrole
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="has-text-centered has-text-grey py-5">
                    Aucun étudiant pour ce filtre.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table></div>

    {{ $users->withQueryString()->links() }}

</div>

<script nonce="{{ $cspNonce ?? '' }}">
document.getElementById('annee-select').addEventListener('change', function () {
    this.form.submit();
});
</script>
@endsection
