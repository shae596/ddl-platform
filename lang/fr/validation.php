<?php

return [

    'accepted' => 'Le champ :attribute doit être accepté.',
    'required' => 'Le champ :attribute est obligatoire.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'date' => 'Le champ :attribute doit être une date valide.',
    'array' => 'Le champ :attribute doit être une liste.',
    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min élément(s).',
        'string' => 'Le champ :attribute doit contenir au moins :min caractère(s).',
    ],
    'max' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max élément(s).',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractère(s).',
        'file' => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
    ],
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'mimes' => 'Le fichier :attribute doit être de type : :values.',

    'attributes' => [
        'action' => 'action',
        'titre' => 'titre du projet',
        'service_demandeur' => 'service demandeur',
        'nom_demandeur' => 'nom du demandeur',
        'email_demandeur' => 'e-mail du demandeur',
        'telephone_demandeur' => 'téléphone',
        'date_souhaitee_livraison' => 'date souhaitée de livraison',
        'contexte' => 'contexte',
        'problematique' => 'problématique',
        'objectif_general' => 'objectif général',
        'objectifs_specifiques' => 'objectifs spécifiques',
        'description_fonctionnelle' => 'description fonctionnelle',
        'utilisateurs_cibles' => 'utilisateurs cibles',
        'hors_perimetre' => 'hors périmètre',
        'priorite' => 'priorité',
        'contraintes_techniques' => 'contraintes techniques',
        'contraintes_reglementaires' => 'contraintes réglementaires',
        'dependances' => 'dépendances',
        'pieces_jointes' => 'pièces jointes',
    ],

];
