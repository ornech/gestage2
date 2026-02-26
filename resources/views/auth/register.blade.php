<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
    <h1>Inscription</h1>

    @if ($errors->any())
        <div style="color:red;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="/register">
        @csrf

        <label>Nom :</label>
        <input type="text" name="name" required>

        <br><br>

        <label>Email :</label>
        <input type="email" name="email" required>

        <br><br>

        <label>Mot de passe :</label>
        <input type="password" name="password" required>

        <br><br>

        <label>Confirmer le mot de passe :</label>
        <input type="password" name="password_confirmation" required>

        <br><br>

        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
