@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title is-4">Annuaire des entreprises</h1>
</div>
<div class="tags has-addons is-small mb-3">
    <span class="tag is-dark">Entreprises :</span>
    <span class="tag is-link"><b>{{ $companies_count }}</b></span>
</div>

<div class="tags has-addons is-small mb-3">
    <span class="tag is-dark">Stages :</span>
    <span class="tag is-success"><b>{{ $stages_count }}</b></span>
</div>

<div class="tags has-addons is-small mb-4">
    <span class="tag is-dark">Contacts :</span>
    <span class="tag is-warning"><b>{{ $contacts_count }}</b></span>
</div>
<form method="GET" class="mb-4">
    <div class="field has-addons">
        <div class="control is-expanded">
            <input 
                type="text" 
                name="search" 
                class="input" 
                placeholder="Rechercher une entreprise..."
                value="{{ request('search') }}"
            >
        </div>
        <div class="control">
            <button class="button is-link">
                Rechercher
            </button>
        </div>
    </div>
    </form>
    <table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Nom entreprise</th>
            <th>Adresse</th>
            <th>Ville</th>
            <th>NAF</th>
            <th>Code postal</th>
            <th>Stage</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($entreprises as $entreprise)
            <tr>
                <td>
                    <a href="{{ route('entreprises.show', $entreprise->id) }}">
                        {{ $entreprise->raison_sociale }}
                    </a>
                </td>

                <td>
                    {{ $entreprise->adresse }}
                    @if($entreprise->complement_adresse)
                        <br><small>{{ $entreprise->complement_adresse }}</small>
                    @endif
                </td>

                <td>{{ $entreprise->ville }}</td>

                <td>{{ $entreprise->code_naf }}</td>

                <td>{{ $entreprise->code_postal }}</td>

                <td>-</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $entreprises->links() }}



@endsection
