@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container">
        <div class="columns is-centered">
            <div class="column is-8-desktop">
                
                <h1 class="title has-text-primary">
                    <span class="icon is-medium"><i class="fas fa-user-cog"></i></span>
                    Mon Profil
                </h1>
                
                <div class="box shadow-lg">
                    <p class="subtitle is-5">
                        Informations du compte
                    </p>
                    <hr>
                    <p><strong>Nom :</strong> {{ $user->nom }}</p>
                    <p><strong>Prénom :</strong> {{ $user->prenom }}</p>
                    <p><strong>Email :</strong> {{ $user->email }}</p>
                    {{-- D'autres informations (téléphone, etc.) pourraient être ajoutées ici --}}
                </div>

            </div>
        </div>
    </div>
</section>
@endsection