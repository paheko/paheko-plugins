# Stockage des factures

Les factures sont sérialisées en interne dans un format JSON proche du standard EN 16931. Le format est identique à la sérialisation effectuée par SuperPDP dans son modèle `en_invoice`: <https://www.superpdp.tech/openapi/#superpdp/model/en_invoice>

Les devis sont stockés de la même manière mais avec le type 231 (Quotation). Ils ne peuvent alors être envoyés aux plateformes PDP/Peppol qui ne les supportent pas.

Quand une facture ou un devis est en statut `draft` (brouillon), le champ "content" est NULL et la facture est séralisée en JSON à la volée.

Une fois que la facture est validée, la sérialisation est stockée dans le champ "content", et ne peut plus être modifiée.

# Export des factures

Les factures sérialisées en JSON peuvent être converties en HTML, UBL ou CII. Le CII peut ensuite être utilisé pour créer un fichier Factur-X.

Cela permet aussi de visualiser des factures reçues. Cependant l'export développé ne gère pas la totalité des spécificités des factures UBL/CII.

## Notes facturation électronique

* FAQ : https://www.impots.gouv.fr/sites/default/files/media/1_metier/2_professionnel/EV/2_gestion/290_facturation_electronique/faq_fe_05_01_2024_vf.pdf
* https://github.com/OCA/l10n-france/tree/16.0/l10n_fr_chorus_account


Factur-X:
* https://www.votre-expert-des-associations.fr/est-ce-que-les-associations-sont-concernees-par-la-facture-electronique/
* PDF + entête XMP spécifique + fichier XML
* prince --attach=factur-x.xml https://kd2.org/ -o w.pdf --pdf-profile="PDF/A-3a" --pdf-xmp=Factur-X_extension_schema.xmp
* Validator: https://services.fnfe-mpe.org/
* Other validator: https://www.mustangproject.org/commandline/

* Python/CLI: https://github.com/akretion/factur-x/tree/master
* PHP: https://github.com/atgp/factur-x/tree/master (2.3MB)
* https://github.com/akretion/factur-x-libreoffice-extension/blob/master/extension/package/libreoffice_facturx_macro.py
* Génération de PDF conforme en PHP : https://github.com/horstoeko/zugferd/blob/master/src/ZugferdPdfWriter.php

{{:facturx template="./invoice.html" invoice=$invoice}}

Ghostscript:
* https://ghostscript.com/blog/zugferd.html

## Envoyer des factures Chorus

* https://www.dolibarr.fr/forum/t/connexion-api-chorus-pro-environnement-de-test-qualif/46738/4


* Créer un compte sur https://piste.gouv.fr/
* Créer une nouvelle application
* Cliquer sur le lien "Click here to access to the consent page"
* Cocher la case devant "Factures" et valider
* Revenir sur la page de l'application : la case pour "Factures" et désormais dégrisée, la cocher et valider
* Se rendre dans l'onglet "Authentication"
* Copier le Client ID et Client Secret de "OAuth credentials"

