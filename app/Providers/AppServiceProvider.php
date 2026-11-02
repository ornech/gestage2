<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Services\EchecsMail;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Events\BackupWasSuccessful;

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
        // Production : migrate:fresh/refresh/reset et db:wipe refusés (même avec --force), pour ne jamais effacer la base par erreur
        DB::prohibitDestructiveCommands($this->app->isProduction());

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

        // Sauvegarde réussie : l'archive (dump SQL gzip) est jointe au mail envoyé à BACKUP_MAIL_TO.
        // Un échec d'envoi (taille refusée par la messagerie, etc.) ne doit jamais faire échouer la sauvegarde.
        Event::listen(BackupWasSuccessful::class, function (BackupWasSuccessful $e) {
            try {
                $backup = $e->backupDestination->newestBackup();
                $nom = basename($backup->path());
                $contenu = $backup->disk()->get($backup->path());

                Mail::raw(
                    sprintf('Sauvegarde de %s du %s (%.1f Mo) en pièce jointe.', config('app.name'), $backup->date()->format('d/m/Y H:i'), strlen($contenu) / 1048576),
                    fn ($m) => $m->to(config('backup.notifications.mail.to'))
                        ->subject('Sauvegarde ' . config('app.name') . ' du ' . $backup->date()->format('d/m/Y'))
                        ->attachData($contenu, $nom, ['mime' => 'application/zip'])
                );
            } catch (\Throwable $ex) {
                Log::error('Envoi de la sauvegarde par mail impossible : ' . $ex->getMessage());
            }
        });
    }
}
