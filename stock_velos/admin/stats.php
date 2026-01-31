<?php

namespace Paheko;

require_once __DIR__ . '/_inc.php';

$period = ($_GET['period'] ?? '') === 'year' ? 'year' : 'quarter';
$type = ($_GET['type'] ?? '') === 'entry' ? 'entry' : 'exit';

$list = $velos->getStats($type, $period);
$list->loadFromQueryString();

$tpl->assign(compact('type', 'period', 'list'));

$tpl->display(PLUGIN_ROOT . '/templates/stats.tpl');
