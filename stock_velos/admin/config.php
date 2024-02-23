<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velos;

require_once __DIR__ . '/_inc.php';

$csrf_key = 'velo_config';

$form->runIf('save', function () use ($plugin) {
	foreach (Velos::DEFAULTS as $name => $values) {
		$list = $_POST[$name] ?? '';
		$list = preg_replace("!\r?\n|\r|\n{2,}!", "\n", trim($list));
		$list = explode("\n", $list);
		$list = array_map('trim', $list);
		$list = array_filter($list);
		$list = array_values($list);
		$plugin->setConfigProperty($name, $list);
	}

	$plugin->save();
}, $csrf_key, PLUGIN_ADMIN_URL);

$defaults = $velos->getDefaults($plugin);
$defaults = array_map(fn ($a) => implode("\n", $a), $defaults);

$tpl->assign(compact('defaults', 'csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
