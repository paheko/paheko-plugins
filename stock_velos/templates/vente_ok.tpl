<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Vélos</title>

    {literal}
    <style type="text/css" media="all">
    header { border-bottom: 2px dashed #999; }
    section, article { margin: 0; padding: 0; }
    body { font-family: "Qlassik Medium", Trebuchet MS; font-size: 14pt; }
    .signature { float: left; width: 49%; text-align: center; font-style: italic; margin-bottom: 5rem ;}
    h1 { text-align: center; }
    b { font-weight: normal; padding: 5pt 10pt; border: 1px solid #000; }

    .vente {
        display: table;
        width: 100%;
    }
    .vente article {
        width: 50%;
        display: table-cell;
    }
    .vente article:first-child {
        margin-right: 5%;
    }

    .completion {
        clear: both;
        border: 1px solid #000;
        padding: .5rem;
        margin-top: 3em;
        background: #ddd;
    }
    </style>
    <style type="text/css" media="print">
    header { display: none; }
    body {
        margin: 0;
        padding: 0;
    }
    </style>
    {/literal}
</head>
<body>

<header>
    <h3>Procédure de vente</h3>
    <ol>
        <li>Imprimer cette page en <strong>deux</strong> exemplaires</li>
        <li>Barrer la mention ne correspondant pas au moyen de paiement choisi</li>
        <li>Faire signer les deux exemplaires par l'acheteur</li>
        <li>Signer les deux exemplaires</li>
        <li>Encaisser le paiement</li>
        <li>Remettre un exemplaire à l'acheteur</li>
    </ol>
    <p><a href="{plugin_url}">Retour à la gestion des vélos</a></p>
</header>

<section class="vente">
    <h1>Contrat de vente d'un vélo d'occasion</h1>
    <h4>Entre le vendeur :</h4>
    <ul>
        <li>L'association loi 1901 « {$config.org_name} » dont le siège est situé au {$config.org_address}.</li>
    </ul>
    <h4>Et l'acquéreur :</h4>
    <ul>
        <li>Numéro d'adhérent : {$velo.details_sortie}</li>
        <li>Nom et prénom : {if $velo.details_sortie}<strong>{$velo->membre_sortie()}</strong>{else}………………………………{/if}</li>
    </ul>
    <p>L'association « {$config.org_name} » vend à l'acquéreur le vélo décrit ci-après :</p>
    <ul>
        <li>Type : {$velo.type}</li>
        <li>Taille : {$velo.roues}</li>
        <li>Genre : {$velo.genre}</li>
        <li>Couleur : {$velo.couleur}</li>
        <li>Modèle : {$velo.modele}</li>
    </ul>
    <h3>État : <strong>{$etat}</strong></h3>
    <p>(Numéro référence : {$velo.id})</p>
    <p>en contrepartie du paiement du montant de <strong>{$velo.prix} €</strong>, réglé à l'établissement du présent contrat.</p>
    <p>L'acquéreur déclare avoir examiné en détail le vélo, l'avoir essayé et avoir constaté
        qu'il est conforme à l'état indiqué ci-dessus. L'acquéreur déclare renoncer à toute
        action à l'encontre du vendeur quelle qu'en soit la nature, même fondée sur un vice caché
        non connu du vendeur.</p>
    <p>Le vendeur déclare que le vélo n'est pas d'origine frauduleuse et qu'il est conforme à
        l'état indiqué ci-dessus.</p>
    <p>Fait en deux exemplaires, le {$velo.date_sortie|date_short}.</p>

    <p class="signature">(Signature du vendeur)</p>
    <p class="signature">(Signature de l'acquéreur, ou d'un parent pour les mineurs)</p>
    <p class="completion">
        Cadre réservé à {$config.org_name} : Note de caisse N° ……………………………………………………
    </p>
</section>

</body>
</html>