@extends('layouts.app')

@section('content')
<h1>Contacts de l'entreprise {{ $company->nom }}</h1>


<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {{-- TODO: Boucle d'affichage des contacts --}}
    </tbody>
</table>
@endsection
