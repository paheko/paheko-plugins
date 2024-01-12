<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Categories;
use Paheko\Plugin\Caisse\Methods;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Sessions;

require __DIR__ . '/_inc.php';

$graph = qg('graph');
$year = qg('year');

if ($year) {
	if ($graph == 'methods') {
		header('Content-Type: image/svg+xml');
		echo Methods::graphStatsPerMonth($year);
		exit;
	}
	elseif ($graph == 'categories') {
		header('Content-Type: image/svg+xml');
		echo Categories::graphStatsPerMonth($year);
		exit;
	}
	elseif ($graph == 'categories_qty') {
		header('Content-Type: image/svg+xml');
		echo Categories::graphStatsQtyPerMonth($year);
		exit;
	}

	$page = qg('page');
	$list = null;

	if ($page === 'methods_in') {
		$list = Methods::listSalesPerMonth($year);
	}
	elseif ($page === 'methods_out') {
		$list = Methods::listExitsPerMonth($year);
	}
	elseif ($page === 'sales_categories') {
		$list = Categories::listSalesPerMonth($year);
	}
	elseif ($page === 'sales_products_month') {
		$list = Products::listSalesPerMonth($year);
	}
	elseif ($page === 'sales_products_year') {
		$list = Products::listSales($year);
	}

	if ($list) {
		$list->loadFromQueryString();
	}

	$tpl->assign(compact('page', 'list'));
}
else {
	$tpl->assign('years', Sessions::listYears());
}

$tpl->assign(compact('year'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/stats.tpl');
