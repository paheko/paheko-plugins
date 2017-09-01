<?php

namespace Garradin;

if ($plugin->needUpgrade())
{
	$plugin->upgrade();
}

require_once __DIR__ . '/_inc.php';

if (qg('id'))
{
    require_once __DIR__ . '/fiche.php';
    exit;
}

$order = 'etiquette';
$desc = false;

if (qg('o'))
    $order = qg('o');

if (qg('d') !== null)
    $desc = true;

$tpl->assign('order', $order);
$tpl->assign('desc', $desc);
$tpl->assign('liste', $velos->listVelosStock($order, $desc));

$tpl->assign('total', $velos->countVelosStock());

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
