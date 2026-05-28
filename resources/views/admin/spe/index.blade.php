@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <div class="level">
        <div class="level-left">
            <h1 class="title">Affectation des spécialités</h1>
        </div>
        @role('Administrateur')
        <div class="level-right">
            <a href="{{ route('admin.parametres.index') }}" class="button is-light">
                <i class="fas fa-cog mr-2"></i> Gérer dans Paramètres
            </a>
        </div>
        @endrole
    </div>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif

    @if(!$isOpen)
        <div class="notification is-warning is-light">
            <i class="fas fa-clock mr-2"></i>
            L'affectation des spécialités est fermée. Elle sera ouverte par l'administration au second semestre.
        </div>
    @endif

    @if($classes->isEmpty())
        <p class="has-text-grey">Aucun étudiant actif trouvé.</p>
    @else
        <div class="columns is-multiline mt-3">
            @foreach($classes as $classe)
            <div class="column is-one-third">
                <div class="box">
                    <p class="title is-5"><i class="fas fa-users mr-2"></i>{{ $classe }}</p>
                    @if($isOpen)
                        <a href="{{ route('spe.edit-classe', urlencode($classe)) }}"
                           class="button is-primary is-fullwidth">
                            Affecter SLAM / SISR
                        </a>
                    @else
                        <button class="button is-fullwidth" disabled>
                            Disponible au second semestre
                        </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
