<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Plugin\Invoice\Entities\ReceivedInvoice;

use KD2\Form;

use const Paheko\PLUGIN_ROOT;

$status = Form::getQueryString('status') ?: null;
$type = null;

$list = ReceivedInvoices::getList($status);
$list->loadFromQueryString();

$statuses = array_merge(
	['' => 'Toutes'],
	ReceivedInvoice::STATUSES);

foreach ($statuses as $name => &$s) {
	$s = [
		'status' => $name,
		'label' => $s,
		'color' => ReceivedInvoice::STATUSES_COLORS[$name] ?? 'white',
	];
}

$tpl->assign(compact('list', 'type', 'status', 'statuses'));

$tpl->display(PLUGIN_ROOT . '/templates/received/index.tpl');
