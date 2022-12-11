<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Vélos</title>

    {literal}
    <style type="text/css" media="all">
    header { border-bottom: 2px dashed #999; }
    body { font-family: "Qlassik Medium", Trebuchet MS; font-size: 14pt; }
    .signature { float: left; width: 49%; text-align: center; font-style: italic; }
    h1 { text-align: center; }
    b { font-weight: normal; padding: 5pt 10pt; border: 1px solid #000; }
    .complete { border-bottom: 1pt dotted #000; width: 10em; display: inline-block; }
    .numbers { list-style-type: none; }
    .numbers li { width: 25%; float: left; border: 1pt solid #000; margin: .5em;  text-align: center; padding: .5em; }
    .numbers li i { display: block; }
    .numbers li b { border: none; padding: 0; font-size: 2em; }
    h4 { clear: both; }
    </style>
    <style type="text/css" media="print">
    header { display: none; }
    section { page-break-after: always; }
    </style>
    {/literal}
</head>
<body>

{foreach from=$velos item="velo"}
<section class="vente">
    <h1>Contrat de vente d'un vélo d'occasion</h1>
    <ul class="numbers">
        <li><i>Numéro référence</i><b>{$velo.id}</b></li>
        <li><i>Numéro stock</i><b>{$velo.etiquette}</b></li>
        <li><i>Numéro bourse</i><b>...</b></li>
    </ul>
    <h4>Entre le vendeur :</h4>
    <ul>
        <li>L'association loi 1901 « {$config.org_name} » dont le siège est situé au {$config.org_address}.</li>
    </ul>
    <h4>Et l'acquéreur :</h4>
    <ul>
        <li>Nom et prénom : <span class="complete"></span></li>
    </ul>
    <p>L'association « {$config.org_name} » vend à l'acquéreur le vélo décrit ci-après :</p>
    <ul>
        <li>Type : {$velo.type}</li>
        <li>Taille : {$velo.roues}</li>
        <li>Genre : {$velo.genre}</li>
        <li>Couleur : {$velo.couleur}</li>
        <li>Modèle : {$velo.modele}</li>
    </ul>
    <h3>État : <span class="complete"></span></h3>
    <p>en contrepartie du paiement du montant de <strong class="complete"></strong> €
        réglé en <b>espèces</b> <b>chèque</b> <em>(barrer la mention inutile)</em> à l'établissement du présent contrat.</p>
    <p>L'acquéreur déclare avoir examiné en détail le vélo, l'avoir essayé et avoir constaté
        qu'il est conforme à l'état indiqué ci-dessus. L'acquéreur déclare renoncer à toute
        action à l'encontre du vendeur quelle qu'en soit la nature, même fondée sur un vice caché
        non connu du vendeur.</p>
    <p>Le vendeur déclare que le vélo n'est pas d'origine frauduleuse et qu'il est conforme à
        l'état indiqué ci-dessus.</p>
    <p>Fait en deux exemplaires, à Dijon, le <span class="complete"></span>.</p>

    <p class="signature">(Signature du vendeur)</p>
    <p class="signature">(Signature de l'acquéreur, ou d'un parent pour les mineurs)</p>
</section>
{/foreach}

</body>
</html>
