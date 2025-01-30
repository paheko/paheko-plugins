<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velos;

require_once __DIR__ . '/_inc.php';

if (!qg('id'))
	throw new UserException('Manque ID dans URL');

$id = (int) qg('id');

$velo = Velos::get($id);

if (!$velo) {
	throw new UserException('Ce vélo n\'existe pas !');
}

$csrf_key = 'ajout_velo';

$form->runIf('save', function () use ($velo) {
	$velo->importForm();
	$velo->save();

	utils::redirect(utils::plugin_url(['query' => 'id=' . $velo->id]));
}, $csrf_key);

$tpl->assign('fields', $velos->getFields($plugin));
$tpl->assign('abaques', $velos::ABAQUES);

$tpl->assign('now', new \DateTime);
$tpl->assign('velo', $velo);
$tpl->assign('csrf_key', $csrf_key);

$tpl->display(PLUGIN_ROOT . '/templates/modifier.tpl');
