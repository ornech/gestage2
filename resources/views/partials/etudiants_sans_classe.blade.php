@if(!empty($orphelins) && $orphelins->isNotEmpty())
<div class="notification is-warning is-light mb-4">
    <p class="has-text-weight-semibold mb-2">
        <i class="fas fa-user-graduate mr-1"></i>
        {{ $orphelins->count() }} étudiant(s) actif(s) sans classe en {{ $annee }} — à traiter (diplômé, redoublant ou promo à corriger).
        Tant qu'ils n'ont pas de classe, ils ne peuvent pas saisir de stage.
    </p>
    <table class="table is-narrow is-fullwidth" style="background:transparent;">
        <tbody>
        @foreach($orphelins as $e)
            <tr>
                <td>{{ $e->nom }} {{ $e->prenom }}</td>
                <td class="has-text-grey">{{ $e->promo ? 'promo '.$e->promo : 'promo non définie' }}</td>
                <td class="has-text-right" style="white-space:nowrap;">
                    @if($e->promo)
                    <form action="{{ route('admin.users.redoubler', $e) }}" method="POST" style="display:inline"
                          data-confirm="Confirmer que {{ $e->prenom }} {{ $e->nom }} redouble (promo {{ $e->promo }} → {{ $e->promo + 1 }}) ?">
                        @csrf @method('PATCH')
                        <button class="button is-small is-light">Redouble</button>
                    </form>
                    @endif
                    <form action="{{ route('admin.users.statut', $e) }}" method="POST" style="display:inline"
                          data-confirm="Confirmer que {{ $e->prenom }} {{ $e->nom }} est diplômé(e) ? Son accès sera désactivé.">
                        @csrf @method('PATCH')
                        <input type="hidden" name="statut" value="diplome">
                        <button class="button is-small is-light">Diplômé</button>
                    </form>
                    <a href="{{ route('admin.users.edit', $e) }}" class="button is-small is-light">Fiche</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
