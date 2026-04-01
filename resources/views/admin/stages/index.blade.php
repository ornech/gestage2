    @extends('layouts.app')

    @section('content')
    <div class="container mt-6">
        <h1 class="title">Gestion des stages (Admin)</h1>
        
    @if(session('success'))
        <div class="notification is-success">
            {{ session('success') }}
        </div>
    @endif

        @if($stages->count() === 0)
            <div class="notification is-warning">
                Aucun stage enregistré pour le moment.
            </div>
        @else
        <form method="GET" class="mb-4">
    <div class="field is-grouped">

        {{-- Filtre par promo --}}
        <div class="control">
            <div class="select">
                <select name="promo" onchange="this.form.submit()">
                    <option value="">Toutes les promos</option>
                    <option value="SIO1" {{ request('promo') == 'SIO1' ? 'selected' : '' }}>SIO1</option>
                    <option value="SIO2" {{ request('promo') == 'SIO2' ? 'selected' : '' }}>SIO2</option>
                </select>
            </div>
        </div>

        {{-- Filtre par statut (si vous en avez un) --}}
        {{-- On peut le laisser vide pour l'instant --}}
    </div>
</form>

            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Entreprise</th>
                        <th>Tuteur Pro</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Promo</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($stages as $stage)
                        <tr>
                            <td>{{ $stage->etudiant->name ?? '—' }}</td>
                            <td>{{ $stage->entreprise->nom ?? '—' }}</td>
                            <td>{{ $stage->etudiant->classe ?? '—' }}</td>
                            <td>{{ $stage->date_debut }}</td>
                            <td>{{ $stage->date_fin }}</td>
                            <td>
        <form action="{{ route('admin.stages.assign', $stage->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="select is-small">
                <select name="maitre_de_stage_id" onchange="this.form.submit()">
                    <option value="">— Choisir —</option>

                    @foreach($tuteurs as $tuteur)
                        <option value="{{ $tuteur->id }}"
                            {{ $stage->maitre_de_stage_id == $tuteur->id ? 'selected' : '' }}>
                            {{ $tuteur->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
                    </form>
        </td>
                            <td>
    <a href="{{ route('stages.edit', $stage->id) }}" class="button is-small is-warning">
        Modifier
    </a>

    <form action="{{ route('stages.destroy', $stage->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button class="button is-small is-danger" onclick="return confirm('Supprimer ce stage ?')">
            Supprimer
        </button>
    </form>
</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $stages->links() }}
        @endif
    </div>
    @endsection
