<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>

    <form method="POST" action="/login">
        @csrf

        <label>Email :</label>
        <input type="email" name="email" required>

    //ajout des directives d'affichage des erreurs de validation
        @error('email') 
        <p style="color:red;">{{ $message }}</p>
         @enderror

        <br><br>

        <label>Mot de passe :</label>
        <input type="password" name="password" required>

        @error('password')
         <p style="color:red;">{{ $message }}</p>
          @enderror

        <br><br>

        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
