<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Forms;

require __DIR__ . '/_inc.php';

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

if (!$ha->getLastSync()) {
	Utils::redirect('./sync.php');
}

$type = $_GET['type'] ?? null;
$list = Forms::listByType($type);

if (count($list) === 1) {
	Utils::redirect('./orders.php?id=' . $list[0]->id);
}

$tpl->assign(compact('list', 'type'));

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
