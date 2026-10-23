{{-- Lien d'invitation à transmettre à la main (mode lien, ou adresse que le serveur ne peut pas joindre) --}}
@if(session('lien_invitation'))
    @php $l = session('lien_invitation'); @endphp
    <div class="notification is-warning is-light mb-4">
        <p>
            <strong>Lien à transmettre à {{ $l['nom'] }}</strong> ({{ $l['email'] }})
            <span class="has-text-grey">— aucun email envoyé : {{ $l['raison'] }}</span>
        </p>
        <div class="field has-addons mt-2">
            <div class="control is-expanded">
                <input id="lien-invitation" class="input is-small" type="text" readonly value="{{ $l['lien'] }}">
            </div>
            <div class="control">
                <button type="button" class="button is-small is-warning" data-copy="#lien-invitation">
                    <i class="fas fa-copy mr-1"></i> Copier
                </button>
            </div>
        </div>
        <p class="help">
            Lien personnel, à usage unique, valable 7 jours. Ne le transmettez qu'à cette personne :
            il permet de choisir le mot de passe du compte.
        </p>
    </div>
@endif
