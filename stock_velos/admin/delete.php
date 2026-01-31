<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velos;

require_once __DIR__ . '/_inc.php';

$id = (int) qg('id');
$velo = Velos::get($id);

if (!$velo) {
	throw new UserException('Ce vélo n\'existe pas !');
}

$csrf_key = 'delete_bike';

$form->runIf('delete', function () use ($velo) {
	$velo->delete();
	utils::redirect(utils::plugin_url());
}, $csrf_key);

$tpl->assign('velo', $velo);
$tpl->assign('csrf_key', $csrf_key);

$tpl->display(PLUGIN_ROOT . '/templates/delete.tpl');
