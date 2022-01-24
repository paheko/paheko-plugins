<?php

namespace Garradin;

use Garradin\Plugin\Caisse\Session;
use function Garradin\Plugin\Caisse\get_amount;

require __DIR__ . '/_inc.php';

$pos_session = null;
$csrf_key = 'pos_open_session';

if (null !== qg('id')) {
	$pos_session = new Session((int)qg('id'));
}
elseif ($current_pos_session = Session::getCurrentId()) {
	$pos_session = new Session($current_pos_session);
}

$form->runIf('open', function () use ($session) {
	if (trim(f('amount')) === '') {
		throw new UserException('Le solde de la caisse ne peut être laissé vide.');
	}

	Session::open($session->getUser()->id, get_amount(f('amount')));
}, $csrf_key, Utils::plugin_url(['file' => 'tab.php']));

$tpl->assign(compact('csrf_key', 'pos_session'));

$tpl->assign('current_pos_session', Session::getCurrentId());

if ($pos_session) {
	echo $pos_session->export((bool) qg('details'), qg('pdf') ? 2 : 0);
}
else {
	$tpl->display(PLUGIN_ROOT . '/templates/session_open.tpl');
}
