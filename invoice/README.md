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

# Configuration globale de SuperPDP

Il est possible d'avoir un compte SuperPDP qui gérera la facturation de toutes les associations (en mode usine/factory).

Pour cela il faut définir les constantes suivantes dans config.local.php :

```
const SUPERPDP_CLIENT_ID = 'xxx';
const SUPERPDP_CLIENT_SECRET = 'xxxsecret';
```

Il est aussi possible de définir des signaux système permettant de gérer un quota de facturation :

```php
use Paheko\Entities\Signal;

const SYSTEM_SIGNALS = [
	['superpdp.credit.get' => 'superpdp_credit_get'],
	['superpdp.credit.consume' => 'superpdp_credit_consume'],
];

function superpdp_credit_get(Signal $signal)
{
	// This is just an example code
	$db = MySQL::getInstance();
	$row = $db->firstColumn('SELECT superpdp_invoice_credit FROM organizations WHERE id = ?;', CURRENT_ORG_ID);

	// credit is an integer: how many invoices the user can still send or receive
	// if credit is empty the user won't be able to use SuperPDP
	$signal->setOut('credit', $row->superpdp_invoice_credit);
	$signal->stop();
}

function superpdp_credit_consume(Signal $signal)
{
	// This is just an example code
	$db = MySQL::getInstance();
	$row = $db->query(
		'UPDATE organizations SET superpdp_invoice_credit = superpdp_invoice_credit - ? WHERE id = ?;',
		$signal->getIn('credits'),
		CURRENT_ORG_ID
	);
}
```
