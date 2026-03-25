@extends('layouts.app')

@section('content')
<div class="container mt-6">
    <h1 class="title">Gestion des stages (Admin)</h1>
    @if($stages->count() === 0)
        <div class="notification is-warning">
            Aucun stage enregistré pour le moment.
        </div>
    @else
        <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Entreprise</th>
                    <th>Tuteur Pro</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($stages as $stage)
                    <tr>
                        <td>{{ $stage->etudiant->name ?? '—' }}</td>
                        <td>{{ $stage->entreprise->nom ?? '—' }}</td>
                        <td>{{ $stage->maitreDeStage->nom ?? 'Non assigné' }}</td>
                        <td>{{ $stage->date_debut }}</td>
                        <td>{{ $stage->date_fin }}</td>
                        <td>
                            <button class="button is-small is-info">Assigner tuteur</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $stages->links() }}
    @endif
</div>
@endsection
