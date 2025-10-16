<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Products;

require __DIR__ . '/../_inc.php';

$archived = !empty($_GET['archived']);
$search = $_GET['q'] ?? null;
$id = (int)($_GET['id'] ?? null);
$list = Products::getListForLinking($id, $archived, $search);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'archived', 'search', 'id'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/products/list_for_linking.tpl');
