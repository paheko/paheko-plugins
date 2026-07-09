<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

Session::getInstance()->requireAccess(Session::SECTION_ACCOUNTING, Session::ACCESS_WRITE);

$orig = Invoices::get((int)$_GET['id']);

if (!$orig) {
	throw new UserException('Unknown invoice ID');
}

$invoice = $orig->duplicate();

if ($invoice->isQuote()) {
	$title = 'Dupliquer le devis';
}
else {
	$title = 'Dupliquer la facture';
}

$csrf_key = 'copy_invoice';

$form->runIf('save', function () use ($invoice, $orig) {
	$invoice->importForm();
	$invoice->save();
	$invoice->saveAsCopyOf($orig);
	Utils::redirectParent('!p/invoice/details.php?id=' . $invoice->id());
}, $csrf_key);

$tpl->assign('now', new \DateTime);
$tpl->assign(compact('invoice', 'title', 'csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/edit.tpl');
