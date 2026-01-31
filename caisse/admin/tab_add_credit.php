<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Tabs;
use Paheko\Plugin\Caisse\Methods;

require __DIR__ . '/_inc.php';

$tab = Tabs::get(intval($_GET['id'] ?? 0));

if (!$tab || $tab->closed) {
	throw new UserException('La note sélectionnée n\'existe pas ou plus.');
}

if (!$tab->user_id) {
	throw new UserException('La note n\'est pas liée à un membre. Merci de sélectionner un membre pour pouvoir créditer son ardoise.');
}

$csrf_key = 'tab_add_credit';
$url = Utils::plugin_url(['file' => 'tab.php', 'query' => 'id=' . $tab->id]);
$list = Methods::listCreditMethodsAssoc();

if (!count($list)) {
	throw new UserException('Aucun moyen de paiement de type porte-monnaie.');
}

if (count($list) === 1) {
	$id_method = key($list);
}
else {
	$id_method = $_POST['id_method'] ?? '';
}

$form->runIf('save', function () use ($tab, $id_method) {
	$tab->addUserCredit($id_method, Utils::moneyToInteger($_POST['amount'] ?? ''));
}, $csrf_key, $url);

$title = 'Créditer';
$id_default_method = Methods::getDefaultMethodId();
$tpl->assign(compact('list', 'title', 'csrf_key', 'id_default_method'));

$tpl->display(PLUGIN_ROOT . '/templates/tab_add_credit.tpl');
