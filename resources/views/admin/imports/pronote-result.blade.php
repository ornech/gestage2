@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Import terminé</h1>

    <div class="box">
        <table class="table is-fullwidth">
            <tbody>
                <tr>
                    <td><span class="tag is-success is-light is-medium">Créés</span></td>
                    <td class="has-text-right"><strong>{{ $counts['cree'] }}</strong></td>
                </tr>
                <tr>
                    <td><span class="tag is-warning is-light is-medium">Promo incrémentée</span></td>
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
            Chaque nouveau compte reçoit un email d'invitation pour choisir son mot de passe
            ({{ $counts['invites'] }} envoi(s) en file, partis progressivement).
            L'affectation des spécialités SLAM/SISR se fera au second semestre.
        </p>
    </div>

    @if(!empty($liens))
    <div class="notification is-warning is-light">
        <p class="mb-2"><strong>{{ count($liens) }} compte(s) sans adresse email distribuable</strong> —
        transmettez-leur ce lien personnel (valable 7 jours, usage unique) :</p>
        <ul>
            @foreach($liens as $l)
                <li class="mb-1"><strong>{{ $l['nom'] }}</strong> : <code style="word-break:break-all;">{{ $l['lien'] }}</code></li>
            @endforeach
        </ul>
    </div>
    @endif

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
