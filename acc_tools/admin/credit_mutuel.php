<?php

namespace Paheko;

const TABULA_URL = 'https://github.com/tabulapdf/tabula-java/releases/download/v1.0.5/tabula-1.0.5-jar-with-dependencies.jar';

$tabula_path = SHARED_CACHE_ROOT . '/tabula.jar';

if (!file_exists($tabula_path)) {
	if (!exec('which java')) {
		throw new UserException('Java n\'est pas disponible');
	}

	copy(TABULA_URL, $tabula_path);

	if (!@filesize($tabula_path)) {
		throw new \RuntimeException('Impossible de télécharger tabula.jar');
	}
}

$csrf_key = 'pdf_credit_mutuel';

function debug_log(string $message, ...$params) {
	//vprintf($message, $params);
}

$form->runIf('load', function () use ($tabula_path) {
	if (empty($_FILES['file']['tmp_name'])) {
		throw new UserException('Fichier invalide ou vide');
	}

	$files = [];

	foreach ((array)$_FILES['file']['tmp_name'] as $k => $path) {
		$name = $_FILES['file']['name'][$k] ?? $k;

		if (empty($path)) {
			throw new UserException($name . ' est invalide ou vide');
		}

		if (mime_content_type($path) !== 'application/pdf') {
			throw new UserException($name . ' n\'est pas un PDF');
		}

		$files[] = $path;
	}

	if (count($files) > 12) {
		throw new UserException('Impossible de charger plus de 12 fichiers');
	}

	$header = null;
	$sum_header = null;
	$out = [];
	$i = 0;

	foreach ($files as $file) {
		$command = sprintf('LC_ALL=fr_FR.UTF-8 java -jar %s -g -l -p all %s', escapeshellarg($tabula_path), escapeshellarg($file));
		$csv = '';

		$stderr = '';

		$r = Utils::exec($command, 10, null, function (string $l) use (&$csv) {
			$csv .= $l;
		}, function(string $l) use (&$stderr) {
			$stderr .= $l;
		});


		if ($r) {
			throw new \RuntimeException(sprintf("Tabula execution failed (%s):\n%s", $command, $stderr));
		}

		if (empty($csv)) {
			throw new UserException('Aucune donnée trouvée');
		}

		$csv = explode("\n", $csv);

		/*
			Date
			Date valeur
			Opération
			Débit EUROS
			Crédit EUROS
		 */
		foreach ($csv as $count => $line) {
			$row = str_getcsv($line);

			if (count($row) < 2) {
				debug_log('Saut ligne vide');
				continue;
			}

			if (preg_match('!^Solde.*(?:AU\s+(\d+/\d+/\d+))!i', trim($row[0]), $match)) {
				if (null === $sum_header) {
					$row = [$match[1], null, $row[0], null, $row[2]];
					$out[$i++] = $row;
					$sum_header = $row;
					continue;
				}

				debug_log('Saut solde : %s', implode(', ', $row));
				continue;
			}
			elseif (preg_match('!^(?:Solde|Total|Réf\s+:.*SOLDE)!i', trim($row[0]))) {
				debug_log('Saut solde : %s', implode(', ', $row));
				continue;
			}

			if (null === $header) {
				debug_log('Saut entête');
				$header = $row;
				continue;
			}

			if ($header === $row) {
				debug_log('Saut répétition entête');
				continue;
			}

			if (count($row) !== count($header)) {
				throw new UserException(sprintf('Ligne %d : nombre de colonnes incohérent', $count));
			}

			foreach ($row as &$cell) {
				$cell = preg_replace('/\s\s+/', ' ', $cell);
			}

			unset($cell);

			if (empty($row[0])) {
				$out[$i - 1][2] .= PHP_EOL . $row[2];
				continue;
			}

			$out[$i++] = $row;
		}

		unset($row, $csv, $cell);
	}

	// Création du CSV de sortie
	$fp = fopen('php://temp', 'w');

	fputcsv($fp, ['Numéro d\'écriture', 'Date', 'Libellé', 'Compte de débit', 'Compte de crédit', 'Montant', 'Numéro pièce comptable', 'Référence paiement', 'Notes']);

	foreach ($out as $line) {
		$label = $line[2];
		$notes = null;
		$ref = null;

		if (false !== ($pos = strpos($label, "\n"))) {
			$notes = trim(substr($label, $pos));
			$label = trim(substr($label, 0, $pos));
		}

		if (preg_match('/^VRST (REF.*)/', $label, $match)) {
			$label = 'Versement espèces';
			$ref = $match[1];
		}
		elseif (preg_match('/^REM CHQ (REF.*)/', $label, $match)) {
			$label = 'Remise de chèques';
			$ref = $match[1];
		}
		elseif (preg_match('/^FACTURE (SGT.*)/', $label, $match)) {
			$label = 'Frais bancaires Crédit Mutuel';
			$ref = $match[1];
		}
		elseif (preg_match('/^CHEQUE (\d+)/', $label, $match)) {
			$label = 'Chèque ' . $match[1];
			$ref = $match[1];
		}

		$amount = !empty($line[4]) ? $line[4] : '-' . $line[3];
		$amount = str_replace('.', '', $amount);

		fputcsv($fp, ['', $line[0], $label, '', '', $amount, '', $ref, $notes]);
	}

	header('Content-Type: text/csv; charset="utf-8"');
	header('Content-Disposition: attachment; filename="credit_mutuel.csv"');

	fseek($fp, 0);

	fpassthru($fp);

	fclose($fp);
	exit;
}, $csrf_key);

$tpl->assign(compact('csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/credit_mutuel.tpl');
