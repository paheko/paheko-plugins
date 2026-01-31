<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Products;

require __DIR__ . '/../_inc.php';

$product = Products::get((int) qg('id'));

if (!$product) {
	throw new UserException('Unknown product ID');
}

$events_only = qg('events_only') !== null;
$list = $product->getHistoryList($events_only);
$list->loadFromQueryString();

$tpl->assign(compact('product', 'list', 'events_only'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/products/history.tpl');
