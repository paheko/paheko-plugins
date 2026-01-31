<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;

// FIXME
Utils::redirect('./config_client.php');

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = sprintf('config_plugin_%s', $plugin->id);

$ha = HelloAsso::getInstance();

$form->runIf('save', function () use ($ha) {
	$ha->saveConfig(f('map'), f('merge_names'), f('match_email_field'));
}, $csrf_key, '?ok');

$match_options = [
	0 => 'Nom et prénom',
	1 => 'Adresse e-mail',
];

$merge_names_options = $ha::MERGE_NAMES_OPTIONS;

$fields_names = $ha::PAYER_FIELDS;

$champs = Config::getInstance()->get('champs_membres')->getAll();

$target_fields = [
	null => '-- Ne pas importer',
];

foreach ($champs as $key => $config) {
	if (!isset($config->title)) {
		continue;
	}

	$target_fields[$key] = $config->title;
}

$plugin_config = $ha->getConfig();

$tpl->assign(compact('merge_names_options', 'match_options', 'csrf_key', 'fields_names', 'target_fields', 'plugin_config'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
