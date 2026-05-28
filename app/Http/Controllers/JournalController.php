<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Stage;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Stage $stage)
    {
        $this->authorize('view', $stage);

        $entries = $stage->journalEntries()->orderBy('semaine')->get();

        return view('journal.index', compact('stage', 'entries'));
    }

    public function create(Stage $stage)
    {
        $this->authorize('update', $stage);

        return view('journal.create', compact('stage'));
    }

    public function store(Request $request, Stage $stage)
    {
        $this->authorize('update', $stage);

        $request->validate([
            'semaine'           => 'required|integer|min:1|max:52',
            'date_debut_semaine'=> 'required|date',
            'activites'         => 'required|string',
            'competences'       => 'nullable|string',
        ]);

        $stage->journalEntries()->create([
            'user_id'            => auth()->id(),
            'semaine'            => $request->semaine,
            'date_debut_semaine' => $request->date_debut_semaine,
            'activites'          => $request->activites,
            'competences'        => $request->competences,
        ]);

        return redirect()->route('stages.journal.index', $stage)
                         ->with('success', 'Entrée ajoutée.');
    }

    public function edit(Stage $stage, JournalEntry $entry)
    {
        $this->authorize('update', $stage);

        return view('journal.edit', compact('stage', 'entry'));
    }

    public function update(Request $request, Stage $stage, JournalEntry $entry)
    {
        $this->authorize('update', $stage);

        $request->validate([
            'activites'   => 'required|string',
            'competences' => 'nullable|string',
        ]);

        $entry->update($request->only('activites', 'competences'));

        return redirect()->route('stages.journal.index', $stage)
                         ->with('success', 'Entrée mise à jour.');
    }

    public function destroy(Stage $stage, JournalEntry $entry)
    {
        $this->authorize('update', $stage);

        $entry->delete();

        return redirect()->route('stages.journal.index', $stage)
                         ->with('success', 'Entrée supprimée.');
    }
}
