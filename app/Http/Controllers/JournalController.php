<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Stage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Stage $stage, \Illuminate\Http\Request $request)
    {
        $this->authorize('view', $stage);

        [$nbSemaines, $autoSemaine] = $this->weekContext($stage);

        // ?semaine=X depuis le bouton de stages/show prend la priorité sur la semaine courante
        $requested = (int) $request->query('semaine', 0);
        $selectedSemaine = ($requested >= 1 && $requested <= $nbSemaines) ? $requested : $autoSemaine;

        $entries = $stage->journalEntries()
            ->orderBy('semaine')
            ->get()
            ->groupBy('semaine');

        return view('journal.index', compact('stage', 'entries', 'nbSemaines', 'selectedSemaine'));
    }

    public function create(Stage $stage)
    {
        return redirect()->route('stages.journal.index', $stage);
    }

    public function store(Request $request, Stage $stage)
    {
        $this->authorize('manageJournal', $stage);

        $request->validate([
            'semaine'        => 'required|integer|min:1|max:52',
            'titre'          => 'required|string|max:255',
            'activites'      => 'required|string',
            'competences'    => 'nullable|array',
            'competences.*'  => 'integer|in:1,2,4,8,16,32',
        ]);

        $semaine  = (int) $request->semaine;
        $bitmask  = array_sum($request->competences ?? []);
        $dateDebut = $stage->date_debut
            ? Carbon::parse($stage->date_debut)->addDays(($semaine - 1) * 7)
            : now();

        $stage->journalEntries()->create([
            'user_id'            => auth()->id(),
            'semaine'            => $semaine,
            'date_debut_semaine' => $dateDebut,
            'titre'              => $request->titre,
            'activites'          => $request->activites,
            'competences'        => $bitmask ?: null,
        ]);

        return redirect()->route('stages.journal.index', $stage)
                         ->with('success', 'Réalisation ajoutée.');
    }

    public function edit(Stage $stage, JournalEntry $entry)
    {
        return redirect()->route('stages.journal.index', $stage);
    }

    public function update(Request $request, Stage $stage, JournalEntry $entry)
    {
        $this->authorize('manageJournal', $stage);

        $request->validate([
            'titre'          => 'required|string|max:255',
            'activites'      => 'required|string',
            'competences'    => 'nullable|array',
            'competences.*'  => 'integer|in:1,2,4,8,16,32',
        ]);

        $entry->update([
            'titre'       => $request->titre,
            'activites'   => $request->activites,
            'competences' => array_sum($request->competences ?? []) ?: null,
        ]);

        return redirect()->route('stages.journal.index', $stage)
                         ->with('success', 'Réalisation modifiée.');
    }

    public function destroy(Stage $stage, JournalEntry $entry)
    {
        $this->authorize('manageJournal', $stage);

        $entry->delete();

        return redirect()->route('stages.journal.index', $stage)
                         ->with('success', 'Réalisation supprimée.');
    }

    private function weekContext(Stage $stage): array
    {
        $debut = $stage->date_debut ? Carbon::parse($stage->date_debut) : now();
        $fin   = $stage->date_fin   ? Carbon::parse($stage->date_fin)   : $debut->copy()->addWeeks(8);

        $nbSemaines = max(1, (int) ceil($debut->diffInDays($fin) / 7));

        $today           = now()->startOfDay();
        $selectedSemaine = 0;

        for ($i = 1; $i <= $nbSemaines; $i++) {
            $start = $debut->copy()->addDays(($i - 1) * 7);
            $end   = $debut->copy()->addDays($i * 7 - 1)->endOfDay();
            if ($today->between($start, $end)) {
                $selectedSemaine = $i;
                break;
            }
        }

        return [$nbSemaines, $selectedSemaine];
    }
}
