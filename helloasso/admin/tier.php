<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Forms;
use Paheko\Users\DynamicFields;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$tier = Forms::getTier((int)$_GET['id']);

if (!$tier) {
	throw new UserException('Tarif inconnu');
}

$csrf_key = 'helloasso_tier_' . $tier->id();
$f = $tier->form();

$form->runIf('save', function () use ($tier) {
	$ha->saveConfig(f('fields_map'), f('merge_names_order'), f('match_email_field'));
}, $csrf_key, './orders.php?id=' . $f->id());

$options = $tier->listOptions();
$ha_fields = $tier->custom_fields;

$df = DynamicFields::getInstance();
$fields_assoc = $df->listImportAssocNames();

$account = $tier->account_code ? [$tier->account_code => $tier->account_code] : null;

$tpl->assign(compact('tier', 'csrf_key', 'f', 'ha_fields', 'fields_assoc', 'account'));

$tpl->display(PLUGIN_ROOT . '/templates/tier.tpl');
