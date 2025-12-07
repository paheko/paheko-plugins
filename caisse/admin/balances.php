<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Entities\Method;
use Paheko\Plugin\Caisse\Tabs;

require __DIR__ . '/_inc.php';

$type = intval($_GET['type'] ?? 0);
$is_debt = false;

if ($type === Method::TYPE_DEBT) {
	$title = 'Ardoises en cours';
	$is_debt = true;
}
else {
	$title = 'Porte-monnaie des membres';
}

$list = Tabs::listBalances($type);
$list->loadFromQueryString();
$tpl->assign(compact('type', 'list', 'title', 'is_debt'));

$tpl->display(PLUGIN_ROOT . '/templates/balances.tpl');
