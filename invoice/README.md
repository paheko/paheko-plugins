# Fonctionnalités

* Factures
* Devis
* Avoirs
* Création de facture à partir d'un devis accepté
* Création d'avoir à partir d'une facture annulée
* Prévisualisation de facture
* Génération de facture en Factur-X (PDF) et CII
* Gestion des exemptions de TVA spécifiques à la France
* Gestion des champs spécifiques à Chorus Pro
* Mentions légales obligatoires sur les facture aux entreprises
* Instructions de paiement sur les factures (IBAN+BIC)

# Fonctionnalités non supportées pour le moment

- Facture d'acompte
- Auto-facturation
- Facture rectificative
- Remises et rabais (à venir)
- Cas spécifiques de TVA : auto-liquidation, exemption pour export hors UE, îles Canaries, Ceuta et Mellila
- Envoi de facture électronique à un code routage autre que le SIREN

# Cycle de vie d'une facture

* Création de la facture (statut = brouillon)
* Validation (statut = en attente d'envoi)
* Envoi par e-mail ou à une plateforme (statut = en attente de paiement)

Puis soit :

1. Paiement en une ou plusieurs fois, jusqu'à paiement total (statut = payée)
2. Annulation en cas d'erreur (statut = annulée) et création d'une facture d'avoir.

# Cycle de vie d'une facture d'avoir

* Création automatique à partir de la facture à annuler (statut = en attente d'envoi)
* Envoi par e-mail ou à une plateforme (statut = en attente de remboursement)
* Remboursement en une ou plusieurs fois, jusqu'à remboursement total (statut = remboursée)

# Cycle de vie d'un devis

* Création du devis (statut = brouillon)
* Validation (statut = en attente d'envoi)
* Envoi par e-mail ou par courrier (statut = en attente de validation par le client)

Puis soit :

1. Acceptation par le client (statut = accepté) et création d'une facture identique au devis
2. Refus par le client (statut = annulé)


# Diverses notes

## Anonymisation factures

> Sur ce sujet, En mentionnant UNIQUEMENT un numéro de contrat et en détaillant la nature (bien/service) et le montant de TVA, on doit pouvoir se passer intégralement de la facture détaillée. Mes fournisseurs en Allemagne le font déjà.
>
> Exemple: Facture N° 001 relative au contrat N°A01
> Bien alimentaire :1000€
> Transport sur vente 10€H.T.
> Total HT 1010€
> 
> On peut ainsi éviter de transmettre le détail de l’information produit vendus..sans contrefaire aux obligations fiscales et en aidant l’état à lutter contre la fraude.
>
> <https://www.dolibarr.fr/forum/t/facturation-electronique-retard-tolerance/51774/10>

## SuperPDP - routage

> Quand on reçoit un message Peppol, il y a une enveloppe avec un from/to, et à l’intérieur un message. C’est dans « l’enveloppe SBDH » qu’OVH met une adresse « from » spéciale à laquelle les messages de cycle de vie seront renvoyés.
> 
> C’est comme le protocole SMTP, tu as un un « MAIL FROM » dans le protocole SMTP, puis dans le message lui-même tu as un autre « From » qui est potentiellement différent.

<https://www.dolibarr.fr/forum/t/super-pdp-les-factures-b2bint-ne-supportent-que-le-statut-fr-212/51860/49>