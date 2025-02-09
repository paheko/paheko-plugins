<?php

namespace Paheko\Plugin\PIM;

use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$events = new Events(Session::getUserId());

if (!empty($_GET['set_default'])) {
	$events->setDefaultCategory((int) $_GET['set_default']);
}

$list = $events->listCategories();
$tpl->assign(compact('list'));

$tpl->display(__DIR__ . '/../../templates/categories/index.tpl');
