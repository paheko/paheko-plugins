<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

$archived = boolval($_GET['archived'] ?? false);

$list = Clients::getList($archived);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'archived'));

$tpl->display(PLUGIN_ROOT . '/templates/clients/index.tpl');
