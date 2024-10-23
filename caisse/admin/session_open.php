<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Locations;
use Paheko\Plugin\Caisse\Sessions;
use function Paheko\Plugin\Caisse\get_amount;

require __DIR__ . '/_inc.php';

$pos_session = null;
$csrf_key = 'pos_open_session';

$form->runIf('open', function () use ($session) {
	if (trim(f('amount')) === '') {
		throw new UserException('Le solde de la caisse ne peut être laissé vide.');
	}

	$l = intval(f('id_location')) ?: null;
	$amount = get_amount(f('amount'));
	$name = f('user_name') ?: $session->getUser()->name();
	$s = Sessions::open($name, $amount, $l);
	Utils::redirect(Utils::plugin_url(['file' => 'tab.php', 'query' => 'session=' . $s->id()]));
}, $csrf_key);

$locations = Locations::listAssoc();

$tpl->assign(compact('csrf_key', 'pos_session', 'locations'));

$tpl->assign('user_name', $session->getUser()->name());
$tpl->assign('current_pos_session', Sessions::getCurrentId());

$tpl->display(PLUGIN_ROOT . '/templates/session_open.tpl');
