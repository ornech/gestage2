@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Prévisualisation — {{ count($preview) }} élève(s)</h1>

    @php
        $nouveaux  = collect($preview)->where('action', 'create')->count();
        $existants = count($preview) - $nouveaux;
    @endphp

    <div class="tags mb-4">
        <span class="tag is-success is-medium">
            <i class="fas fa-user-plus mr-1"></i> {{ $nouveaux }} nouveau(x)
        </span>
        <span class="tag is-info is-medium">
            <i class="fas fa-sync mr-1"></i> {{ $existants }} compte(s) existant(s) mis à jour
        </span>
    </div>

    <div class="table-container">
        <table class="table is-striped is-fullwidth is-hoverable is-size-7">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Classe</th>
                    <th>Promo</th>
                    <th>Entrée</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($preview as $row)
                <tr>
                    <td>{{ $row['nom'] }}</td>
                    <td>{{ $row['prenom'] }}</td>
                    <td>{{ $row['email'] }}</td>
                    <td>{{ $row['classe'] }}</td>
                    <td>{{ $row['promo'] }}</td>
                    <td>{{ $row['date_entree'] }}</td>
                    <td>
                        @if($row['action'] === 'create')
                            <span class="tag is-success is-light">Nouveau</span>
                        @else
                            <span class="tag is-info is-light">Mise à jour</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="field is-grouped mt-5">
        <div class="control">
            <form action="{{ route('imports.pronote.confirm') }}" method="POST">
                @csrf
                <button type="submit" class="button is-primary">
                    <i class="fas fa-check mr-2"></i> Confirmer l'import
                </button>
            </form>
        </div>
        <div class="control">
            <a href="{{ route('imports.pronote.form') }}" class="button is-light">Annuler</a>
        </div>
    </div>

</div>
@endsection
