<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velo;

require_once __DIR__ . '/_inc.php';

$csrf_key = 'ajout_velo';

$form->runIf('save', function () {
    $velo = new Velo;
    $velo->importForm();
    $velo->save();

    utils::redirect(utils::plugin_url(['query' => 'id=' . $velo->id]));
}, $csrf_key);

$tpl->assign('velo', null);

$tpl->assign('fields', $velos->getFields($plugin));
$tpl->assign('abaques', $velos::ABAQUES);

$tpl->assign('libre', $velos->getEtiquetteLibre());

$tpl->assign('now', new \DateTime);
$tpl->assign('csrf_key', $csrf_key);

$tpl->display(PLUGIN_ROOT . '/templates/ajout.tpl');
