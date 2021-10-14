<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_WRITE);

$ha = HelloAsso::getInstance();

$payment = $ha->getPayment((int)qg('id'), $payment_json);

if (!$payment) {
	throw new UserException('Formulaire inconnu');
}

$found_user = $ha->findUserForPayment($payment->payer);
$mapped_user = $ha->getMappedUser($payment->payer);

$tpl->assign(compact('payment', 'payment_json', 'found_user', 'mapped_user'));

$tpl->display(PLUGIN_ROOT . '/templates/payment.tpl');
