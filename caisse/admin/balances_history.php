<?php

namespace Paheko;

use Paheko\Users\Users;
use Paheko\Plugin\Caisse\Tabs;
use Paheko\Plugin\Caisse\Entities\Method;

require __DIR__ . '/_inc.php';

$id_user = intval($_GET['id_user'] ?? 0);
$id_tab = intval($_GET['id_tab'] ?? 0);
$type = intval($_GET['type'] ?? 0);
$is_debt = false;
$user_name = null;

if ($id_user) {
	$user_name = Users::getName($id_user);

	if (!$user_name) {
		throw new UserException('Membre introuvable');
	}
}

if ($type === Method::TYPE_DEBT) {
	$section_title = 'Ardoises en cours';
	$title = 'Historique des ardoises et remboursements';
	$is_debt = true;
}
else {
	$title = 'Historique des porte-monnaie';
	$section_title = 'Porte-monnaie des membres';
}

if ($user_name) {
	if ($type === Method::TYPE_CREDIT) {
		$title = 'Historique du porte-monnaie';
	}

	$title = $user_name . ' : ' . $title;
}

$list = Tabs::listBalancesHistory($type, $id_user);
$list->loadFromQueryString();
$tpl->assign(compact('list', 'title', 'is_debt', 'type', 'id_user', 'section_title', 'id_tab'));

$tpl->display(PLUGIN_ROOT . '/templates/balances_history.tpl');
