<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Stock;

require __DIR__ . '/../_inc.php';

$event = Stock::get((int) qg('id'));

$csrf_key = sprintf('event_change_%d', $event->id);

$tpl->assign(compact('event', 'csrf_key', ''));

$tpl->display(PLUGIN_ROOT . '/templates/manage/stock/add_change.tpl');
