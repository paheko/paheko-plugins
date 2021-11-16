<?php

namespace Garradin;

require __DIR__ . '/_inc.php';

$csrf_key = 'sync';

$form->runIf('sync', function() use ($ha) {
	$ha->sync();
}, $csrf_key, PLUGIN_URL);

$tpl->assign('last_sync', $ha->getLastSync());
$tpl->assign('csrf_key', $csrf_key);

$tpl->display(PLUGIN_ROOT . '/templates/sync.tpl');
