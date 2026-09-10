<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

Session::getInstance()->requireAccess(Session::SECTION_ACCOUNTING, Session::ACCESS_WRITE);

$invoice = Invoices::get(intval($_GET['id'] ?? 0));

if (!$invoice) {
	throw new UserException('Unknown invoice ID');
}

$csrf_key = 'cancel_invoice_' . $invoice->id();

$form->runIf('delete', function () use ($invoice) {
	$new = $invoice->cancel();

	if ($new) {
		$args = 'msg=CREDIT&id=' . $new->id();
	}
	else {
		$args = 'id=' . $invoice->id();
	}

	Utils::redirect('!p/invoice/details.php?' . $args);
}, $csrf_key);

if ($invoice->isQuote()) {
	$question = 'Annuler le devis ?';
	$details = 'Il ne sera plus possible de transformer le devis en facture.';
}
else {
	$question = 'Annuler la facture ?';
	$details = 'Un avoir sera automatiquement créé.';
}

$tpl->assign(compact('invoice', 'csrf_key', 'question', 'details'));

$tpl->display(PLUGIN_ROOT . '/templates/cancel.tpl');
