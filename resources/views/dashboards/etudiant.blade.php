@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-8-desktop">
                
                <h1 class="title has-text-info">
                    <span class="icon is-medium"><i class="fas fa-user-graduate"></i></span>
                    Espace Étudiant
                </h1>
                
                <div class="box shadow-lg">
                    <p class="subtitle is-5">
                        Bienvenue, <strong>{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</strong>.
                    </p>
                    <hr>
                    <p class="mb-4">C'est ici que tu pourras gérer tes conventions de stage et suivre tes candidatures.</p>
                    
                    <div class="buttons">
                        <a href="/force-logout" class="button is-light is-danger">Se déconnecter</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection