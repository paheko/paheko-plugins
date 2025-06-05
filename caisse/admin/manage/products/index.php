<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Products;

require __DIR__ . '/../_inc.php';

$archived = !empty($_GET['archived']);
$list = Products::getList($archived);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'archived'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/products/index.tpl');
