<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Locations;

require __DIR__ . '/../_inc.php';

$list = Locations::getList();
$list->loadFromQueryString();

$tpl->assign(compact('list'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/locations/index.tpl');
