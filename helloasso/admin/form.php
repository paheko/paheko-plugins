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
	$ha->saveConfig(f('fields_map'), f('merge_names_order'), f('match_email_field'));
}, $csrf_key, '?ok');

$tiers = $f->listTiers();
$years_assoc = Years::listOpenAssoc();

$tpl->assign(compact('tiers', 'csrf_key', 'years_assoc'));
$tpl->assign('haform', $f);

$tpl->display(PLUGIN_ROOT . '/templates/form.tpl');
