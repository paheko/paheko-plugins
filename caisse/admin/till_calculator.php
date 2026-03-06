<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Locations;
use Paheko\Plugin\Caisse\Sessions;
use function Paheko\Plugin\Caisse\get_amount;

require __DIR__ . '/_inc.php';

$id = intval($_GET['id'] ?? 0);

$denominations = [
	'EUR' => [
		'coins' => [1, 2, 5, 10, 20, 50, 100, 200], // in cents
		'notes' => [5, 10, 20, 50, 100, 200, 500],
	],
	'CHF' => [
		'coins' => [5, 10, 20, 50, 100, 200, 500], // in cents
		'notes' => [10, 20, 50, 100, 200, 500, 1000],
	],
	'XPF' => [
		'coins' => [500, 1000, 2000, 5000, 10000, 20000],
		'notes' => [500, 1000, 5000, 10000],
	],
	'CAD' => [
		'coins' => [1, 5, 10, 25, 50, 100, 200],
		'notes' => [5, 10, 20, 50, 100]
	],
];

$currency = Config::getInstance()->currency;

if (!array_key_exists($currency, $denominations)) {
	$currency = 'EUR';
}

$denominations = $denominations[$currency];

foreach ($denominations['notes'] as &$value) {
	$value *= 100;
}

unset($value);

sort($denominations['notes']);
rsort($denominations['notes']);
sort($denominations['coins']);
rsort($denominations['coins']);

$tpl->assign(compact('denominations', 'currency', 'id'));

$tpl->display(PLUGIN_ROOT . '/templates/till_calculator.tpl');
