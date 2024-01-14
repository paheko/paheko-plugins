<?php

namespace Paheko;

use Paheko\Accounting\Export;
use Paheko\Users\Session;

$columns = array_flip(Export::COLUMNS[Export::SIMPLE]);
$columns['debit'] = 'Débit';
$columns['credit'] = 'Crédit';

$mandatory_columns = ['date', 'label'];

$csv = new CSV_Custom(Session::getInstance(), 'acc_tools_affectation');
$csv->setColumns($columns);
$csrf_key = 'acc_tools_affectation';
$lines = [];

$form->runIf('load', function () use ($csv) {
	$csv->load($_FILES['file'] ?? []);
}, $csrf_key, Utils::getSelfURI());

$form->runIf('cancel', function () use ($csv) {
	$csv->clear();
}, $csrf_key, Utils::getSelfURI());

$form->runIf(f('preview') && $csv->loaded(), function () use (&$csv, $mandatory_columns) {
	$csv->skip((int)f('skip_first_line'));
	$table = f('translation_table');

	if (!in_array('amount', $table)) {
		$mandatory_columns[] = 'debit';
		$mandatory_columns[] = 'credit';
	}
	else {
		$mandatory_columns[] = 'amount';
	}

	$csv->setMandatoryColumns($mandatory_columns);
	$csv->setTranslationTable($table);
}, $csrf_key, Utils::getSelfURI());

$form->runIf($csv->ready(), function () use ($csv, $plugin, &$lines) {
	$rules = $plugin->getConfig('rules') ?? [];

	if (empty($rules)) {
		throw new UserException('Aucune règle');
	}

	foreach ($rules as &$rule) {
		$rule->regexp = '%' . str_replace('%', '\\%', $rule->match) . '%i';
	}

	unset($rule);

	foreach ($csv->iterate() as $row) {
		if (!isset($row->amount)) {
			if (!empty($row->debit)) {
				$row->amount = -1 * abs(Utils::moneyToInteger($row->debit));
			}
			else {
				$row->amount = abs(Utils::moneyToInteger($row->credit));
			}
		}
		else {
			$row->amount = Utils::moneyToInteger($row->amount);
		}

		foreach ($rules as $rule) {
			if ($rule->only_if === 'positive' && $row->amount <= 0) {
				continue;
			}
			elseif ($rule->only_if === 'negative' && $row->amount >= 0) {
				continue;
			}
			elseif (!preg_match($rule->regexp, $row->label . ' ' . ($row->notes ?? ''))) {
				continue;
			}

			$row->debit_account = $rule->debit;
			$row->credit_account = $rule->credit;

			if (!empty($rule->new_label)) {
				$row->label = $rule->new_label;
			}

			break;
		}

		$lines[] = $row;
	}
});

$header = $csv->getHeader();

if ($header) {
	unset($header['date'], $header['amount'], $header['label'], $header['debit'], $header['credit'], $header['debit_account'], $header['credit_account']);
}

$show_options = [
	'all'       => 'Afficher toutes les lignes',
	'empty'     => 'Seulement les lignes sans affectation',
	'not_empty' => 'Seulement les lignes affectées',
];
$show = qg('show') ?: 'all';

$tpl->assign(compact('csv', 'lines', 'csrf_key', 'header', 'show', 'show_options'));

$tpl->display(PLUGIN_ROOT . '/templates/affectation.tpl');
