<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

$tpl->assign('stats_years', $velos->statsByYear());
$tpl->assign('stats_months', $velos->statsByMonth());

$tpl->display(PLUGIN_ROOT . '/templates/stats.tpl');
