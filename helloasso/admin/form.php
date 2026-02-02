<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;
use Paheko\Plugin\HelloAsso\Forms;
use Paheko\Accounting\Years;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$f = Forms::get((int)$_GET['id']);

if (!$f) {
	throw new UserException('Formulaire inconnu');
}

$csrf_key = 'helloasso_form_' . $f->id();

$form->runIf('save', function () use ($f) {
	$f->importForm();
	$f->save();
}, $csrf_key, './form.php?msg=SAVED&id=' . $f->id());

$tiers = $f->listTiers();
$years_assoc = Years::listOpenAssoc();
$payment_account = !empty($f->payment_account_code) ? [$f->payment_account_code => $f->payment_account_code] : null;
$tiers = $f->listTiers();
$options = $f->listOptions();

$ha = HelloAsso::getInstance();
$plugin_config = $ha->getConfig();

$tpl->assign(compact('tiers', 'csrf_key', 'years_assoc', 'f', 'payment_account', 'tiers', 'options', 'plugin_config'));

$tpl->display(PLUGIN_ROOT . '/templates/form.tpl');
