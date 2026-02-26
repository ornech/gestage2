<!DOCTYPE html>
<html>
<head>
    <title>Mot de passe oublié</title>
</head>
<body>
<h1>Mot de passe oublié</h1>

<form method="POST" action="/forgot-password">
    @csrf
    <input type="email" name="email" placeholder="Email"><br><br>
    <button type="submit">Envoyer le lien</button>
</form>

</body>
</html>
