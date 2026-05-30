@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Spécialités — {{ $classe }}</h1>
    <p class="subtitle has-text-grey">{{ $etudiants->count() }} étudiant(s) actif(s)</p>

    @if($errors->any())
        <div class="notification is-danger is-light">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form action="{{ route('spe.update-classe', urlencode($classe)) }}" method="POST">
        @csrf

        <div class="table-container">
            <div class="table-scroll"><table class="table is-striped is-fullwidth">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th class="has-text-centered" style="width:120px;">SLAM</th>
                        <th class="has-text-centered" style="width:120px;">SISR</th>
                        <th class="has-text-centered" style="width:120px;">Non défini</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($etudiants as $etudiant)
                    <tr>
                        <td>{{ $etudiant->nom }}</td>
                        <td>{{ $etudiant->prenom }}</td>
                        <td class="has-text-centered">
                            <input type="radio"
                                   name="spe[{{ $etudiant->id }}]"
                                   value="SLAM"
                                   {{ $etudiant->spe === 'SLAM' ? 'checked' : '' }}>
                        </td>
                        <td class="has-text-centered">
                            <input type="radio"
                                   name="spe[{{ $etudiant->id }}]"
                                   value="SISR"
                                   {{ $etudiant->spe === 'SISR' ? 'checked' : '' }}>
                        </td>
                        <td class="has-text-centered">
                            <input type="radio"
                                   name="spe[{{ $etudiant->id }}]"
                                   value=""
                                   {{ is_null($etudiant->spe) ? 'checked' : '' }}>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>

        <div class="field is-grouped mt-4">
            <div class="control">
                <button type="submit" class="button is-primary">
                    <i class="fas fa-save mr-2"></i> Enregistrer
                </button>
            </div>
            <div class="control">
                <a href="{{ route('spe.index') }}" class="button is-light">Retour</a>
            </div>
        </div>
    </form>

</div>
@endsection
