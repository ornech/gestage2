@extends('layouts.app')

@section('content')
<div class="container">
    <div class="box mt-5">
        <h1 class="title">Conditions Générales d'Utilisation</h1>

        @if(session('success'))
            <div class="notification is-success">{{ session('success') }}</div>
        @endif

        <div class="content">
            {{-- TODO : insérer le texte des CGU --}}
            <p>Les CGU seront affichées ici.</p>
        </div>

        <form action="{{ route('cgu.accept') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="button is-primary">
                J'accepte les CGU
            </button>
        </form>
    </div>
</div>
@endsection
