<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Product;

require __DIR__ . '/../_inc.php';

$product = Product::get((int) qg('id'));
$events_only = qg('events_only') !== null;
$history = $product->history($events_only);

$tpl->assign(compact('product', 'history', 'events_only'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/products/history.tpl');
