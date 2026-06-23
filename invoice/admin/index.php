<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

$type = intval($_GET['type'] ?? 0) ?: null;
$status = $_GET['status'] ?? null;

$list = Invoices::getList($type, $status);
$list->loadFromQueryString();

if ($type && array_key_exists($type, Invoice::TYPES_PLURAL)) {
	$title = Invoice::TYPES_PLURAL[$type];
}
else {
	$title = 'Factures et devis';
}

if ($type === Invoice::TYPE_QUOTE) {
	$current_tab = 'quotes';
}
elseif ($type === Invoice::TYPE_INVOICE) {
	$current_tab = 'invoices';
}
else {
	$current_tab = 'all';
}

$tpl->assign(compact('list', 'title', 'current_tab', 'type', 'status'));

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
