@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Spécialités SLAM / SISR</h1>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif

    @if($classes->isEmpty())
        <p class="has-text-grey">Aucun étudiant actif trouvé.</p>
    @else
        <div class="columns is-multiline mt-3">
            @foreach($classes as $classe)
            <div class="column is-one-third">
                <div class="box">
                    <p class="title is-5">
                        <i class="fas fa-users mr-2"></i>{{ $classe }}
                    </p>
                    <a href="{{ route('spe.edit-classe', urlencode($classe)) }}"
                       class="button is-primary is-fullwidth">
                        Affecter SLAM / SISR
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
