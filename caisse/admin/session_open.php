<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Locations;
use Paheko\Plugin\Caisse\Sessions;
use function Paheko\Plugin\Caisse\get_amount;

require __DIR__ . '/_inc.php';

$csrf_key = 'pos_open_session';
$balances = Sessions::listOpeningBalances();

$form->runIf('open', function () use ($session, $balances) {
	$amounts = $_POST['balance'];

	foreach ($balances as $balance) {
		if (trim($amounts[$balance->id] ?? '') === '') {
			throw new UserException(sprintf('Le solde "%s" ne peut être laissé vide.', $balance->name));
		}
	}

	$location = intval($_POST['id_location'] ?? 0) ?: null;
	$name = $_POST['user_name'] ?? $session->getUser()->name();
	$s = Sessions::open($name, $amounts, $location);
	Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'session=' . $s->id()]));
}, $csrf_key);

$locations = Locations::listAssoc();

$tpl->assign(compact('balances', 'csrf_key', 'locations'));

$tpl->assign('user_name', $session->getUser()->name());
$tpl->assign('current_pos_session', Sessions::getCurrentId());

$tpl->display(PLUGIN_ROOT . '/templates/session_open.tpl');
