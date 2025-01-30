<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velos;
use Paheko\Users\Session;

require_once __DIR__ . '/_inc.php';

Session::getInstance()->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = 'velo_config';

$fields = $velos->getFields($plugin);
$field = $fields[$_GET['field']] ?? null;

if (!$field) {
	throw new UserException('Champ inconnu');
}

$form->runIf('save', function () use ($plugin, $field) {
	$list = $_POST[$field['name']] ?? '';
	$list = preg_replace("!\r?\n|\r|\n{2,}!", "\n", trim($list));
	$list = explode("\n", $list);
	$list = array_map('trim', $list);
	$list = array_filter($list);
	$list = array_values($list);

	$defaults = (array) ($plugin->getConfig('defaults') ?? []);
	$defaults[$field['name']] = $list;
	$plugin->setConfigProperty('defaults', $defaults);
	$plugin->save();
	Utils::closeFrameIfDialog();
}, $csrf_key);

$options = implode("\n", $field['options']);
$tpl->assign(compact('field', 'csrf_key', 'options'));

$tpl->display(PLUGIN_ROOT . '/templates/config_options.tpl');
