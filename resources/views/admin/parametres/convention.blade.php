@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width:900px;">

    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">
                    <i class="fas fa-file-contract mr-2"></i> Convention de stage — Paramètres
                </h1>
                <p class="is-size-7 has-text-grey mt-1">
                    Informations de l'établissement et texte des articles juridiques
                </p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.parametres.index') }}" class="button is-light is-small mr-2">← Paramètres</a>
            @if(request()->has('stage'))
                <a href="{{ route('pdf.convention', request('stage')) }}" target="_blank" class="button is-primary is-small">
                    <i class="fas fa-eye mr-1"></i> Aperçu convention
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light py-2 mb-4">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.parametres.convention.update') }}" method="POST">
        @csrf @method('PUT')

        {{-- ── Établissement ────────────────────────────────────────── --}}
        <div class="box mb-4">
            <p class="menu-label mb-3">Établissement & représentant légal</p>
            <div class="columns is-multiline">
                <div class="column is-half">
                    <div class="field">
                        <label class="label is-small">Nom de l'établissement</label>
                        <input class="input is-small" type="text" name="etablissement[nom]"
                               value="{{ $etablissement['nom'] }}" placeholder="Lycée Merleau-Ponty">
                    </div>
                </div>
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label is-small">Nom du/de la proviseur(e)</label>
                        <input class="input is-small" type="text" name="etablissement[proviseur_nom]"
                               value="{{ $etablissement['proviseur_nom'] }}" placeholder="Sylvie KOCIK">
                    </div>
                </div>
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label is-small">Titre</label>
                        <input class="input is-small" type="text" name="etablissement[proviseur_titre]"
                               value="{{ $etablissement['proviseur_titre'] }}" placeholder="Proviseure">
                    </div>
                </div>
                <div class="column is-half">
                    <div class="field">
                        <label class="label is-small">Adresse</label>
                        <input class="input is-small" type="text" name="etablissement[adresse]"
                               value="{{ $etablissement['adresse'] }}">
                    </div>
                </div>
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label is-small">BP / lieu-dit</label>
                        <input class="input is-small" type="text" name="etablissement[bp]"
                               value="{{ $etablissement['bp'] }}" placeholder="BP 229">
                    </div>
                </div>
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label is-small">CP + Ville</label>
                        <input class="input is-small" type="text" name="etablissement[cp_ville]"
                               value="{{ $etablissement['cp_ville'] }}" placeholder="17304 ROCHEFORT CEDEX">
                    </div>
                </div>
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label is-small">Téléphone</label>
                        <input class="input is-small" type="text" name="etablissement[tel]"
                               value="{{ $etablissement['tel'] }}">
                    </div>
                </div>
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label is-small">Email</label>
                        <input class="input is-small" type="email" name="etablissement[mel]"
                               value="{{ $etablissement['mel'] }}">
                    </div>
                </div>
                <div class="column is-half">
                    <div class="field">
                        <label class="label is-small">Lieu de signature</label>
                        <input class="input is-small" type="text" name="etablissement[lieu]"
                               value="{{ $etablissement['lieu'] }}" placeholder="Rochefort">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Articles ─────────────────────────────────────────────── --}}
        <div class="box mb-4">
            <p class="menu-label mb-1">TITRE I — Dispositions générales</p>
            <p class="is-size-7 has-text-grey mb-3">
                Dans l'article 3, utilisez <code>{DATE_DEBUT}</code> et <code>{DATE_FIN}</code>
                pour insérer les dates du stage automatiquement.
            </p>
            @foreach(['conv_art1','conv_art2','conv_art3','conv_art4','conv_art5','conv_art6','conv_art7','conv_art8','conv_art9','conv_art10','conv_art11'] as $cle)
            <div class="field mb-4">
                <label class="label is-small">
                    Titre — <code>{{ $cle }}</code>
                </label>
                <input class="input is-small mb-1" type="text"
                       name="articles[{{ $cle }}][titre]"
                       value="{{ $articles[$cle]['titre'] }}">
                <label class="label is-small">Corps de l'article</label>
                <textarea class="textarea is-small" name="articles[{{ $cle }}][corps]"
                          rows="4">{{ $articles[$cle]['corps'] }}</textarea>
            </div>
            @endforeach
        </div>

        <div class="box mb-4">
            <p class="menu-label mb-3">TITRE II — Dispositions particulières</p>
            @foreach(['conv_part1','conv_part2'] as $cle)
            <div class="field mb-4">
                <label class="label is-small">Titre — <code>{{ $cle }}</code></label>
                <input class="input is-small mb-1" type="text"
                       name="articles[{{ $cle }}][titre]"
                       value="{{ $articles[$cle]['titre'] }}">
                <label class="label is-small">Corps</label>
                <textarea class="textarea is-small" name="articles[{{ $cle }}][corps]"
                          rows="4">{{ $articles[$cle]['corps'] }}</textarea>
            </div>
            @endforeach
        </div>

        <div class="field">
            <button type="submit" class="button is-primary">
                <i class="fas fa-save mr-1"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
