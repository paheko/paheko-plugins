<?php

namespace Paheko\Plugin\Invoice;

require __DIR__ . '/_inc.php';

$list = Invoices::getList();
$list->loadFromQueryString();

$tpl->assign(compact('list'));

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
