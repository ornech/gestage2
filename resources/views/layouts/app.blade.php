<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Connexion' }}</title>

    <!-- Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css">

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
    </style>
</head>

<body>
    @yield('content')
</body>
</html>
