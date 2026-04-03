@extends('layouts.app')

@section('content')
<div class="container">

    <h1 class="title is-3">Mes Conventions</h1>
    <p class="subtitle is-6">Sélectionne ton stage pour obtenir la convention ou accéder au journal de bord.</p>

    @if($stages->isEmpty())
        <div class="notification is-warning">
            Aucun stage enregistré pour le moment.
        </div>
    @else
        @foreach($stages as $stage)
            <div class="box">

                <h2 class="title is-4">{{ $stage->titre }}</h2>
                <p><strong>Durée :</strong> 
                    {{ \Carbon\Carbon::parse($stage->date_debut)->format('d/m/Y') }} 
                    – 
                    {{ \Carbon\Carbon::parse($stage->date_fin)->format('d/m/Y') }}
                </p>

                <p><strong>Entreprise :</strong> 
                    {{ $stage->entreprise->raison_sociale ?? '-' }}
                </p>

                <div class="buttons mt-3">

                    {{-- Bouton Convention PDF --}}
                    <a href="{{ route('pdf.convention', $stage) }}" 
                       class="button is-info">
                        <i class="fas fa-file-pdf mr-2"></i> Télécharger la convention
                    </a>

                    {{-- Journal de bord --}}
                    <a href="#" class="button is-warning">
                        <i class="fas fa-book mr-2"></i> Journal de bord
                    </a>

                    {{-- Détails du stage --}}
                    <a href="{{ route('stages.show', $stage) }}" 
                       class="button is-light">
                        <i class="fas fa-eye mr-2"></i> Plus de détails
                    </a>

                </div>

            </div>
        @endforeach
    @endif

</div>
@endsection
