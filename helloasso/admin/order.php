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

if (!empty($_GET['set_user_id']) && !$order->id_user) {
	$order->setUserId((int) $_GET['set_user_id']);
	Utils::redirect('./order.php?id=' . $order->id());
}

if (!empty($_GET['item_set_user_id'])) {
	$item = $order->getItem((int)$_GET['item_id']);

	if (!$item->id_user) {
		$item->setUserId((int) $_GET['item_set_user_id']);
	}

	Utils::redirect('./order.php?id=' . $order->id());
}

$id_creator = Session::getUserId();

$form->runIf('sync_all', function () use ($order, $id_creator) {
	$order->importAll($id_creator);
}, null, './order.php?id=' . $order->id());

$form->runIf('create_transaction', function () use ($order, $id_creator) {
	$order->importTransaction($id_creator);
}, null, './order.php?id=' . $order->id());

$form->runIf('create_users', function () use ($order, $id_creator) {
	$order->importMembershipUsers($id_creator);
}, null, './order.php?id=' . $order->id());

$form->runIf('create_subscriptions', function () use ($order, $id_creator) {
	$order->importMembershipSubscriptions($id_creator);
}, null, './order.php?id=' . $order->id());

$payments = Payments::list($order);
$items = Items::list($order, $ha);

$payer_infos = $order->getPayerInfos();
$payer = $order->getRawPayerData();

if (!$order->id_user) {
	$found_user = $ha->findMatchingUser($payer);
	$mapped_user = $ha->getMappedUser($payer);
}
else {
	$found_user = null;
	$mapped_user = null;
}

$f = $order->form();
$type = $f->type;
$has_all_users = $order->hasAllUsers();
$has_all_subscriptions = $order->hasAllSubscriptions();
$is_synced = $order->isSynced($has_all_users, $has_all_subscriptions);

$tpl->assign(compact(
	'order',
	'payments',
	'items',
	'payer_infos',
	'found_user',
	'mapped_user',
	'f',
	'type',
	'ha',
	'has_all_subscriptions',
	'has_all_users',
	'is_synced'
));

$tpl->display(PLUGIN_ROOT . '/templates/order.tpl');
