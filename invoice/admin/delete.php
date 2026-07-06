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

if (!$invoice->isDraft()) {
	throw new UserException('Il n\'est pas possible de supprimer un document qui n\'est pas en brouillon');
}

if ($invoice->isQuote()) {
	$question = 'Supprimer le devis ?';
}
else {
	$question = 'Supprimer la facture ?';
}

$csrf_key = 'delete_invoice';

$form->runIf('delete', function () use ($invoice) {
	$invoice->delete();
	Utils::redirectParent('!p/invoice/');
}, $csrf_key);

$tpl->assign(compact('invoice', 'question', 'csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/delete.tpl');
