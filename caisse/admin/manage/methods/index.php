<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Locations;
use Paheko\Plugin\Caisse\Methods;

require __DIR__ . '/../_inc.php';

$has_locations = Locations::count() > 0;
$list = Methods::getList($has_locations);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'has_locations'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/methods/index.tpl');
