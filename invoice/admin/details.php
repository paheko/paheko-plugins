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

if ($invoice->isQuote()) {
	$title = sprintf('Devis : %s', $invoice->number ?? '(brouillon)');
}
else {
	$title = sprintf('Facture : %s', $invoice->number ?? '(brouillon)');
}

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

		$form->runIf('validate', function () use ($invoice) {
			$invoice->validate(intval($_POST['number'] ?? 1));
		}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());
	}

	$export = $invoice->content ?? $invoice->exportForInvoice();

	$payments = $invoice->getPaymentsList();

	$tpl->assign(compact('invoice', 'title', 'payments', 'csrf_key', 'export'));

	$tpl->display(PLUGIN_ROOT . '/templates/details.tpl');

}