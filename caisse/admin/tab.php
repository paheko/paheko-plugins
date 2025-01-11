<?php

namespace Paheko;

use Paheko\Plugin\Caisse\{Sessions, Methods, Tabs, Products};

use function Paheko\Plugin\Caisse\{reload,get_amount};

require __DIR__ . '/_inc.php';

$tab = null;

if (null !== qg('id')) {
	$tab = Tabs::get(qg('id'));

	if (!$tab) {
		throw new UserException('La note sélectionnée n\'existe pas ou plus.');
	}

	$current_pos_session = Sessions::get($tab->session);
	$tab->session($current_pos_session);
}
elseif (qg('session')) {
	$current_pos_session = Sessions::get((int)qg('session'));
}
else {
	$current_pos_session = Sessions::getCurrent();
}

if (!$current_pos_session) {
	throw new UserException('Aucune session de caisse en cours et aucune note sélectionnée');
}

$form->runIf(qg('code') !== null, function () use ($current_pos_session, &$tab) {
	$tab = $current_pos_session->getFirstOpenTab();
	$tab ??= $current_pos_session->openTab();

	$tab->addItemByCode(qg('code'));
	Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'id=' . $tab->id]));
});

if (!empty($_GET['payoff_amount']) && !empty($_GET['payoff_account'])) {
	if (!$current_pos_session) {
		throw new UserException('Aucune session de caisse n\'est ouverte.');
	}

	if (!empty($_GET['payoff_user'])) {
		$tab = $current_pos_session->findOpenTabByUser((int) $_GET['payoff_user']);
	}

	if (!$tab) {
		$tab = $current_pos_session->openTab(intval($_GET['payoff_user']) ?: null);
	}

	$tab->addDebt($_GET['payoff_account'], (int) $_GET['payoff_amount']);

	Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'id=' . $tab->id()]));
}
elseif (null !== qg('new')) {
	$tab = $current_pos_session->openTab();
	Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'id=' . $tab->id()]));
}
elseif ($tab) {
	if (!empty($_POST['add_item'])) {
		$id = key($_POST['add_item']);
		$price = current($_POST['add_item']);

		if (substr($id, 0, 4) === 'fee_') {
			$tab->addSubscriptionItem((int) substr($id, 4), (int) $price);
		}
		else {
			$tab->addItem((int) $id);
		}
		reload();
	}
	elseif (!empty($_POST['add_item']) && substr($_POST['add_item'], 0, 4) === 'fee_') {
		reload();
	}
	elseif (!empty($_GET['add_debt'])) {
		$tab->addUserDebt();
		Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'id=' . $tab->id]));
	}
	elseif (qg('delete_item')) {
		$tab->removeItem((int)qg('delete_item'));
		Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'id=' . $tab->id]));
	}
	elseif (!empty($_POST['change_qty'])) {
		$tab->updateItemQty((int)key($_POST['change_qty']), (int)current($_POST['change_qty']));
		reload();
	}
	elseif (!empty($_POST['change_weight'])) {
		$tab->updateItemWeight((int)key($_POST['change_weight']), current($_POST['change_weight']));
		reload();
	}
	elseif (!empty($_POST['change_price'])) {
		$tab->updateItemPrice((int)key($_POST['change_price']), current($_POST['change_price']));
		reload();
	}
	elseif (!empty($_POST['pay'])) {
		$tab->pay((int)$_POST['method_id'], get_amount(f('amount')), $_POST['reference'], $plugin->getConfig('auto_close_tabs') ?? false, $plugin->getConfig('debt_account'));
		reload();
	}
	elseif (qg('delete_payment')) {
		$tab->removePayment((int) qg('delete_payment'));
		Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'id=' . $tab->id]));
	}
	elseif (!empty($_POST['rename_name'])) {
		$tab->rename($_POST['rename_name'], (int) f('rename_id') ?: null);
		reload();
	}
	elseif (!empty($_POST['rename_item'])) {
		$tab->renameItem((int) key($_POST['rename_item']), current($_POST['rename_item']));
		reload();
	}
	elseif (!empty($_POST['close'])) {
		$tab->close();
		reload();
	}
	elseif (!empty($_POST['reopen'])) {
		$tab->reopen();
		reload();
	}
	elseif (!empty($_POST['delete'])) {
		$tab->delete();
		Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'session=' . $current_pos_session->id()]));
	}
}

$tabs = Tabs::listForSession($current_pos_session->id);

$tpl->assign('pos_session', $current_pos_session);
$tpl->assign('tab_id', $tab ? $tab->id : null);

$tpl->assign('products_categories', Products::listBuyableByCategory($plugin->getConfig('show_services'), $tab->user_id ?? null));
$tpl->assign('has_weight', Products::checkUserWeightIsRequired());
$tpl->assign('tabs', $tabs);

if ($tab) {
	$tpl->assign('current_tab', $tab);
	$tpl->assign('items', $tab->listItems());
	$tpl->assign('existing_payments', $tab->listPayments());
	$tpl->assign('remainder', $tab->getRemainder());
	$tpl->assign('payment_options', $tab->listPaymentOptions());
	$tpl->assign('debt', $tab->getUserDebt());
}

$tpl->assign('selected_cat', qg('cat'));
$tpl->assign('debt_total', Tabs::getUnpaidDebtAmount());

$tpl->assign('title', 'Caisse ouverte le ' . Utils::date_fr($current_pos_session->opened));
$tpl->display(PLUGIN_ROOT . '/templates/tab.tpl');
