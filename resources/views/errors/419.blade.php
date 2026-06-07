@extends('layouts.app')

@section('content')
<section class="hero is-fullheight-with-navbar">
    <div class="hero-body">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-8-mobile is-6-tablet is-5-desktop is-4-widescreen">

                    <div class="box shadow-lg has-text-centered">
                        <span class="icon is-large has-text-warning mb-3">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </span>
                        <h1 class="title is-4 has-text-grey-dark">Session expirée</h1>
                        <p class="mb-4">
                            Votre session a expiré, merci de recharger votre page.
                        </p>
                        <button onclick="window.location.reload()" class="button is-primary is-medium">
                            <span class="icon"><i class="fas fa-sync-alt"></i></span>
                            <span>Recharger la page</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
