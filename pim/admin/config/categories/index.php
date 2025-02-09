<?php

namespace Paheko\Plugin\PIM;

use Paheko\Users\Session;

require __DIR__ . '/../../_inc.php';

$events = new Events(Session::getUserId());

if (!empty($_GET['export'])) {
	if ($_GET['export'] === 'all') {
		$events->exportAll();
	}
	else {
		$events->exportCategory((int) $_GET['export']);
	}

	return;
}

if (!empty($_GET['set_default'])) {
	$events->setDefaultCategory((int) $_GET['set_default']);
}

$list = $events->listCategories();
$tpl->assign(compact('list'));

$tpl->display(__DIR__ . '/../../../templates/config/categories/index.tpl');
