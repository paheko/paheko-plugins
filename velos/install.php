<?php

namespace Garradin;

$db = DB::getInstance();

// Création table
$db->exec(<<<EOF
    CREATE TABLE plugin_stock_velos
    (
        id INTEGER NOT NULL PRIMARY KEY, -- Numéro unique
        etiquette INTEGER, -- Numéro étiquette
        bicycode TEXT,

        source TEXT NOT NULL, -- Don, récup, etc.
        source_details TEXT, -- Détails de la source, comme nom du donneur, etc.

        type TEXT, -- Type de vélo : Ville, VTT, VTC, etc.
        roues TEXT, -- Taille des roues
        genre TEXT, -- homme, femme, mixte

        couleur TEXT, -- Une ou plusieurs couleurs
        modele TEXT, -- Marque / modèle

        prix FLOAT, -- Prix du vélo

        date_entree TEXT,
        etat_entree TEXT,

        date_sortie TEXT,
        raison_sortie TEXT,
        details_sortie TEXT,

        notes TEXT
    );

    CREATE INDEX prv_etiquette ON plugin_stock_velos (etiquette);
    CREATE INDEX prv_type ON plugin_stock_velos (type);
    CREATE INDEX prv_roues ON plugin_stock_velos (roues);
    CREATE INDEX prv_genre ON plugin_stock_velos (genre);
    CREATE INDEX prv_couleur ON plugin_stock_velos (couleur);
    CREATE INDEX prv_date_entree ON plugin_stock_velos (date_entree);
    CREATE INDEX prv_date_sortie ON plugin_stock_velos (date_sortie);
EOF
);

$plugin->registerSignal('boucle.velos', ['Garradin\Velos_Signaux', 'LoopVelos']);