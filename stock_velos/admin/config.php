<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velos;
use Paheko\Users\Session;

require_once __DIR__ . '/_inc.php';

Session::getInstance()->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = 'velo_config';

$fields = $velos->getFields($plugin);

$form->runIf('save', function () use ($plugin, $fields) {
	$status = [];

	foreach ($fields as $name => $field) {
		if (!empty($_POST['enabled'][$name]) && !empty($_POST['required'][$name])) {
			$status[$name] = 2;
		}
		elseif (!empty($_POST['enabled'][$name])) {
			$status[$name] = 1;
		}
		else {
			$status[$name] = 0;
		}
	}

	$plugin->setConfigProperty('fields', $status);
	$plugin->save();
}, $csrf_key, '?ok');

$tpl->assign(compact('fields', 'csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
