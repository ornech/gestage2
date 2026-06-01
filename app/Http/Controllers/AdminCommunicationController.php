<?php

namespace App\Http\Controllers;

use App\Mail\CommunicationBTS;
use App\Models\Employe;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminCommunicationController extends Controller
{
    public function index()
    {
        $employes = Employe::with('entreprise')
            ->orderBy('nom')
            ->get();

        $rgpdSuppresses = Employe::with('entreprise')
            ->whereNull('email')
            ->whereNotNull('email_supprime_at')
            ->orderByDesc('email_supprime_at')
            ->get();

        $templateObjetPrefix = Parametre::get('mail_bienvenue_objet_prefix', 'Accueil en stage de');
        $templateIntro       = Parametre::get('mail_bienvenue_intro', "Nous vous remercions chaleureusement d'accueillir [PRENOM] [NOM], étudiant(e) en BTS SIO au sein de votre structure pour un stage.\n\nVotre accompagnement est précieux pour la formation de nos étudiant(e)s, et nous vous en sommes sincèrement reconnaissants.");
        $templateComplement  = Parametre::get('mail_bienvenue_intro_custom', '');

        return view('admin.communication.index', compact(
            'employes',
            'rgpdSuppresses',
            'templateObjetPrefix',
            'templateIntro',
            'templateComplement',
        ));
    }

    public function previewBienvenue()
    {
        $employe  = Employe::whereNotNull('email')->with('entreprise')->first();
        $etudiant = \App\Models\User::role('Etudiant')->with('tuteur')->whereNotNull('tuteur_id')->first()
                 ?? \App\Models\User::role('Etudiant')->with('tuteur')->first();

        if (!$employe || !$etudiant) {
            abort(404, 'Aucun employé ou étudiant disponible pour la prévisualisation.');
        }

        return new \App\Mail\BienvenueMaitreDeStage($employe, $etudiant, $etudiant->tuteur);
    }

    public function updateTemplate(Request $request)
    {
        $request->validate([
            'mail_bienvenue_objet_prefix' => 'nullable|string|max:100',
            'mail_bienvenue_intro'        => 'nullable|string|max:2000',
            'mail_bienvenue_intro_custom' => 'nullable|string|max:1000',
        ]);

        Parametre::set('mail_bienvenue_objet_prefix', $request->mail_bienvenue_objet_prefix ?? 'Accueil en stage de');
        Parametre::set('mail_bienvenue_intro',        $request->mail_bienvenue_intro ?? '');
        Parametre::set('mail_bienvenue_intro_custom', $request->mail_bienvenue_intro_custom ?? '');

        return back()->with('success_template', 'Template mis à jour.');
    }

    public function envoyer(Request $request)
    {
        $request->validate([
            'sujet'        => 'required|string|max:200',
            'corps'        => 'required|string|max:5000',
            'mode'         => 'required|in:tous,jury,manuelle',
            'destinataires' => 'required_if:mode,manuelle|array',
            'destinataires.*' => 'exists:employes,id',
        ]);

        $employes = match ($request->mode) {
            'tous'     => Employe::whereNotNull('email')->get(),
            'jury'     => Employe::whereNotNull('email')->where('jury', true)->get(),
            'manuelle' => Employe::whereNotNull('email')
                              ->whereIn('id', $request->destinataires ?? [])
                              ->get(),
        };

        if ($employes->isEmpty()) {
            return back()->withErrors(['destinataires' => 'Aucun destinataire avec un email valide.']);
        }

        $expediteur = auth()->user();

        // 3 secondes d'écart entre chaque mail → ~1 200 mails/heure max
        // Évite d'être signalé comme spammer par les serveurs destinataires
        foreach ($employes as $i => $employe) {
            Mail::to($employe->email)->later(
                now()->addSeconds($i * 3),
                new CommunicationBTS($employe, $request->sujet, $request->corps, $expediteur)
            );
        }

        return back()->with('success_envoi', "{$employes->count()} mail(s) mis en file d'envoi — ils partiront progressivement.");
    }
}
