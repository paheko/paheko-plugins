<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Invoice\Entities\ReceivedInvoice;

use const Paheko\PLUGIN_ROOT;

require __DIR__ . '/../_inc.php';

Session::getInstance()->requireAccess(Session::SECTION_ACCOUNTING, Session::ACCESS_WRITE);

$csrf_key = 'import_invoice';

$form->runIf('save', function () {
	$invoice = new ReceivedInvoice;
	$invoice->upload('invoice');
	$invoice->save();
	Utils::redirectParent('!p/invoice/received/details.php?id=' . $invoice->id());
}, $csrf_key);

$extensions = array_values(ReceivedInvoice::FILES_TYPES);
$extensions = array_map(fn($v) => '.' . $v, $extensions);

$accepted_files = implode(',', array_merge($extensions, array_keys(ReceivedInvoice::FILES_TYPES)));

$tpl->assign(compact('csrf_key', 'accepted_files'));

$tpl->display(PLUGIN_ROOT . '/templates/received/upload.tpl');
