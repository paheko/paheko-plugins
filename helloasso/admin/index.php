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

$tpl->assign('list', Forms::list());

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
