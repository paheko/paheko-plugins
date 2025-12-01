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

if ($tab) {
	$url = Utils::plugin_url(['file' => 'tab.php', 'query' => 'id=' . $tab->id]);
	$csrf_key = null;
}

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
	$form->runIf('rename_name', function () use ($tab) {
		$tab->rename($_POST['rename_name'], intval($_POST['rename_id'] ?? 0) ?: null);
	}, $csrf_key, $url);
}

if ($tab && !$current_pos_session->closed) {
	$form->runIf(!empty($_POST['add_item']) && is_array($_POST['add_item']), function () use ($tab) {
		$tab->addItem((int)key($_POST['add_item']), current($_POST['add_item']));
	}, $csrf_key, $url);

	$form->runIf('add_debt', function () use ($tab) {
		$tab->addUserDebt();
	}, $csrf_key, $url);

	$form->runIf('delete_item', function () use ($tab) {
		$tab->removeItem((int)$_POST['delete_item']);
	}, $csrf_key, $url);

	$form->runIf('change_qty', function () use ($tab) {
		$tab->updateItemQty((int)key($_POST['change_qty']), (int)current($_POST['change_qty']));
	}, $csrf_key, $url);

	$form->runIf('change_weight', function () use ($tab) {
		$tab->updateItemWeight((int)key($_POST['change_weight']), current($_POST['change_weight']));
	}, $csrf_key, $url);

	$form->runIf('change_price', function () use ($tab) {
		$tab->updateItemPrice((int)key($_POST['change_price']), current($_POST['change_price']));
	}, $csrf_key, $url);

	$form->runIf('pay', function () use ($tab, $plugin) {
		$tab->pay(intval($_POST['method_id'] ?? 0),
			get_amount($_POST['amount'] ?? 0),
			$_POST['reference'] ?? null,
			$plugin->getConfig('auto_close_tabs') ?? false,
			$plugin->getConfig('force_tab_name') ?? false
		);
	}, $csrf_key, $url);

	$form->runIf('delete_payment', function () use ($tab) {
		$tab->removePayment((int) $_POST['delete_payment']);
	}, $csrf_key, $url);

	$form->runIf('rename_item', function () use ($tab) {
		$tab->renameItem((int) key($_POST['rename_item']), current($_POST['rename_item']));
	}, $csrf_key, $url);

	$form->runIf('close', function () use ($tab, $plugin) {
		$tab->close($plugin->getConfig('force_tab_name') ?? false);
	}, $csrf_key, $url);

	$form->runIf('reopen', function () use ($tab) {
		$tab->reopen();
	}, $csrf_key, $url);

	$form->runIf('delete', function () use ($tab) {
		$id = $tab->session;
		$tab->delete();
		Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'session=' . $id]));
	}, $csrf_key);
}

$tabs = Tabs::listForSession($current_pos_session->id);

$tpl->assign('pos_session', $current_pos_session);
$tpl->assign('tab_id', $tab ? $tab->id : null);

$tpl->assign('products_categories', Products::listBuyableByCategory());
$tpl->assign('has_weight', Products::checkUserWeightIsRequired());
$tpl->assign('tabs', $tabs);
$has_credit_methods = Methods::hasCreditMethods();
$tpl->assign('has_credit_methods', $has_credit_methods);

if ($tab) {
	$tpl->assign('current_tab', $tab);
	$tpl->assign('items', $tab->listItems());
	$tpl->assign('existing_payments', $tab->listPayments());
	$tpl->assign('remainder', $tab->getRemainder());
	$tpl->assign('payment_options', $tab->listPaymentOptions());
	$tpl->assign('debt', $tab->getUserDebt());
	$tpl->assign('missing_user', $tab->isUserIdMissing());
}

$tpl->assign('selected_cat', qg('cat'));
$tpl->assign('debt_balance', Tabs::getGlobalDebtBalance());

$tpl->assign('title', 'Caisse ouverte le ' . Utils::date_fr($current_pos_session->opened));
$tpl->display(PLUGIN_ROOT . '/templates/tab.tpl');
