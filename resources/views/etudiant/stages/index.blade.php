@extends('layouts.app')

@section('content')
    <h1>Mes stages</h1>

    @can('create', App\Models\Stage::class)
        <a href="{{ route('stages.create') }}" class="btn btn-primary">Créer un stage</a>
    @endcan

    @if($stages->isEmpty())
        <p>Aucun stage pour le moment.</p>
    @else
        @foreach($stages as $stage)
            <div class="card mb-3">
                <div class="card-body">
                    <h3>{{ $stage->titre }}</h3>
                    <p>Entreprise : {{ $stage->entreprise->nom ?? 'N/A' }}</p>
                    <p>Début : {{ $stage->date_debut }}</p>
                    <p>Fin : {{ $stage->date_fin }}</p>

                    @can('update', $stage)
                        <a href="{{ route('stages.edit', $stage) }}" class="btn btn-sm btn-warning">Modifier</a>
                    @endcan

                    @can('delete', $stage)
                        <form method="POST" action="{{ route('stages.destroy', $stage) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    @endcan
                </div>
            </div>
        @endforeach
    @endif
@endsection
