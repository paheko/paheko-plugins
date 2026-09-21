<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Forms;
use Paheko\Accounting\Projects;
use Paheko\Services\Services;
use Paheko\Users\DynamicFields;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$tier = Forms::getTier((int)$_GET['id']);

if (!$tier) {
	throw new UserException('Tarif inconnu');
}

$csrf_key = 'helloasso_tier_' . $tier->id();
$f = $tier->form();

$form->runIf('save', function () use ($tier) {
	$tier->importForm();
	$tier->save();
}, $csrf_key, './form.php?id=' . $f->id());

$ha_fields = $tier->custom_fields;

$df = DynamicFields::getInstance();
$fields_assoc = $df->listImportAssocNames();

$fees = Services::listGroupedWithFeesForSelect(false);

$account = $tier->account_code ? [$tier->account_code => $tier->account_code] : null;

$projects = Projects::listAssoc();

$tpl->assign(compact('tier', 'csrf_key', 'f', 'ha_fields', 'fields_assoc', 'account', 'fees', 'projects'));

$tpl->display(PLUGIN_ROOT . '/templates/form_tier.tpl');
