<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
   
  @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>Connexion</title>

</head>
<body>
    <div class="login-box">
    <h1 class="text-red-500">Connexion</h1>

    <form method="POST" action="/login">
        @csrf

        <label>Email :</label>
        <input type="email" name="email" required>

  
        @error('email') 
        <p class="text-red-500">{{ $message }}</p>
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
