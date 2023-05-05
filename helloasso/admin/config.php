<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\HelloAsso;

use Garradin\Users\DynamicFields;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$ha = HelloAsso::getInstance();

if ((array_key_exists('tab', $_GET) && $_GET['tab'] === 'client') || !$ha->isConfigured())
	Utils::redirect(PLUGIN_ADMIN_URL . 'config_client.php');

$csrf_key = sprintf('config_plugin_%s', $plugin->id);

$form->runIf('save', function () use ($ha) {
	$ha->saveConfig(f('map'), f('merge_names'), f('match_email_field'));
}, $csrf_key, '?ok');

$match_options = [
	0 => 'Nom et prénom',
	1 => 'Adresse e-mail',
];

$merge_names_options = $ha::MERGE_NAMES_OPTIONS;

//$fields_names = $ha::PAYER_FIELDS;
$fields_names = [];

$champs = DynamicFields::getInstance()->all();

$target_fields = [
	null => '-- Ne pas importer',
];

foreach ($champs as $key => $config) {
	if (!isset($config->label)) {
		continue;
	}

	$target_fields[$key] = $config->label;
}

$plugin_config = $ha->getConfig();

$tpl->assign(compact('merge_names_options', 'match_options', 'csrf_key', 'fields_names', 'target_fields', 'plugin_config'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
