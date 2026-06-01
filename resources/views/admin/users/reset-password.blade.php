@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width: 680px;">

    <div class="level mb-4">
        <div class="level-left">
            <h1 class="title is-4 mb-0">
                <span class="icon has-text-warning mr-2"><i class="fas fa-key"></i></span>
                Réinitialiser un mot de passe
            </h1>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.dashboard') }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if(session('success'))
    <div class="notification is-success is-light mb-4">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
    @endif

    <div class="box">
        <form action="{{ route('admin.reset-password.do') }}" method="POST" id="reset-form">
            @csrf
            <input type="hidden" name="user_id" id="selected-user-id">

            <div class="notification is-info is-light py-2 mb-4">
                <i class="fas fa-info-circle mr-1"></i>
                Le mot de passe sera remplacé par <strong>achanger</strong>.
                L'utilisateur devra le modifier à sa prochaine connexion.
            </div>

            <div class="field">
                <label class="label">Rechercher un utilisateur</label>
                <div style="position:relative;">
                    <input class="input" type="text" id="user-search"
                           placeholder="Tapez un nom, prénom ou email…"
                           autocomplete="off">
                    <div id="search-results" style="
                        display:none;
                        position:absolute;
                        top:100%;
                        left:0;
                        right:0;
                        z-index:100;
                        background:#fff;
                        border:1px solid #dbdbdb;
                        border-top:none;
                        border-radius:0 0 4px 4px;
                        max-height:300px;
                        overflow-y:auto;
                        box-shadow:0 4px 12px rgba(0,0,0,.1);
                    "></div>
                </div>
                @error('user_id')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div id="selected-display" style="display:none;" class="notification is-info is-light py-2 mb-3">
                <span class="icon"><i class="fas fa-user-check"></i></span>
                <span id="selected-label"></span>
                <button type="button" id="clear-selection" class="delete is-small ml-2"></button>
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-warning" id="submit-btn" disabled
                            onclick="return confirm('Réinitialiser le mot de passe de cet utilisateur ?')">
                        <i class="fas fa-key mr-1"></i> Réinitialiser
                    </button>
                </div>
                <div class="control">
                    <a href="{{ route('admin.dashboard') }}" class="button is-light">Annuler</a>
                </div>
            </div>
        </form>
    </div>

</div>

<script nonce="{{ $cspNonce ?? '' }}">
const USERS = @json($users->map(fn($u) => [
    'id'    => $u->id,
    'label' => $u->nom . ' ' . $u->prenom . ' — ' . $u->email,
]));

const searchInput  = document.getElementById('user-search');
const hiddenInput  = document.getElementById('selected-user-id');
const resultsBox   = document.getElementById('search-results');
const selectedDisp = document.getElementById('selected-display');
const selectedLbl  = document.getElementById('selected-label');
const clearBtn     = document.getElementById('clear-selection');
const submitBtn    = document.getElementById('submit-btn');

searchInput.addEventListener('input', () => {
    const q = searchInput.value.trim().toLowerCase();
    hiddenInput.value = '';
    submitBtn.disabled = true;

    if (q.length < 1) { closeResults(); return; }

    const safe    = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const matches = USERS.filter(u => u.label.toLowerCase().includes(q)).slice(0, 15);

    if (matches.length === 0) {
        resultsBox.innerHTML = '<div style="padding:10px 14px; color:#888;">Aucun résultat.</div>';
    } else {
        resultsBox.innerHTML = matches.map(u => `
            <div class="search-item" data-id="${u.id}" data-label="${escHtml(u.label)}"
                 style="padding:10px 14px; cursor:pointer; border-bottom:1px solid #f5f5f5;">
                ${u.label.replace(new RegExp(`(${safe})`, 'gi'), '<strong>$1</strong>')}
            </div>`).join('');

        resultsBox.querySelectorAll('.search-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.background = '#f0f7ff');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click', () => selectUser(item.dataset.id, item.dataset.label));
        });
    }

    resultsBox.style.display = 'block';
});

function selectUser(id, label) {
    hiddenInput.value      = id;
    searchInput.value      = '';
    selectedLbl.textContent = label;
    selectedDisp.style.display = '';
    submitBtn.disabled     = false;
    closeResults();
}

clearBtn.addEventListener('click', () => {
    hiddenInput.value = '';
    selectedDisp.style.display = 'none';
    submitBtn.disabled = true;
    searchInput.focus();
});

document.addEventListener('click', e => {
    if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) closeResults();
});

function closeResults() { resultsBox.style.display = 'none'; resultsBox.innerHTML = ''; }
function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/"/g,'&quot;'); }
</script>
@endsection
