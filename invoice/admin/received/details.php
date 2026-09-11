<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Plugin\Invoice\Entities\ReceivedInvoice;

use const Paheko\PLUGIN_ROOT;

require __DIR__ . '/../_inc.php';

$invoice = ReceivedInvoices::get(intval($_GET['id'] ?? 0));

if (!$invoice) {
	throw new UserException('Unknown invoice ID');
}

if (isset($_GET['download'])) {
	$invoice->download();
	return;
}
elseif (isset($_GET['print'])) {
	$invoice->streamAs('html');
	return;
}

$title = sprintf('Facture reçue %s', $invoice->getReference());
$csrf_key = 'edit_received_' . $invoice->id();

if ($session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)) {
	$form->runIf('set_status', function () use ($invoice) {
		$invoice->validate(Form::getPostString('status') ?? '');
	}, $csrf_key, '!p/invoice/received/details.php?id=' . $invoice->id());
}

$export = $invoice->getExport();
//$payments = $invoice->getPaymentsList();
$payments = null;
$person = $invoice->getPerson();

$tpl->assign(compact('invoice', 'title', 'payments', 'csrf_key', 'export', 'person'));

$tpl->display(PLUGIN_ROOT . '/templates/received/details.tpl');
