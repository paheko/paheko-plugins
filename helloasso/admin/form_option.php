<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Forms;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$option = Forms::getOption((int)$_GET['id']);

if (!$option) {
	throw new UserException('Option inconnue');
}

$csrf_key = 'helloasso_option_' . $option->id();
$f = $option->form();

$form->runIf('save', function () use ($option) {
	$option->importForm();
	$option->save();
}, $csrf_key, './form.php?id=' . $f->id());

$account = $option->account_code ? [$option->account_code => $option->account_code] : null;

$tpl->assign(compact('option', 'csrf_key', 'f', 'account'));

$tpl->display(PLUGIN_ROOT . '/templates/form_option.tpl');
