<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

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

if ($invoice->isDraft()) {
	$form->runIf('delete_line', function () use ($invoice) {
		$line = $invoice->getLine(intval($_POST['delete_line'] ?? 0));
		$line->delete();
	}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());

	$form->runIf('validate', function () use ($invoice) {
		$invoice->validate();
	}, $csrf_key, '!p/invoice/details.php?id=' . $invoice->id());
}

$lines = $invoice->getLinesList();
$lines->loadFromQueryString();

$payments = $invoice->getPaymentsList();

$tpl->assign(compact('invoice', 'title', 'lines', 'payments'));

$tpl->display(PLUGIN_ROOT . '/templates/details.tpl');
