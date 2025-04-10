<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Stock;

require __DIR__ . '/../_inc.php';

$list = Stock::getHistoryList();
$list->loadFromQueryString();

$tpl->assign(compact('list'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/stock/history.tpl');
