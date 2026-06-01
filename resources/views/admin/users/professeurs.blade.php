@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="level mb-4">
        <div class="level-left">
            <h1 class="title is-4 mb-0">
                <span class="icon has-text-link mr-2"><i class="fas fa-chalkboard-teacher"></i></span>
                Gestion des professeurs
            </h1>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.dashboard') }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light py-2 mb-4">{{ session('success') }}</div>
    @endif

    <div class="box">
        <table class="table is-fullwidth is-hoverable is-size-7">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôles</th>
                    <th class="has-text-right">Droits admin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($professeurs as $prof)
                <tr>
                    <td>
                        <strong>{{ $prof->nom }}</strong> {{ $prof->prenom }}
                    </td>
                    <td class="has-text-grey">{{ $prof->email }}</td>
                    <td>
                        <span class="tag is-link is-light">Professeur</span>
                        @if($prof->hasRole('Administrateur'))
                            <span class="tag is-danger is-light ml-1">Administrateur</span>
                        @endif
                    </td>
                    <td class="has-text-right">
                        @if($prof->id === auth()->id())
                            <span class="tag is-light has-text-grey">Votre compte</span>
                        @else
                            <form action="{{ route('admin.users.toggle-admin', $prof) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                @if($prof->hasRole('Administrateur'))
                                    <button type="submit"
                                            class="button is-small is-danger is-light"
                                            onclick="return confirm('Retirer les droits admin à {{ $prof->prenom }} {{ $prof->nom }} ?')">
                                        <span class="icon"><i class="fas fa-shield-alt"></i></span>
                                        <span>Retirer admin</span>
                                    </button>
                                @else
                                    <button type="submit"
                                            class="button is-small is-warning is-light"
                                            onclick="return confirm('Donner les droits admin à {{ $prof->prenom }} {{ $prof->nom }} ?')">
                                        <span class="icon"><i class="fas fa-shield-alt"></i></span>
                                        <span>Donner admin</span>
                                    </button>
                                @endif
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr><td colspan="4" class="has-text-grey has-text-centered">Aucun professeur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
