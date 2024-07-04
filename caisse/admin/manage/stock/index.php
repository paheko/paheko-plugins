<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Stock;

require __DIR__ . '/../_inc.php';

$tpl->assign('list', Products::listByCategory(false, true));
$tpl->assign('categories', Stock::listCategoriesValue());

$tpl->display(PLUGIN_ROOT . '/templates/manage/stock/index.tpl');
