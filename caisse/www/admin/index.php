<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Sessions;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

require __DIR__ . '/_inc.php';

$tpl->assign('current_pos_session', Sessions::getCurrentId());
$tpl->assign('pos_sessions', Sessions::list());
$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
