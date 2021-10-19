<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$ha = HelloAsso::getInstance();

if (!$ha->getOAuth()) {
	Utils::redirect(PLUGIN_URL . 'config_client.php');
}

if (isset($_GET['send_debug'])) {
	echo "Envoi en cours, ne pas annuler SVP... ";
	echo str_repeat("  ", 2048);
	flush();
	ob_end_flush();
	$list = $ha->listForms();

	$form = current($ha->listForms());

	if (!$form) {
		die();
	}

	$payments = $ha->listOrganizationPayments($form->org_slug);
	$keys_org = [];

	foreach ($payments as $payment) {
		$keys_org = array_unique(array_merge($keys_org, array_keys((array)$payment->payer)));
	}

	$keys_forms = [];

	foreach ($list as $form) {
		$payments = $ha->listPayments($form);
		$keys_form = [];

		foreach ($payments as $payment) {
			$keys_form = array_unique(array_merge($keys_form, array_keys((array)$payment->payer)));
		}

		$keys_forms[] = $keys_form;

	}

	mail('bohwaz@garradin.eu', 'Liste de champs HelloAsso', json_encode(compact('keys_org', 'keys_forms'), JSON_PRETTY_PRINT));
	die('Envoi réalisé, merci !');
}

$tpl->assign('list', $ha->listForms());
$tpl->assign('restricted', $ha->isTrial());

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
