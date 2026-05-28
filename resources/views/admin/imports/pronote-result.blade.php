@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:560px;">

    <h1 class="title">Import terminé</h1>

    <div class="box">
        <table class="table is-fullwidth">
            <tbody>
                <tr>
                    <td><span class="tag is-success is-light is-medium">Créés</span></td>
                    <td class="has-text-right"><strong>{{ $counts['cree'] }}</strong></td>
                </tr>
                <tr>
                    <td><span class="tag is-warning is-light is-medium">Redoublants</span></td>
                    <td class="has-text-right"><strong>{{ $counts['redoublant'] }}</strong></td>
                </tr>
                <tr>
                    <td><span class="tag is-info is-light is-medium">Mis à jour</span></td>
                    <td class="has-text-right"><strong>{{ $counts['mis_a_jour'] }}</strong></td>
                </tr>
                <tr>
                    <td><span class="tag is-danger is-light is-medium">Démissionnaires</span></td>
                    <td class="has-text-right"><strong>{{ $counts['demissionnaire'] }}</strong></td>
                </tr>
            </tbody>
        </table>

        <p class="has-text-grey is-size-7 mt-3">
            <i class="fas fa-info-circle mr-1"></i>
            Les nouveaux comptes ont le mot de passe provisoire <code>achanger</code>.
            L'affectation des spécialités SLAM/SISR se fera au second semestre.
        </p>
    </div>

    <div class="buttons">
        <a href="{{ route('admin.users.index') }}" class="button is-primary">
            <i class="fas fa-users mr-2"></i> Voir les utilisateurs
        </a>
        <a href="{{ route('imports.pronote.form') }}" class="button is-light">
            Nouvel import
        </a>
    </div>

</div>
@endsection
