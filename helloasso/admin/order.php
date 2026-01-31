<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Orders;
use Paheko\Plugin\HelloAsso\Payments;
use Paheko\Plugin\HelloAsso\Items;

require __DIR__ . '/_inc.php';

$order = Orders::get((int)qg('id'));

if (!$order) {
	throw new UserException('Commande inconnue');
}

if (!empty($_GET['set_user_id']) && !$order->id_user) {
	$order->setUserId((int) $_GET['set_user_id']);
	Utils::redirect('./order.php?id=' . $order->id());
}

$payments = Payments::list($order);
$items = Items::list($order);

$payer_infos = $order->getPayerInfos();
$payer = $order->getRawPayerData();

if (!$order->id_user) {
	$found_user = $ha->findUserForPayment($payer);
	$mapped_user = $ha->getMappedUser($payer);
}
else {
	$found_user = null;
	$mapped_user = null;
}

$tpl->assign(compact('order', 'payments', 'items', 'payer_infos', 'found_user', 'mapped_user'));

$tpl->display(PLUGIN_ROOT . '/templates/order.tpl');
