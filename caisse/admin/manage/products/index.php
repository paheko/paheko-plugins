<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Products;

require __DIR__ . '/../_inc.php';

$archived = !empty($_GET['archived']);
$search = $_GET['q'] ?? null;
$list = Products::getList($archived, $search);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'archived', 'search'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/products/index.tpl');
