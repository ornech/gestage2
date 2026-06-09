@extends('layouts.app')

@section('content')
<div class="container">
    <div class="box mt-5">
        <h1 class="title">Modifier mon profil</h1>

        @if($errors->any())
            <div class="notification is-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label class="label">Nom</label>
                <div class="control">
                    <input class="input" type="text" name="nom"
                           value="{{ old('nom', $user->nom) }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Prénom</label>
                <div class="control">
                    <input class="input" type="text" name="prenom"
                           value="{{ old('prenom', $user->prenom) }}" required>
                </div>
            </div>

            {{-- Bloc email — adapté selon présence d'un email Pronote --}}
            <div class="field">
                <label class="label">Adresse e-mail</label>

                @if($user->email_pronote)
                    @if($user->email === $user->email_pronote)
                        {{-- L'utilisateur utilise encore l'adresse Pronote --}}
                        <div class="notification is-info is-light py-2 px-4 mb-2" style="font-size:.88rem;">
                            <i class="fas fa-info-circle mr-1"></i>
                            Votre adresse a été importée depuis <strong>Pronote</strong> :
                            <strong>{{ $user->email_pronote }}</strong>.<br>
                            En application de l'<strong>art. 21 RGPD</strong>, vous pouvez vous opposer
                            à son utilisation en saisissant une adresse alternative ci-dessous.
                        </div>
                    @else
                        {{-- L'utilisateur a déjà renseigné une adresse alternative --}}
                        <div class="notification is-success is-light py-2 px-4 mb-2" style="font-size:.88rem;">
                            <i class="fas fa-check-circle mr-1"></i>
                            Vous utilisez une adresse alternative à votre adresse Pronote
                            (<em>{{ $user->email_pronote }}</em>).
                            Vous pouvez revenir à l'adresse Pronote en resaisissant
                            <strong>{{ $user->email_pronote }}</strong> dans le champ ci-dessous.
                        </div>
                    @endif
                @endif

                <div class="control">
                    <input class="input" type="email" name="email"
                           value="{{ old('email', $user->email) }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Téléphone</label>
                <div class="control">
                    <input class="input" type="text" name="telephone"
                           value="{{ old('telephone', $user->telephone) }}">
                </div>
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-primary">Enregistrer</button>
                </div>
                <div class="control">
                    <a href="{{ route('profile.show') }}" class="button is-light">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
