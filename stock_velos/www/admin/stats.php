<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

$tpl->assign('stats', $velos->statsByMonth());

$tpl->display(PLUGIN_ROOT . '/templates/stats.tpl');
