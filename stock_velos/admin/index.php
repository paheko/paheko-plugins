<?php

namespace Paheko;

if ($plugin->needUpgrade())
{
	$plugin->upgrade();
}

require_once __DIR__ . '/_inc.php';

$list = $velos->listVelosStock();
$list->loadFromQueryString();

$tpl->assign('list', $list);

$tpl->assign('total', $velos->countVelosStock());

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
