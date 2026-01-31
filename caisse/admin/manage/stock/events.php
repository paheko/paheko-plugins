<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Stock;

require __DIR__ . '/../_inc.php';

$tpl->assign('list', Stock::listEvents());

$tpl->display(PLUGIN_ROOT . '/templates/manage/stock/events.tpl');
