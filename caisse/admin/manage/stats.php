<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Categories;
use Paheko\Plugin\Caisse\Methods;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Sessions;

require __DIR__ . '/_inc.php';

$graph = qg('graph');
$year = qg('year');
$page = qg('page') ?? 'sales_categories';
$period = qg('period') ?? 'year';
$list = null;

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

	if ($page === 'methods_in') {
		$list = Methods::listSales($year, $period);
	}
	elseif ($page === 'methods_out') {
		$list = Methods::listExits($year, $period);
	}
	elseif ($page === 'sales_categories') {
		$list = Categories::listSales($year, $period);
	}
	elseif ($page === 'sales_products') {
		$list = Products::listSales($year, $period);
	}

	if ($list) {
		$list->loadFromQueryString();
	}
}

$title = $list ? $list->getTitle() : 'Statistiques';

$tpl->assign('years', Sessions::listYears());
$tpl->assign(compact('year', 'period', 'page', 'list', 'title'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/stats.tpl');
