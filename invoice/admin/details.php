<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

require __DIR__ . '/_inc.php';

$invoice = Invoices::get(intval($_GET['id'] ?? 0));

if (!$invoice) {
	throw new UserException('Unknown invoice ID');
}

if (isset($_GET['preview'])) {
	$invoice->streamAs($_GET['preview'] ?: 'facturx');
	return;
}

if (isset($_GET['download'])) {
	$invoice->downloadAs($_GET['download'] ?: 'facturx');
	return;
}

if (isset($_GET['print'])) {
	$invoice->streamAs('html');
	return;
}

$title = sprintf('%s %s', $invoice->getTypeLabel(), $invoice->number ?? '(brouillon)');
$csrf_key = 'edit_invoice_details';

// Allow to select first invoice/quote number
if ($invoice->isDraft()
	&& !empty($_POST['validate'])
	&& !Invoices::count($invoice->isQuote())
	&& empty($_POST['number']))
{
	$tpl->assign(compact('invoice', 'csrf_key'));
	$tpl->display(PLUGIN_ROOT . '/templates/first_number.tpl');
}
else {
	if ($invoice->isDraft()) {
		$form->runIf('delete_line', function () use ($invoice) {
			$line = $invoice->getLine(intval($_POST['delete_line'] ?? 0));
			$line->delete();
		}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());
	}

	$form->runIf('validate', function () use ($invoice) {
		$invoice->validate(intval($_POST['number'] ?? 1));
	}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());

	$form->runIf('mark_sent', function () use ($invoice) {
		$invoice->markAsSent();
	}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());

	$form->runIf('send_email', function () use ($invoice) {
		$invoice->sendEmail();
	}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());

	$form->runIf('mark_paid', function () use ($invoice) {
		$invoice->markAsPaid();
	}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());

	$form->runIf('mark_unpaid', function () use ($invoice) {
		$invoice->markAsUnpaid();
	}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());

	$export = $invoice->getExport();

	$payments = $invoice->getPaymentsList();

	if (!$invoice->isDraft()) {
		$tpl->assign('facturx_enabled', $invoice->canExportAsFacturX());
	}

	$tpl->assign(compact('invoice', 'title', 'payments', 'csrf_key', 'export'));

	$tpl->display(PLUGIN_ROOT . '/templates/details.tpl');

}