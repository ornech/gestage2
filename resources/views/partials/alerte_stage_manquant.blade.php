@auth
    @role('Etudiant')
    @php
        $convPapier = auth()->user()->conventionPapier;
        $aStage     = auth()->user()->stages()->exists();
    @endphp
    @if($convPapier && !$aStage)
        <div style="background:#fff3cd; border-bottom:3px solid #ff9800; padding:12px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <strong>⚠ Action requise</strong> —
                Votre convention de stage a été enregistrée par votre professeur
                (statut : <strong>{{ match($convPapier->statut) {
                    'a_faire_signer'       => "à faire signer par l'employeur",
                    'en_attente' => 'en attente de signature',
                    'validee'      => 'remise ✓',
                    default                => $convPapier->statut,
                } }}</strong>),
                mais les informations de votre stage ne sont pas encore saisies dans l'application.
                <strong>Vous devez les renseigner pour accéder au journal de bord.</strong>
            </div>
            <a href="{{ route('entreprises.index') }}"
               class="button is-warning is-small ml-4" style="white-space:nowrap;">
                Saisir mon stage →
            </a>
        </div>
    @endif
    @endrole
@endauth
