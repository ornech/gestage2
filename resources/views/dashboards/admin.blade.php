@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-8-desktop">
                
                <h1 class="title has-text-danger">
                    <span class="icon is-medium"><i class="fas fa-cogs"></i></span>
                    Console d'Administration
                </h1>
                
                <div class="box shadow-lg">
                    <p class="subtitle is-5">
                        Session active : <strong>{{ auth()->user()->email }}</strong>
                    </p>
                    <hr>
                    <p class="mb-4">Gestion globale de la plateforme, paramétrage du système et administration des comptes utilisateurs.</p>
                    
                    <div class="buttons">
                        <a href="/force-logout" class="button is-light is-danger">Se déconnecter</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection