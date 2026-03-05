@extends('layouts.app')

@section('content')

<div class="login-box">
    <h1>Connexion</h1>

    <form method="POST" action="/login">
        @csrf

        <label>Email :</label>
        <input type="email" name="email" required>

        @error('email') 
            <p style="color:red;">{{ $message }}</p>
        @enderror

        <label>Mot de passe :</label>
        <input type="password" name="password" required>

        @error('password')
            <p style="color:red;">{{ $message }}</p>
        @enderror

        <button type="submit" class="button is-primary">Se connecter</button>
    </form>
</div>

@endsection
