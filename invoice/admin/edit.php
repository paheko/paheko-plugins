<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Invoice\Entities\Invoice;

use const Paheko\PLUGIN_ROOT;

require __DIR__ . '/_inc.php';

Session::getInstance()->requireAccess(Session::SECTION_ACCOUNTING, Session::ACCESS_WRITE);

if (!Clients::countActiveClients()) {
	Utils::redirect('!p/invoice/clients/edit.php?msg=CREATE');
}

if (isset($_GET['id'])) {
	$invoice = Invoices::get((int)$_GET['id']);

	if (!$invoice) {
		throw new UserException('Unknown invoice ID');
	}

	if (!$invoice->canEdit()) {
		throw new UserException('Ce document n\'est plus un brouillon et ne peut plus être modifié');
	}

	if ($invoice->isQuote()) {
		$title = 'Modifier le devis';
	}
	else {
		$title = 'Modifier la facture';
	}
}
else {
	$type = intval($_GET['type'] ?? 0) ?: Invoice::TYPE_QUOTE;

	if (!array_key_exists($type, Invoice::TYPES)) {
		throw new UserException('Invalid invoice type');
	}

	$invoice = new Invoice;
	$invoice->type = $type;
	$invoice->date_expiry = new \KD2\DB\Date('+1 month');

	if ($invoice->isQuote()) {
		$title = 'Nouveau devis';
	}
	else {
		$title = 'Nouvelle facture';
	}
}

$csrf_key = 'edit_invoice';

$form->runIf('save', function () use ($invoice) {
	$invoice->importForm();
	$invoice->save();
	Utils::redirectParent('!p/invoice/details.php?id=' . $invoice->id());
}, $csrf_key);

$tpl->assign('now', new \DateTime);
$tpl->assign(compact('invoice', 'title', 'csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/edit.tpl');
