<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;
use Paheko\Users\DynamicFields;

require_once __DIR__ . '/_inc.php';

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = 'hello_config_users';
$ha = HelloAsso::getInstance();

$form->runIf('save', function () use ($ha) {
	$ha->saveConfig($_POST ?? []);
}, $csrf_key, '?msg=SAVED');

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

$tpl->assign(compact(
	'csrf_key',
	'merge_names_order_options',
	'match_options',
	'ha_fields',
	'fields_assoc',
	'name_field',
	'plugin_config',
));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
