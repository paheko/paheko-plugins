<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Categories;
use Paheko\Plugin\Caisse\Locations;
use Paheko\Plugin\Caisse\Methods;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Sessions;
use Paheko\Plugin\Caisse\Tabs;

require __DIR__ . '/_inc.php';

$graph = qg('graph');
$year = qg('year');
$page = qg('page') ?? 'sales_categories';
$period = qg('period') ?? 'year';
$location = intval(qg('location')) ?: null;
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
		$list = Methods::listSales($year, $period, $location);
	}
	elseif ($page === 'methods_out') {
		$list = Methods::listExits($year, $period, $location);
	}
	elseif ($page === 'sales_categories') {
		$list = Categories::listSales($year, $period, $location);
	}
	elseif ($page === 'sales_products') {
		$list = Products::listSales($year, $period, $location);
	}
	elseif ($page === 'tabs') {
		$list = Tabs::listStats($year, $period, $location);
	}

	if ($list) {
		$list->loadFromQueryString();
	}
}

$title = $list ? $list->getTitle() : 'Statistiques';
$locations = Locations::listAssoc();

$tpl->assign('years', Sessions::listYears());
$tpl->assign(compact('year', 'period', 'page', 'list', 'title', 'locations', 'location'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/stats.tpl');
