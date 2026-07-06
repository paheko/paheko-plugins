<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Invoice\Entities\Line;

use const Paheko\PLUGIN_ROOT;

Session::getInstance()->requireAccess(Session::SECTION_ACCOUNTING, Session::ACCESS_WRITE);

if (isset($_GET['id'])) {
	$line = Invoices::getLine(intval($_GET['id'] ?? 0));

	if (!$line) {
		throw new UserException('Unknown line ID');
	}

	if (!$line->invoice()->isDraft()) {
		throw new UserException('Le document n\'est plus un brouillon et ne peut donc être modifié');
	}

	$title = 'Modifier une ligne';
}
else {
	$invoice = Invoices::get(intval($_GET['id_invoice'] ?? 0));

	if (!$invoice) {
		throw new UserException('Unknown invoice ID');
	}

	$line = new Line;
	$line->id_invoice = $invoice->id();
	$title = 'Nouvelle ligne';
}

$csrf_key = 'edit_line';

$form->runIf('save', function () use ($line) {
	$line->importForm();
	$line->saveAndUpdateInvoice();
	Utils::redirectParent('!p/invoice/details.php?id=' . $line->id_invoice);
}, $csrf_key);

$tpl->assign(compact('line', 'title', 'csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/line.tpl');
