<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Locations;
use Paheko\Plugin\Caisse\Methods;
use Paheko\Plugin\Caisse\Sessions;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

// @FIXME: temporary fix, if the update failed at some point
if (!DB::getInstance()->hasTable('plugin_pos_sessions_balances')) {
	$plugin->set('version', '0.8.11');
	$plugin->upgrade();
}

require __DIR__ . '/_inc.php';

$has_locations = Locations::count() > 0;
$list = Sessions::list($has_locations);
$list->loadFromQueryString();

$has_credit_methods = Methods::hasCreditMethods();

$tpl->assign('current_pos_session', Sessions::getCurrentId());
$tpl->assign(compact('list', 'has_locations', 'has_credit_methods'));
$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
