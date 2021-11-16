<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\Orders;
use Garradin\Plugin\HelloAsso\Payments;
use Garradin\Plugin\HelloAsso\Items;

require __DIR__ . '/_inc.php';

$order = Orders::get((int)qg('id'));

if (!$order) {
	throw new UserException('Commande inconnue');
}

$payments = Payments::list($order);
$items = Items::list($order);

$payer_infos = $order->getPayerInfos();

//$found_user = $ha->findUserForPayment($order->payer);
//$mapped_user = $ha->getMappedUser($order->payer);
$found_user = $mapped_user = [];

$tpl->assign(compact('order', 'payments', 'items', 'payer_infos', 'found_user', 'mapped_user'));

$tpl->display(PLUGIN_ROOT . '/templates/order.tpl');
