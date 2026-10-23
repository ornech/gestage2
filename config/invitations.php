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

];
