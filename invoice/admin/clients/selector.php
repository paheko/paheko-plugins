<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

require __DIR__ . '/../_inc.php';

$search = trim($_GET['search'] ?? '');

$list = Clients::getList(false, $search);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'search'));

$tpl->display(PLUGIN_ROOT . '/templates/clients/selector.tpl');
