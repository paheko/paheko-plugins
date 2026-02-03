<?php

namespace Paheko;

require __DIR__ . '/_inc.php';

$csrf_key = 'sync';
$last_sync = $ha->getLastSync();

$sync = [
	'forms' => !$last_sync || !empty($_POST['forms']),
	'orders' => !$last_sync || !empty($_POST['orders']),
];

$form->runIf('sync', function() use ($ha, $sync) {
	$ha->sync($sync['forms'], $sync['orders']);
}, $csrf_key, './');

$tpl->assign(compact('last_sync', 'csrf_key', 'sync'));

$tpl->display(PLUGIN_ROOT . '/templates/sync.tpl');
