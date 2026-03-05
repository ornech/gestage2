<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>

<style>
    body {
        font-family: Roboto, Arial, sans-serif;
        background: #f5f6f8;
        display:flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .login-box {
        background:white;
        border: 1px solid #0A2A43;
        width:380px;
        padding:35px;
        border-radius:6px;
    }
    h1 {
        color:#0A2A43;
        text-align:center;
        margin-bottom:30px;
        font-size:24px;
    }
    label {
        color: #0A2A43;
        font-weight: bold;
    }
    input {
        width:100%;
        padding:10px;
        margin-top:6px;
        margin-bottom:18px;
        border:1px solid #0A2A43;
        border-radius:4px;
        font-size:14px;
    }
    button {
        width:100%;
        padding : 12px;
        background: #0A2A43;
        color:white;
        border:none;
        border-radius:4px;
        font-size:16px;
        cursor:pointer;
    }
    button:hover {
        background: #09304f;
    }
    .forgot {
        display:block;
        text-align:right;
        margin-top:10px;
        color:#0A2A43;
        font-size:13px;
        text-decoration: none;
    }
    .error {
        color:red;
        font-size:13px;
        margin-top:-12px;
        margin-bottom:12px;
    }
</style>
</head>
<body>
    <div class="login-box">
    <h1>Connexion</h1>

    <form method="POST" action="/login">
        @csrf

        <label>Email :</label>
        <input type="email" name="email" required>

  
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
