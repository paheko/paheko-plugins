<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Forms;
use Paheko\Plugin\HelloAsso\Orders;

require __DIR__ . '/_inc.php';

$f = Forms::get((int)qg('id'));

if (!$f) {
	throw new UserException('Formulaire inconnu');
}

$list = Orders::list($f);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'f'));

$tpl->display(PLUGIN_ROOT . '/templates/orders.tpl');
