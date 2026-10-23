@if(!empty($orphelins) && $orphelins->isNotEmpty())
<div class="notification is-warning is-light mb-4">
    <p class="has-text-weight-semibold mb-2">
        <i class="fas fa-user-graduate mr-1"></i>
        {{ $orphelins->count() }} étudiant(s) actif(s) sans classe en {{ $annee }} — à traiter (diplômé, redoublant ou promo à corriger).
        Tant qu'ils n'ont pas de classe, ils ne peuvent pas saisir de stage.
    </p>

    <form action="{{ route('admin.users.action-groupee') }}" method="POST" id="form-sans-classe">
        @csrf
        <div style="max-height:360px; overflow-y:auto;" class="mb-3">
            <table class="table is-narrow is-fullwidth" style="background:transparent;">
                <tbody>
                @foreach($orphelins->groupBy(fn($e) => $e->promo ?? 0)->sortKeysDesc() as $promo => $groupe)
                    <tr style="background:rgba(0,0,0,.05);">
                        <td style="width:2rem;">
                            <input type="checkbox" class="cocher-promo" data-promo="{{ $promo }}" aria-label="Cocher toute la promo">
                        </td>
                        <td colspan="2" class="has-text-weight-semibold">
                            {{ $promo ? 'Promo '.$promo : 'Promo non définie' }} — {{ $groupe->count() }} étudiant(s)
                        </td>
                    </tr>
                    @foreach($groupe as $e)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $e->id }}" class="cocher-etudiant" data-promo="{{ $promo }}"></td>
                        <td>{{ $e->nom }} {{ $e->prenom }}</td>
                        <td class="has-text-right"><a href="{{ route('admin.users.edit', $e) }}" class="button is-small is-light">Fiche</a></td>
                    </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="buttons">
            <button type="submit" name="action" value="diplome" class="button is-small is-link"
                    data-confirm="Marquer les étudiants cochés comme diplômés ? Leur accès à l'application sera désactivé.">
                <i class="fas fa-graduation-cap mr-1"></i> Marquer diplômés
            </button>
            <button type="submit" name="action" value="redouble" class="button is-small is-light"
                    data-confirm="Faire redoubler les étudiants cochés (promo + 1) ?">
                <i class="fas fa-redo mr-1"></i> Redoublent (promo + 1)
            </button>
            <span class="is-size-7 has-text-grey">Coche des étudiants ou toute une promo, puis choisis l'action.</span>
        </div>
    </form>
</div>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    document.querySelectorAll('.cocher-promo').forEach((entete) => {
        entete.addEventListener('change', () => {
            document.querySelectorAll('.cocher-etudiant[data-promo="' + entete.dataset.promo + '"]')
                .forEach((c) => { c.checked = entete.checked; });
        });
    });
</script>
@endpush
@endif
