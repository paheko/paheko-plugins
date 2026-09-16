<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\Orders;
use Paheko\Plugin\HelloAsso\Payments;
use Paheko\Plugin\HelloAsso\Items;
use Paheko\Users\Session;

require __DIR__ . '/_inc.php';

$order = Orders::get((int)qg('id'));

if (!$order) {
	throw new UserException('Commande inconnue');
}

$csrf_key = 'order_' . $order->id;

$form->runIf('link', function () use ($order) {
	$order->set('id_transaction', (int) $_POST['id_transaction']);
	$order->save();
}, $csrf_key, './order.php?id=' . $order->id());

$tpl->assign(compact(
	'order',
	'csrf_key',
));

$tpl->display(PLUGIN_ROOT . '/templates/order_link_transaction.tpl');
