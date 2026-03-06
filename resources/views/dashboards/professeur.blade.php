@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-8-desktop">
                
                <h1 class="title has-text-success">
                    <span class="icon is-medium"><i class="fas fa-chalkboard-teacher"></i></span>
                    Tableau de bord Professeur
                </h1>
                
                <div class="box shadow-lg">
                    <p class="subtitle is-5">
                        Bienvenue, <strong>{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</strong>.
                    </p>
                    <hr>
                    <p class="mb-4">Supervision des étudiants, validation des offres d'entreprise et suivi des conventions.</p>
                    
                    <div class="buttons">
                        <a href="#" class="button is-success">Liste des étudiants</a>
                        <a href="/force-logout" class="button is-light is-danger">Se déconnecter</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection