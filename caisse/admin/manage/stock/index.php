<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Stock;

require __DIR__ . '/../_inc.php';

$archived = !empty($_GET['archived']);
$search = $_GET['q'] ?? null;
$list = Products::getStockList($archived, $search);
$list->loadFromQueryString();
$list->setPageSize(null);

$tpl->assign(compact('list', 'archived', 'search'));
$tpl->assign('categories', Stock::listCategoriesValue());

$tpl->display(PLUGIN_ROOT . '/templates/manage/stock/index.tpl');
