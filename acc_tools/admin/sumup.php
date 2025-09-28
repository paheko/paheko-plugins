<?php

namespace Paheko;

$csrf_key = 'sumup_csv';

/*
	Colonnes :
	Email	Date	Identifiant de la transaction	Status	Type de carte	Méthode de paiement	Description	Montant total	Prix net	Total TVA	Frais	Virement	Date du virement
*/

$form->runIf('load', function() {
	if (empty($_FILES['csv']['tmp_name'])) {
		throw new UserException('Fichier invalide ou vide');
	}

	$group_fees = (bool) f('group_fees');
	$only_paid = (bool) f('only_paid');

	$fp = fopen($_FILES['csv']['tmp_name'], 'r');
	$first_line = fgets($fp, 4096);
	fclose($fp);


	$columns = [
		'date'   => 'Date',
		'status' => 'Status',
		'fee'    => 'Frais',
		'net'    => 'Prix net',
		'total'  => 'Montant total',
		'label'  => 'Description',
		'ref'    => 'Identifiant de la transaction',
	];

	$mandatory_columns = [
		'date',
		'status',
		'fee',
		'net',
		'total',
		'ref',
	];

	// Création du CSV de sortie
	$fp = fopen('php://temp', 'w+');
	$fees_sum = 0;

	fputcsv($fp, ['Date', 'Libellé', 'Compte de débit', 'Compte de crédit', 'Montant', 'Référence paiement', 'Remarques', 'Statut'], ',', '"', '\\');

	foreach (CSV::import($_FILES['csv']['tmp_name'], $columns, $mandatory_columns) as $row) {
		$row = (object)$row;

		// Ignore failed payments
		if ($only_paid
			&& $row->status !== 'Payé') {
			continue;
		}

		$label = $row->label;

		if (empty($label)) {
			$label = 'SumUp - ' . $row->ref;
		}

		if ($fee = Utils::moneyToInteger($row->fee)) {
			$fees_sum += $fee;
		}

		$date = strtotime($row->date);

		$amount = preg_replace('/[\s ]+/U', '', $row->total);
		$amount = Utils::moneyToInteger($amount);

		fputcsv($fp, [date('d/m/Y', $date), $label, '', '', Utils::money_format($amount, ',', ''), $row->ref, '', $row->status], ',', '"', '\\');

		if ($fee && !$group_fees) {
			if ($fees_sum) {
				fputcsv($fp, [date('d/m/Y', $date), 'Commission SumUp sur transaction', '', '', Utils::money_format($fee, ',', ''), $row->ref, '', ''], ',', '"', '\\');
			}
		}
	}

	if ($fees_sum && $group_fees) {
		fputcsv($fp, [date('d/m/Y', $date), 'Commission SumUp sur transactions', '', '', Utils::money_format($fees_sum, ',', ''), '', '', ''], ',', '"', '\\');
	}

	header('Content-Type: text/csv; charset="utf-8"');
	header('Content-Disposition: attachment; filename="sumup.csv"');

	fseek($fp, 0);

	fpassthru($fp);

	fclose($fp);
	exit;
}, $csrf_key);

$tpl->assign(compact('csrf_key'));
$tpl->display(PLUGIN_ROOT . '/templates/sumup.tpl');
