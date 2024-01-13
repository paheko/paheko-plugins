<?php

namespace Paheko;

/*
	Colonnes :
	Date
	Heure
	Fuseau horaire
	Nom
	Type
	État
	Devise
	Avant commission
	Commission
	Net
	De l'adresse email
	À l'adresse email
	Numéro de transaction
	Adresse de livraison
	État de l'adresse
	Titre de l'objet
	Numéro de l'objet
	Montant des frais d'expédition et de traitement
	Montant de l'assurance
	TVA
	Nom de l'option 1
	Valeur de l'option 1
	Nom de l'option 2
	Valeur de l'option 2
	Numéro de la transaction de référence
	Numéro de facture
	Numéro de client
	Quantité
	Numéro de reçu
	Solde
	Adresse
	Adresse (suite)/District/Quartier
	Ville
	État/Province/Région/Comté/Territoire/Préfecture/République
	Code postal
	Pays
	Numéro de téléphone du contact
	Objet
	Remarque
	Indicatif pays
	Impact sur le solde
*/

$form->runIf('load', function() {
	if (empty($_FILES['csv']['tmp_name'])) {
		throw new UserException('Fichier invalide ou vide');
	}

	$group_fees = (bool) f('group_fees');

	$columns = [
		'date'        => 'Date',
		'name'        => 'Nom',
		'type'        => 'Type',
		'fee'         => 'Commission',
		'net'         => 'Net',
		'gross'       => 'Avant commission',
		'label'       => 'Titre de l\'objet',
		'notes'       => 'Remarque',
		'ref'         => 'Numéro de transaction',
		'invoice_ref' => 'Numéro de facture',
		'client_ref'  => 'Numéro de client',
		'object'      => 'Objet',
		'from'        => 'De l\'adresse email',
		'to'          => 'À l\'adresse email',
	];

	// Création du CSV de sortie
	$fp = fopen('php://temp', 'w');
	$fees_sum = 0;
	$notes_keys = ['object', 'invoice_ref', 'client_ref', 'notes', 'name', 'from', 'to'];
	$label_keys = ['label', 'name', 'type'];

	fputcsv($fp, ['Date', 'Libellé', 'Compte de débit', 'Compte de crédit', 'Montant', 'Référence paiement', 'Remarques']);

	foreach (CSV::import($_FILES['csv']['tmp_name'], $columns, array_keys($columns)) as $row) {
		$row = (object)$row;
		$label = [];

		foreach ($label_keys as $k) {
			if (!empty($row->$k)) {
				$label[] = $row->$k;
			}
		}

		$label = implode(' — ', $label);

		$notes = [];

		foreach ($notes_keys as $k) {
			if (!empty($row->$k)) {
				$notes[] = $columns[$k] . ': ' . $row->$k;
			}
		}

		$notes = implode("\n", $notes);

		if ($fee = Utils::moneyToInteger($row->fee)) {
			$fees_sum += $fee;
		}

		$amount = preg_replace('/[\s ]+/U', '', $row->gross);
		fputcsv($fp, [$row->date, $label, '', '', $amount, $row->ref, $notes]);

		if ($fee && !$group_fees) {
			if ($fees_sum) {
				fputcsv($fp, [$row->date, 'Commission PayPal sur transaction', '', '', Utils::money_format($fee, ',', ''), $row->ref, '']);
			}
		}
	}

	if ($fees_sum && $group_fees) {
		fputcsv($fp, [$row->date, 'Commission PayPal sur transactions', '', '', Utils::money_format($fees_sum, ',', ''), '', '']);
	}

	header('Content-Type: text/csv; charset="utf-8"');
	header('Content-Disposition: attachment; filename="paypal.csv"');

	fseek($fp, 0);

	fpassthru($fp);

	fclose($fp);
	exit;
});

$tpl->display(PLUGIN_ROOT . '/templates/paypal.tpl');
