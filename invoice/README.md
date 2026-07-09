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
