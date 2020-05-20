<?php

namespace Garradin;

use Garradin\Plugin\Caisse\Tab;
use Garradin\Plugin\Caisse\Product;

use function Garradin\Plugin\Caisse\{reload,get_amount};

require __DIR__ . '/_inc.php';

if ($tab_id = $session->get('pos_tab_id')) {
	$tab = new Tab($tab_id);
}

if (!empty($_POST['add_item'])) {
	$tab->addItem((int)key($_POST['add_item']));
	reload();
}
elseif (qg('delete_item')) {
	$tab->removeItem((int)qg('delete_item'));
	reload();
}
elseif (!empty($_POST['change_qty'])) {
	$tab->updateItemQty((int)key($_POST['change_qty']), (int)current($_POST['change_qty']));
	reload();
}
elseif (!empty($_POST['change_price'])) {
	$tab->updateItemPrice((int)key($_POST['change_price']), (int)get_amount(current($_POST['change_price'])));
	reload();
}
elseif (!empty($_POST['pay'])) {
	$tab->pay((int)$_POST['method_id'], get_amount(f('amount')), $_POST['reference']);
	reload();
}
elseif (qg('delete_payment')) {
	$tab->removePayment((int) qg('delete_payment'));
	reload();
}
elseif (null !== qg('new')) {
	$id = Tab::open($pos_session->id);
	$session->set('pos_tab_id', $id);
	reload();
}
elseif (!empty($_GET['change'])) {
	$session->set('pos_tab_id', (int) $_GET['change']);
	reload();
}
elseif (!empty($_POST['rename'])) {
	$tab->rename($_POST['rename']);
	reload();
}
elseif (!empty($_POST['close'])) {
	$tab->close();
	$remainder = $tab->getRemainder();
	$session->set('pos_tab_id', null);
	reload();
}
elseif (!empty($_POST['delete'])) {
	$tab->delete();
	$session->set('pos_tab_id', null);
	reload();
}

$tabs = Tab::listForSession($pos_session->id);

if ($tab_id && !isset($tabs[$tab_id])) {
	$tab_id = null;
	$session->set('pos_tab_id', null);
}

$tpl->assign('pos_session', $pos_session);
$tpl->assign('tab_id', $tab_id);

$tpl->assign('products_categories', Product::listByCategory());
$tpl->assign('tabs', $tabs);

if ($tab_id) {
	$tpl->assign('current_tab', $tabs[$tab_id]);
	$tpl->assign('items', $tab->listItems());
	$tpl->assign('existing_payments', $tab->listPayments());
	$tpl->assign('remainder', $tab->getRemainder());
	$tpl->assign('payment_options', $tab->listPaymentOptions());
}

$tpl->register_modifier('show_methods', function ($m) {
	$m = explode(',', $m);
	if (in_array(3, $m)) {
		return '<i>🚲</i>';
	}
});

$tpl->display(PLUGIN_ROOT . '/templates/tab.tpl');
