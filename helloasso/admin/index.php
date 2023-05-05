<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\Forms;

require __DIR__ . '/_inc.php';

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

if (!$ha->getLastSync()) {
	Utils::redirect(PLUGIN_ADMIN_URL . 'sync.php');
}

$tpl->assign('list', Forms::list());
$tpl->assign('restricted', $ha::isTrial());

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
