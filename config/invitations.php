<?php

return [

    /*
    | Domaines vers lesquels l'application n'envoie PAS d'email d'invitation : le lien est alors affiché
    | à l'administrateur, qui le transmet lui-même (par sa propre messagerie, Pronote, en classe…).
    | La messagerie de l'académie refuse les mails de l'hébergeur (550 #5.7.1) : inutile d'essayer.
    | Liste séparée par des virgules ; un domaine couvre aussi ses sous-domaines.
    */
    'domaines_sans_mail' => array_values(array_filter(array_map(
        fn ($d) => strtolower(trim($d)),
        explode(',', env('INVITATION_DOMAINES_SANS_MAIL', 'ac-poitiers.fr,education.gouv.fr'))
    ))),


    /*
    | Domaines de messagerie courants : une adresse dont le domaine en est proche sans en être un
    | (gmial.com, hotmial.fr…) est très probablement une faute de frappe → aucun mail n'est envoyé
    | (il pourrait arriver à un inconnu) ; le lien est affiché à l'administrateur.
    */
    'domaines_courants' => [
        'gmail.com', 'googlemail.com', 'hotmail.com', 'hotmail.fr', 'outlook.com', 'outlook.fr', 'live.fr', 'live.com',
        'yahoo.fr', 'yahoo.com', 'orange.fr', 'wanadoo.fr', 'free.fr', 'sfr.fr', 'neuf.fr', 'laposte.net', 'bbox.fr',
        'icloud.com', 'me.com', 'msn.com', 'protonmail.com', 'proton.me', 'gmx.fr', 'gmx.com', 'aol.com', 'ac-poitiers.fr',
    ],

    /*
    | Vérifie que le domaine possède un serveur de messagerie (enregistrement MX ou A dans le DNS).
    */
    'verifier_dns' => env('INVITATION_VERIFIER_DNS', true),

];
