<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>

    <!-- Bulma-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">

</head>

<body>
    <section class="section">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-5">

                    <h1 class="title has-text-centered">Inscription</h1>

                    @if ($errors->any())
                        <div class="notification is-danger">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="box">
                        <form method="POST" action="/register">
                            @csrf

                            <div class="field">
                                <label class="label">Nom</label>
                                <div class="control">
                                    <input class="input" type="text" name="nom" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Prénom</label>
                                <div class="control">
                                    <input class="input" type="text" name="prenom" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Email</label>
                                <div class="control">
                                    <input class="input" type="email" name="email" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Mot de passe</label>
                                <div class="control">
                                    <input class="input" type="password" name="password" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Confirmer le mot de passe</label>
                                <div class="control">
                                    <input class="input" type="password" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="field mt-4">
                                <button class="button is-primary is-fullwidth" type="submit">
                                    S'inscrire
                                </button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>
</body>
</html>
