<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;
use Paheko\Users\DynamicFields;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = sprintf('config_plugin_%s', $plugin->id);

$ha = HelloAsso::getInstance();

$form->runIf('save', function () use ($ha) {
	$ha->saveConfig(f('fields_map'), f('merge_names_order'), f('match_email_field'));
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

$tpl->assign(compact('merge_names_order_options', 'match_options', 'csrf_key', 'ha_fields', 'fields_assoc', 'name_field', 'plugin_config'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
