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
    .signature { float: left; width: 49%; text-align: center; font-style: italic; }
    h1 { text-align: center; }
    b { font-weight: normal; padding: 5pt 10pt; border: 1px solid #000; }
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
    <h3>Procédure de rachat</h3>
    <ol>
        <li>Imprimer cette page en <strong>deux</strong> exemplaires</li>
        <li>Barrer la mention ne correspondant pas au moyen de paiement choisi</li>
        <li>Faire signer les deux exemplaires par l'adhérent</li>
        <li>Signer les deux exemplaires</li>
        <li>Payer le rachat</li>
        <li>Remettre un exemplaire à l'adhérent</li>
    </ol>
    <p><a href="{plugin_url}">Retour à la gestion des vélos</a></p>
</header>

<section class="vente">
    <article>
        <h1>Contrat de rachat d'un vélo d'occasion</h1>
        <h4>Entre le vendeur :</h4>
        <ul>
            <li>Numéro d'adhérent : {$velo.source_details}</li>
            <li>Nom et prénom : <strong>{$velo->membre_rachat()}</strong></li>
        </ul>
        <h4>Et l'acquéreur :</h4>
        <ul>
            <li>L'association loi 1901 « {$config.org_name} » dont le siège est situé au {$config.org_address}.</li>
        </ul>
        <p>L'association « {$config.org_name} » rachète à l'adhérent le vélo décrit ci-après qu'il avait lui-même précédemment acheté auprès de l'association :</p>
        <ul>
            <li>Type : {$velo.type}</li>
            <li>Taille : {$velo.roues}</li>
            <li>Genre : {$velo.genre}</li>
            <li>Couleur : {$velo.couleur}</li>
            <li>Modèle : {$velo.modele}</li>
        </ul>
        <h3>État : <strong>{$velo.etat_entree}</strong></h3>
        <p>(Ancien numéro référence : {$velo.source_details}, nouveau numéro référence : {$velo.id})</p>
        <p>en contrepartie du paiement du montant de <strong>{$prix} €</strong>
            réglé en <b>espèces</b> <b>chèque</b> <em>(barrer la mention inutile)</em> à l'établissement du présent contrat.</p>
        <p>Le vendeur déclare que le vélo n'est pas d'origine frauduleuse et qu'il est conforme à
            l'état indiqué ci-dessus.</p>
        <p>Fait en deux exemplaires, à Dijon, le {$velo.date_entree|date_short}.</p>

        <p class="signature">(Signature de l'acquéreur)</p>
        <p class="signature">(Signature du vendeur, ou d'un parent pour les mineurs)</p>
    </article>
</section>

</body>
</html>