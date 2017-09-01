<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

$order = 'date_sortie';
$desc = true;

if (qg('o'))
    $order = qg('o');

if (qg('a') !== null)
    $desc = false;

$tpl->assign('order', $order);
$tpl->assign('desc', $desc);
$tpl->assign('liste', $velos->listVelosHistorique($order, $desc));

$tpl->assign('total', $velos->countVelosHistorique());

$tpl->display(PLUGIN_ROOT . '/templates/historique.tpl');
