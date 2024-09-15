<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Users\Users;
use Paheko\Plugin\Caisse\Tabs;

require __DIR__ . '/_inc.php';

$user_id = (int)qg('user') ?: null;
$title = "Historique des ardoises et remboursements";

if ($user_id) {
	$user_name = Users::getName($user_id);

	if (!$user_name) {
		throw new UserException('Membre introuvable');
	}

	$title = $user_name . ' — ' . $title;
}

$list = Tabs::listDebtsHistory($user_id);
$list->loadFromQueryString();
$tpl->assign(compact('list', 'title'));

$tpl->display(PLUGIN_ROOT . '/templates/debts_history.tpl');
