<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_WRITE);

$ha = HelloAsso::getInstance();

$order = $ha->getOrder((int)qg('id'));

if (!$order) {
	throw new UserException('Commande inconnue');
}

//$found_user = $ha->findUserForPayment($order->payer);
//$mapped_user = $ha->getMappedUser($order->payer);
$found_user = $mapped_user = [];

$tpl->assign(compact('order', 'found_user', 'mapped_user'));

$tpl->display(PLUGIN_ROOT . '/templates/order.tpl');
