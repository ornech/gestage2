@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Liste des stages</h1>

    {{-- ❌ Bouton supprimé : un stage ne se crée jamais depuis ici --}}
    {{-- <a href="{{ route('stages.create') }}" class="btn btn-primary mb-3">+ Ajouter un stage</a> --}}

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Maître de stage</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th style="width: 180px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stages as $stage)
                <tr>
                    <td>{{ $stage->titre }}</td>

                    <td>
                        {{ $stage->maitreDeStage?->nom ?? '—' }}
                        {{ $stage->maitreDeStage?->prenom ?? '' }}
                    </td>

                    <td>{{ \Carbon\Carbon::parse($stage->date_debut)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($stage->date_fin)->format('d/m/Y') }}</td>


                    <td>
                        @unless(auth()->user()->isEtudiant())
                            <a href="{{ route('stages.edit', $stage) }}" class="btn btn-warning btn-sm">
                                Modifier
                            </a>

                            <form action="{{ route('stages.destroy', $stage) }}" 
                                  method="POST" 
                                  style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce stage ?')">
                                    Supprimer
                                </button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @endforeach

            @if($stages->isEmpty())
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Aucun stage enregistré pour le moment.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    {{ $stages->links() }}

</div>
@endsection
