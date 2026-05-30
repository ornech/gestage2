<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentHasStage
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasRole('Etudiant')) {
            return $next($request);
        }

        $user = auth()->user();

        if (!$user->stages()->exists()) {
            return redirect()->route('etudiant.stages.index')
                ->withErrors([
                    'stage' => 'Vous devez saisir les informations de votre stage avant d\'accéder au journal de bord.',
                ]);
        }

        return $next($request);
    }
}
