<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Forms;
use Paheko\Plugin\HelloAsso\Items;

require __DIR__ . '/_inc.php';

$f = Forms::get((int)qg('id'));

if (!$f) {
	throw new UserException('Formulaire inconnu');
}

$list = Items::list($f);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'f'));
$tpl->assign('type', $f->type);

$tpl->display(PLUGIN_ROOT . '/templates/items.tpl');
