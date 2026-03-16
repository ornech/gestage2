<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        // On laisse Laravel traiter la requête et générer la réponse avant d'ajouter les en-têtes
        $response = $next($request);
        // 1. Générer un nonce unique pour cette requête
        $nonce = bin2hex(random_bytes(16));

        // 2. Partager ce nonce avec toutes les vues Blade
        view()->share('cspNonce', $nonce);

        // Empêche le navigateur de deviner le type de contenu (MIME Sniffing)
        // Force l'utilisation du Content-Type déclaré (ex: empêche d'exécuter un .txt comme du .js)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Protection contre le Clickjacking : autorise l'affichage du site dans une iframe
        // uniquement si l'appelant provient du même domaine (SAMEORIGIN)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Configuration de la Politique de Sécurité du Contenu (CSP)
        $csp = "default-src 'self'; " // Par défaut, on n'autorise que les ressources du même domaine

            // Autorise les scripts du domaine ('self') et les blocs JS intégrés spécifiques
            // identifiés par leurs empreintes SHA-256 (évite l'utilisation de 'unsafe-inline')
            ."script-src 'self' 'sha256-0hUgG2dplh9gkMgRQWXKTltn1bQhXqQSku0VnkhJZaI=' 'sha256-ZswfTY7H35rbv8WC7NXBoiC7WNu86vSzCDChNWwZZDM='; "
            // Autorise les CSS venant du domaine ('self') et du CDN jsDelivr (pour Bulma)
            ."style-src 'self' https://cdn.jsdelivr.net; "

            // Autorise les images du domaine et les images encodées en base64 (data:)
            ."img-src 'self' data:; "

            // Autorise uniquement les polices de caractères hébergées sur notre serveur
            ."font-src 'self';";

        // Applique la politique CSP à la réponse HTTP
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
