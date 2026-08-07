<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Users\Session;

use const Paheko\PLUGIN_ROOT;

require __DIR__ . '/_inc.php';

Session::getInstance()->requireAccess(Session::SECTION_CONFIG, Session::ACCESS_ADMIN);

$csrf_key = 'invoice_config';

$form->runIf('save', function () use ($plugin) {
	$plugin->setConfigProperty('vat_number', $_POST['vat_number'] ?? '');
	$plugin->setConfigProperty('exemption_code', $_POST['exemption_code'] ?? '');
	$plugin->setConfigProperty('exemption_text', $_POST['exemption_text'] ?? '');
	$plugin->setConfigProperty('iban', $_POST['iban'] ?? '');
	$plugin->setConfigProperty('bic', $_POST['bic'] ?? '');
	$plugin->setConfigProperty('payment_instructions', $_POST['payment_instructions'] ?? '');
	$plugin->save();
}, $csrf_key, './config.php?ok');

$vat_exemption_codes = Invoices::VAT_EXEMPTIONS;
$default_vat_exemption_code = Invoices::DEFAULT_VAT_EXEMPTION;

$tpl->assign(compact('csrf_key', 'vat_exemption_codes'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
