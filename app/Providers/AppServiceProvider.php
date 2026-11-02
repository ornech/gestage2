<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Services\EchecsMail;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination au gabarit Bulma du projet (sinon Laravel prend la vue Tailwind, dont les SVG s'affichent en énorme sous Bulma)
        Paginator::defaultView('pagination::default');

        // Recherche SIRET : protège le quota de l'API INSEE (par utilisateur, sinon par IP)
        RateLimiter::for('siret', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));

        // Envoi d'emails en masse (communications admin)
        RateLimiter::for('mass-mail', fn (Request $request) => Limit::perMinute(5)->by($request->user()?->id ?: $request->ip()));

        // Mails non remis : alerte sur la fiche et la liste des étudiants, close dès qu'un mail repart vers l'adresse
        Event::listen(JobFailed::class, fn (JobFailed $e) => app(EchecsMail::class)->depuisJobEchoue($e));
        Event::listen(MessageSent::class, function (MessageSent $e) {
            foreach ($e->message->getTo() as $destinataire) {
                app(EchecsMail::class)->resoudre($destinataire->getAddress());
            }
        });
    }
}
