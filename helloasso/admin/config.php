<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;
use Paheko\Users\DynamicFields;

require_once __DIR__ . '/_inc.php';

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = sprintf('config_plugin_%s', $plugin->id);

$ha = HelloAsso::getInstance();

$form->runIf('save', function () use ($ha) {
	$ha->saveConfig($_POST ?? []);
}, $csrf_key, '?ok');

$match_options = [
	0 => 'Nom et prénom',
	1 => 'Adresse e-mail',
];

$plugin_config = $ha->getConfig();

$merge_names_order_options = $ha::MERGE_NAMES_ORDER_OPTIONS;
$ha_fields = $ha::PAYER_FIELDS;

$df = DynamicFields::getInstance();
$fields_assoc = $df->listImportAssocNames();
$name_fields = $df->getNameFields();
$name_field = count($name_fields) === 1 ? $df->get(current($name_fields)) : null;

$bank_account = !empty($plugin_config->bank_account_code) ? [$plugin_config->bank_account_code => $plugin_config->bank_account_code] : null;
$provider_account = !empty($plugin_config->provider_account_code) ? [$plugin_config->provider_account_code => $plugin_config->provider_account_code] : null;

$tpl->assign(compact(
	'csrf_key',
	'merge_names_order_options',
	'match_options',
	'ha_fields',
	'fields_assoc',
	'name_field',
	'plugin_config',
	'bank_account',
	'provider_account'
));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
