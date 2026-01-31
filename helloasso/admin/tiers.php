<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Forms;
use Paheko\Accounting\Years;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$f = Forms::get((int)$_GET['id']);

if (!$f) {
	throw new UserException('Formulaire inconnu');
}

$tiers = $f->listTiers();

$tpl->assign(compact('tiers', 'f'));

$tpl->display(PLUGIN_ROOT . '/templates/tiers.tpl');
