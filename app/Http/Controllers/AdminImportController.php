<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class AdminImportController extends Controller
{
    public function pronoteForm()
    {
        return view('admin.imports.pronote');
    }

    public function pronotePreview(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $rows    = $this->parseCsv($request->file('fichier'));
        $preview = $this->analyzeRows($rows);

        session(['pronote_preview' => $preview]);

        return view('admin.imports.pronote-preview', compact('preview'));
    }

    public function pronoteConfirm(Request $request)
    {
        $preview = session('pronote_preview');

        if (!$preview) {
            return redirect()->route('imports.pronote.form')
                ->withErrors('Session expirée, veuillez relancer l\'import.');
        }

        $counts = ['cree' => 0, 'redoublant' => 0, 'mis_a_jour' => 0, 'demissionnaire' => 0];

        foreach ($preview as $row) {
            if ($row['action'] === 'create') {
                $user = User::create([
                    'nom'                   => $row['nom'],
                    'prenom'                => $row['prenom'],
                    'email'                 => $row['email'],
                    'password'              => Hash::make('achanger'),
                    'force_password_change' => true,
                    'classe'                => $row['classe'],
                    'promo'                 => $row['promo'],
                    'date_entree'           => $row['date_entree'],
                    'date_sortie'           => $row['date_sortie'],
                    'statut'                => $row['statut'],
                ]);
                $user->assignRole('Etudiant');
                $counts['cree']++;

            } elseif ($row['action'] === 'redoublant') {
                // Un redoublant reste actif, sa promo est incrémentée
                $existing = User::find($row['existing_id']);
                User::where('id', $row['existing_id'])->update([
                    'classe'      => $row['classe'],
                    'promo'       => $existing ? $existing->promo + 1 : $row['promo'],
                    'date_entree' => $row['date_entree'],
                    'statut'      => 'actif',
                ]);
                $counts['redoublant']++;

            } elseif ($row['action'] === 'update') {
                User::where('id', $row['existing_id'])->update([
                    'classe'      => $row['classe'],
                    'promo'       => $row['promo'],
                    'date_entree' => $row['date_entree'],
                    'statut'      => $row['statut'],
                ]);
                $counts['mis_a_jour']++;

            } elseif ($row['action'] === 'demissionnaire') {
                User::where('id', $row['existing_id'])->update([
                    'statut'      => 'demissionnaire',
                    'date_sortie' => $row['date_sortie'],
                ]);
                $counts['demissionnaire']++;
            }
        }

        session()->forget('pronote_preview');

        return view('admin.imports.pronote-result', compact('counts'));
    }

    // -----------------------------------------------------------------------
    // Parsing
    // -----------------------------------------------------------------------

    private function parseCsv(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());

        // Supprimer le BOM UTF-8
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($content));

        // Lire l'en-tête et repérer les colonnes par leur NOM
        $headerLine = array_shift($lines);
        $header     = str_getcsv($headerLine, ';');

        $idx = $this->resolveColumnIndices($header);

        $rows = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $fields = str_getcsv($line, ';');

            $email = strtolower(trim($fields[$idx['email']] ?? '', " \t\n\r\0\x0B\""));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $rows[] = [
                'eleve'       => trim($fields[$idx['eleve']]       ?? '', '"'),
                'email'       => $email,
                'classe'      => trim($fields[$idx['classe']]      ?? '', '"'),
                'date_entree' => trim($fields[$idx['date_entree']] ?? '', '"'),
                'date_sortie' => trim($fields[$idx['date_sortie']] ?? '', '"'),
            ];
        }

        return $rows;
    }

    private function resolveColumnIndices(array $header): array
    {
        // Indices par défaut (format Pronote standard)
        $idx = ['eleve' => 0, 'email' => 4, 'classe' => 5, 'date_entree' => 6, 'date_sortie' => 7];

        foreach ($header as $i => $col) {
            // Normaliser : minuscules, sans accents ni caractères spéciaux
            $n = mb_strtolower(trim($col));
            $n = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $n);
            $n = preg_replace('/[^a-z0-9]/', '', $n);

            if (str_contains($n, 'lve') || str_contains($n, 'lve')) $idx['eleve']       = $i; // Élèves
            elseif ($n === 'classe')                                  $idx['classe']      = $i;
            elseif (str_contains($n, 'mail'))                        $idx['email']       = $i;
            elseif (str_contains($n, 'entre'))                       $idx['date_entree'] = $i; // Entrée
            elseif (str_contains($n, 'sortie'))                      $idx['date_sortie'] = $i;
        }

        return $idx;
    }

    private function analyzeRows(array $rows): array
    {
        $annee       = Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));
        $currentYear = (int) explode('-', $annee)[0];

        $preview = [];

        foreach ($rows as $row) {
            ['nom' => $nom, 'prenom' => $prenom] = $this->parseName($row['eleve']);
            ['classe' => $classe, 'annee' => $anneeClasse] = $this->parseClasse($row['classe']);

            // promo = année de début de l'année scolaire + années restantes
            $promo = $currentYear + (3 - $anneeClasse);

            $dateEntree  = $this->parseDate($row['date_entree']);
            $dateSortie  = $this->parseDate($row['date_sortie']);

            $statut = ($dateSortie && $dateSortie->isPast()) ? 'demissionnaire' : 'actif';

            // 1er filet : recherche par email
            $existing = User::where('email', $row['email'])->first();

            // 2e filet : nom+prénom si email non trouvé (ex. faute de frappe dans le CSV)
            if (!$existing) {
                $existing = User::where('nom', $nom)
                    ->where('prenom', $prenom)
                    ->role('Etudiant')
                    ->first();
            }

            if ($existing) {
                if ($statut === 'demissionnaire') {
                    $action = 'demissionnaire';
                } elseif ($existing->classe === $classe
                    && $existing->date_entree
                    && $dateEntree
                    && $existing->date_entree->format('Y-m-d') !== $dateEntree->format('Y-m-d')
                ) {
                    // Même classe mais nouvelle année d'entrée → redoublant réel
                    $action = 'redoublant';
                } else {
                    // Même fichier réimporté ou passage de classe → mise à jour
                    $action = 'update';
                }
            } else {
                $action = 'create';
            }

            $preview[] = [
                'nom'         => $nom,
                'prenom'      => $prenom,
                'email'       => $row['email'],
                'classe'      => $classe,
                'promo'       => $promo,
                'date_entree' => $dateEntree?->format('Y-m-d'),
                'date_sortie' => $dateSortie?->format('Y-m-d'),
                'statut'      => $statut,
                'action'      => $action,
                'existing_id' => $existing?->id,
            ];
        }

        return $preview;
    }

    private function parseName(string $fullName): array
    {
        $tokens      = explode(' ', trim($fullName));
        $nomTokens   = [];
        $prenomTokens = [];

        foreach ($tokens as $token) {
            if ($token === '' ) {
                continue;
            }
            // Token tout en majuscules (hors accents) → nom de famille
            if (preg_match('/^[A-ZÀÂÄÉÈÊËÎÏÔÙÛÜÇ\-]+$/u', $token)) {
                $nomTokens[] = $token;
            } else {
                $prenomTokens[] = $token;
            }
        }

        // Sécurité : si tout est en majuscules (ex. "DUPONT")
        if (empty($prenomTokens) && !empty($nomTokens)) {
            $prenomTokens = [array_pop($nomTokens)];
        }

        return [
            'nom'    => implode(' ', $nomTokens),
            'prenom' => implode(' ', $prenomTokens),
        ];
    }

    private function parseClasse(string $classe): array
    {
        preg_match('/(\d+)\s*$/', $classe, $matches);
        $annee = (int) ($matches[1] ?? 1);

        return [
            'classe' => 'SIO' . $annee,   // BTS SIO 1 → SIO1, BTS SIO 2 → SIO2
            'annee'  => $annee,
        ];
    }

    private function parseDate(string $date): ?Carbon
    {
        $date = trim($date);
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $date);
        } catch (\Exception) {
            return null;
        }
    }
}
