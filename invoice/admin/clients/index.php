<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

require __DIR__ . '/../_inc.php';

$archived = boolval($_GET['archived'] ?? false);

Clients::reloadUsersData();

$list = Clients::getList($archived);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'archived'));

$tpl->display(PLUGIN_ROOT . '/templates/clients/index.tpl');
