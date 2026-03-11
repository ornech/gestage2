@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Titre principal de la page --}}
    <h1 class="mb-4">Liste des employés</h1>

    {{-- Affichage d'un message de succès après une action (ajout, modification, suppression) --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Bouton pour accéder au formulaire de création d'un nouvel employé --}}
    <a href="{{ route('employes.create') }}" class="btn btn-primary mb-3">
        Ajouter un employé
    </a>

    {{-- Tableau affichant la liste des employés --}}
    <table class="table table-bordered">
        <thead>
            <tr>
                {{-- En-têtes du tableau --}}
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Entreprise</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            {{-- Boucle sur tous les employés envoyés par le contrôleur --}}
            @foreach($employes as $employe)
                <tr>
                    {{-- Affichage des informations de l'employé --}}
                    <td>{{ $employe->id }}</td>
                    <td>{{ $employe->nom }}</td>
                    <td>{{ $employe->prenom }}</td>
                    <td>{{ $employe->email }}</td>
                    <td>{{ $employe->telephone }}</td>
                    <td>{{ $employe->entreprise }}</td>

                    <td class="d-flex gap-2">

                        {{-- Bouton pour accéder au formulaire d'édition --}}
                        <a href="{{ route('employes.edit', $employe) }}" class="btn btn-warning btn-sm">
                            Modifier
                        </a>

                        {{-- Formulaire pour supprimer un employé --}}
                        <form action="{{ route('employes.destroy', $employe) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            {{-- Bouton de suppression avec confirmation --}}
                            <button class="btn btn-danger btn-sm"
                                onclick="return confirm('Supprimer cet employé ?')">
                                Supprimer
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
{{ $employes->links() }}
</div>
@endsection
