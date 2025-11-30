<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Locations;
use Paheko\Plugin\Caisse\Sessions;
use function Paheko\Plugin\Caisse\get_amount;

require __DIR__ . '/_inc.php';

$csrf_key = 'pos_open_session';
$id_location = intval($_POST['id_location'] ?? 0) ?: null;

$form->runIf('open', function () use ($session, $id_location) {
	if (!isset($_POST['balances']) || !is_array($_POST['balances'])) {
		$_POST['balances'] = [];
	}

	$name = $_POST['user_name'] ?? $session->getUser()->name();
	$s = Sessions::open($name, $_POST['balances'], $id_location);
	Utils::redirectParent(Utils::plugin_url(['file' => 'tab.php', 'query' => 'session=' . $s->id()]));
}, $csrf_key);

$locations = Locations::listAssoc();

if (count($locations) === 1) {
	$id_location = key($locations);
}

$tpl->assign(compact('locations', 'id_location'));

if ($id_location || !count($locations)) {
	$balances = Sessions::listOpeningBalances($id_location);
	$tpl->assign(compact('balances', 'csrf_key'));
}

$tpl->assign('user_name', $session->getUser()->name());
$tpl->assign('current_pos_session', Sessions::getCurrentId());

$tpl->display(PLUGIN_ROOT . '/templates/session_open.tpl');
