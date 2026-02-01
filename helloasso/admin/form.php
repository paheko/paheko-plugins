<?php

namespace Paheko;

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
}, $csrf_key, './orders.php?id=' . $f->id());

$tiers = $f->listTiers();
$years_assoc = Years::listOpenAssoc();
$payment_account = !empty($f->payment_account_code) ? [$f->payment_account_code => $f->payment_account_code] : null;

$tpl->assign(compact('tiers', 'csrf_key', 'years_assoc', 'f', 'payment_account'));

$tpl->display(PLUGIN_ROOT . '/templates/form.tpl');
