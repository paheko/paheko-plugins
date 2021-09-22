<?php

namespace Garradin;

use Garradin\Plugin\Webstats\Stats;

$tpl->assign([
	'stats' => Stats::getStats(),
	'hits' => Stats::getHits(),
]);

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
