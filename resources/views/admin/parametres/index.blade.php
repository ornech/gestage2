@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:960px;">

    <h1 class="title">Paramètres des stages</h1>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="notification is-danger is-light">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    {{-- ── Sélecteur d'années ──────────────────────────────────────────── --}}
    <div class="box p-4 mb-5">
        <div class="level is-mobile" style="flex-wrap:wrap; gap:8px;">
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
                @foreach($annees as $annee)
                    <a href="{{ route('admin.parametres.index', ['annee' => $annee]) }}"
                       class="button {{ $annee === $anneeSelectionnee ? 'is-link' : 'is-light' }}">
                        {{ $annee }}
                        @if($annee === $anneeActive)
                            <span class="tag is-success is-light ml-2" style="font-size:0.65rem;">active</span>
                        @endif
                    </a>
                @endforeach
            </div>

            @if($anneeSelectionnee !== $anneeActive)
                @role('Administrateur')
                <form action="{{ route('admin.parametres.set-active') }}" method="POST">
                    @csrf
                    <input type="hidden" name="annee_scolaire" value="{{ $anneeSelectionnee }}">
                    <button class="button is-warning is-light is-small">
                        <i class="fas fa-star mr-1"></i> Définir comme année active
                    </button>
                </form>
                @endrole
            @endif
        </div>

        @role('Administrateur')
        <form action="{{ route('admin.parametres.nouvelle-annee') }}" method="POST"
              class="mt-3 pt-3" style="border-top:1px solid #eee;">
            @csrf
            <div class="field has-addons">
                <div class="control">
                    <input class="input is-small" type="text" name="annee_scolaire"
                           placeholder="ex. 2026-2027" pattern="\d{4}-\d{4}" required
                           style="width:140px;">
                </div>
                <div class="control">
                    <button class="button is-primary is-small">
                        <i class="fas fa-plus mr-1"></i> Nouvelle année
                    </button>
                </div>
            </div>
        </form>
        @endrole
    </div>

    {{-- ── Formulaire de configuration ─────────────────────────────────── --}}
    <form action="{{ route('admin.parametres.update') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="annee_scolaire" value="{{ $anneeSelectionnee }}">

        <div class="columns">

            @foreach(['SIO1' => ['is-info','Première année'], 'SIO2' => ['is-primary','Deuxième année']] as $classe => [$color, $label])
            @php
                $cfg     = $configs->get($classe);
                $key     = strtolower($classe);
                $semaines = $cfg?->duree_en_semaines ?? 6;
            @endphp
            <div class="column">
                <div class="box">
                    <div class="tags has-addons mb-4">
                        <span class="tag {{ $color }} is-medium">{{ $classe }}</span>
                        <span class="tag is-light is-medium">{{ $label }}</span>
                    </div>

                    {{-- Prof principal --}}
                    <div class="field">
                        <label class="label is-small">Professeur principal</label>
                        <div class="select is-fullwidth">
                            <select name="{{ $key }}[prof_principal_id]">
                                <option value="">— Non défini —</option>
                                @foreach($profs as $prof)
                                    <option value="{{ $prof->id }}"
                                        {{ $cfg?->prof_principal_id == $prof->id ? 'selected' : '' }}>
                                        {{ $prof->prenom }} {{ $prof->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Date de début --}}
                    <div class="field">
                        <label class="label is-small">Date de début du stage</label>
                        <input class="input date-debut"
                               type="date"
                               name="{{ $key }}[stage_date_debut]"
                               data-target="date-fin-{{ $key }}"
                               data-semaines="semaines-{{ $key }}"
                               value="{{ $cfg?->stage_date_debut?->format('Y-m-d') }}"
                               oninput="calculerDateFin('{{ $key }}')">
                    </div>

                    {{-- Durée en semaines --}}
                    <div class="field">
                        <label class="label is-small">Durée du stage</label>
                        <div class="select is-fullwidth">
                            <select name="{{ $key }}[duree_semaines]"
                                    id="semaines-{{ $key }}"
                                    onchange="calculerDateFin('{{ $key }}')">
                                @for($s = 1; $s <= 16; $s++)
                                    <option value="{{ $s }}" {{ $semaines == $s ? 'selected' : '' }}>
                                        {{ $s }} semaine{{ $s > 1 ? 's' : '' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Date de fin calculée --}}
                    <div class="field">
                        <label class="label is-small has-text-grey">
                            Date de fin <span class="tag is-light is-small ml-1">calculée</span>
                        </label>
                        <input class="input" type="text" id="date-fin-{{ $key }}"
                               value="{{ $cfg?->stage_date_fin?->format('d/m/Y') }}"
                               readonly style="background:#f5f5f5; cursor:default;">
                    </div>
                </div>
            </div>
            @endforeach

        </div>

        <div class="field mt-2">
            <button type="submit" class="button is-primary">
                <i class="fas fa-save mr-2"></i> Enregistrer {{ $anneeSelectionnee }}
            </button>
        </div>
    </form>

    {{-- ── Spécialités (admin uniquement) ─────────────────────────────── --}}
    @role('Administrateur')
    @php $isOpen = \App\Models\Parametre::isOpen('spe_assignments_open'); @endphp
    <div class="box mt-5">
        <p class="menu-label">Affectation des spécialités SLAM/SISR</p>
        <div class="level">
            <div class="level-left">
                <span class="tag is-medium {{ $isOpen ? 'is-success' : 'is-warning' }}">
                    <i class="fas fa-{{ $isOpen ? 'lock-open' : 'lock' }} mr-2"></i>
                    {{ $isOpen ? 'Ouvert' : 'Fermé' }}
                </span>
            </div>
            <div class="level-right">
                <form action="{{ route('admin.parametres.toggle-spe') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="button is-small {{ $isOpen ? 'is-danger is-light' : 'is-success is-light' }}">
                        {{ $isOpen ? 'Fermer' : 'Ouvrir au second semestre' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endrole

</div>

<script>
function calculerDateFin(key) {
    const debut    = document.querySelector(`input[name="${key}[stage_date_debut]"]`);
    const selectSem = document.getElementById(`semaines-${key}`);
    const output   = document.getElementById(`date-fin-${key}`);

    if (!debut.value || !selectSem.value) { output.value = ''; return; }

    const d = new Date(debut.value);
    d.setDate(d.getDate() + parseInt(selectSem.value) * 7);

    output.value = d.toLocaleDateString('fr-FR');
}

// Initialiser à l'ouverture de la page
document.addEventListener('DOMContentLoaded', () => {
    ['sio1', 'sio2'].forEach(k => calculerDateFin(k));
});
</script>
@endsection
