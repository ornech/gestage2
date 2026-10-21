<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class RoutesIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /** Toute route('nom') écrite dans une vue ou le code doit exister (sinon : lien mort → erreur 500). */
    public function test_toutes_les_routes_referencees_existent(): void
    {
        $manquantes = [];

        foreach ((new Finder)->files()->in([base_path('resources/views'), base_path('app')])->name('*.php') as $fichier) {
            // `(?<!->)` exclut `$request->route('employe')` (paramètre de route, pas un nom de route)
            preg_match_all('/(?<!->)\broute\(\s*[\'"]([^\'"]+)[\'"]/', $fichier->getContents(), $m);

            foreach ($m[1] as $nom) {
                if (! Route::has($nom)) {
                    $manquantes[] = $nom.' ('.$fichier->getRelativePathname().')';
                }
            }
        }

        $this->assertSame([], $manquantes, 'Routes référencées mais non définies');
    }

    /** Aucune page sans paramètre ne doit planter (500) pour un utilisateur autorisé. */
    public function test_les_pages_sans_parametre_ne_plantent_pas(): void
    {
        foreach (['Etudiant', 'Professeur', 'Administrateur'] as $role) {
            Role::findOrCreate($role);
        }

        $acteurs = [
            User::factory()->administrateur()->create(),
            User::factory()->professeur()->create(),
            User::factory()->etudiant()->create(['statut' => 'actif']),
        ];

        $erreurs = [];
        $pagesOk = 0;

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (! in_array('GET', $route->methods(), true)
                || str_contains($uri, '{')
                || preg_match('#^(_|up$|storage|sanctum|two-factor|user/|forgot|reset|email|login|register|confirm|logout|rgpd)#', $uri)) {
                continue;
            }

            foreach ($acteurs as $acteur) {
                $status = $this->actingAs($acteur)->get('/'.ltrim($uri, '/'))->getStatusCode();

                $pagesOk += $status === 200 ? 1 : 0;

                if ($status >= 500) {
                    $erreurs[] = "$status  /$uri  (".$acteur->getRoleNames()->first().')';
                }
            }
        }

        $this->assertSame([], $erreurs, 'Pages en erreur serveur');
        $this->assertGreaterThan(30, $pagesOk, 'le test doit réellement afficher des pages');
    }
}
