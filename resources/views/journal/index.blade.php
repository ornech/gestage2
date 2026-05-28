@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Journal de bord</h1>
    <h2 class="subtitle">{{ $stage->entreprise->raison_sociale ?? '' }} — {{ $stage->date_debut?->format('d/m/Y') }}</h2>

    @if(session('success'))
        <div class="notification is-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('stages.journal.create', $stage) }}" class="button is-primary mb-4">
        Ajouter une semaine
    </a>

    @if($entries->isEmpty())
        <p class="has-text-grey">Aucune entrée pour l'instant.</p>
    @else
        @foreach($entries as $entry)
        <div class="box">
            <div class="level">
                <div class="level-left">
                    <strong>Semaine {{ $entry->semaine }}</strong>
                    <span class="ml-3 has-text-grey">
                        {{ $entry->date_debut_semaine?->format('d/m/Y') }}
                    </span>
                </div>
                <div class="level-right">
                    <a href="{{ route('stages.journal.edit', [$stage, $entry]) }}"
                       class="button is-small is-warning mr-2">Éditer</a>
                    <form action="{{ route('stages.journal.destroy', [$stage, $entry]) }}"
                          method="POST" style="display:inline">
                        @csrf @method('DELETE')
                        <button class="button is-small is-danger"
                                onclick="return confirm('Supprimer cette entrée ?')">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
            <p><strong>Activités :</strong> {{ $entry->activites }}</p>
            @if($entry->competences)
                <p><strong>Compétences :</strong> {{ $entry->competences }}</p>
            @endif
        </div>
        @endforeach
    @endif

    <a href="{{ route('stages.show', $stage) }}" class="button is-light mt-4">Retour au stage</a>

</div>
@endsection
