<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Stock;

require __DIR__ . '/../_inc.php';

$tpl->assign('list', Stock::listEvents());

$tpl->display(PLUGIN_ROOT . '/templates/manage/stock/index.tpl');
