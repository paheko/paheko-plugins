<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Plugin\Invoice\Entities\Document;

use const Paheko\PLUGIN_ROOT;

$type = intval($_GET['type'] ?? 0) ?: null;
$status = $_GET['status'] ?? null;

$list = Invoices::getList($type, $status);
$list->loadFromQueryString();

if ($type && array_key_exists($type, Document::TYPES_PLURAL)) {
	$title = Document::TYPES_PLURAL[$type];
}
else {
	$title = 'Factures et devis';
}

if ($type === Document::TYPE_QUOTE) {
	$current_tab = 'quotes';
}
elseif ($type === Document::TYPE_INVOICE) {
	$current_tab = 'invoices';
}
else {
	$current_tab = 'all';
}

$tpl->assign(compact('list', 'title', 'current_tab', 'type', 'status'));

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
