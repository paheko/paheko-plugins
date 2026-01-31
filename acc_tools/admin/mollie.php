<?php

namespace Paheko;

use KD2\HTML\TableToCSV;

$csrf_key = 'mollie_import';
$columns = [
	'status'  => 'Statut',
	'date'    => 'Date',
	'ref'     => 'Référence',
	'gross'   => 'Revenu',
	'deduced' => 'Déduction',
	'fees'    => 'Frais',
	'total'   => 'Total',
];

$form->runIf('load', function () use ($columns) {
	$table = f('table');
	$csv = new TableToCSV;
	$csv->import($table);
	$tmpfile = tempnam(CACHE_ROOT, 'mollie-csv');
	$fp = fopen('php://temp', 'w+');

	$months = [
		'January'   => 'janvier',
		'February'  => 'février',
		'March'     => 'mars',
		'April'     => 'avril',
		'May'       => 'mai',
		'June'      => 'juin',
		'July'      => 'juillet',
		'August'    => 'août',
		'September' => 'septembre',
		'October'   => 'octobre',
		'November'  => 'novembre',
		'December'  => 'décembre',
	];

	$months = array_flip($months);

	fputcsv($fp, ['Numéro d\'écriture', 'Date', 'Libellé', 'Compte de débit', 'Compte de crédit', 'Montant', 'Numéro pièce comptable', 'Référence paiement', 'Notes'], ',', '"', '\\');

	try {
		file_put_contents($tmpfile, $csv->fetch());

		foreach (CSV::import($tmpfile, $columns, array_keys($columns)) as $row) {
			$row = (object) $row;
			$gross = preg_replace('/[^\d,]/', '', $row->gross);
			$net = preg_replace('/[^\d,]/', '', $row->total);
			$fees = Utils::moneyToInteger($row->fees) + Utils::moneyToInteger($row->deduced);
			$fees = Utils::money_format($fees, ',', '');

			$date = strtr($row->date, $months);
			$date = \DateTime::createFromFormat('d F Y', $date);

			if (!$date) {
				$date = $row->date;
			}
			else {
				$date = $date->format('d/m/Y');
			}

			fputcsv($fp, ['', $date, 'Dons reçus via Mollie', '512E', '754', $gross, '', $row->ref, ''], ',', '"', '\\');
			fputcsv($fp, ['', $date, 'Frais Mollie', '627', '512E', $fees, '', $row->ref, ''], ',', '"', '\\');
			fputcsv($fp, ['', $date, 'Virement Mollie', '580', '512E', $net, '', $row->ref, ''], ',', '"', '\\');
		}
	}
	catch (\Throwable $e) {
		Utils::safe_unlink($tmpfile);
		fclose($fp);
		throw $e;
	}

	header('Content-Type: text/csv; charset="utf-8"');
	header('Content-Disposition: attachment; filename="mollie.csv"');

	fseek($fp, 0);

	fpassthru($fp);

	fclose($fp);
	exit;

}, $csrf_key);

$tpl->assign(compact('csrf_key'));
$tpl->display(PLUGIN_ROOT . '/templates/mollie.tpl');
