<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Product;

require __DIR__ . '/../_inc.php';

$tpl->assign('list', Product::listByCategory(false));

$tpl->display(PLUGIN_ROOT . '/templates/manage/products/index.tpl');
