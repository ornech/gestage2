@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <div class="level mb-4">
        <div class="level-left">
            <h1 class="title is-4 mb-0">{{ $employe->prenom }} {{ $employe->nom }}</h1>
        </div>
        <div class="level-right">
            <a href="{{ route('entreprises.show', $employe->entreprise_id) }}"
               class="button is-light is-small">← Retour à l'entreprise</a>
        </div>
    </div>

    <div class="box">
        <p class="menu-label">Coordonnées</p>
        <table class="table is-fullwidth is-size-7">
            <tbody>
                <tr>
                    <th style="width:40%">Entreprise</th>
                    <td>
                        <a href="{{ route('entreprises.show', $employe->entreprise_id) }}">
                            {{ $employe->entreprise?->raison_sociale ?? '—' }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $employe->email ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td>{{ $employe->telephone ?? '—' }}</td>
                </tr>
                @if($employe->service)
                <tr>
                    <th>Service</th>
                    <td>{{ $employe->service }}</td>
                </tr>
                @endif
                @if($employe->fonction)
                <tr>
                    <th>Fonction</th>
                    <td>{{ $employe->fonction }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>
@endsection
