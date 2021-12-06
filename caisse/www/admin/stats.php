<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Method;
use Garradin\Plugin\Caisse\Session;
use Garradin\Plugin\Caisse\Product;

require __DIR__ . '/_inc.php';

$graph = qg('graph');
$year = qg('year');

if ($year) {
	if ($graph == 'methods') {
		header('Content-Type: image/svg+xml');
		echo Method::graphStatsPerMonth($year);
		exit;
	}
	elseif ($graph == 'categories') {
		header('Content-Type: image/svg+xml');
		echo Product::graphStatsPerMonth($year);
		exit;
	}
	elseif ($graph == 'categories_qty') {
		header('Content-Type: image/svg+xml');
		echo Product::graphStatsQtyPerMonth($year);
		exit;
	}

	$tpl->assign('methods_per_month', Method::getStatsPerMonth($year));
}
else {
	$tpl->assign('years', Session::listYears());
}

$tpl->assign(compact('year'));

$tpl->display(PLUGIN_ROOT . '/templates/stats.tpl');
