<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Stock;

require __DIR__ . '/../_inc.php';

$event = Stock::get((int) qg('id'));

$csrf_key = sprintf('event_%d', $event->id);

$list = $event->listChanges();

$tpl->assign(compact('event', 'csrf_key', 'list'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/stock/details.tpl');
